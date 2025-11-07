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
            'Poda de árvore',
            'Remoção de galho caído',
            'Avaliação de risco de queda',
            'Solicitação de nova muda',
            'Árvore com praga ou doença',
            'Árvore interferindo na fiação elétrica',
            'Outro motivo',
        ];

        // Loop para inserir cada um
        foreach ($topicos as $nomeDoTopico) {
            // 'firstOrCreate' evita duplicatas se você rodar o seeder de novo
            Topico::firstOrCreate(['nome' => $nomeDoTopico]);
        }
    }
}