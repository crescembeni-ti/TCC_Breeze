<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Topico; // <-- Importe o Model

class TopicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de tópicos/solicitações frequentes
        $topicos = [
            'Solicitação de Plantio',
            'Solicitação de Poda',
            'Solicitação de Remoção',
        ];

        // Loop para inserir cada um
        foreach ($topicos as $nomeDoTopico) {
            // 'firstOrCreate' evita duplicatas se você rodar o seeder de novo
            Topico::firstOrCreate(['nome' => $nomeDoTopico]);
        }
    }
}