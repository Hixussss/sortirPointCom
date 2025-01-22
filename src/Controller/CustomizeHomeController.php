<?php

namespace App\Controller;

use App\Entity\SeasonIdea;
use App\Form\SeasonIdeaType;
use App\Repository\SeasonIdeaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/season-ideas')]
class CustomizeHomeController extends AbstractController
{
    #[Route('/', name: 'admin_season_ideas_index', methods: ['GET'])]
    public function index(SeasonIdeaRepository $repository): Response
    {
        $seasonIdeas = $repository->findAll();

        return $this->render('admin/season_idea/index.html.twig', [
            'seasonIdeas' => $seasonIdeas,
        ]);
    }

    #[Route('/new', name: 'admin_season_idea_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $seasonIdea = new SeasonIdea();
        $form = $this->createForm(SeasonIdeaType::class, $seasonIdea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($seasonIdea);
            $em->flush();

            $this->addFlash('success', 'Season idea added successfully.');
            return $this->redirectToRoute('admin_season_ideas_index');
        }

        return $this->render('admin/season_idea/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_season_idea_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SeasonIdea $seasonIdea, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SeasonIdeaType::class, $seasonIdea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Season idea updated successfully.');
            return $this->redirectToRoute('admin_season_ideas_index');
        }

        return $this->render('admin/season_idea/edit.html.twig', [
            'form' => $form->createView(),
            'seasonIdea' => $seasonIdea,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_season_idea_delete', methods: ['POST'])]
    public function delete(Request $request, SeasonIdea $seasonIdea, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$seasonIdea->getId(), $request->request->get('_token'))) {
            $em->remove($seasonIdea);
            $em->flush();

            $this->addFlash('success', 'Season idea deleted successfully.');
        }

        return $this->redirectToRoute('admin_season_ideas_index');
    }

}
