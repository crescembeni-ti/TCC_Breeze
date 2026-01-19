<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona a coluna JSON
        Schema::table('about_pages', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('content');
        });

        // 2. Migra os dados antigos para o novo formato (opcional, para não perder o que já tem)
        $page = DB::table('about_pages')->first();
        if ($page) {
            $sections = [];
            
            if (!empty($page->mission_content)) {
                $sections[] = [
                    'title' => $page->mission_title ?? 'Nossa Missão',
                    'content' => $page->mission_content
                ];
            }
            if (!empty($page->how_it_works_content)) {
                $sections[] = [
                    'title' => 'Como Funciona',
                    'content' => $page->how_it_works_content
                ];
            }
            if (!empty($page->benefits_content)) {
                $sections[] = [
                    'title' => 'Benefícios das Árvores',
                    'content' => $page->benefits_content
                ];
            }

            DB::table('about_pages')
                ->where('id', $page->id)
                ->update(['sections' => json_encode($sections)]);
        }

        // 3. Remove as colunas antigas
        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn(['mission_title', 'mission_content', 'how_it_works_content', 'benefits_content']);
        });
    }

    public function down(): void
    {
        // Reverte as mudanças (apaga sections e recria as colunas)
        Schema::table('about_pages', function (Blueprint $table) {
            $table->string('mission_title')->nullable();
            $table->longText('mission_content')->nullable();
            $table->longText('how_it_works_content')->nullable();
            $table->longText('benefits_content')->nullable();
            $table->dropColumn('sections');
        });
    }
};