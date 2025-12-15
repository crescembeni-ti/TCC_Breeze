<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('trees', function (Blueprint $table) {
        // Cria uma coluna de TEXTO (para caber muito texto) e NULLABLE (opcional)
        // Adicionamos 'after' apenas para organizar no banco, se seu banco suportar (MySQL suporta)
        $table->text('description')->nullable()->after('species_id');
    });
    }

public function down(): void
    {
    Schema::table('trees', function (Blueprint $table) {
        $table->dropColumn('description');
    });
    }
};
