<?php

namespace App\Repository;

use App\Entity\CurrentRead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CurrentRead|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrentRead|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrentRead[]    findAll()
 * @method CurrentRead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrentReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrentRead::class);
    }

    // /**
    //  * @return CurrentRead[] Returns an array of CurrentRead objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CurrentRead
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
