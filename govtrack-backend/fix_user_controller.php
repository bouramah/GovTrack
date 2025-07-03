<?php
// Script de correction pour UserController.php

$file = 'app/Http/Controllers/Api/UserController.php';
$content = file_get_contents($file);

// Correction 1: Ajouter name dans les données de base
$content = str_replace(
    "'email' => \$user->email,",
    "'email' => \$user->email,
            'name' => \$user->name,",
    $content
);

// Correction 2: Corriger l'affectation actuelle pour retourner les noms directement
$content = str_replace(
    "'affectation_actuelle' => \$user->affectations()
                ->where('statut', true)
                ->with(['poste', 'entite.typeEntite'])
                ->first(),",
    "(\$affectationActuelle = \$user->affectations()
                ->where('statut', true)
                ->with(['poste', 'entite'])
                ->first()) ? [
                    'poste' => \$affectationActuelle->poste->nom,
                    'entite' => \$affectationActuelle->entite->nom,
                    'date_debut' => \$affectationActuelle->date_debut,
                ] : null,",
    $content
);

// Correction 3: Supprimer les lignes obsolètes des entités dirigées
$content = preg_replace(
    "/\s*'id' => \\\$direction->entite->id,\s*\n\s*'nom' => \\\$direction->entite->nom,\s*\n\s*'type' => \\\$direction->entite->typeEntite->nom,\s*\n\s*\],/",
    "",
    $content
);

// Correction 4: Supprimer with('entite.typeEntite') et le remplacer par with('entite')
$content = str_replace(
    "->with('entite.typeEntite')",
    "->with('entite')",
    $content
);

file_put_contents($file, $content);
echo "Corrections appliquées avec succès!\n";
