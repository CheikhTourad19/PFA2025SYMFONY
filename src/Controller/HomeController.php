<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('donotreplyemedical@gmail.com')
            ->to('elghothvadel@gmail.com')
            ->subject('Test Email')
            ->text('This is a test email sent from Symfony!');

        $mailer->send($email);

        return $this->render('home/index.html.twig');
    }


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
