<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour gérer le changement de langue de l'application.
 */
class LanguageController extends AbstractController
{
    /**
     * Change la langue de l'application et redirige l'utilisateur vers la page précédente ou la page d'accueil.
     *
     * @Route("/change-language/{locale}", name="app_change_language")
     *
     * @param string $locale La nouvelle langue choisie par l'utilisateur (par exemple : 'fr', 'en').
     * @param Request $request L'objet de la requête HTTP.
     * @return Response Une redirection vers la page précédente ou la page d'accueil.
     */
    #[Route('/change-language/{locale}', name: 'app_change_language')]
    public function changeLanguage(string $locale, Request $request): Response
    {
        // Stocker la langue dans la session
        $request->getSession()->set('_locale', $locale);

        // Retourner à la page précédente ou à l'accueil
        $referer = $request->headers->get('referer', $this->generateUrl('home'));
        return $this->redirect($referer);
    }
}
