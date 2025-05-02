<?php

namespace App\Controller;
use App\Entity\Adresse;
use App\Entity\Infermier;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Pharmacie;
use App\Entity\User;
use App\Enum\Role;
use App\Form\InfermierType;
use App\Form\MedecinType;
use App\Form\PatientType;
use App\Form\PharmacieType;
use App\Form\UserType;
use App\Repository\InfermierRepository;
use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use App\Repository\PharmacieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormTypeInterface;
use App\Service\TwilioService;
#[Route('/admin', name: 'app_admin')]
final class AdminController extends AbstractController
{



    #[Route('/home', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    #[Route('/users', name: '_users')]
    public function users(Request $request , EntityManagerInterface $em, MedecinRepository $medecinRepo,
                          PharmacieRepository $pharmacieRepo,
                          InfermierRepository $infermierRepo,
    PatientRepository $patientRepos,UserRepository $userRepository):Response
    {
//        $medecins = $medecinRepo->findAll();
//        $pharmacies = $pharmacieRepo->findAll();
//        $infermiers = $infermierRepo->findAll();
//        $patients = $patientRepos->findAll();
        $medecins=$userRepository->findBy(['role'=>Role::MEDECIN]);
        $pharmacies=$userRepository->findBy(['role'=>Role::PHARMACIE]);
        $infermiers=$userRepository->findBy(['role'=>Role::INFERMIER]);
        $patients=$userRepository->findBy(['role'=>Role::PATIENT]);
        return $this->render('admin/users.html.twig', [
            'medecins' => $medecins,
            'pharmacies' => $pharmacies,
            'infermiers' => $infermiers,
            'patients' => $patients,
        ]);
    }
    #[Route('/user/{type}/{id}', name: '_user_info')]
    public function userInfo(string $type, int $id, EntityManagerInterface $em): Response
    {
        $entity = null;
        $templateVariable = null;

        switch (strtolower($type)) {
            case 'medecin':
                $entity = $em->getRepository(Medecin::class)->find($id);
                $templateVariable = 'medecin';
                break;

            case 'pharmacie':
                $entity = $em->getRepository(Pharmacie::class)->find($id);
                $templateVariable = 'pharmacie';
                break;

            case 'infermier':
                $entity = $em->getRepository(Infermier::class)->find($id);
                $templateVariable = 'infermier';
                break;

            case 'patient':
                $entity = $em->getRepository(Patient::class)->find($id);
                $templateVariable = 'patient';
                break;
            default:
                throw $this->createNotFoundException('Type utilisateur invalide');
        }

        if (!$entity || !method_exists($entity, 'getUser')) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('admin/user_info.html.twig', [
            $templateVariable => $entity,
            'userType' => $type,
            'entity' => $entity
        ]);
    }
    #[Route('/reports', name: '_reports')]
    public function reports():Response
    {
        return $this->render('admin/reports.html.twig');
    }
    #[Route('/settings', name: '_settings')]
    public function settings():Response
    {
        return $this->render('admin/settings.html.twig');
    }
    #[Route('/chatbot', name: '_chatbot')]
    public function chatbot():Response
    {
        return $this->render('admin/chatbot.html.twig');
    }
    #[Route('/supports', name: '_supports')]
    public function supports():Response
    {
        return $this->render('admin/contactUS.html.twig');
    }
    #[Route('/profile', name: '_profile')]
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
            return $this->redirectToRoute('app_admin_home');
        }

        // Render both forms in the same template
        return $this->render('admin/profile.html.twig', [
            'profileForm' => $profileForm->createView()
        ]);
    }
    #[Route('/users/add/{type}', name: '_user_add')]
    public function addUser(string $type,
                            Request $request,
                            UserPasswordHasherInterface $passwordHasher,
                            EntityManagerInterface $em): Response
    {

        switch ($type) {
            case 'medecin':
                $form = $this->createForm(MedecinType::class, new Medecin());
                break;
            case 'pharmacie':
                $form = $this->createForm(PharmacieType::class, new Pharmacie());
                break;
            case 'infermier':
                $form = $this->createForm(InfermierType::class, new Infermier());
                break;
            case 'patient':
                $form = $this->createForm(PatientType::class, new Patient());
                break;
            default:
                throw $this->createNotFoundException('Invalid user type');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();
            $user = $entity->getUser();
            // Set role based on type
            switch ($type) {
                case 'medecin': $user->setRole(Role::MEDECIN);
                break;
                case 'pharmacie': $user->setRole(Role::PHARMACIE);
                break;
                case 'infermier': $user->setRole(Role::INFERMIER);
                break;
                case 'patient': $user->setRole(Role::PATIENT);
                break;
            }

            // Hash password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('user')->get('newPassword')->getData()
                )
            );
            $em->persist($user);
            switch ($type){
                case 'medecin':$medecin=new Medecin();$medecin->setUser($user);
                $medecin->setService($entity->getService());
                $em->persist($medecin);
                break;
                case 'pharmacie':$pharmacie=new Pharmacie();$pharmacie->setUser($user);
                $adresse=new Adresse();
                $adresse->setRue($form->get('adresse')->get('rue')->getData());
                $adresse->setVille($form->get('adresse')->get('ville')->getData());
                $adresse->setQuartier($form->get('adresse')->get('quartier')->getData());
                $pharmacie->setAdresse($adresse);
                $pharmacie->setCin($entity->getCin());
                $em->persist($pharmacie);
                break;
                case 'infermier':$infermier=new Infermier();$infermier->setUser($user);
                $infermier->setService($entity->getService());
                $em->persist($infermier);
                break;
                case 'patient':$patient=new Patient();$patient->setUser($user);$patient->setCin($entity->getCin());
                $em->persist($patient);
                break;
            }


            $em->flush();

            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }
    #[Route('/users/{type}/delete/{id}', name: '_user_delete')]
    public function deleteUser(int $id,UserRepository $userRepository,EntityManagerInterface $em):Response{
        try {
            $user=$em->getRepository(User::class)->find($id);

            if (!$user) {
                throw $this->createNotFoundException('Utilisateur non trouvé');
            }

            $em->remove($user);
            $em->flush();
            $this->addFlash('success', $user->getNom() . ' supprimé(e) avec succès.');
            return $this->redirectToRoute('app_admin_users');

        } catch (\Exception $e) {
            $this->addFlash('error', 'error:' . $e->getMessage());
            return $this->redirectToRoute('app_admin_users');
        }
    }
    #[Route('/users/{type}/edit/{id}', name: '_user_edit')]
    public function editUser(string $type, int $id, Request $request, EntityManagerInterface $em): Response
    {
        // Retrieve the appropriate entity based on the user type
        $entityMap = [
            'medecin' => Medecin::class,
            'pharmacie' => Pharmacie::class,
            'infermier' => Infermier::class,
            'patient' => Patient::class,
        ];
        if (!array_key_exists($type, $entityMap)) {
            throw $this->createNotFoundException('Type utilisateur invalide: ' . $type);
        }
        $entityClass = $entityMap[$type];

        $entity = $em->getRepository($entityClass)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Entité introuvable pour le type et ID spécifiés.');
        }

        $user = $entity->getUser();

        $formMap = [
            'medecin' => MedecinType::class,
            'pharmacie' => PharmacieType::class,
            'infermier' => InfermierType::class,
            'patient' => PatientType::class,
        ];
        $formClass = $formMap[$type];
        $form = $this->createForm($formClass, $entity);
        $form->handleRequest($request);
            if( $form->isSubmitted() && $form->isValid()){
                try {
                    switch ($type) {
                        case 'medecin':
                            $entity->setService($form->get('service')->getData());
                            break;
                        case 'pharmacie':
                            $adresse = $entity->getAdresse();
                            if (!$adresse) {
                                throw new \InvalidArgumentException('Adresse introuvable pour la pharmacie.');
                            }
                            $adresse->setRue($form->get('adresse')->get('rue')->getData());
                            $adresse->setVille($form->get('adresse')->get('ville')->getData());
                            $adresse->setQuartier($form->get('adresse')->get('quartier')->getData());
                            $entity->setAdresse($adresse);
                            $entity->setCin($form->get('cin')->getData());
                            break;
                        case 'infermier':
                            $entity->setService($form->get('service')->getData());
                            break;
                        case 'patient':
                            $entity->setCin($form->get('cin')->getData());
                            break;
                    }
                    $em->persist($entity);
                    $em->flush();

                    $this->addFlash('success', ucfirst($type) . ' mis à jour avec succès.');
                    return $this->redirectToRoute('app_admin_users');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage());
                }
            }


        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'entityId' => $entity ? $entity->getId() : null,
        ]);
    }
    #[Route('/init/password/{id}', name: '_init_password')]
    public function initPassword(
        int $id,
        EntityManagerInterface $entityManager,
        TwilioService $twilioService,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Find the user by ID
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Initialize the password to 'changeme'
        $newPassword = 'changeme';
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $newPassword
            )
        );

        // Save the updated user

        $entityManager->flush();

        // Send SMS using Twilio
        $twilioService=new TwilioService();
        try {
            $twilioService->sendSms(
                $user->getNumero(),
                "Bonjour, votre nouveau mot de passe est: changeme"
            );

            $this->addFlash('success', "Mot de passe réinitialisé et message envoyé.");
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_users');
    }
}

