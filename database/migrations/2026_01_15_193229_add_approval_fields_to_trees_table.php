<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            
            // 1. Coluna de Aprovação
            if (!Schema::hasColumn('trees', 'aprovado')) {
                $table->boolean('aprovado')->default(true)->after('id');
            }

            // 2. Coluna do Analista
            if (!Schema::hasColumn('trees', 'analyst_id')) {
                if (Schema::hasTable('analysts')) {
                    $table->foreignId('analyst_id')->nullable()->constrained('analysts')->onDelete('set null');
                } else {
                    $table->unsignedBigInteger('analyst_id')->nullable();
                }
            }

            // 3. Coluna do Admin
            if (!Schema::hasColumn('trees', 'admin_id')) {
                if (Schema::hasTable('admins')) {
                    $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
                } else {
                    $table->unsignedBigInteger('admin_id')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            if (Schema::hasColumn('trees', 'analyst_id')) {
                try { $table->dropForeign(['analyst_id']); } catch (\Exception $e) {}
                $table->dropColumn('analyst_id');
            }

            if (Schema::hasColumn('trees', 'admin_id')) {
                try { $table->dropForeign(['admin_id']); } catch (\Exception $e) {}
                $table->dropColumn('admin_id');
            }

            if (Schema::hasColumn('trees', 'aprovado')) {
                $table->dropColumn('aprovado');
            }
        });
    }
};