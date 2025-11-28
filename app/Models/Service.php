<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Service extends Authenticatable
{
    use Notifiable;

    protected $table = 'services';

    protected $fillable = [
    'name',   // CORRETO
    'email',
    'cpf',
    'password',
];


    protected $hidden = [
        'password',
        'remember_token',
    ];
}
