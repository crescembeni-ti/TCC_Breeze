<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normaliza os valores dos campos de árvores para garantir consistência.
     * Baseado nos dados reais do BD enviado pelo usuário.
     */
    public function up(): void
    {
        // -------------------------------------------------------
        // INJURIES: "Leves ou ausentes" -> "Leves ou Ausentes"
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET injuries = 'Leves ou Ausentes' WHERE LOWER(TRIM(injuries)) = 'leves ou ausentes'");
        DB::statement("UPDATE trees SET injuries = 'Moderadas' WHERE LOWER(TRIM(injuries)) = 'moderadas'");
        DB::statement("UPDATE trees SET injuries = 'Graves' WHERE LOWER(TRIM(injuries)) = 'graves'");

        // -------------------------------------------------------
        // CROWN_BALANCE: "Medianamente desequilibrada" -> "Medianamente Desequilibrada"
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET crown_balance = 'Equilibrada' WHERE LOWER(TRIM(crown_balance)) = 'equilibrada'");
        DB::statement("UPDATE trees SET crown_balance = 'Medianamente Desequilibrada' WHERE LOWER(TRIM(crown_balance)) IN ('medianamente desequilibrada', 'mediamente desequilibrada', 'mediamente equilibrada', 'medianamente equilibrada')");
        DB::statement("UPDATE trees SET crown_balance = 'Desequilibrada' WHERE LOWER(TRIM(crown_balance)) = 'desequilibrada'");
        DB::statement("UPDATE trees SET crown_balance = 'Muito Desequilibrada' WHERE LOWER(TRIM(crown_balance)) = 'muito desequilibrada'");

        // -------------------------------------------------------
        // ORGANISMS: "Infestação média" -> "Infestação Média"
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET organisms = 'Ausente' WHERE LOWER(TRIM(organisms)) = 'ausente'");
        DB::statement("UPDATE trees SET organisms = 'Infestação Inicial' WHERE LOWER(TRIM(organisms)) = 'infestação inicial'");
        DB::statement("UPDATE trees SET organisms = 'Infestação Média' WHERE LOWER(TRIM(organisms)) IN ('infestação média', 'infestação media')");
        DB::statement("UPDATE trees SET organisms = 'Infestação Avançada' WHERE LOWER(TRIM(organisms)) IN ('infestação avançada', 'infestação avancada')");

        // -------------------------------------------------------
        // TARGET: "Avenidas ou ruas principais com fluxo intenso de veículos ou pessoas"
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET target = 'Ruas secundárias estritamente residenciais com pouca circulação de veículos e pessoas' WHERE LOWER(TRIM(target)) LIKE '%secundárias estritamente residenciais%'");
        DB::statement("UPDATE trees SET target = 'Ruas principais ou secundárias com fluxo intermediário de veículos e pessoas' WHERE LOWER(TRIM(target)) LIKE '%fluxo intermediário%'");
        DB::statement("UPDATE trees SET target = 'Avenidas ou ruas principais com fluxo intenso de veículos e pessoas' WHERE LOWER(TRIM(target)) LIKE '%fluxo intenso%'");

        // -------------------------------------------------------
        // WIRING_STATUS
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET wiring_status = 'Não Interfere' WHERE LOWER(TRIM(wiring_status)) IN ('não interfere', 'nao interfere')");
        DB::statement("UPDATE trees SET wiring_status = 'Pode Interferir' WHERE LOWER(TRIM(wiring_status)) = 'pode interferir'");
        DB::statement("UPDATE trees SET wiring_status = 'Interfere' WHERE LOWER(TRIM(wiring_status)) = 'interfere'");
        DB::statement("UPDATE trees SET wiring_status = 'Ausente' WHERE LOWER(TRIM(wiring_status)) = 'ausente'");

        // -------------------------------------------------------
        // STEM_BALANCE
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET stem_balance = 'Ausente' WHERE LOWER(TRIM(stem_balance)) = 'ausente'");
        DB::statement("UPDATE trees SET stem_balance = 'Maior que 45°' WHERE LOWER(TRIM(stem_balance)) IN ('maior que 45°', 'maior que 45')");
        DB::statement("UPDATE trees SET stem_balance = 'Menor que 45°' WHERE LOWER(TRIM(stem_balance)) IN ('menor que 45°', 'menor que 45')");
        DB::statement("UPDATE trees SET stem_balance = 'Acidental ou associada à elevação da superfície do terreno pelo conjunto de raízes no lado oposto à inclinação' WHERE LOWER(TRIM(stem_balance)) LIKE '%acidental%'");

        // -------------------------------------------------------
        // HEALTH_STATUS
        // -------------------------------------------------------
        DB::statement("UPDATE trees SET health_status = 'Boa' WHERE LOWER(TRIM(health_status)) IN ('boa', 'good', 'bom')");
        DB::statement("UPDATE trees SET health_status = 'Regular' WHERE LOWER(TRIM(health_status)) IN ('regular', 'fair')");
        DB::statement("UPDATE trees SET health_status = 'Ruim' WHERE LOWER(TRIM(health_status)) IN ('ruim', 'poor', 'bad')");
    }

    public function down(): void
    {
        // Operação irreversível
    }
};
