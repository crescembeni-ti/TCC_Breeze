<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODELO TREE (Árvore)
 * Representa uma árvore cadastrada no sistema e suas características.
 */
class Tree extends Model
{
    /**
     * CAMPOS PERMITIDOS (Fillable)
     * Lista de colunas que podem ser preenchidas em massa (ex: via formulário).
     */
    protected $fillable = [
        'bairro_id', 'latitude', 'longitude', 'trunk_diameter', 'health_status',
        'planted_at', 'address', 'photo',
        'vulgar_name', 'scientific_name', 'cap', 'height', 'crown_height',
        'crown_diameter_longitudinal', 'crown_diameter_perpendicular',
        'bifurcation_type', 'stem_balance', 'crown_balance', 'organisms',
        'target', 'injuries', 'wiring_status', 'total_width', 'street_width',
        'gutter_height', 'gutter_width', 'gutter_length', 'no_species_case', 'description',
        'admin_id', 'aprovado', 'analyst_id',
    ];

    /**
     * CONVERSÃO DE TIPOS (Casts)
     * Garante que os dados venham do banco no formato correto (ex: data, decimal).
     */
    protected $casts = [
        'planted_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'trunk_diameter' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Uma árvore pertence a um Bairro.
     */
    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }

    /**
     * Uma árvore pode ter várias Atividades (histórico).
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Uma árvore pode ter sido cadastrada por um Administrador.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Uma árvore pode ter sido cadastrada ou editada por um Analista.
     */
    public function analyst(): BelongsTo
    {
        return $this->belongsTo(Analyst::class, 'analyst_id');
    }
}
