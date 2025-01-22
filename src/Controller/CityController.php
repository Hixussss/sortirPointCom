<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour gérer les villes dans l'administration.
 */
class CityController extends AbstractController
{
    /**
     * Affiche la liste de toutes les villes.
     *
     * @Route("/admin/city", name="admin_cities_index")
     *
     * @param CityRepository $cityRepository Le dépôt des villes.
     * @return Response La réponse HTTP contenant la liste des villes.
     */
    #[Route('/admin/city', name: 'admin_cities_index')]
    public function index(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();

        return $this->render('admin/cities/index.html.twig', [
            'cities' => $cities,
        ]);
    }

    /**
     * Crée une nouvelle ville.
     *
     * @Route("admin/city/new", name="admin_cities_new")
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités.
     * @return Response La réponse HTTP affichant le formulaire de création.
     */
    #[Route('admin/city/new', name: 'admin_cities_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($city);
            $em->flush();

            $this->addFlash('success', 'City created successfully.');

            return $this->redirectToRoute('admin_cities_index');
        }

        return $this->render('admin/cities/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie une ville existante.
     *
     * @Route("admin/city/edit/{id}", name="admin_cities_edit", requirements={"id"="\d+"})
     *
     * @param City $city L'entité de la ville à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités.
     * @return Response La réponse HTTP affichant le formulaire d'édition.
     */
    #[Route('admin/city/edit/{id}', name: 'admin_cities_edit', requirements: ['id' => '\d+'])]
    public function edit(City $city, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'City updated successfully.');

            return $this->redirectToRoute('admin_cities_index');
        }

        return $this->render('admin/cities/edit.html.twig', [
            'form' => $form->createView(),
            'city' => $city,
        ]);
    }

    /**
     * Supprime une ville.
     *
     * @Route("admin/city/delete/{id}", name="admin_cities_delete", requirements={"id"="\d+"}, methods={"POST"})
     *
     * @param City $city L'entité de la ville à supprimer.
     * @param EntityManagerInterface $em Le gestionnaire d'entités.
     * @return Response La réponse HTTP redirigeant vers la liste des villes.
     */
    #[Route('admin/city/delete/{id}', name: 'admin_cities_delete', requirements: ['id' => '\d+'] , methods: ['POST'])]
    public function delete(City $city, EntityManagerInterface $em): Response
    {
        $em->remove($city);
        $em->flush();

        $this->addFlash('success', 'City deleted successfully.');

        return $this->redirectToRoute('admin_cities_index');
    }
}
