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
    Schema::table('service_orders', function (Blueprint $table) {
        // Cria a coluna 'viewed_at' do tipo TIMESTAMP (data e hora), 
        // podendo ser NULO (pois no início ninguém viu ainda)
        $table->timestamp('data_do_visto')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('service_orders', function (Blueprint $table) {
        $table->dropColumn('data_do_visto');
    });

    }
};
