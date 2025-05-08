<?php
// src/Service/MercurePublisherService.php

namespace App\Service;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisherService
{
    public function __construct(private HubInterface $hub)
    {
    }

    public function publishMessage(Message $message): void
    {
        $topic = 'chat/messages/' . $message->getReceiver()->getId();

        // Format the message for the frontend
        $data = json_encode([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
            'senderId' => $message->getSender()->getId(),
            'senderName' => $message->getSender()->getPrenom() . ' ' . $message->getSender()->getNom()
        ]);

        // Create and publish the update
        $update = new Update($topic, $data);
        $this->hub->publish($update);
    }
    // In MercurePublisherService.php
    public function publishUserStatus(User $user, bool $isOnline): void
    {
        $topic = 'user/status';

        $data = json_encode([
            'userId' => $user->getId(),
            'isOnline' => $isOnline,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        $update = new Update($topic, $data);
        $this->hub->publish($update);
    }
}