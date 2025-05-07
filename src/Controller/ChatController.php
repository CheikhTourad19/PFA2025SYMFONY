<?php
// src/Controller/Api/ChatController.php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chat')]
class ChatController extends AbstractController
{

    #[Route('/conversations', name: 'api_chat_conversations', methods: ['GET'])]
    public function getConversations(ChatService $chatService

    ): JsonResponse
    { $currentUser = $this->getUser();
        $messages = $chatService->getLastMessagesWithUserInfo($currentUser);

        return $this->json(['conversations' => $messages]);

    }

    #[Route('/messages/{userId}', name: 'api_chat_messages', methods: ['GET'])]
    public function getMessages(int $userId, UserRepository $userRepository, MessageRepository $messageRepository): JsonResponse
    {
        $currentUser = $this->getUser();
        $otherUser = $userRepository->find($userId);

        if (!$otherUser) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $messages = $messageRepository->findMessagesBetweenUsers($currentUser, $otherUser);

        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'isSentByMe' => $message->getSender()->getId() === $currentUser->getId()
            ];
        }

        return $this->json([
            'messages' => $formattedMessages,
            'withUser' => [
                'id' => $otherUser->getId(),
                'nom' => $otherUser->getNom(),
                'prenom' => $otherUser->getPrenom()
            ]
        ]);
    }

    #[Route('/messages/{userId}', name: 'api_chat_send_message', methods: ['POST'])]
    public function sendMessage(
        int $userId,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $currentUser = $this->getUser();
        $receiver = $userRepository->find($userId);

        if (!$receiver) {
            return $this->json(['error' => 'Destinataire non trouvé'], 404);
        }

        $content = json_decode($request->getContent(), true)['content'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'Contenu du message manquant'], 400);
        }

        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setSentAt(new \DateTime());

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json([
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'isSentByMe' => true
            ]
        ]);
    }
}