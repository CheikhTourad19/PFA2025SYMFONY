<?php
namespace App\Controller;
use App\Entity\FirstTime;
use App\Entity\Ordonnance;
use App\Entity\OrdonnanceMedicament;
use App\Entity\Rdv;
use App\Entity\User;
use App\Form\UserType;
use App\Form\DossierMedicaleType;
use App\Repository\MedecinRepository;
use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\PatientRepository;
use App\Repository\RdvRepository;
use App\Repository\DossierMedicaleRepository;
use App\Entity\DossierMedicale;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
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
    public function dossierMedical(
        UserRepository $userRepository,
        DossierMedicaleRepository $dossierRepo
    ): Response {
        $patients = $userRepository->findBy(['role'=>'patient']);

        $dossiers = [];
        foreach ($patients as $patientUser) {
            $dossier = $dossierRepo->findOneBy(['patient' => $patientUser]);
            $dossiers[$patientUser->getId()] = $dossier;
        }

        return $this->render('medecin/dossier_medical.html.twig', [
            'patients' => $patients,
            'dossiers' => $dossiers,
        ]);
    }
    #[Route('/dossier-medical/show/{id}',name:'app_medecin_dossier_medical_show')]
    public function showDossierMedical(
        UserRepository $userRepository,
        DossierMedicaleRepository $dossierRepo,
        int $id,
    ):Response
    {
        $user = $userRepository->findOneBy(['id'=>$id]);
        $dossiers = $dossierRepo->findOneBy(['patient'=>$user]);
        if(!$dossiers){
            $this->addFlash('warning', 'Ce patient n "a pas de dossier medicale encore');
            return $this->redirectToRoute('app_medecin_dossier_medical');
        }


        return $this->render('medecin/dossier.html.twig', ['dossier' => $dossiers]);
    }
    #[Route('/dossier-medical/new/{id}', name: 'app_medecin_dossier_medical_create')]
    public function createdossierMedical(
        int $id,
        UserRepository $patientRepo,
        DossierMedicaleRepository $dossierRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $patient = $patientRepo->find($id);

        if (!$patient) {
            throw $this->createNotFoundException("Patient introuvable.");
        }

        // ❌ Empêcher la création si le dossier existe déjà
        $existing = $dossierRepo->findOneBy(['patient' => $patient]);

        if ($existing) {
            $this->addFlash('warning', 'Ce patient a déjà un dossier médical.');
            return $this->redirectToRoute('app_medecin_dossier_medical_edit', ['id' => $existing->getId()]);
        }

        $dossier = new DossierMedicale();
        $dossier->setPatient($patient);
        $dossier->setDateCreation(new \DateTimeImmutable());

        $form = $this->createForm(DossierMedicaleType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical créé.');
            return $this->redirectToRoute('app_medecin_dossier_medical_edit', ['id' => $dossier->getId()]);
        }

        return $this->render('medecin/dossier_form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/dossier_medical/edit/{id}', name: 'app_medecin_dossier_medical_edit')]
    public function editdossierMedical(
        DossierMedicale $dossier,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(DossierMedicaleType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Dossier mis à jour.');
        }

        return $this->render('medecin/dossier_form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
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
        $rdv = $rdvRepository->findBy(['medecin'=>$this->getUser()->getMedecin()]);
        return $this->render('medecin/calendrier.html.twig',['rdv'=>$rdv]);
    }

    /**
     * Update a rendez-vous (appointment) from the calendar
     */
    #[Route('/rdv/{id}/update', name: 'app_medecin_rdv_update', methods: ['POST'])]
    public function updateRdv(
        Request $request,
        EntityManagerInterface $entityManager,
        RdvRepository $rdvRepository,
        int $id,
        CsrfTokenManagerInterface $csrfTokenManager,
        MailerInterface $mailer
    ): JsonResponse {
        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('edit-event', $submittedToken))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
        }

        // Find the rendez-vous
        $rdv = $rdvRepository->find($id);

        // Check if the rendez-vous exists
        if (!$rdv) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Check if the connected user is the medecin of this rendez-vous
        if ($rdv->getMedecin()->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à modifier ce rendez-vous'], Response::HTTP_FORBIDDEN);
        }

        try {
            // Store old status for comparison
            $oldStatus = $rdv->getStatut();

            // Update rdv data
            $newDate = new \DateTime($request->request->get('event_date'));
            $newStatus = $request->request->get('event_status');
            $newNotes = $request->request->get('event_notes', '');

            // Validate status
            $validStatuses = ['attente', 'confirme', 'refuse', 'annule', 'termine'];
            if (!in_array($newStatus, $validStatuses)) {
                return new JsonResponse(['success' => false, 'message' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
            }

            // Update the rdv entity
            $rdv->setDate($newDate);
            $rdv->setStatut($newStatus);

            if (method_exists($rdv, 'setNotes')) {
                $rdv->setNotes($newNotes);
            }

            // Save changes
            $entityManager->flush();

            // Send email notification if status changed
            if ($oldStatus !== $newStatus) {
                $this->sendStatusChangeEmail($rdv, $mailer, $oldStatus, $newStatus);
            }

            // Return the updated event data for real-time calendar update
            return new JsonResponse([
                'success' => true,
                'message' => 'Rendez-vous mis à jour avec succès',
                'updatedEvent' => [
                    'id' => $rdv->getId(),
                    'patientName' => $rdv->getPatient()->getPrenom() . ' ' . $rdv->getPatient()->getNom(),
                    'date' => $rdv->getDate()->format('Y-m-d\TH:i:s'),
                    'status' => $newStatus,
                    'notes' => $newNotes
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function sendStatusChangeEmail(Rdv $rdv, MailerInterface $mailer, string $oldStatus, string $newStatus): void
    {
        $patient = $rdv->getPatient();
        $medecin = $rdv->getMedecin();

        // Map status to human-readable text
        $statusText = [
            'attente' => 'en attente',
            'confirme' => 'confirmé',
            'refuse' => 'refusé',
            'annule' => 'annulé',
            'termine' => 'terminé'
        ];

        $subject = 'Mise à jour de votre rendez-vous';

        try {
            $email = (new Email())
                ->from('noreply@e-medical.com')
                ->to($patient->getEmail())
                ->subject($subject)
                ->html($this->renderView(
                    'emails/status_change.html.twig',
                    [
                        'patient' => $patient,
                        'medecin' => $medecin,
                        'date' => $rdv->getDate(),
                        'oldStatus' => $statusText[$oldStatus] ?? $oldStatus,
                        'newStatus' => $statusText[$newStatus] ?? $newStatus
                    ]
                ));

            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Log the error or handle it as needed
            error_log('Failed to send email: ' . $e->getMessage());
        }
    }


    #[Route('/messages', name: 'app_medecin_messages')]
    public function messages(
        MedecinRepository $medecinRepository
    ): Response
    {
        $user = $this->getUser();
        $currentMedecin = $user->getMedecin();

        // Récupérer tous les médecins
        $allMedecins = $medecinRepository->findAll();

        // Filtrer pour exclure le médecin actuel
        $medecins = array_filter($allMedecins, function($medecin) use ($currentMedecin) {
            return $medecin->getId() !== $currentMedecin->getId();
        });

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