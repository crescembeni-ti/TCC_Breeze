<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // 1. Importar User
use Illuminate\Support\Carbon; // 2. Importar Carbon

class PruneUnverifiedUsers extends Command
{
    /**
     * O nome e a assinatura do comando.
     */
    protected $signature = 'app:prune-unverified-users'; // 3. Nome do comando

    /**
     * A descrição do comando.
     */
    protected $description = 'Apaga usuários não verificados mais antigos que 24 horas';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('Iniciando limpeza de usuários não verificados...');

        // 4. A Lógica
        $cutoffDate = Carbon::now()->subDay(); // Pega a data de 24h atrás

        $deletedCount = User::whereNull('email_verified_at') // 1. Onde e-mail NÃO foi verificado
                            ->where('created_at', '<=', $cutoffDate) // 2. E foi criado antes da data de corte
                            ->forceDelete(); // 3. Apaga permanentemente

        $this->info("Limpeza concluída. $deletedCount usuários fantasmas apagados.");
    }
}