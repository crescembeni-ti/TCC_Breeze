<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove a conexão da tabela trees com species
        Schema::table('trees', function (Blueprint $table) {
            // Remove a restrição de chave estrangeira
            // O Laravel geralmente nomeia como: tabela_coluna_foreign
            $table->dropForeign(['species_id']); 
            
            // Remove a coluna
            $table->dropColumn('species_id');
        });

        // 2. Exclui a tabela de espécies
        Schema::dropIfExists('species');
    }

    public function down(): void
    {
        // Caso precise reverter (recria a estrutura básica)
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('trees', function (Blueprint $table) {
            $table->foreignId('species_id')->nullable()->constrained('species');
        });
    }
};