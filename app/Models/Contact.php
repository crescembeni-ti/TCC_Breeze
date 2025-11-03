<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Importe o Model User

class Contact extends Model
{
    protected $fillable = [
        // CAMPOS DO USUÁRIO
        'user_id', 
        'nome_solicitante', 
        'email_solicitante',
        
        // CAMPOS DO FORMULÁRIO
        'bairro',   
        'rua',
        'numero',
        'descricao',

        // [NOVO] CAMPO DE STATUS
        'status', 
    ];

    /**
     * Define o relacionamento: Uma Solicitação pertence a um Usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}