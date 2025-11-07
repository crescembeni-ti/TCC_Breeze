<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Status; // Importa o novo modelo de Status

class Contact extends Model
{
    use HasFactory; // Adicionado (padrão do Laravel)

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
        'foto_path', // <-- ADICIONADO PARA PERMITIR O SALVAMENTO DA FOTO

        //CAMPOS DE STATUS (ATUALIZADOS)
        // 'status', // Removido, substituído por status_id
        'status_id',     // <-- ADICIONADO
        'justificativa', // <-- ADICIONADO
    ];

    /**
     * Define o relacionamento: Uma Solicitação pertence a um Usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * [NOVO]
     * Define o relacionamento: Uma Solicitação pertence a um Status.
     */
    public function status()
    {
        // O Laravel vai procurar 'status_id' automaticamente
        return $this->belongsTo(Status::class);
    }
}