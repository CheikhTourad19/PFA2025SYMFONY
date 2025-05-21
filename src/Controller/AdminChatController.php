<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminChatController extends AbstractController{
    #[Route('/admin/chat', name: 'app_admin_chatbot')]
    public function index(): Response
    {
        return $this->render('admin_chat/index.html.twig', [
            'controller_name' => 'AdminChatController',
        ]);
    }
}
