<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{
    #[Route('/medecin/', name: 'app_home_medecin')]
    public function indexMedecin(): Response{
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }
        $nom=$this->getUser()->getNom();
        return $this->render('medecin/index.html.twig', ['nom'=>$nom]);
    }
}
