<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin')]
final class AdminController extends AbstractController
{
    #[Route('/home', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    #[Route('/users', name: '_users')]
    public function users():Response
    {
        return $this->render('admin/users.html.twig');
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

}
