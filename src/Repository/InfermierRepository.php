<?php

namespace App\Repository;

use App\Entity\Infermier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Infermier>
 */
class InfermierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Infermier::class);
    }
//    public function findAll(): array
//    {
//        return $this->createQueryBuilder('i')
//            ->leftJoin('i.user', 'u')
//            ->addSelect('u')
//            ->orderBy('u.nom', 'ASC')
//            ->getQuery()
//            ->getResult();
//    }

    //    /**
    //     * @return Infermier[] Returns an array of Infermier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Infermier
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
