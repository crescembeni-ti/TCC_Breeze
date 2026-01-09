<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $table = 'service_orders';

    protected $fillable = [
        'contact_id',
        'analyst_id',
        'service_id',
        'supervisor_id',
        
        // Datas
        'data_vistoria',
        'data_execucao',
        
        // Dados Técnicos (Texto simples ou Números)
        'especies',
        'quantidade',
        'latitude',
        'longitude',
        
        // Arrays (Listas de seleção múltipla)
        'motivos',
        'servicos',
        'equipamentos',
        'procedimentos',
        
        // Textos Longos
        'observacoes',
        'laudo_tecnico',

        // Controle de Fluxo (CRUCIAL PARA SEU PROBLEMA)
        'status',
        'destino', 
    ];

    // AQUI ESTÁ A CORREÇÃO DO ERRO DA TELA
    // Isso converte automaticamente o JSON do banco para Array no PHP
    protected $casts = [
        'data_vistoria' => 'date',
        'data_execucao' => 'date',
        'motivos'       => 'array',
        'servicos'      => 'array',
        'equipamentos'  => 'array',
        'procedimentos' => 'array',
        'especies'      => 'array', // Se você salvar múltiplas espécies, mantenha. Se for texto único, remova.
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function analyst()
    {
        return $this->belongsTo(Analyst::class, 'analyst_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}