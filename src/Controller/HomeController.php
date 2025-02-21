<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(UserRepository $userRepository): Response
    {

        $user=$userRepository->findOneBy(['email'=>'gheith@test.com']);


        return $this->render('home/index.html.twig', [
            'nom' => $user->getNom(),
        ]);
    }
}
