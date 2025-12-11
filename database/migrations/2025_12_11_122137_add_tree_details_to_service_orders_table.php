<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Adicionando as colunas faltantes para dados da vistoria
            $table->string('especies')->nullable()->after('data_execucao');
            $table->integer('quantidade')->nullable()->after('especies');
            $table->string('latitude')->nullable()->after('quantidade');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['especies', 'quantidade', 'latitude', 'longitude']);
        });
    }
};