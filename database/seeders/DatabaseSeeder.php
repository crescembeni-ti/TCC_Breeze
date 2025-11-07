<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Species;
use App\Models\Tree;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // <-- 1. IMPORTAR O HASH

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chamar os seeders de dados mestres primeiro
        $this->call([
            BairroSeeder::class,
            TopicoSeeder::class, 
            // Se você tiver outros seeders (ex: StatusSeeder), adicione-os aqui.
        ]);

        // --- 2. BLOCO DE USUÁRIO ATUALIZADO ---
        // Cria o usuário de teste APENAS SE ele não existir
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'], // <-- O campo para procurar
            [                               // <-- Os dados para criar se não achar
                'name' => 'Test User',
                'password' => Hash::make('password') // Define uma senha padrão
            ]
        );
        // --- FIM DA ATUALIZAÇÃO ---

        // Criar espécies de árvores (só cria se não existirem)
        $species = [
            [
                'name' => 'London Planetree',
                'scientific_name' => 'Platanus × acerifolia',
                'description' => 'Uma árvore de grande porte, resistente à poluição urbana.',
                'color_code' => '#4CAF50',
            ],
            // ... (resto da sua lista de espécies) ...
            [
                'name' => 'Honeylocust',
                'scientific_name' => 'Gleditsia triacanthos',
                'description' => 'Árvore de sombra com folhas compostas delicadas.',
                'color_code' => '#FF9800',
            ],
        ];

        foreach ($species as $speciesData) {
            // Usa firstOrCreate para evitar erro de duplicidade nas espécies também
            Species::firstOrCreate(['name' => $speciesData['name']], $speciesData);
        }

        // Criar árvores de exemplo
        // Para evitar duplicidade aqui, podemos checar se já existem árvores
        if (Tree::count() == 0) { 
            $trees = [
                ['species_id' => 1, 'latitude' => -22.6111, 'longitude' => -43.7089, 'trunk_diameter' => 45.5, 'address' => 'Praça da Matriz, Centro', 'photo' => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800'],
                // ... (resto da sua lista de árvores) ...
                ['species_id' => 5, 'latitude' => -22.6098, 'longitude' => -43.7102, 'trunk_diameter' => 31.5, 'address' => 'Rua Nova, 70', 'photo' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?w=800'],
            ];

            foreach ($trees as $treeData) {
                $treeData['user_id'] = $user->id;
                $treeData['planted_at'] = now()->subYears(rand(1, 10));
                Tree::create($treeData);
            }
        }

        // Criar atividades de exemplo
        // Também checa se já existem atividades
        if (Activity::count() == 0) {
            $activityTypes = ['watered', 'weeded', 'mulched', 'pruned', 'fertilized'];
            $treeIds = Tree::pluck('id')->toArray();

            // Só roda se existirem árvores
            if (!empty($treeIds)) {
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
    }
}