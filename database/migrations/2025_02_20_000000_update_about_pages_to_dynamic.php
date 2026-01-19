<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona a coluna JSON (SÓ SE ELA NÃO EXISTIR)
        if (!Schema::hasColumn('about_pages', 'sections')) {
            Schema::table('about_pages', function (Blueprint $table) {
                $table->json('sections')->nullable()->after('content');
            });
        }

        // 2. Migra os dados antigos para o novo formato
        $page = DB::table('about_pages')->first();
        if ($page) {
            $sections = [];
            
            // Verifica se as colunas antigas existem antes de tentar ler
            $missionContent = $page->mission_content ?? null;
            $missionTitle = $page->mission_title ?? 'Nossa Missão';
            
            if (!empty($missionContent)) {
                $sections[] = [
                    'title' => $missionTitle,
                    'content' => $missionContent
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

            // Só atualiza se houver algo para migrar
            if (!empty($sections)) {
                DB::table('about_pages')
                    ->where('id', $page->id)
                    ->update(['sections' => json_encode($sections)]);
            }
        }

        // 3. Remove as colunas antigas (SÓ SE ELAS EXISTIREM)
        Schema::table('about_pages', function (Blueprint $table) {
            $columnsToDrop = [];
            $candidates = ['mission_title', 'mission_content', 'how_it_works_content', 'benefits_content'];

            foreach ($candidates as $column) {
                if (Schema::hasColumn('about_pages', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            // Recria as colunas se não existirem
            if (!Schema::hasColumn('about_pages', 'mission_title')) $table->string('mission_title')->nullable();
            if (!Schema::hasColumn('about_pages', 'mission_content')) $table->longText('mission_content')->nullable();
            if (!Schema::hasColumn('about_pages', 'how_it_works_content')) $table->longText('how_it_works_content')->nullable();
            if (!Schema::hasColumn('about_pages', 'benefits_content')) $table->longText('benefits_content')->nullable();
            
            // Remove a coluna sections
            if (Schema::hasColumn('about_pages', 'sections')) {
                $table->dropColumn('sections');
            }
        });
    }
};