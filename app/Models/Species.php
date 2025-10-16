<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    protected $fillable = [
        'name',
        'scientific_name',
        'description',
        'color_code',
    ];

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }
}

