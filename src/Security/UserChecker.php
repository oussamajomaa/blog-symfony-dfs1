<?php
// src/Security/UserChecker.php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        // Vérifie si l'utilisateur passé à cette méthode est bien une instance de la classe "User"
        // Si ce n'est pas une instance de "User", on quitte simplement la méthode.
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur n'a pas confirmé son compte, on l'empêche de se connecter
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException("Votre compte n'a pas encore été confirmé.");
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // Ici, tu peux ajouter d'autres vérifications après l'authentification si nécessaire.
    }
}

