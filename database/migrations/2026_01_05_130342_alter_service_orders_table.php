<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {

            // 🔹 Para quem a OS foi enviada
            if (!Schema::hasColumn('service_orders', 'analyst_id')) {
                $table->foreignId('analyst_id')
                      ->nullable()
                      ->after('supervisor_id')
                      ->constrained('analysts')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('service_orders', 'service_id')) {
                $table->foreignId('service_id')
                      ->nullable()
                      ->after('analyst_id')
                      ->constrained('services')
                      ->nullOnDelete();
            }

            // 🔹 Controle do fluxo da OS
            if (!Schema::hasColumn('service_orders', 'flow')) {
                $table->enum('flow', ['analista', 'servico', 'finalizada'])
                      ->default('analista')
                      ->after('service_id');
            }

            // 🔹 Dados técnicos (caso ainda não existam)
            if (!Schema::hasColumn('service_orders', 'motivos')) {
                $table->json('motivos')->nullable();
            }

            if (!Schema::hasColumn('service_orders', 'servicos')) {
                $table->json('servicos')->nullable();
            }

            if (!Schema::hasColumn('service_orders', 'equipamentos')) {
                $table->json('equipamentos')->nullable();
            }

            if (!Schema::hasColumn('service_orders', 'procedimentos')) {
                $table->json('procedimentos')->nullable();
            }

            if (!Schema::hasColumn('service_orders', 'observacoes')) {
                $table->text('observacoes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {

            if (Schema::hasColumn('service_orders', 'flow')) {
                $table->dropColumn('flow');
            }

            if (Schema::hasColumn('service_orders', 'analyst_id')) {
                $table->dropForeign(['analyst_id']);
                $table->dropColumn('analyst_id');
            }

            if (Schema::hasColumn('service_orders', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }
};
