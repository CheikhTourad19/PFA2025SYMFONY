<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

// In UserRepository.php
    public function findUsersForChat(?string $searchTerm = null): array
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.nom', 'ASC');

        if ($searchTerm) {
            $query->where('u.nom LIKE :term')
                ->orWhere('u.prenom LIKE :term')
                ->orWhere('u.email LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Trouve tous les médecins de l'application
     *
     * @return User[]
     */



}
