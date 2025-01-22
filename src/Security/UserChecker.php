<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Vérifiez que l'utilisateur implémente bien UserInterface
        if (!$user instanceof \App\Entity\User) {
            return;
        }

        // Si l'utilisateur n'est pas actif, lancez une exception
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException("Votre compte est désactivé. Veuillez contacter l'administrateur.");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Cette méthode est appelée après l'authentification, nous n'en avons pas besoin ici
    }
}