<?php
namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MedecinRepository;
use App\Repository\MessageRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/medecin')]
class ChatController extends AbstractController
{
    #[Route('/messages', name: 'app_medecin_messages')]
    public function messages(
        Request $request,
        MedecinRepository $medecinRepository,
        MessageRepository $messageRepository,
        Security $security
    ): Response {
        /** @var User $currentUser */
        $currentUser = $security->getUser();

        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $searchTerm = $request->query->get('q');

        // Si un terme de recherche est spécifié, filtrons les utilisateurs
        if ($searchTerm) {
            // Vous devrez implémenter cette méthode dans UserRepository
            $filteredUsers = $medecinRepository->searchByNomOuPrenom($searchTerm);
            return $this->render('medecin/messages.html.twig', [
                'users' => $filteredUsers,
                'searchTerm' => $searchTerm,
                'selectedUser' => null,
                'messages' => []
            ]);
        }

        $conversations = $messageRepository->findLastConversationsForUser($currentUser);
        $users = [];
        foreach ($conversations as $conv) {
            if (isset($conv['user'])) {
                $users[] = $conv['user']->getMedecin();
            }
        }

        return $this->render('medecin/messages.html.twig', [
            'users' => $users,
            'searchTerm' => $searchTerm ?? null,
            'selectedUser' => null, // Explicitement défini comme null
            'messages' => []
        ]);
    }

    #[Route('/messages/{id}', name: 'app_medecin_chat')]
    public function chat(
        User $user,
        MessageRepository $messageRepository,
        Security $security
    ): Response {
        /** @var User $currentUser */
        $currentUser = $security->getUser();

        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $conversations = $messageRepository->findLastConversationsForUser($currentUser);
        $users = [];
        foreach ($conversations as $conv) {
            if (isset($conv['user'])) {
                $users[] = $conv['user'];
            }
        }

        // Récupérer les messages
        $messages = $messageRepository->findMessagesBetweenUsers($currentUser, $user);

        return $this->render('medecin/messages.html.twig', [
            'users' => $users, // Utiliser la variable $users correctement traitée
            'selectedUser' => $user,
            'messages' => $messages,
            'searchTerm' => ''
        ]);
    }

    #[Route('/messages/{id}/send', name: 'app_medecin_send_message', methods: ['POST'])]
    public function sendMessage(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        /** @var User $currentUser */
        $currentUser = $security->getUser();
        $content = $request->request->get('message');

        if (empty($content)) {
            $this->addFlash('error', 'Le message ne peut pas être vide');
            return $this->redirectToRoute('app_medecin_chat', ['id' => $user->getId()]);
        }

        $message = new Message();
        $message->setContent($content)
            ->setSender($currentUser)
            ->setReceiver($user)
            ->setSentAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('app_medecin_chat', ['id' => $user->getId(),'searchTerm' => '']);
    }



}