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
        

        // CAMPOS DE STATUS E GESTÃO
        'status_id',
        'justificativa',

        'fotos', 
        
        // =======================================================
        //  NOVO CAMPO ADICIONADO (Para designar funcionário)
        // =======================================================
        'designated_to', 
    ];

    protected $casts = [
    'fotos' => 'array',
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



        /**
     * [ANALISTA] Lista de vistorias para a blade vistorias-pendentes.
     */
    public function vistoriasPendentes()
    {
        // 1. Defina quais status aparecem para o analista
        $statusPendentes = ['Em Análise', 'Deferido', 'Em Execução'];

        // 2. Busca as solicitações com esses status
        // Se tiver relacionamento com 'bairro', adicione no array: with(['status', 'user', 'bairro'])
        $vistorias = Contact::with(['status', 'user'])
            ->whereHas('status', function ($query) use ($statusPendentes) {
                $query->whereIn('name', $statusPendentes);
            })
            ->latest()
            ->get();

        // 3. Retorna a view correta dentro da pasta 'analista'
        return view('analista.vistorias-pendentes', compact('vistorias'));
    }



} // Fim da classe
