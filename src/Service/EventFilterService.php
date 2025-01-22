<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class EventFilterService
{
    public function applyFilters(QueryBuilder $queryBuilder, Request $request): void
    {
        if ($search = $request->get('search')) {
            $queryBuilder->andWhere('e.name LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    
        if ($state = $request->get('state')) {
            $queryBuilder->andWhere('e.state = :state')
                ->setParameter('state', $state);
        }
        if ($minParticipants = $request->get('minParticipants')) {
            $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
                ->select('COUNT(p_min.id)')
                ->from('App\Entity\User', 'p_min')
                ->innerJoin('p_min.events', 'event_min')
                ->where('event_min.id = e.id');
    
            $queryBuilder->andWhere('(' . $subQuery->getDQL() . ') >= :minParticipants')
                ->setParameter('minParticipants', $minParticipants);
        }
    
        if ($maxParticipants = $request->get('maxParticipants')) {
            $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
                ->select('COUNT(p_max.id)')
                ->from('App\Entity\User', 'p_max')
                ->innerJoin('p_max.events', 'event_max')
                ->where('event_max.id = e.id');
    
            $queryBuilder->andWhere('(' . $subQuery->getDQL() . ') <= :maxParticipants')
                ->setParameter('maxParticipants', $maxParticipants);
        }
    
        if ($startDate = $request->get('startDate')) {
            $queryBuilder->andWhere('e.startDate >= :startDate')
                ->setParameter('startDate', $startDate);
        }
    
        if ($endDate = $request->get('endDate')) {
            $queryBuilder->andWhere('e.startDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }
    
        if ($city = $request->get('city')) {
            $queryBuilder->join('e.location', 'loc')
                ->join('loc.city', 'city')
                ->andWhere('city.name LIKE :city')
                ->setParameter('city', '%' . $city . '%');
        }
    
        if ($postalCode = $request->get('postalCode')) {
            $queryBuilder->join('e.location', 'loc')
                ->join('loc.city', 'city')
                ->andWhere('city.postalCode = :postalCode')
                ->setParameter('postalCode', $postalCode);
        }
    
        if ($organizer = $request->get('organizer')) {
            $queryBuilder->join('e.organizer', 'org')
                ->andWhere('org.username LIKE :organizer')
                ->setParameter('organizer', '%' . $organizer . '%');
        }
    
        if ($duration = $request->get('duration')) {
            $queryBuilder->andWhere('e.duration = :duration')
                ->setParameter('duration', $duration);
        }
    }
    


    
}
