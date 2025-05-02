<?php

namespace App\Controller;


use App\Entity\Ordonnance;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function home(MailerInterface $mailer): Response
    {

//        $email = (new Email())
//            ->from('donotreplyemedical@gmail.com')
//            ->to('elghothvadel@gmail.com')
//            ->subject('Test Email')
//            ->text('This is a test email sent from Symfony!');
//
//        $mailer->send($email);

        return $this->render('home/index.html.twig');
    }
    #[Route('/api/users')]
    public  function sendapi(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json($users);
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

    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(RouterInterface $router): Response
    {
        $urls = [];

        // Routes statiques
        $urls[] = [
            'loc' => $router->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];
        $urls[] = [
            'loc' => $router->generate('app_home_patient', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
        $urls[] = [
            'loc' => $router->generate('app_patient_pharmacie', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ];
        $urls[] = [
            'loc' => $router->generate('app_ordonnances_patient', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ];




        // Génération du XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $urlData) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($urlData['loc']));
            $url->addChild('lastmod', $urlData['lastmod']);
            $url->addChild('changefreq', $urlData['changefreq']);
            $url->addChild('priority', $urlData['priority']);
        }

        return new Response($xml->asXML(), 200, ['Content-Type' => 'application/xml']);
    }


}
