<?php

namespace App\MessageHandler;

use App\Message\EventStateUpdateMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;	
use App\Entity\Event;
use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class EventStateUpdateMessageHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(EventStateUpdateMessage $message)
    {
        $now = new \DateTime();

        $eventRepository = $this->entityManager->getRepository(Event::class);
        $stateRepository = $this->entityManager->getRepository(State::class);
        $events = $eventRepository->findAll();

        foreach ($events as $event) {
            $startDate = $event->getStartDate();
            $duration = $event->getDuration();
            $endDate = (clone $startDate)->modify("+{$duration} minutes");
            $archivedDate = (clone $endDate)->modify("+1 month");

            if ($now > $startDate && $now < $endDate) { 
                if($event->getState()->getLabel() != 'ONGOING') {
                    $event->setState($stateRepository->findOneBy(['label' => 'ONGOING']));
                    $state = $stateRepository->findOneBy(['label' => 'ONGOING']);
                    $event->setState($state);
                    dump("Modification de l'état de l'évènement {$event->getName()} en ONGOING");
                }
            } elseif ($now < $startDate && $now > $event->getRegistrationEndDate()) {
                if($event->getState()->getLabel() != 'REGISTRATION CLOSED') {
                    $event->setState($stateRepository->findOneBy(['label' => 'REGISTRATION CLOSED']));
                    $state = $stateRepository->findOneBy(['label' => 'REGISTRATION CLOSED']);
                    $event->setState($state);
                    dump("Modification de l'état de l'évènement {$event->getName()} en REGISTRATION CLOSED");
                }
            }
            if($archivedDate < $now) {
                if($event->getState()->getLabel() != 'ARCHIVED') {
                    $event->setState($stateRepository->findOneBy(['label' => 'ARCHIVED']));
                    $state = $stateRepository->findOneBy(['label' => 'ARCHIVED']);
                    $event->setState($state);
                    dump("Modification de l'état de l'évènement {$event->getName()} en ARCHIVED");
                }
            } else if ($now > $endDate){
                if($event->getState()->getLabel() != 'FINISHED') {
                    $event->setState($stateRepository->findOneBy(['label' => 'FINISHED']));
                    $state = $stateRepository->findOneBy(['label' => 'FINISHED']);
                    $event->setState($state);
                    dump("Modification de l'état de l'évènement {$event->getName()} en FINISHED");
                }
            }


            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();
    }
}