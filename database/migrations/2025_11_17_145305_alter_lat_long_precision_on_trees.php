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
    Schema::table('trees', function (Blueprint $table) {
        $table->decimal('latitude', 12, 8)->change();
        $table->decimal('longitude', 12, 8)->change();
    });
}

public function down()
{
    Schema::table('trees', function (Blueprint $table) {
        $table->decimal('latitude', 10, 7)->change();
        $table->decimal('longitude', 10, 7)->change();
    });
}

};
