<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeProjet;
use App\Models\Projet;
use App\Models\Tache;
use App\Models\User;
use Carbon\Carbon;

class Partie2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🚀 Début du seeding de la Partie 2 - Gestion des Projets et Instructions...\n";

        // 1. Créer les types de projets
        $this->createTypesProjets();

        // 2. Créer des projets d'exemple
        $this->createProjets();

        // 3. Créer des tâches pour les projets
        $this->createTaches();

        echo "✅ Seeding de la Partie 2 terminé avec succès !\n";
    }

    private function createTypesProjets()
    {
        echo "📋 Création des types de projets...\n";

        $typesProjets = [
            [
                'nom' => 'Instruction Ministérielle',
                'description' => 'Instructions officielles données par le Ministre lors des conseils de cabinet ou réunions ministérielles',
                'duree_previsionnelle_jours' => 30,
                'description_sla' => 'Délai standard de 30 jours pour les instructions ministérielles'
            ],
            [
                'nom' => 'Recommandation Technique',
                'description' => 'Recommandations techniques pour améliorer les processus ou résoudre des problèmes spécifiques',
                'duree_previsionnelle_jours' => 15,
                'description_sla' => 'Délai de 15 jours pour les recommandations techniques'
            ],
            [
                'nom' => 'Projet Stratégique',
                'description' => 'Grands projets stratégiques de transformation ou de développement',
                'duree_previsionnelle_jours' => 90,
                'description_sla' => 'Délai de 90 jours pour les projets stratégiques'
            ],
            [
                'nom' => 'Mise en Conformité',
                'description' => 'Projets de mise en conformité réglementaire ou normative',
                'duree_previsionnelle_jours' => 45,
                'description_sla' => 'Délai de 45 jours pour les mises en conformité'
            ],
            [
                'nom' => 'Formation et Sensibilisation',
                'description' => 'Projets de formation du personnel et sensibilisation',
                'duree_previsionnelle_jours' => 21,
                'description_sla' => 'Délai de 21 jours pour les formations'
            ],
            [
                'nom' => 'Urgence',
                'description' => 'Instructions ou projets urgents nécessitant une exécution rapide',
                'duree_previsionnelle_jours' => 7,
                'description_sla' => 'Délai de 7 jours pour les urgences'
            ]
        ];

        foreach ($typesProjets as $typeData) {
            TypeProjet::create([
                ...$typeData,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'system_seeder',
                'modifier_par' => 'system_seeder',
            ]);
        }

        echo "✅ " . count($typesProjets) . " types de projets créés\n";
    }

    private function createProjets()
    {
        echo "📊 Création des projets d'exemple...\n";

        // Récupérer les utilisateurs et types de projets
        $users = User::all();
        $typesProjets = TypeProjet::all();

        if ($users->count() < 3 || $typesProjets->count() == 0) {
            echo "⚠️ Pas assez d'utilisateurs ou de types de projets pour créer des projets\n";
            return;
        }

        $projets = [
            [
                'titre' => 'Digitalisation des Procédures Administratives',
                'description' => 'Mise en place d\'un système de gestion électronique des documents et procédures administratives pour améliorer l\'efficacité et réduire les délais de traitement.',
                'type_projet_id' => $typesProjets->where('nom', 'Projet Stratégique')->first()->id,
                'porteur_id' => $users->where('email', 'amadou.diop@govtrack.gov')->first()?->id ?? $users->random()->id,
                'donneur_ordre_id' => $users->where('email', 'admin@govtrack.gov')->first()?->id ?? $users->random()->id,
                'statut' => 'en_cours',
                'niveau_execution' => 35,
                'date_debut_previsionnelle' => Carbon::now()->subDays(15),
                'date_fin_previsionnelle' => Carbon::now()->addDays(75),
                'date_debut_reelle' => Carbon::now()->subDays(12),
            ],
            [
                'titre' => 'Formation sur les Nouvelles Réglementations',
                'description' => 'Organisation de sessions de formation pour tout le personnel sur les nouvelles réglementations en vigueur et les bonnes pratiques.',
                'type_projet_id' => $typesProjets->where('nom', 'Formation et Sensibilisation')->first()->id,
                'porteur_id' => $users->where('email', 'fatou.fall@govtrack.gov')->first()?->id ?? $users->random()->id,
                'donneur_ordre_id' => $users->where('email', 'amadou.diop@govtrack.gov')->first()?->id ?? $users->random()->id,
                'statut' => 'a_faire',
                'niveau_execution' => 0,
                'date_debut_previsionnelle' => Carbon::now()->addDays(5),
                'date_fin_previsionnelle' => Carbon::now()->addDays(26),
            ],
            [
                'titre' => 'Mise à Jour du Système de Sécurité',
                'description' => 'Mise à jour urgente du système de sécurité informatique suite aux nouvelles menaces identifiées.',
                'type_projet_id' => $typesProjets->where('nom', 'Urgence')->first()->id,
                'porteur_id' => $users->random()->id,
                'donneur_ordre_id' => $users->where('email', 'admin@govtrack.gov')->first()?->id ?? $users->random()->id,
                'statut' => 'demande_de_cloture',
                'niveau_execution' => 90,
                'date_debut_previsionnelle' => Carbon::now()->subDays(5),
                'date_fin_previsionnelle' => Carbon::now()->addDays(2),
                'date_debut_reelle' => Carbon::now()->subDays(4),
            ],
            [
                'titre' => 'Audit des Processus Financiers',
                'description' => 'Réalisation d\'un audit complet des processus financiers pour assurer la conformité aux normes comptables.',
                'type_projet_id' => $typesProjets->where('nom', 'Mise en Conformité')->first()->id,
                'porteur_id' => $users->random()->id,
                'donneur_ordre_id' => $users->random()->id,
                'statut' => 'bloque',
                'niveau_execution' => 25,
                'date_debut_previsionnelle' => Carbon::now()->subDays(10),
                'date_fin_previsionnelle' => Carbon::now()->addDays(35),
                'date_debut_reelle' => Carbon::now()->subDays(8),
            ],
            [
                'titre' => 'Optimisation du Réseau Informatique',
                'description' => 'Amélioration de la performance et de la fiabilité du réseau informatique de l\'organisation.',
                'type_projet_id' => $typesProjets->where('nom', 'Recommandation Technique')->first()->id,
                'porteur_id' => $users->random()->id,
                'donneur_ordre_id' => $users->random()->id,
                'statut' => 'termine',
                'niveau_execution' => 100,
                'date_debut_previsionnelle' => Carbon::now()->subDays(20),
                'date_fin_previsionnelle' => Carbon::now()->subDays(5),
                'date_debut_reelle' => Carbon::now()->subDays(18),
                'date_fin_reelle' => Carbon::now()->subDays(3),
            ]
        ];

        foreach ($projets as $projetData) {
            $projet = Projet::create([
                ...$projetData,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'system_seeder',
                'modifier_par' => 'system_seeder',
            ]);

            // Créer l'historique initial
            $projet->historiqueStatuts()->create([
                'ancien_statut' => null,
                'nouveau_statut' => $projetData['statut'],
                'user_id' => $projetData['donneur_ordre_id'],
                'commentaire' => 'Création du projet',
                'date_changement' => $projetData['date_creation'] ?? now(),
            ]);
        }

        echo "✅ " . count($projets) . " projets créés\n";
    }

    private function createTaches()
    {
        echo "📝 Création des tâches d'exemple...\n";

        $projets = Projet::all();
        $users = User::all();

        if ($projets->count() == 0 || $users->count() == 0) {
            echo "⚠️ Pas de projets ou d'utilisateurs pour créer des tâches\n";
            return;
        }

        $tachesTemplate = [
            // Tâches pour le projet de digitalisation
            [
                'titre' => 'Analyse des besoins',
                'description' => 'Analyser les besoins actuels et identifier les processus à digitaliser',
                'statut' => 'termine',
                'niveau_execution' => 100,
            ],
            [
                'titre' => 'Sélection de la solution technique',
                'description' => 'Évaluer et sélectionner la meilleure solution technique pour la digitalisation',
                'statut' => 'termine',
                'niveau_execution' => 100,
            ],
            [
                'titre' => 'Développement des modules',
                'description' => 'Développer les modules de base du système de gestion électronique',
                'statut' => 'en_cours',
                'niveau_execution' => 60,
            ],
            [
                'titre' => 'Tests et validation',
                'description' => 'Effectuer les tests complets et valider le fonctionnement du système',
                'statut' => 'a_faire',
                'niveau_execution' => 0,
            ],
            [
                'titre' => 'Formation des utilisateurs',
                'description' => 'Former le personnel à l\'utilisation du nouveau système',
                'statut' => 'a_faire',
                'niveau_execution' => 0,
            ],
        ];

        // Créer des tâches pour chaque projet
        foreach ($projets->take(3) as $index => $projet) {
            $nombreTaches = rand(2, 4);

            for ($i = 0; $i < $nombreTaches; $i++) {
                $tacheTemplate = $tachesTemplate[$i] ?? [
                    'titre' => 'Tâche ' . ($i + 1),
                    'description' => 'Description de la tâche ' . ($i + 1),
                    'statut' => 'a_faire',
                    'niveau_execution' => 0,
                ];

                Tache::create([
                    ...$tacheTemplate,
                    'projet_id' => $projet->id,
                    'responsable_id' => $users->random()->id,
                    'date_debut_previsionnelle' => $projet->date_debut_previsionnelle,
                    'date_fin_previsionnelle' => Carbon::parse($projet->date_fin_previsionnelle)->subDays(rand(5, 15)),
                    'date_debut_reelle' => $tacheTemplate['statut'] !== 'a_faire'
                        ? Carbon::parse($projet->date_debut_previsionnelle)->addDays(rand(1, 5))
                        : null,
                    'date_fin_reelle' => $tacheTemplate['statut'] === 'termine'
                        ? Carbon::parse($projet->date_debut_previsionnelle)->addDays(rand(5, 20))
                        : null,
                    'date_creation' => now(),
                    'date_modification' => now(),
                    'creer_par' => 'system_seeder',
                    'modifier_par' => 'system_seeder',
                ]);
            }
        }

        $totalTaches = Tache::count();
        echo "✅ {$totalTaches} tâches créées\n";
    }
}
