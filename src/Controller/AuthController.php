<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends AbstractController
{
    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si l'email et le mot de passe sont fournis
        if (!isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Missing email or password'], 400);
        }

        // Authentification via le fournisseur de sécurité (automatique)
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Générer le token JWT
        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
        ]);
    }
}
