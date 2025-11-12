<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Noticia; // Importe o modelo

class NoticiaSeeder extends Seeder
{
    public function run(): void
    {
        Noticia::firstOrCreate(['titulo' => 'Alerta de Chuvas Fortes'], [
            'conteudo' => 'A Defesa Civil emite alerta para a possibilidade de chuvas intensas na região...',
            'imagem_url' => 'https://picsum.photos/seed/chuva/400/200'
        ]);

        Noticia::firstOrCreate(['titulo' => 'Campanha de Vacinação'], [
            'conteudo' => 'Neste sábado, 11 de outubro, ocorrerá a campanha de vacinação contra a gripe...',
            'imagem_url' => 'https://picsum.photos/seed/vacina/400/200'
        ]);

        Noticia::firstOrCreate(['titulo' => 'Interdição da Rua Principal'], [
            'conteudo' => 'A rua principal será interditada para obras na próxima segunda-feira...',
            'imagem_url' => 'https://picsum.photos/seed/rua/400/200'
        ]);
    }
}