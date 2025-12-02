<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seeders base
        $this->call([
            BairroSeeder::class,
            TopicoSeeder::class,
            StatusSeeder::class,
        ]);

        // 2. Criar admin padrão
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password')
            ]
        );

        // 3. Criar espécies (mantém igual)
        $species = [
            [
                'name' => 'London Planetree',
                'scientific_name' => 'Platanus × acerifolia',
                'description' => 'Uma árvore de grande porte, resistente à poluição urbana.',
                'color_code' => '#4CAF50',
            ],
            [
                'name' => 'Honeylocust',
                'scientific_name' => 'Gleditsia triacanthos',
                'description' => 'Árvore de sombra com folhas compostas delicadas.',
                'color_code' => '#FF9800',
            ],
            // adicione mais se quiser...
        ];

        foreach ($species as $speciesData) {
            Species::firstOrCreate(['name' => $speciesData['name']], $speciesData);
        }

        // 4. NÃO geramos árvores nem atividades automaticamente,
        //    porque agora os admins vão cadastrar manualmente
    }
}
