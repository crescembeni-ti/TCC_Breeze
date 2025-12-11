<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            // campos opcionais para armazenar quem recebeu a solicitação
            $table->unsignedBigInteger('analyst_id')->nullable()->after('status_id');
            $table->unsignedBigInteger('service_id')->nullable()->after('analyst_id');

            // foreign keys opcionais (mantemos nullable)
            $table->foreign('analyst_id')->references('id')->on('analysts')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['analyst_id']);
            $table->dropColumn(['service_id', 'analyst_id']);
        });
    }
};

