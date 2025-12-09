<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Bairro;

class BairroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * ============================================================
         *  LISTA DOS BAIRROS OFICIAIS
         *  A ORDEM DAQUI DEFINE O ID FINAL NO BANCO
         * ============================================================
         */
        $bairros = [
            'Amapá',
            'Barreira',
            'BNH de Baixo',
            'BNH de Cima',
            'Boa Vista',
            'Bom Jardim',
            'Boqueirão',
            'Cabral',
            'Capinheira',
            'Cascata',
            'Centro',
            'Copê',
            'Coroado',
            'Costa Verde',
            'Distrito Industrial do Cabral',
            'Fábrica',
            'Fazenda Retiro',
            'Floresta',
            'Guarajuba',
            'Gurgel',
            'Jardim Nova Era',
            'Lages',
            'Lago Azul',
            'Lanari',
            'Mario Belo',
            'Mutirão',
            'Pacheco',
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
            'Vila Nova',
        ];

        /**
         * ============================================================
         *  APAGAR TUDO E RECRIAR DO ZERO
         *  (AGORA É SEGURO, JÁ QUE VOCÊ REMOVEU AS ÁRVORES)
         * ============================================================
         */
        DB::table('bairros')->truncate();

        /**
         * ============================================================
         *  RECRIAR NA ORDEM EXATA DA LISTA (IDs reiniciam)
         * ============================================================
         */
        foreach ($bairros as $nome) {
            Bairro::create(['nome' => $nome]);
        }
    }
}
