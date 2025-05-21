<?php

namespace App\Controller;


use App\Entity\FirstTime;
use App\Entity\Ordonnance;
use App\Entity\OrdonnanceMedicament;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\MedecinRepository;
use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\RdvRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/medecin')]
final class MedecinController extends AbstractController
{
    #[Route('/', name: 'app_medecin_home')]
    public function indexMedecin(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user->getFirstTime()){
            $firsTime=new FirstTime();
            $firsTime->setUser($user);
            $firsTime->setIsFirstTime(false);
            $entityManager->persist($firsTime);
            $entityManager->flush();
            $this->addFlash('error','vous devez changer votre mot de passe');
            return $this->redirectToRoute('app_medecin_profil');

        }
        return $this->render('medecin/home.html.twig');
    }
    #[Route('/ordonnance', name: 'app_medecin_ordonnace', methods: ['GET'])]
    public function ordonnanceMedecin(MedicamentRepository $medicamentRepository,UserRepository $userRepository): Response
    {
        $medicaments=$medicamentRepository->findBy([], ['nom' => 'ASC']);
        $patients=$userRepository->findBy(['role'=>'patient']);
        return $this->render('medecin/ordonnance.html.twig',['medicaments'=>$medicaments,'patients'=>$patients]);
    }
    #[Route('/ordonnance', name: 'app_medecin_sauvegarder_ordonnance', methods: ['POST'])]
    public function save(EntityManagerInterface $entityManager,UserRepository $userRepository,MedicamentRepository $medicamentRepository,Request $request): Response
    {
        $medecin=$this->getUser()->getMedecin();
        $patient=$userRepository->find($request->request->get('patient'));
        $date=$request->request->get('date');
        $medicaments = $request->request->all('medicaments');
        $quantites = $request->request->all('quantites');
        $instructions = $request->request->all('instructions');
        $ordonnance=new Ordonnance();
        $ordonnance->setPatient($patient);
        $ordonnance->setMedecin($medecin);
        $ordonnance->setDateCreation(new \DateTime($date));
        for($i=0;$i<count($medicaments);$i++){
            $med=$medicamentRepository->find($medicaments[$i]);
            $ordmed=new OrdonnanceMedicament();
            $ordmed->setMedicament($med);
            $ordmed->setOrdonnance($ordonnance);
            $ordmed->setQuantite($quantites[$i]);
            $ordmed->setInstructions($instructions[$i]);
            $entityManager->persist($ordmed);
        }
        $entityManager->persist($ordonnance);
        $entityManager->flush();
        $this->addFlash('success','Ordonnance cree avec succes');
        return $this->redirectToRoute('app_medecin_ordonnance');
    }




    #[Route('/dossier-medical', name: 'app_medecin_dossier_medical')]
    public function dossierMedical(): Response
    {
        return $this->render('medecin/dossier_medical.html.twig');
    }



    #[Route('/rendez-vous', name: 'app_medecin_rendez_vous')]
    public function rendezVous(): Response
    {
        return $this->render('medecin/rendez_vous.html.twig');
    }


    #[Route('/profil', name: 'app_medecin_profil')]
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
            return $this->redirectToRoute('app_medecin_profil');
        }

        // Render both forms in the same template
        return $this->render('medecin/profil.html.twig', [
            'profileForm' => $profileForm->createView(),

        ]);
    }


    #[Route('/ordonnance', name: 'app_medecin_ordonnance')]
    public function ordonnance(): Response
    {
        return $this->render('medecin/ordonnance.html.twig');
    }

    #[Route('/calendrier', name: 'app_medecin_calendrier')]
    public function calendrier(RdvRepository $rdvRepository): Response
    {
        $rdv = $rdvRepository->findBy(['medecin'=>$this->getUser()]);
        return $this->render('medecin/calendrier.html.twig',['rdv'=>$rdv]);
    }
    #[Route('/messages', name: 'app_medecin_messages')]
    public function messages(
        MedecinRepository $medecinRepository
    ): Response
    {
        $user = $this->getUser();

        // Récupérer tous les médecins
        $medecins = $medecinRepository->findAll();

        // Préparer les données pour le JavaScript
        $medecinsData = [];
        foreach ($medecins as $medecin) {
            $medecinsData[] = [
                'id' => $medecin->getUser()->getId(),
                'nom' => $medecin->getUser()->getNom(),
                'prenom' => $medecin->getUser()->getPrenom(),
                'service' => $medecin->getService()
            ];
        }

        return $this->render('medecin/messages.html.twig', [
            'medecins' => $medecinsData,
            'userId' => $user->getId(),
            'mercure_public_url' => $this->getParameter('mercure_public_url')

        ]);
    }
    #[Route('/deconnexion', name: 'app_medecin_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException('This should be intercepted by the firewall.');
    }
}
