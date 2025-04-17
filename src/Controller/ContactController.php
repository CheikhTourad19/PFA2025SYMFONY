<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request,MailerInterface $mailer): Response
    {
        $route=$request->headers->get('referer');
        $formData = [
            'email' => $request->request->get('email'),
            'role' => $request->request->get('role'),
            'name' => $request->request->get('name'),
            'issue_type' => $request->request->get('issue-type'),
            'message' => $request->request->get('message'),
        ];
        $email = (new Email())
            ->from('noreply@votrepharmacie.com')  // Adresse d'envoi
            ->to('elghothvadel@gmail.com')      // Votre adresse admin
            ->subject('Nouveau message de contact - ' . $formData['issue_type'])
            ->html($this->renderView(
                'emails/contact_dev.html.twig',
                ['data' => $formData]
            ));


        try {
            $mailer->send($email);
            $this->addFlash('success', 'Votre message a bien été transmis à l\'équipe de développement et sera traité.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
        // my future logic l pageat lkoul

        }
        return $this->redirect($route);
    }
}
