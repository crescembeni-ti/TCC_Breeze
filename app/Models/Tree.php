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
        'cap1', 'cap2', 'cap3', 'cap4', 'cap5', 'cap6', 'cap7', 'cap8', 'cap9', 'cap10',
        'cap11', 'cap12', 'cap13', 'cap14', 'cap15', 'cap16', 'cap17', 'cap18', 'cap19', 'cap20',
        'dap1', 'dap2', 'dap3', 'dap4', 'dap5', 'dap6', 'dap7', 'dap8', 'dap9', 'dap10',
        'dap11', 'dap12', 'dap13', 'dap14', 'dap15', 'dap16', 'dap17', 'dap18', 'dap19', 'dap20',
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
