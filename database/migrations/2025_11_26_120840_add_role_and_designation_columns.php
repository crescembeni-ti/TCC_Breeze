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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna de cargo/função
            $table->string('role')->default('user')->after('email'); 
            // Valores esperados: 'admin', 'analista', 'user'
        });

        Schema::table('contacts', function (Blueprint $table) {
            // Adiciona a coluna para designar um funcionário responsável
            $table->foreignId('designated_to')
                  ->nullable()
                  ->after('status_id')
                  ->constrained('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('contacts', function (Blueprint $table) {
            // Remove a chave estrangeira primeiro, depois a coluna
            $table->dropForeign(['designated_to']);
            $table->dropColumn('designated_to');
        });
    }
};