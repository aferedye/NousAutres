<?php

namespace App\Repository;

use App\Entity\Circle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Circle>
 */
class CircleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Circle::class);
    }

    /**
     * @return Circle[]
     */
    public function findPublicActives(int $limit = 12): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.visibility = :v')->setParameter('v', 'public')
            ->andWhere('c.status = :s')->setParameter('s', 'active')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
