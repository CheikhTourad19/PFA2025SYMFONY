<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatbotController extends AbstractController
{
    #[Route('/patient/chatbot', name: 'app_patient_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig', [
            'controller_name' => 'ChatbotController',
        ]);
    }
}