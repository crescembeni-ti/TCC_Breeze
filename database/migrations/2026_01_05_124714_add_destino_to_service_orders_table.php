<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {

            /**
             * destino define PARA QUEM a ordem de serviço
             * está enviada no momento.
             *
             * Valores possíveis:
             * - analista → aparece na aba "Analista"
             * - servico  → aparece na aba "Serviço"
             * - null     → não aparece na página de OS
             */
            $table->string('destino')
                  ->nullable()
                  ->after('supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('destino');
        });
    }
};
