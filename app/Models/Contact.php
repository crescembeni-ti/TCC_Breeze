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
    'user_id',
    'analyst_id',
    'service_id',
    'nome_solicitante',
    'email_solicitante',
    'telefone', // <--- ADICIONE AQUI
    'topico',
    'bairro',
    'rua',
    'numero',
    'descricao',
    'status_id',
    'justificativa',
    'fotos',
    'designated_to',
];

    protected $casts = [
        'fotos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'designated_to');
    }

    // --- CORREÇÃO AQUI ---
    // Mudei de serviceOrders (plural) para serviceOrder (singular)
    // Mudei de hasMany para hasOne (Uma solicitação tem UMA ordem de serviço)
    public function serviceOrder()
    {
        return $this->hasOne(ServiceOrder::class, 'contact_id');
    }

} // Fim da classe