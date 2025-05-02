<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function getAverageRatingForMovie(int $movieId): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.value) as average')
            ->andWhere('r.movie = :movieId')
            ->setParameter('movieId', $movieId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function findByMovieAndUser(int $movieId, int $userId): ?Rating
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.movie = :movieId')
            ->andWhere('r.user = :userId')
            ->setParameter('movieId', $movieId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 