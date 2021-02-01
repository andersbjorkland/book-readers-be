<?php

namespace App\Repository;

use App\Entity\Flair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Flair|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flair|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flair[]    findAll()
 * @method Flair[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlairRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flair::class);
    }

    // /**
    //  * @return Flair[] Returns an array of Flair objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Flair
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
