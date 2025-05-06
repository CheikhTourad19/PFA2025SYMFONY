<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medecin>
 */
class MedecinRepository extends ServiceEntityRepository
{

    //    /**
    //     * @return Medecin[] Returns an array of Medecin objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Medecin
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medecin::class);
    }

    public function searchMedecins(string $searchTerm = null): array
    {
        $query = $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->addSelect('u')
            ->orderBy('u.nom', 'ASC');

        if ($searchTerm) {
            $query->andWhere('
                u.nom LIKE :searchTerm OR 
                u.prenom LIKE :searchTerm OR 
                u.email LIKE :searchTerm OR 
                m.service LIKE :searchTerm
            ')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $query->getQuery()->getResult();
    }

    public function findByService(string $service): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.service = :service')
            ->setParameter('service', $service)
            ->getQuery()
            ->getResult();
    }

    public function findDistinctServices(): array
    {
        return $this->createQueryBuilder('m')
            ->select('DISTINCT m.service')
            ->orderBy('m.service', 'ASC')
            ->getQuery()
            ->getScalarResult();
    }

    public function findWithFilters(?string $searchTerm, ?string $service): array
    {
        $query = $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->addSelect('u');

        $this->applySearchFilter($query, $searchTerm);
        $this->applyServiceFilter($query, $service);

        return $query
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function applySearchFilter(QueryBuilder $query, ?string $searchTerm): void
    {
        if ($searchTerm) {
            $query->andWhere('
                u.nom LIKE :searchTerm OR 
                u.prenom LIKE :searchTerm OR 
                u.email LIKE :searchTerm OR 
                m.service LIKE :searchTerm
            ')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    }

    private function applyServiceFilter(QueryBuilder $query, ?string $service): void
    {
        if ($service) {
            $query->andWhere('m.service = :service')
                ->setParameter('service', $service);
        }
    }
    public function searchByNomOuPrenom(string $term): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.user', 'u')  // Add this join statement
            ->where('u.nom LIKE :term')
            ->orWhere('u.prenom LIKE :term')
            ->setParameter('term', '%' . $term . '%')  // Changed to use % on both sides
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
