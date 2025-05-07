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
    public function findLastMessagesWithUserInfo(User $currentUser): array
    {
        // Étape 1 : Trouver tous les utilisateurs avec qui l'utilisateur courant a échangé des messages
        $subquery = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT CASE WHEN m.sender = :currentUser THEN IDENTITY(m.receiver) ELSE IDENTITY(m.sender) END')
            ->from(Message::class, 'm')
            ->where('m.sender = :currentUser OR m.receiver = :currentUser');

        $userIds = array_column($subquery->getQuery()->setParameter('currentUser', $currentUser)->getScalarResult(), 1);

        if (empty($userIds)) {
            return [];
        }

        // Pour simplifier le problème, nous allons faire une requête séparée pour chaque utilisateur
        $results = [];
        foreach ($userIds as $otherUserId) {
            // Trouver l'autre utilisateur
            $otherUser = $this->getEntityManager()->getRepository(User::class)->find($otherUserId);
            if (!$otherUser) continue;

            // Trouver le dernier message échangé entre les deux utilisateurs
            $lastMessageQb = $this->createQueryBuilder('m')
                ->where('(m.sender = :currentUser AND m.receiver = :otherUser) OR (m.sender = :otherUser AND m.receiver = :currentUser)')
                ->setParameter('currentUser', $currentUser)
                ->setParameter('otherUser', $otherUser)
                ->orderBy('m.sentAt', 'DESC')
                ->setMaxResults(1);

            $lastMessage = $lastMessageQb->getQuery()->getOneOrNullResult();

            if ($lastMessage) {
                $results[] = [
                    'user' => [
                        'id' => $otherUser->getId(),
                        'nom' => $otherUser->getNom(),
                        'prenom' => $otherUser->getPrenom()
                    ],
                    'message' => [
                        'id' => $lastMessage->getId(),
                        'content' => $lastMessage->getContent(),
                        'sentAt' => $lastMessage->getSentAt()->format('Y-m-d H:i:s')
                    ]
                ];
            }

        }

    // Trier les résultats par date de message décroissante (les plus récents d'abord)
usort($results, function($a, $b) {
    return strtotime($b['message']['sentAt']) - strtotime($a['message']['sentAt']);
});

return $results;
}


public function findLastMessagesBetweenUsers(User $user): array
    {
        // Trouver tous les utilisateurs avec qui l'utilisateur courant a échangé des messages
        $qb1 = $this->createQueryBuilder('m1')
            ->select('DISTINCT CASE WHEN m1.sender = :user THEN m1.receiver ELSE m1.sender END AS userId')
            ->where('m1.sender = :user OR m1.receiver = :user')
            ->setParameter('user', $user);

        $userIds = array_column($qb1->getQuery()->getArrayResult(), 'userId');

        if (empty($userIds)) {
            return [];
        }

        // Pour chaque utilisateur, trouver le dernier message échangé
        $results = [];
        foreach ($userIds as $otherUserId) {
            $qb2 = $this->createQueryBuilder('m2')
                ->where('(m2.sender = :user AND m2.receiver = :otherUser) OR (m2.sender = :otherUser AND m2.receiver = :user)')
                ->setParameter('user', $user)
                ->setParameter('otherUser', $otherUserId)
                ->orderBy('m2.sentAt', 'DESC')
                ->setMaxResults(1);

            $lastMessage = $qb2->getQuery()->getOneOrNullResult();

            if ($lastMessage) {
                $results[] = $lastMessage;
            }
        }

        return $results;

    }


    /**
     * Trouve tous les messages entre deux utilisateurs
     */
    public function findMessagesBetweenUsers(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

}