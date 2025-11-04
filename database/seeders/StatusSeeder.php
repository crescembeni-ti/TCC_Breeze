<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Você já deve ter este
use App\Models\Status; // <--- ESTA É A CORREÇÃO (O IMPORT)

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa a tabela antes de popular
        DB::table('statuses')->delete();
            
        // Cria os status (sem o "Enviado", como solicitado)
        Status::create(['name' => 'Em Análise']);
        Status::create(['name' => 'Deferido']);   // Aprovado
        Status::create(['name' => 'Concluído']);  // Realizado
        Status::create(['name' => 'Indeferido']); // Rejeitado
    }
}