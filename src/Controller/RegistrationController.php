<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Patient;
use App\Enum\Role;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $patient = new Patient();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe avant la persistance
            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );
            $patient->setUser($user);
            $patient->setCin(1);
            $entityManager->persist($user);
            $entityManager->persist($patient);
            $entityManager->flush();
            $this->addFlash('success', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            // Collect all form errors
            $errors = $form->getErrors(true, true);

            // Add each error to the flash system
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            // Optionally, redirect back to the form
            return $this->redirectToRoute('app_register');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}