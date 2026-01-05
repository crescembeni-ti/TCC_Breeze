<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    /**
     * Campos liberados para mass assignment
     */
    protected $fillable = [
        'contact_id',
        'supervisor_id',
        'analyst_id',
        'service_id',
        'flow',
        'data_vistoria',
        'data_execucao',
        'motivos',
        'servicos',
        'equipamentos',
        'procedimentos',
        'observacoes',
        'especies',
        'quantidade',
        'latitude',
        'longitude',
    ];

    /**
     * Casts automáticos
     * Converte JSON do banco em array no PHP
     */
    protected $casts = [
        'motivos' => 'array',
        'servicos' => 'array',
        'equipamentos' => 'array',
        'procedimentos' => 'array',
        'data_vistoria' => 'date',
        'data_execucao' => 'date',
    ];

    /**
     * =============================
     * RELACIONAMENTOS
     * =============================
     */

    // Solicitação original do usuário
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // Analista responsável (destino atual ou criador)
    public function analyst()
    {
        return $this->belongsTo(Analyst::class);
    }

    // Equipe de serviço responsável
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
