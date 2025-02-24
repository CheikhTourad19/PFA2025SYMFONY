<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PharmaController extends AbstractController
{
    #[Route('/pharmacie',  name: 'app_home_pharmacie')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
           'user' => $user,
        ]);
    }
    #[Route('/pharmacie/stock',  name: 'app_pharma_stock')]
    public function Stockindex(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/pharmacie/ordonnance',  name: 'app_pharma_ordonnance')]
    public function Ordonnanceindex(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/pharmacie/profil',  name: 'app_pharma_profil')]
    public function profilindex(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
            'user' => $user,
        ]);
    }

}
