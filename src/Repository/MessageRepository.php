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

    /**
     * @return User[]
     */
    public function findMedecinsConversation(Medecin $medecin): array // Renamed method & parameter
    {
        return $this->createQueryBuilder('m')
            ->select('DISTINCT m2') // Changed alias to m2 (medecin)
            ->join('m.sender', 'm2') // Join with Medecin entity
            ->where('m.receiver = :medecin')
            ->orWhere('m.sender = :medecin')
            ->setParameter('medecin', $medecin)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function findConversationBetween(Medecin $m1, Medecin $m2): array // Changed parameters to Medecin
    {
        return $this->createQueryBuilder('m')
            ->where(
                '(m.sender = :m1 AND m.receiver = :m2) OR ' .
                '(m.sender = :m2 AND m.receiver = :m1)'
            )
            ->setParameters(['m1' => $m1, 'm2' => $m2])
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}