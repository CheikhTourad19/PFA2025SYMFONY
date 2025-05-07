<?php
// src/Repository/MessageRepository.php

namespace App\Repository;

use App\Entity\Medecin;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;



class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }
    public function findLastConversationsForUser(User $user): array
    {
        $messages = $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->leftJoin('m.receiver', 'r')
            ->addSelect('s', 'r')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.sentAt', 'DESC') // Changé de createdAt à sentAt
            ->getQuery()
            ->getResult();
        $conversations = [];
        foreach ($messages as $message) {
            $otherUser = $message->getSender() === $user
                ? $message->getReceiver()
                : $message->getSender();

            if (!$otherUser || isset($conversations[$otherUser->getId()])) continue;

            $conversations[$otherUser->getId()] = [
                'user' => $otherUser,
                'message' => $message->getContent(),
                
            ];
        }

        return array_values($conversations);
    }
    public function findMessagesBetweenUsers(User $currentUser, User $otherUser): array
{
    return $this->createQueryBuilder('m')
        ->leftJoin('m.sender', 's')
        ->leftJoin('m.receiver', 'r')
        ->addSelect('s', 'r')
        ->where('(m.sender = :currentUser AND m.receiver = :otherUser)')
        ->orWhere('(m.sender = :otherUser AND m.receiver = :currentUser)')
        ->setParameters([
            'currentUser' => $currentUser,
            'otherUser' => $otherUser
        ])
        ->orderBy('m.createdAt', 'ASC')
        ->getQuery()
        ->getResult();
}


}