<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeTache;

class TypeTacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typesTaches = [
            [
                'nom' => 'Tâche ordinaire',
                'description' => 'Tâche standard de travail quotidien',
                'couleur' => '#3B82F6', // Bleu
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'nom' => 'Recommandation',
                'description' => 'Tâche de type recommandation ou suggestion',
                'couleur' => '#10B981', // Vert
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'nom' => 'Urgente',
                'description' => 'Tâche nécessitant une attention immédiate',
                'couleur' => '#EF4444', // Rouge
                'actif' => true,
                'ordre' => 3,
            ],
            [
                'nom' => 'Maintenance',
                'description' => 'Tâche de maintenance ou de correction',
                'couleur' => '#F59E0B', // Orange
                'actif' => true,
                'ordre' => 4,
            ],
            [
                'nom' => 'Formation',
                'description' => 'Tâche liée à la formation ou à l\'apprentissage',
                'couleur' => '#8B5CF6', // Violet
                'actif' => true,
                'ordre' => 5,
            ],
        ];

        foreach ($typesTaches as $typeTache) {
            TypeTache::firstOrCreate([
                'nom' => $typeTache['nom'],
            ], [
                ...$typeTache,
                'date_creation' => now(),
                'creer_par' => 'seeder',
            ]);
        }
    }
}
