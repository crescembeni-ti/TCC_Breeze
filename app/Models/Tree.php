<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;

class Tree extends Model
{
    protected $fillable = [
        'species_id', 'bairro_id','latitude', 'longitude', 'trunk_diameter', 'health_status',
        'planted_at', 'address', 'photo',
        'vulgar_name', 'scientific_name', 'cap', 'height', 'crown_height',
        'crown_diameter_longitudinal', 'crown_diameter_perpendicular',
        'bifurcation_type', 'stem_balance', 'crown_balance', 'organisms',
        'target', 'injuries', 'wiring_status', 'total_width', 'street_width',
        'gutter_height', 'gutter_width', 'gutter_length', 'no_species_case', 'description',
        'admin_id', 'aprovado', 'analyst_id',
    ];

    protected $casts = [
        'planted_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'trunk_diameter' => 'decimal:2',
    ];

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function analyst() {
        return $this->belongsTo(Analyst::class, 'analyst_id');
    }
    
}
