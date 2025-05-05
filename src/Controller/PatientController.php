<?php

namespace App\Controller;

use App\Entity\FirstTime;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Rdv;
use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
use App\Repository\FirstTimeRepository;
use App\Repository\MedecinRepository;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\OrdonnanceRepository;
use App\Repository\PharmacieRepository;
use App\Repository\RdvRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{

    #[Route('/patient/ordonnance/pdf/{id}', name: 'ordonnance_pdf')]
    public function exportOrdonnancePdf(int $id,OrdonnanceRepository $ordonnanceRepository,OrdonnanceMedicamentRepository $ordonnanceMedicamentRepository): Response
    {
        $ordonnance=$ordonnanceMedicamentRepository->findBy(['ordonnance'=>$id]);
        $total=0;
        foreach ($ordonnance as $m) {
            $total=$total+($m->getMedicament()->getPrix()*$m->getQuantite() );
        }
        $signaturePath = $this->getParameter('kernel.project_dir') . '/public/assets/signature.png';

        $signatureBase64 = base64_encode(file_get_contents($signaturePath));

        // Render HTML
        $html = $this->renderView('pdf/ordonnance.html.twig', [
            'ordonnance'=>$ordonnance,
            'total'=>$total,
            'signatureBase64' => $signatureBase64,
        ]);

        // Setup Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();


        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ordonnance.pdf"',
            ]
        );
    }

    #[Route('/patient/annuler/rdv/{id}', name: 'app_patient_annuler_rdv')]
    public function annuler_rdv(int $id,RdvRepository $rdvRepository,EntityManagerInterface $em)
    {
        $rdv=$rdvRepository->find($id);
        $rdv->setStatut('annule');
        $em->persist($rdv);
        $em->flush();
        $this->addFlash('success','RDV Annule.');
        return $this->redirectToRoute('app_patient_rdv');
    }
    #[Route('/patient/calendrier{id}', name: 'app_patient_calendrier')]
    public function calendrier(RdvRepository $rdvRepository,int $id): Response
    {
        // Récupérer les rendez-vous du patient connecté
        $rdv = $rdvRepository->createQueryBuilder('r')
            ->where('r.medecin = :medecin')
            ->andWhere('r.statut = :confirme OR r.statut = :attente')
            ->setParameter('medecin', $id)
            ->setParameter('confirme', 'confirme')
            ->setParameter('attente', 'attente')
            ->getQuery()
            ->getResult();

        return $this->render('patient/calendrier.html.twig', [
            'rdv' => $rdv
        ]);
    }

    #[Route('/patient/prendre-rdv/{id}/{date}', name: 'app_patient_prendre_rdv')]
    public function prendreRdv(int $id, string $date, EntityManagerInterface $em,MailerInterface $mailer): Response
    {
        // Récupérer le médecin
        $medecin = $em->getRepository(Medecin::class)->find($id);

        if (!$medecin) {
            $this->addFlash('error', 'Médecin introuvable.');
            return $this->redirectToRoute('app_patient_rdv');
        }

        try {
            $dateRdv = new \DateTime($date);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Format de date invalide.');
            return $this->redirectToRoute('app_patient_rdv');
        }

        // Vérifier si le patient est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté en tant que patient pour prendre un rendez-vous.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si le médecin est disponible à cette date/heure


        // Créer un nouveau rendez-vous
        $rdv = new Rdv();

        $rdv->setMedecin($medecin);
        $rdv->setPatient($user);
        $rdv->setDate($dateRdv);
        $rdv->setStatut('attente'); // Statut initial: en attente

        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', 'Votre rendez-vous a été créé avec succès et est en attente de confirmation.');
        $email = (new Email())
            ->from('noreply@e-medical.com')  // Adresse d'envoi
            ->to($medecin->getUser()->getEmail())      // Votre adresse admin
            ->subject('Nouveau Rendez-Vous - ')
            ->html($this->renderView(
                'emails/rendez-vous.html.twig',
                ['patient' => $user,'date'=>$dateRdv,'medecin'=>$medecin]

            ));


        try {

            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');


        }
        return $this->redirectToRoute('app_patient_rdv');
    }
    #[Route('/patient/prendrdv', name: 'app_patient_prendrdv')]
    public function prendrdv(MedecinRepository $medecinRepository)
    {
        $medecins=$medecinRepository->findAll();

        return $this->render('patient/prendrdv.html.twig', ['medecins' => $medecins]);
    }
    #[Route('/patient/supprimer/rdv/{id}', name: 'app_patient_supprimer_rdv')]
    public function supprimer_rdv(int $id,RdvRepository $rdvRepository,EntityManagerInterface $em)
    {
        $rdv=$rdvRepository->find($id);
        $em->remove($rdv);
        $em->flush();
        $this->addFlash('success','RDV Supprimer.');
        return $this->redirectToRoute('app_patient_rdv');
    }

    #[Route('/patient/rdv', name: 'app_patient_rdv')]
    public function rdv(RdvRepository $rdvRepository)
    {
        $rdv = $rdvRepository->findBy(['patient'=>$this->getUser()]);

        return $this->render('patient/rdv.html.twig', ['rdv' => $rdv]);
    }


    #[Route('/patient', name: 'app_home_patient')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $showTutorial = false;
        $firstTime = $em->getRepository(FirstTime::class)->find($user->getId());
        if ($firstTime === null) {

            $showTutorial = true;


            $firstTime = new FirstTime();
            $firstTime->setUser($user);
            $firstTime->setIsFirstTime(false);

            $em->persist($firstTime);
            $em->flush();
        }
        return $this->render('patient/index.html.twig', ['showTutorial' => $showTutorial]);
    }

    #[Route('/patient/pahrmacie', name: 'app_patient_pharmacie')]
    public function pharmacie(PharmacieRepository $pharmacieRepository,UserRepository $userRepository): Response

    {

        $users=$userRepository->findBy(['role'=>Role::PHARMACIE]);

        $pharmacies=$pharmacieRepository->findBy(['user' => $users]);
        return $this->render('patient/pharmacie.html.twig', ['pharmacies' => $pharmacies]);
    }
    #[Route('/patient/ordonnances', name: 'app_ordonnances_patient')]
    public function ordonnances(OrdonnanceRepository $ordonnanceRepository): Response
    {
        $mesordonnance = $ordonnanceRepository->findBy(['patient' => $this->getUser()]);
        $uniqueDoctors = array_unique(array_map(function($ordonnance) {
            return $ordonnance->getMedecin()->getId();
        }, $mesordonnance));
        usort($mesordonnance, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });
        return $this->render('patient/ordonnances.html.twig', ['ordonnances' => $mesordonnance,'uniqueDoctorsCount' => count($uniqueDoctors)]);
    }
    #[Route('/patient/ordonnance/{id}', name: 'app_ordonnance_patient')]
    public function ordonnance(OrdonnanceMedicamentRepository $myRepository, int $id): Response
    {
        $medicament =$myRepository->findBy(['ordonnance' => $id]);
        if (empty($medicament)) {
            $this->addFlash('error','Cette ordonnance n\'existe pas.');
            return $this->redirectToRoute('app_ordonnances_patient');
        }
        else
        {
            $total=0;
            foreach ($medicament as $m) {
                $total=$total+($m->getMedicament()->getPrix()*$m->getQuantite() );
            }
            return $this->render('patient/ordonnance.html.twig', ['medicaments' => $medicament,'total'=>$total]);
        }

    }




    #[Route('/patient/contact',  name: 'app_patient_contact')]
    public function contact(): Response
    {

        return $this->render('patient/contact.html.twig', [
        ]);
    }

    #[Route('/patient/profil', name: 'app_patient_profil')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,

    ): Response {
        // Fetch the current user
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Create the profile form with the current user data
        $profileForm = $this->createForm(UserType::class, $user);
        $profileForm->handleRequest($request);

        // Handle profile form submission
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            // Handle password update if provided
            $currentPassword = $profileForm->get('currentPassword')->getData();
            $newPassword = $profileForm->get('newPassword')->getData();

            if ($currentPassword && $newPassword) {
                if (strlen($newPassword) < 8) {
                    $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
                }
                // Verify the current password
                else if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect et information non enregistrés');
                } else {
                    // Hash and set the new password
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                }
            }

            // Save the updated user to the database
            $entityManager->persist($user); // Ensure the user is persisted
            $entityManager->flush();

            // Add a flash message for success
            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_patient_profil');
        }

        // Render both forms in the same template
        return $this->render('patient/profil.html.twig', [
            'profileForm' => $profileForm->createView()
        ]);
    }
}