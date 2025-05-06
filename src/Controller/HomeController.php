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

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Security\Core\Security;

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
    #[Route('/3D', name: 'app_three')]
    public function three(MailerInterface $mailer): Response
    {

//        $email = (new Email())
//            ->from('donotreplyemedical@gmail.com')
//            ->to('elghothvadel@gmail.com')
//            ->subject('Test Email')
//            ->text('This is a test email sent from Symfony!');
//
//        $mailer->send($email);

        return $this->render('home/3D.html.twig');
    }
    #[Route('/api/users')]
    public  function sendapi(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json($users);
    }
    #[Route('/chat11', name: 'chat')]
    public function chat(): Response
    {
        return $this->render('home/chat.html.twig');
    }
    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        HubInterface $hub,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $recipientId = $data['recipient'];
        $content = $data['message'];

        /** @var User $sender */
        $sender = $this->getUser();
        $recipient = $em->getRepository(User::class)->find($recipientId);

        if (!$recipient) {
            return $this->json(['error' => 'Recipient not found'], 404);
        }

        // CHANGE HERE: Use public topic format (no leading slash)
        $update = new Update(
            "user{$recipientId}", // No leading slash, matches EventSource subscription
            json_encode([
                'sender' => $sender->getId(),
                'message' => $content,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ])
        );

        // For debugging
        try {
            $hub->publish($update);
            return $this->json([
                'success' => true,
                'sent_to' => $recipientId,
                'topic' => "user{$recipientId}" // Show the exact topic used
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
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
