<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projet;
use App\Models\User;

class ProjetPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mettre à jour les projets existants avec des priorités variées
        $projets = Projet::all();
        $priorites = ['faible', 'normale', 'elevee', 'critique'];

        foreach ($projets as $projet) {
            $projet->update([
                'priorite' => $priorites[array_rand($priorites)]
            ]);
        }

        // Ajouter quelques favoris pour les utilisateurs
        $users = User::take(3)->get();
        $projets = Projet::take(5)->get();

        foreach ($users as $user) {
            // Chaque utilisateur aura 2-3 projets favoris
            $projetsFavoris = $projets->random(rand(2, 3));

            foreach ($projetsFavoris as $projet) {
                // Vérifier si le favori existe déjà
                if (!$projet->estFavoriPour($user->id)) {
                    $projet->favoris()->attach($user->id, [
                        'date_ajout' => now()->subDays(rand(1, 30))
                    ]);
                }
            }
        }

        $this->command->info('Priorités et favoris des projets créés avec succès !');
    }
}
