<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('contatos', function (Blueprint $table) {
        // O segredo é o ->nullable()->change();
        // Obs: Você precisa do pacote doctrine/dbal instalado.
        // Se der erro de dependência, apenas apague a migration anterior e rode de novo com ->nullable() na definição original.

        $table->unsignedBigInteger('analyst_id')->nullable()->change();
        $table->unsignedBigInteger('service_id')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contatos', function (Blueprint $table) {
            //
        });
    }
};
