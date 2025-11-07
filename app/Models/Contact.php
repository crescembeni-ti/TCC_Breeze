<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Status; // Importa o novo modelo de Status

class Contact extends Model
{
    use HasFactory; 

    protected $fillable = [
        // CAMPOS DO USUÁRIO
        'user_id', 
        'nome_solicitante', 
        'email_solicitante',
        
        // CAMPOS DO FORMULÁRIO
        'topico', // <-- 1. ADICIONADO O NOVO TÓPICO
        'bairro',  
        'rua',
        'numero',
        'descricao',
        'foto_path', 

        //CAMPOS DE STATUS (ATUALIZADOS)
        'status_id',
        'justificativa',
    ];

    /**
     * Define o relacionamento: Uma Solicitação pertence a um Usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define o relacionamento: Uma Solicitação pertence a um Status.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}