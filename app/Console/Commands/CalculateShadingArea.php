<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tree;

class CalculateShadingArea extends Command
{
    /**
     * O nome e a assinatura do comando.
     */
    protected $signature = 'trees:calculate-shading';

    /**
     * A descrição do comando.
     */
    protected $description = 'Calcula e atualiza a área de sombreamento (shading_area) de todas as árvores';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('Iniciando cálculo de área de sombreamento...');

        // Busca todas as árvores
        $trees = Tree::all();
        $totalTrees = $trees->count();
        $updatedCount = 0;

        $this->info("Total de árvores encontradas: {$totalTrees}");

        // Barra de progresso
        $bar = $this->output->createProgressBar($totalTrees);
        $bar->start();

        foreach ($trees as $tree) {
            // Calcula shading_area usando a fórmula: (π * longitudinal * perpendicular) / 4
            if ($tree->crown_diameter_longitudinal && $tree->crown_diameter_perpendicular) {
                $tree->shading_area = (M_PI * $tree->crown_diameter_longitudinal * $tree->crown_diameter_perpendicular) / 4;
            } else {
                $tree->shading_area = 0;
            }

            // Salva sem disparar eventos (para evitar loop infinito com o boot)
            $tree->saveQuietly();
            $updatedCount++;
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Cálculo concluído! {$updatedCount} árvores atualizadas.");
        
        return Command::SUCCESS;
    }
}
