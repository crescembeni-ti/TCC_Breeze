<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status; // Garanta que o import está aqui

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos firstOrCreate para adicionar apenas os que não existem.
        
        // Os status do seu fluxo
        Status::firstOrCreate(['name' => 'Em Análise']);
        Status::firstOrCreate(['name' => 'Deferido']);   // Aprovado
        Status::firstOrCreate(['name' => 'Concluído']);  // Realizado
        Status::firstOrCreate(['name' => 'Indeferido']); // Rejeitado

        // O NOVO STATUS
        Status::firstOrCreate(['name' => 'Cancelado']);
    }
}