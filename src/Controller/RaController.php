<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RaController extends AbstractController{
    #[Route('/ra', name: 'app_patient_ra')]
    public function index(): Response
    {
        return $this->render('ra/index.html.twig', [
            'controller_name' => 'RaController',
        ]);
    }
}
