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
    Schema::table('contacts', function (Blueprint $table) {
        // Vamos adicionar a coluna 'topico' (tipo string)
        // Vou colocar depois da coluna 'foto_path'
        $table->string('topico')->nullable()->after('foto_path');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            //
        });
    }
};
