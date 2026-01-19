<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'title',
        'content',  // Introdução Principal (mantida fixa)
        'sections', // Agora guarda todas as caixas extras dinamicamente (JSON)
    ];

    /**
     * Casts para conversão automática de tipos.
     * Isso converte o JSON do banco para Array no PHP automaticamente.
     */
    protected $casts = [
        'sections' => 'array',
    ];
}