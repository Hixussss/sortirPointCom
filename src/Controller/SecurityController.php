<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur pour la gestion de l'authentification des utilisateurs (connexion et déconnexion).
 */
class SecurityController extends AbstractController
{
    /**
     * Permet à l'utilisateur de se connecter.
     *
     * @Route(path: "/login", name="app_login")
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire de gestion des erreurs d'authentification.
     *
     * @return Response Retourne une réponse HTTP avec le formulaire de connexion.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Permet de se déconnecter.
     *
     * @Route(path: "/logout", name="app_logout")
     *
     * @return void Cette méthode ne doit rien retourner, elle sera interceptée par la clé de déconnexion du pare-feu.
     * @throws \LogicException Exception levée pour indiquer que cette méthode sera interceptée.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
