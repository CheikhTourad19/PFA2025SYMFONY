<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin')]
final class AdminController extends AbstractController
{
    #[Route('/home', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    #[Route('/users', name: '_users')]
    public function users():Response
    {
        return $this->render('admin/users.html.twig');
    }
    #[Route('/reports', name: '_reports')]
    public function reports():Response
    {
        return $this->render('admin/reports.html.twig');
    }
    #[Route('/settings', name: '_settings')]
    public function settings():Response
    {
        return $this->render('admin/settings.html.twig');
    }
    #[Route('/chatbot', name: '_chatbot')]
    public function chatbot():Response
    {
        return $this->render('admin/chatbot.html.twig');
    }
}
