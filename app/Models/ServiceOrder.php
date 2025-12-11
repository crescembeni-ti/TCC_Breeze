<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'data_vistoria',
        'data_execucao',
        'especies', 
        'quantidade', 
        'latitude', 
        'longitude',
        'motivos',
        'servicos',
        'equipamentos',
        'procedimentos',
        'observacoes',
        'supervisor_id'
    ];

    // O segredo está aqui: Converte JSON do banco para Array no PHP automaticamente
    protected $casts = [
        'motivos' => 'array',
        'servicos' => 'array',
        'equipamentos' => 'array',
        'procedimentos' => 'array',
        'data_vistoria' => 'date',
        'data_execucao' => 'date',
        // 'especies', 'quantidade', 'latitude', e 'longitude' são strings/inteiros
        // e não precisam ser casted, a menos que você queira garantir um tipo específico.
    ];

    // Relacionamento inverso
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}