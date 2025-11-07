<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topico extends Model
{
    use HasFactory;

    /**
     * O nome da tabela.
     */
    protected $table = 'topicos';

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = ['nome'];

    /**
     * Indica se o model deve ter timestamps (created_at/updated_at).
     */
    public $timestamps = false;
}