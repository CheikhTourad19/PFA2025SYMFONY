<?php
namespace App\Service;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChatService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private MessageRepository $messageRepository
    ) {}
    public function getLastMessagesWithUserInfo(User $currentUser): array
    {
        $results = $this->messageRepository->findLastMessagesWithUserInfo($currentUser);

        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'message' => [
                    'id' => $result['message']['id'],
                    'content' => $result['message']['content'],
                    'sentAt' => $result['message']['sentAt'],
                ],
                'with' => [
                    'id' => $result['user']['id'],
                    'nom' => $result['user']['nom'],
                    'prenom' => $result['user']['prenom']
                ]
            ];
        }

        return $formattedResults;

    }



    public function getLastMessages(User $currentUser): array
    {
        return $this->messageRepository->findLastMessagesBetweenUsers($currentUser);
    }

    public function getMessagesBetweenUsers(User $user1, User $user2): array
    {
        return $this->messageRepository->findMessagesBetweenUsers($user1, $user2);
    }

    public function sendMessage(User $sender, User $receiver, string $content): Message
    {
        $message = new Message();
        $message->setSender($sender)
            ->setReceiver($receiver)
            ->setContent($content);


        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }
}