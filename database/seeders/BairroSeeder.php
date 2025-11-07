<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bairro; // 1. IMPORTE O MODEL
use Illuminate\Support\Facades\DB; // 2. IMPORTE O DB (para checagem)

class BairroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 3. Lista de bairros para adicionar
        $bairros = [
        'Amapá',
        'Barreira',
        'BNH de Baixo',
        'BNH de Cima',
        'Bom Jardim',
        'Cabral',
        'Canoas',
        'Capinheira',
        'Cascata',
        'Centro',
        'Coroado',
        'Costa Verde',
        'Fábrica',
        'Floresta',
        'Guarajuba',
        'Gurgel',
        'Jardim Nova Era',
        'Km 9',
        'Lages',
        'Mario Belo',
        'Mutirão',
        'Pacheco',
        'Paraíso',
        'Ponte Coberta',
        'Quilombo',
        'Raia',
        'Ramalho',
        'Sabugo',
        'São José',
        'São Lourenço',
        'Saudoso',
        'Vale da Conquista',
        'Vila Militar',
        'Vila Nova'
        ];
        
        // 4. Loop para inserir cada bairro
        foreach ($bairros as $nomeDoBairro) {
            // Verifica se o bairro JÁ EXISTE antes de inserir
            Bairro::firstOrCreate(['nome' => $nomeDoBairro]);
        }
    }
}