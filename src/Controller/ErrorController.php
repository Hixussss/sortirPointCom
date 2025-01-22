<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la gestion des erreurs.
 * Affiche une page d'erreur générique lorsque les erreurs sont rencontrées dans l'application.
 */
class ErrorController extends AbstractController
{
    /**
     * Affiche une page d'erreur générique.
     *
     * @Route("/error", name="app_error")
     * @return Response La réponse contenant la vue d'erreur.
     */
    #[Route('/error', name: 'app_error')]
    public function index(): Response
    {
        return $this->render('error/error.html.twig', [
            'controller_name' => 'ErrorController',
        ]);
    }
}
