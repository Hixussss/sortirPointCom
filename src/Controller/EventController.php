<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\CancellationEventFormType;
use App\Form\EventFormType;
use App\Entity\Location;
use App\Entity\City;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GeocodingService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\EventFilterService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contrôleur de gestion des événements.
 * Permet de lister, créer, modifier, annuler et gérer la participation aux événements.
 */
class EventController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator, StateRepository $stateRepository)
    {
        $this->translator = $translator;
        $this->stateRepository = $stateRepository;
    }
    /**
     * Calcule les coordonnées des tuiles pour un emplacement donné.
     *
     * @param float $latitude Latitude de l'emplacement.
     * @param float $longitude Longitude de l'emplacement.
     * @param int $zoom Niveau de zoom pour la tuile.
     * @return array Coordonnées de la tuile (x, y).
     */
    private function getTileCoordinates(float $latitude, float $longitude, int $zoom): array
    {
        $n = pow(2, $zoom);
        $xTile = floor(($longitude + 180) / 360 * $n);
        $yTile = floor((1 - log(tan(deg2rad($latitude)) + 1 / cos(deg2rad($latitude))) / M_PI) / 2 * $n);

        return ['x' => (int) $xTile, 'y' => (int) $yTile];
    }

    /**
     * Affiche la liste paginée des événements avec des options de filtrage.
     *
     * @Route("/events", name="app_event_list")
     * @param EventRepository $repo Repository des événements.
     * @param PaginatorInterface $paginator Service de pagination.
     * @param Request $request Requête HTTP.
     * @param EventFilterService $eventFilterService Service de filtrage des événements.
     * @param StateRepository $stateRepository Repository des états.
     * @return Response La vue de la liste des événements.
     */
    #[Route('/events', name: 'app_event_list')]
    public function list(
        EventRepository $repo,
        PaginatorInterface $paginator,
        Request $request,
        EventFilterService $eventFilterService,
        StateRepository $stateRepository
    ): Response {
        // Récupérer les états 'CREATION' et 'ARCHIVED'
        $creationState = $stateRepository->findOneBy(['label' => 'CREATION']);
        $archivedState = $stateRepository->findOneBy(['label' => 'ARCHIVED']);

        // Créer le QueryBuilder pour les événements
        $qb = $repo->createQueryBuilder('e')
            ->orderBy('e.startDate', 'DESC');

        // Retirer les évènements en création ou archivés
        $qb->select('e')
            ->where('e.state != :creationState')
            ->andWhere('e.state != :archivedState')
            ->setParameter('creationState', $creationState)
            ->setParameter('archivedState', $archivedState)
            ->getQuery();
        
        // Appliquer les filtres via le service
        $eventFilterService->applyFilters($qb, $request);
    
        // Paginer les résultats
        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            6,
            ['wrap-queries' => true] // Ajoute cette option
        );

        // Récupérer tous les états
        $states = $stateRepository->findPublicStates();

        // Ajouter les URL des cartes statiques pour chaque événement
        foreach ($pagination as $event) {
            if ($event->getLocation()) {
                $tile = $this->getTileCoordinates(
                    $event->getLocation()->getLatitude(),
                    $event->getLocation()->getLongitude(),
                    14
                );
                $event->staticMapUrl = "https://tile.openstreetmap.org/14/{$tile['x']}/{$tile['y']}.png";
            } else {
                $event->staticMapUrl = null;
            }
        }

        // Rendu de la vue
        return $this->render('event/list.html.twig', [
            'events' => $pagination,
            'states' => $states,
        ]);
    }
    

    /**
     * Affiche les détails d'un événement spécifique.
     *
     * @Route("/event/show/{id}", name="app_event_show")
     * @param int $id Identifiant de l'événement.
     * @param EventRepository $repo Repository des événements.
     * @return Response La vue de l'événement.
     */
    #[Route('/event/show/{id}', name: 'app_event_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EventRepository $repo): Response
    {
        // Retrieve event by his id.
        $event = $repo->find($id);

        if(!$event){
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Crée un nouvel événement.
     *
     * @Route("/event/new", name="app_event_new")
     * @param Request $request Requête HTTP.
     * @param StateRepository $stateRepository Repository des états.
     * @param UserRepository $userRepository Repository des utilisateurs.
     * @param LocationRepository $locationRepository Repository des lieux.
     * @param GeocodingService $geocodingService Service de géocodage.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @param ValidatorInterface $validator Validateur Symfony.
     * @return Response La vue de création de l'événement.
     */
    #[Route('/event/new', name: 'app_event_new')]
    public function new(
        Request $request,
        StateRepository $stateRepository,
        UserRepository $userRepository,
        LocationRepository $locationRepository,
        GeocodingService $geocodingService,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        MobileDetector $mobileDetector
    ): Response {
        $locations = $locationRepository->findAll();
        $creationState = $stateRepository->findOneBy(['label' => 'CREATION']);
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create an event.');
        }
        if ($mobileDetector->isMobile()) {
            throw $this->createAccessDeniedException('You are not allowed to create an event on a mobile device.');
        }
    
        $event = new Event();
        $event->setState($creationState);
        $event->setOrganizer($user);
        $event->setOrganizerSite($user->getSite());
    
        $form = $this->createForm(EventFormType::class, $event, [
            'locations' => $locations,
        ]);
        $form->handleRequest($request);

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->render('event/new.html.twig', [
                'eventForm' => $form->createView(),
                'errors' => $errors,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($form->get('newLocation')->getData()) {
                $newLocation = $form->get('newLocation')->getData();
                $location = new Location();
                $location->setName($newLocation->getName());
                $location->setStreet($newLocation->getStreet());
    
                // Utiliser le service de géocodage pour récupérer la ville
                $coordinates = [
                    'latitude' => $newLocation->getLatitude(),
                    'longitude' => $newLocation->getLongitude(),
                ];
                // On récupère les données de la ville
                $cityData = $geocodingService->getCityFromCoordinates($coordinates['latitude'], $coordinates['longitude']);
    
                if ($cityData) {
                    $city = new City();
                    if(isset($cityData['city'])){
                        $city->setName($cityData['address']['town']);
                        $city->setPostalCode($cityData['postalCode']);
                        $em->persist($city);
                        $location->setCity($city);
                    }
                    else {
                    $city->setName($cityData['town']);
                    $city->setPostalCode($cityData['postalCode']);
                    $em->persist($city);
                    $location->setCity($city);
                    }
                } 
                else {
                    $city = new City();
                    $city->setName("ERROR_CITY");
                    $city->setPostalCode("ERROR_POSTAL_CODE");
                    $em->persist($city);
                    $location->setCity($city);
                }
    
                $location->setLatitude($coordinates['latitude']);
                $location->setLongitude($coordinates['longitude']);
                $em->persist($location);
    
                $event->setLocation($location);
            }

            if($form->get('takePartIn')->getData()){
                $event->addParticipant($user);
            }

            $em->persist($event);
            $em->flush();
    
            $this->addFlash('success', $this->translator->trans('success.event_created', ['%event_name%' => $event->getName()]));
    
            return $this->redirectToRoute('app_event_list');
        }
    
        return $this->render('event/new.html.twig', [
            'eventForm' => $form->createView(),
        ]);
    }

    /**
     * Modifie un événement existant.
     *
     * @Route("/event/edit/{id}", name="app_event_edit")
     * @param int $id Identifiant de l'événement.
     * @param Request $request Requête HTTP.
     * @param EventRepository $repo Repository des événements.
     * @param LocationRepository $locationRepository Repository des lieux.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @param ValidatorInterface $validator Validateur Symfony.
     * @return Response La vue de modification de l'événement.
     */
    #[Route('/event/edit/{id}', name: 'app_event_edit', requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        EventRepository $repo,
        LocationRepository $locationRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $locations = $locationRepository->findAll();
        $event = $repo->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        if ($event->getState()->getLabel() !== 'CREATION') {
            $this->addFlash('error', $this->translator->trans('error.event_edit_forbidden'));
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $form = $this->createForm(EventFormType::class, $event, [
            'locations' => $locations,
        ]);
        $form->handleRequest($request);

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->render('event/new.html.twig', [
                'eventForm' => $form->createView(),
                'errors' => $errors,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('newLocation')->getData()){
                $newLocation = $form->get('newLocation')->getData();

                $location = new Location();
                $location->setName($newLocation->getName());
                $location->setStreet($newLocation->getStreet());

                $city = "Nantes";
                $newCity = new City();
                $newCity->setName($city);
                $newCity->setPostalCode('44000');
                $em->persist($newCity);

                // Ici on fait en sorte que si la ville existe dans la table ville on l'attribue sinon on la crée
                // on perist ici comme ça c'est crée avant de persister la location
                $location->setCity($newCity);
                $location->setLatitude($newLocation->getLatitude());
                $location->setLongitude($newLocation->getLongitude());
                $em->persist($location);

                $event->setLocation($location);
            }

            $em->flush();
            $this->addFlash('success', $this->translator->trans('success.event_updated', ['%event%' => $event->getName()]));

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'eventForm' => $form->createView(),
        ]);
    }

    /**
     * Supprime un événement.
     *
     * @Route("/event/delete/{id}", name="app_event_delete")
     * @param Event $event L'événement à supprimer.
     * @param Request $request Requête HTTP.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response Redirection vers la liste des événements.
     */
    #[Route('/event/delete/{id}', name: 'app_event_delete',requirements: ['id' => '\d+'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($event->getState()->getLabel() !== 'CREATION') {
            $this->addFlash('error', $this->translator->trans('error.event_delete_forbidden'));
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))){
            try {
                $em->remove($event);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('success.event_deleted', ['%event%' => $event->getName()]));
            }
            catch(\Exception $e){
                $this->addFlash('error', $this->translator->trans('error.event_delete_failed', ['%event%' => $event->getName()]));
            }
        }
        else{
            $this->addFlash('error', 'CSRF token error !');
        }
        return $this->redirectToRoute('app_event_list');
    }

    /**
     * Affiche les événements de l'utilisateur actuel.
     *
     * @Route("event/user-events", name="app_event_user_list")
     * @param EventRepository $eventRepository Repository des événements.
     * @param StateRepository $stateRepository Repository des états.
     * @return Response La vue des événements de l'utilisateur.
     */
    #[Route('event/user-events', name: 'app_event_user_list')]
    public function userEvents(EventRepository $eventRepository, StateRepository $stateRepository)
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access to your events.');
        }

        $creationState = $stateRepository->findOneBy(['label' => 'CREATION']);
        // Get all user events .
        $creationEvents = $eventRepository->findCreationEventsByUser($user->getId(), $creationState->getId());
        $publishedEvents = $eventRepository->findPublishedEventsByUser($user->getId(), $creationState->getId());

        return $this->render('event/user-events.html.twig', [
            'creationEvents' => $creationEvents,
            'publishedEvents' => $publishedEvents,
        ]);
    }

    /**
     * Annule un événement.
     *
     * @Route("/event/cancel/{id}", name="app_event_cancel")
     * @param int $id Identifiant de l'événement.
     * @param Request $request Requête HTTP.
     * @param EventRepository $repo Repository des événements.
     * @param StateRepository $stateRepository Repository des états.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response La vue de confirmation d'annulation de l'événement.
     */
    #[Route('/event/cancel/{id}', name: 'app_event_cancel', requirements: ['id' => '\d+'])]
    public function cancel(int $id, Request $request, EventRepository $repo,
                            StateRepository $stateRepository, EntityManagerInterface $em): Response
    {
        // Get the event to cancel.
        $event = $repo->find($id);

        // Create the cancellation form.
        $form = $this->createForm(CancellationEventFormType::class, $event, [
            'validation_groups' => ['cancellation'],
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $canceledState = $stateRepository->findOneBy(['label' => 'CANCELED']);
            $event->setState($canceledState);

            $em->persist($event);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('success.event_cancelled', ['%event%' => $event->getName()]));

            return $this->redirectToRoute('app_event_user_list');
        }

        return $this->render('event/cancel.html.twig', [
            'event' => $event,
            'cancelForm' => $form
        ]);
    }

    /**
     * Inscrit l'utilisateur à un événement.
     *
     * @Route("/event/join/{id}", name="app_event_join")
     * @param Event $event L'événement auquel s'inscrire.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response Redirection vers la vue de l'événement.
     */
    #[Route('/event/join/{id}', name: 'app_event_join', requirements: ['id' => '\d+'])]
    public function join(Event $event, EntityManagerInterface $em, StateRepository $stateRepository): Response
    {
        $user = $this->getUser();
        if($event->getParticipants()->count() >= $event->getMaxRegistrations()){
            $this->addFlash('error', $this->translator->trans('error.event_full', ['%event%' => $event->getName()]));
        }
        elseif ($event->getRegistrationEndDate() < new \DateTime()) {
            $this->addFlash('error', $this->translator->trans('error.registration_closed', ['%event%' => $event->getName()]));
        }
        elseif ($event->getState()->getLabel() != 'REGISTRATION OPEN') {
            $this->addFlash('error', $this->translator->trans('error.event_state', [
                '%event%' => $event->getName(),
                '%state%' => $event->getState()->getLabel()
            ]));
        }
        elseif (!$event->getParticipants()->contains($user)) {
            $event->addParticipant($user);
            $em->flush();
            if($event->getParticipants()->count() == $event->getMaxRegistrations()){
                $registrationClosedState = $stateRepository->findOneBy(['label' => 'REGISTRATION CLOSED']);
                $event->setState($registrationClosedState);
                $em->flush($event);
            }
            $this->addFlash('success', $this->translator->trans('success.event_joined', [
                '%event%' => $event->getName()
            ]));            
        } else {
            $this->addFlash('info', $this->translator->trans('info.already_participant', [
                '%event%' => $event->getName()
            ]));            
        }
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    /**
     * Désinscrit l'utilisateur de l'événement.
     *
     * @Route("/event/leave/{id}", name="app_event_leave")
     * @param Event $event L'événement dont se désinscrire.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response Redirection vers la vue de l'événement.
     */
    #[Route('/event/leave/{id}', name: 'app_event_leave', requirements: ['id' => '\d+'])]
    public function leave(Event $event, EntityManagerInterface $em, StateRepository $stateRepository): Response
    {
        $user = $this->getUser();

        if ($event->getParticipants()->contains($user)) {
            $event->removeParticipant($user);
            $em->flush();
            if($event->getParticipants()->count() < $event->getMaxRegistrations()){
                $registrationOpenState = $stateRepository->findOneBy(['label' => 'REGISTRATION OPEN']);
                $event->setState($registrationOpenState);
                $em->flush($event);
            }
            $this->addFlash('success', $this->translator->trans('success.event_left', [
                '%event%' => $event->getName()
            ]));            
        } else {
            $this->addFlash('info', $this->translator->trans('info.not_participant', [
                '%event%' => $event->getName()
            ]));            
        }
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    /**
     * Publie un événement.
     *
     * @Route("/event/publish/{id}", name="app_event_publish")
     * @param int $id Identifiant de l'événement.
     * @param EventRepository $eventRepository Repository des événements.
     * @param StateRepository $stateRepository Repository des états.
     * @param EntityManagerInterface $em Gestionnaire d'entités.
     * @return Response Redirection vers la vue de l'événement.
     */
    #[Route('/event/publish/{id}', name: 'app_event_publish', requirements: ['id' => '\d+'])]
    public function publish(
        int $id,
        EventRepository $eventRepository,
        StateRepository $stateRepository,
        EntityManagerInterface $em): Response
    {
        $event = $eventRepository->find($id);

        if ($event->getState()->getLabel() !== 'CREATION') {
            $this->addFlash('error', $this->translator->trans('error.already_published', [
            ]));            
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($event->getStartDate() < new \DateTime()) {
            $this->addFlash('error', 'La date de début est déjà passé, redéfinissez-là !');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $registrationOpenState = $stateRepository->findOneBy(['label' => 'REGISTRATION OPEN']);
        $event->setState($registrationOpenState);
        $em->flush($event);

        $this->addFlash('success', $this->translator->trans('success.event_published', [
            '%event_name%' => $event->getName()
        ]));        
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    /**
     * Récupère les événements à venir et les formate pour l'affichage dans un calendrier.
     *
     * @Route("/api/calendar/events", name="api_calendar_events", methods={"GET"})
     *
     * @param EventRepository $eventRepository Le dépôt des événements.
     * @return JsonResponse La réponse JSON contenant les événements formatés.
     */
    #[Route('/api/calendar/events', name: 'api_calendar_events', methods: ['GET'])]
    public function getCalendarEvents(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findUpcomingEvents();
    
        $calendarEvents = [];
        foreach ($events as $event) {
            $startDate = $event->getStartDate();
            $duration = $event->getDuration(); // Supposons que cette durée est en minutes
    
            // Calcul de la date de fin
            $endDate = (clone $startDate)->modify("+{$duration} minutes");
    
            $calendarEvents[] = [
                'title' => $event->getName(),
                'start' => $startDate->format('Y-m-d\TH:i:s'),
                'end' => $endDate->format('Y-m-d\TH:i:s'),
                'url' => $this->generateUrl('app_event_show', ['id' => $event->getId()]),
            ];
        }
    
        return new JsonResponse($calendarEvents);
    }

    /**
     * Affiche la page du calendrier utilisateur.
     *
     * @Route("/calendar", name="app_event_user_calendar")
     *
     * @return Response La réponse HTTP contenant le rendu de la page du calendrier.
     */
    #[Route('/calendar', name: 'app_event_user_calendar')]
    public function calendar(): Response
    {
        return $this->render('event/calendar.html.twig');
    }

    /**
     * Récupère les détails d'un événement spécifique.
     *
     * @Route("/api/event-details/{id}", name="api_event_details", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param int $id L'identifiant de l'événement.
     * @param EventRepository $eventRepository Le dépôt des événements.
     * @return JsonResponse La réponse JSON contenant les détails de l'événement ou un message d'erreur si l'événement n'est pas trouvé.
     */
    #[Route('/api/event-details/{id}', name: 'api_event_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getEventDetails(int $id, EventRepository $eventRepository): JsonResponse
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $event->getId(),
            'title' => $event->getName(),
            'description' => substr($event->getDescription(), 0, 100), // Extrait limité
            'link' => $this->generateUrl('app_event_show', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * Route API pour récupérer les événements.
     */
    #[Route('/api/events', name: 'api_events', methods: ['POST'])]
    public function getEvents(
        EventRepository $eventRepository
    ): JsonResponse {
        // Authentification via le fournisseur de sécurité (automatique)
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->json(['error' => 'Invalid token or unauthorized access'], 401);
        }

        // Récupérer les événements depuis le repository
        $events = $eventRepository->findAll();

        // Retourner les événements avec des informations utilisateur
        return $this->json([
            'user' => $user->getUsername(), // Facultatif : renvoyer des détails sur l'utilisateur
            'events' => $events,
        ]);
    }

}
