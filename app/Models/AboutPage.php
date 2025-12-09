<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content_blocks', // NOVO: Campo para armazenar o JSON dos blocos
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // O Laravel converterá automaticamente o JSON da coluna em um array PHP
        'content_blocks' => 'array', 
    ];

    // As colunas antigas (content, mission_title, etc.) foram removidas de $fillable.
}