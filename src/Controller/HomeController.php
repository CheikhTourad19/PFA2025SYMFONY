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
    public function home(): Response
    {
        $user = $this->getUser();
        if ($user) {
            dump('Authenticated as:', $user->getUserIdentifier(), $user->getRoles());
        } else {
            dump('Not authenticated');
        }

        return $this->render('home/index.html.twig');
    }

    #[Route('/pharmacie/', name: 'app_home_pharmacie')]
    public function indexAdmin(): Response{
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }
        $nom=$this->getUser()->getNom();
        return $this->render('home/index.html.twig', ['nom'=>$nom]);
    }
}
