<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Species;
use App\Models\Tree;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Criar espécies de árvores
        $species = [
            [
                'name' => 'London Planetree',
                'scientific_name' => 'Platanus × acerifolia',
                'description' => 'Uma árvore de grande porte, resistente à poluição urbana.',
                'color_code' => '#4CAF50',
            ],
            [
                'name' => 'Dawn Redwood',
                'scientific_name' => 'Metasequoia glyptostroboides',
                'description' => 'Conífera decídua de crescimento rápido.',
                'color_code' => '#8BC34A',
            ],
            [
                'name' => 'Callery Pear',
                'scientific_name' => 'Pyrus calleryana',
                'description' => 'Árvore ornamental com flores brancas na primavera.',
                'color_code' => '#CDDC39',
            ],
            [
                'name' => 'Pin Oak',
                'scientific_name' => 'Quercus palustris',
                'description' => 'Carvalho de médio porte com folhas lobadas.',
                'color_code' => '#FFC107',
            ],
            [
                'name' => 'Honeylocust',
                'scientific_name' => 'Gleditsia triacanthos',
                'description' => 'Árvore de sombra com folhas compostas delicadas.',
                'color_code' => '#FF9800',
            ],
        ];

        foreach ($species as $speciesData) {
            Species::create($speciesData);
        }

        // Criar árvores de exemplo (Paracambi-RJ, Brasil - coordenadas aproximadas)
        // Nota: As fotos são URLs de exemplo do Unsplash para demonstração
        $trees = [
            ['species_id' => 1, 'latitude' => -22.6111, 'longitude' => -43.7089, 'trunk_diameter' => 45.5, 'address' => 'Praça da Matriz, Centro', 'photo' => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800'],
            ['species_id' => 2, 'latitude' => -22.6095, 'longitude' => -43.7105, 'trunk_diameter' => 32.0, 'address' => 'Rua Principal, 100', 'photo' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?w=800'],
            ['species_id' => 3, 'latitude' => -22.6125, 'longitude' => -43.7075, 'trunk_diameter' => 28.3, 'address' => 'Av. dos Trabalhadores, 200', 'photo' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?w=800'],
            ['species_id' => 1, 'latitude' => -22.6105, 'longitude' => -43.7095, 'trunk_diameter' => 52.1, 'address' => 'Rua São José, 50', 'photo' => 'https://images.unsplash.com/photo-1540270776932-e72e7c2d11cd?w=800'],
            ['species_id' => 4, 'latitude' => -22.6118, 'longitude' => -43.7082, 'trunk_diameter' => 38.7, 'address' => 'Av. Central, 150', 'photo' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?w=800'],
            ['species_id' => 5, 'latitude' => -22.6102, 'longitude' => -43.7098, 'trunk_diameter' => 41.2, 'address' => 'Rua das Flores, 30', 'photo' => 'https://images.unsplash.com/photo-1511497584788-876760111969?w=800'],
            ['species_id' => 2, 'latitude' => -22.6115, 'longitude' => -43.7088, 'trunk_diameter' => 29.8, 'address' => 'Rua do Comércio, 60', 'photo' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=800'],
            ['species_id' => 3, 'latitude' => -22.6108, 'longitude' => -43.7092, 'trunk_diameter' => 35.4, 'address' => 'Av. Brasil, 120', 'photo' => 'https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800'],
            ['species_id' => 4, 'latitude' => -22.6120, 'longitude' => -43.7078, 'trunk_diameter' => 44.9, 'address' => 'Rua da Igreja, 40', 'photo' => 'https://images.unsplash.com/photo-1536147116438-62679a5e01f2?w=800'],
            ['species_id' => 5, 'latitude' => -22.6098, 'longitude' => -43.7102, 'trunk_diameter' => 31.5, 'address' => 'Rua Nova, 70', 'photo' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?w=800'],
        ];

        foreach ($trees as $treeData) {
            $treeData['user_id'] = $user->id;
            $treeData['planted_at'] = now()->subYears(rand(1, 10));
            Tree::create($treeData);
        }

        // Criar atividades de exemplo
        $activityTypes = ['watered', 'weeded', 'mulched', 'pruned', 'fertilized'];
        $treeIds = Tree::pluck('id')->toArray();

        for ($i = 0; $i < 15; $i++) {
            Activity::create([
                'tree_id' => $treeIds[array_rand($treeIds)],
                'user_id' => $user->id,
                'type' => $activityTypes[array_rand($activityTypes)],
                'description' => 'Atividade de manutenção realizada.',
                'activity_date' => now()->subDays(rand(0, 30)),
            ]);
        }
    }
}

