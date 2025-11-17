<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrige a tabela admin_logs existente
     */
    public function up(): void
    {
        Schema::table('admin_logs', function (Blueprint $table) {

            // Se tiver user_id errado, renomeie para admin_id
            if (Schema::hasColumn('admin_logs', 'user_id')) {
                $table->renameColumn('user_id', 'admin_id');
            }

            // Garanta que a coluna existe
            if (!Schema::hasColumn('admin_logs', 'admin_id')) {
                $table->foreignId('admin_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('admins')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reversão
     */
    public function down(): void
    {
        Schema::table('admin_logs', function (Blueprint $table) {
            if (Schema::hasColumn('admin_logs', 'admin_id')) {
                $table->dropColumn('admin_id');
            }
        });
    }
};
