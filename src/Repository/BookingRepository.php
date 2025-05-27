<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function hasOverlappingBooking(User $worker, \DateTimeInterface $start, \DateTimeInterface $end, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.worker = :worker')
            ->andWhere('b.start < :end')
            ->andWhere('b.end > :start')
            ->setParameter('worker', $worker)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeId) {
            $qb->andWhere('b.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }


    //    /**
    //     * @return Booking[] Returns an array of Booking objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Booking
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
