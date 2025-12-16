<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     * Isso deve bater exatamente com os nomes das colunas na sua Migration
     * e os 'name' dos inputs no seu formulário.
     */
    protected $fillable = [
        'title',
        'content',              // Introdução / Visão Geral
        'mission_title',        // Título da missão (opcional)
        'mission_content',      // Conteúdo da missão
        'how_it_works_content', // Como funciona
        'benefits_content',     // Benefícios
    ];

    // O $casts para 'content_blocks' foi removido porque
    // agora estamos usando colunas de texto simples (longText)
    // em vez de um JSON complexo.
}