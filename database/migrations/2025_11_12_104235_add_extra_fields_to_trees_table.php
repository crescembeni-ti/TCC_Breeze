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
        $table->string('vulgar_name')->nullable();
        $table->string('scientific_name')->nullable();
        $table->decimal('cap', 8, 2)->nullable();
        $table->decimal('height', 8, 2)->nullable();
        $table->decimal('crown_height', 8, 2)->nullable();
        $table->decimal('crown_diameter_longitudinal', 8, 2)->nullable();
        $table->decimal('crown_diameter_perpendicular', 8, 2)->nullable();
        $table->string('bifurcation_type')->nullable();
        $table->string('stem_balance')->nullable();
        $table->string('crown_balance')->nullable();
        $table->string('organisms')->nullable();
        $table->string('target')->nullable();
        $table->string('injuries')->nullable();
        $table->string('wiring_status')->nullable();
        $table->decimal('total_width', 8, 2)->nullable();
        $table->decimal('street_width', 8, 2)->nullable();
        $table->decimal('gutter_height', 8, 2)->nullable();
        $table->decimal('gutter_width', 8, 2)->nullable();
        $table->decimal('gutter_length', 8, 2)->nullable();
        $table->string('no_species_case')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            //
        });
    }
};
