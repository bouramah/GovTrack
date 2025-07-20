<?php

namespace App\Services;

class PasswordGeneratorService
{
    /**
     * Générer un mot de passe aléatoire sécurisé
     */
    public static function generateSecurePassword(int $length = 12): string
    {
        // Caractères disponibles
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        // S'assurer qu'on a au moins un caractère de chaque type
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Remplir le reste avec des caractères aléatoires
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mélanger le mot de passe
        return str_shuffle($password);
    }

    /**
     * Générer un mot de passe plus simple (pour les tests ou cas spéciaux)
     */
    public static function generateSimplePassword(int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * Générer un mot de passe mémorisable (avec des mots)
     */
    public static function generateMemorablePassword(): string
    {
        $words = [
            'chat', 'chien', 'maison', 'voiture', 'livre', 'table', 'porte', 'fenetre',
            'arbre', 'fleur', 'soleil', 'lune', 'etoile', 'ocean', 'montagne', 'riviere',
            'musique', 'danse', 'peinture', 'sculpture', 'theatre', 'cinema', 'restaurant',
            'cafe', 'boulangerie', 'pharmacie', 'hopital', 'ecole', 'universite', 'bibliotheque'
        ];

        $word1 = $words[array_rand($words)];
        $word2 = $words[array_rand($words)];
        $number = random_int(10, 99);
        $symbol = ['!', '@', '#', '$', '%', '^', '&', '*'][array_rand(['!', '@', '#', '$', '%', '^', '&', '*'])];

        return ucfirst($word1) . ucfirst($word2) . $number . $symbol;
    }

    /**
     * Vérifier la force d'un mot de passe
     */
    public static function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longueur
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if (strlen($password) >= 12) {
            $score += 1;
        }

        // Majuscules
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajoutez au moins une majuscule';
        }

        // Minuscules
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajoutez au moins une minuscule';
        }

        // Chiffres
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajoutez au moins un chiffre';
        }

        // Caractères spéciaux
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajoutez au moins un caractère spécial';
        }

        // Évaluer la force
        if ($score <= 2) {
            $strength = 'faible';
        } elseif ($score <= 4) {
            $strength = 'moyenne';
        } else {
            $strength = 'forte';
        }

        return [
            'score' => $score,
            'strength' => $strength,
            'feedback' => $feedback
        ];
    }
}
