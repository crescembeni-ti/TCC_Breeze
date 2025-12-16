<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            // Vamos verificar se a coluna não existe antes de criar para evitar erro
            if (!Schema::hasColumn('about_pages', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }
            
            if (!Schema::hasColumn('about_pages', 'mission_content')) {
                $table->longText('mission_content')->nullable()->after('content');
            }
            
            if (!Schema::hasColumn('about_pages', 'how_it_works_content')) {
                $table->longText('how_it_works_content')->nullable()->after('mission_content');
            }
            
            if (!Schema::hasColumn('about_pages', 'benefits_content')) {
                $table->longText('benefits_content')->nullable()->after('how_it_works_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn(['content', 'mission_content', 'how_it_works_content', 'benefits_content']);
        });
    }
};