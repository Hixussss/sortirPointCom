<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findEventsByUser(string $userId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.organizer = :user_id')
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findCreationEventsByUser(int $userId, int $stateId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.organizer = :user_id')
            ->andWhere('e.state = :state_id')
            ->setParameter('user_id', $userId)
            ->setParameter('state_id', $stateId)
            ->getQuery()
            ->getResult();
    }

    public function findPublishedEventsByUser(string $userId, int $stateId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM event
            WHERE organizer_id = :user_id
            AND state_id <> :state_id
        ';

        $resultSet = $conn->executeQuery($sql, ['user_id' => $userId, 'state_id' => $stateId]);
        return $resultSet->fetchAllAssociative();
    }

    // EventRepository.php
    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.startDate >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
