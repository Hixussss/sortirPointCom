<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationFormType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour gérer les opérations CRUD sur les localisations (locations) dans la section admin.
 */
class LocationController extends AbstractController
{
    /**
     * Affiche la liste de toutes les localisations.
     *
     * @Route("/admin/location", name="admin_locations")
     *
     * @param LocationRepository $locationRepository Le référentiel des localisations.
     * @return Response La réponse HTTP avec le rendu de la liste des localisations.
     */
    #[Route('/admin/location', name: 'admin_locations')]
    public function index(LocationRepository $locationRepository): Response
    {
        $locations = $locationRepository->findAll();

        return $this->render('admin/locations/index.html.twig', [
            'locations' => $locations,
        ]);
    }
    /**
     * Crée une nouvelle localisation.
     *
     * @Route("admin/location/new", name="admin_locations_new")
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entité.
     * @return Response La réponse HTTP avec le formulaire de création ou une redirection après soumission réussie.
     */
    #[Route('admin/location/new', name: 'admin_locations_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationFormType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($location);
            $em->flush();

            $this->addFlash('success', 'Location created successfully.');

            return $this->redirectToRoute('admin_locations');
        }

        return $this->render('admin/locations/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie une localisation existante.
     *
     * @Route("admin/location/edit/{id}", name="admin_locations_edit")
     *
     * @param Location $location La localisation à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entité.
     * @return Response La réponse HTTP avec le formulaire d'édition ou une redirection après soumission réussie.
     */
    #[Route('admin/location/edit/{id}', name: 'admin_locations_edit', requirements: ['id' => '\d+'])]
    public function edit(Location $location, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LocationFormType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Location updated successfully.');

            return $this->redirectToRoute('admin_locations');
        }

        return $this->render('admin/locations/edit.html.twig', [
            'form' => $form->createView(),
            'location' => $location,
        ]);
    }

    /**
     * Supprime une localisation existante.
     *
     * @Route("admin/location/delete/{id}", name="admin_locations_delete", methods={"POST"})
     *
     * @param Location $location La localisation à supprimer.
     * @param EntityManagerInterface $em Le gestionnaire d'entité.
     * @return Response La réponse HTTP après suppression réussie.
     */
    #[Route('admin/location/delete/{id}', name: 'admin_locations_delete',requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Location $location, EntityManagerInterface $em): Response
    {
        $em->remove($location);
        $em->flush();

        $this->addFlash('success', 'Location deleted successfully.');

        return $this->redirectToRoute('admin_locations');
    }
}
