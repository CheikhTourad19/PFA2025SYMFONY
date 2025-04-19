<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\OrdonnanceMedicament;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{
    #[Route('/patient', name: 'app_home_patient')]
    public function index(): Response
    {

        return $this->render('patient/index.html.twig', []);
    }
    #[Route('/patient/ordonnances', name: 'app_ordonnances_patient')]
    public function ordonnances(OrdonnanceRepository $ordonnanceRepository): Response
    {
        $mesordonnance = $ordonnanceRepository->findBy(['patient' => $this->getUser()]);
        return $this->render('patient/ordonnances.html.twig', ['ordonnances' => $mesordonnance]);
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
