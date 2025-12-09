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
        Schema::table('about_pages', function (Blueprint $table) {
            // 1. Remover colunas antigas de texto fixo
            $table->dropColumn('content');
            $table->dropColumn('mission_title');
            $table->dropColumn('mission_content');
            $table->dropColumn('how_it_works_content');
            $table->dropColumn('benefits_content');
            
            // 2. Adicionar a nova coluna para o JSON de blocos
            $table->longText('content_blocks')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            // 1. Adicionar colunas antigas de volta
            $table->longText('content');
            $table->string('mission_title')->nullable();
            $table->longText('mission_content')->nullable();
            $table->longText('how_it_works_content')->nullable();
            $table->longText('benefits_content')->nullable();

            // 2. Remover a nova coluna de blocos
            $table->dropColumn('content_blocks');
        });
    }
};