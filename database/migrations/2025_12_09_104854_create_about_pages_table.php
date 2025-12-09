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
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Ex: "Árvores de Paracambi"
            $table->longText('content'); // Conteúdo principal (HTML/rich text)
            $table->string('mission_title')->nullable();
            $table->longText('mission_content')->nullable();
            $table->longText('how_it_works_content')->nullable();
            $table->longText('benefits_content')->nullable();
            $table->timestamps();
        });
    }
// ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
