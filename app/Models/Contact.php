<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Status; 

class Contact extends Model
{
    use HasFactory; 

    protected $fillable = [
        // CAMPOS DO USUÁRIO (Cidadão)
        'user_id', 
        'nome_solicitante', 
        'email_solicitante',
        
        // CAMPOS DO FORMULÁRIO
        'topico', 
        'bairro',  
        'rua',
        'numero',
        'descricao',
        'foto_path', 

        // CAMPOS DE STATUS E GESTÃO
        'status_id',
        'justificativa',
        
        // =======================================================
        //  NOVO CAMPO ADICIONADO (Para designar funcionário)
        // =======================================================
        'designated_to', 
    ];

    /**
     * Relação: O Cidadão que criou a solicitação.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relação: O Status atual da solicitação.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // =======================================================
    //  NOVA RELAÇÃO ADICIONADA
    // =======================================================
    /**
     * Relação: O Funcionário (Analista) responsável pela solicitação.
     */
    public function responsible()
    {
        // Relaciona com a tabela 'users', mas usando a coluna 'designated_to'
        return $this->belongsTo(User::class, 'designated_to');
    }
}