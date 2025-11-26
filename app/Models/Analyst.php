<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Analist extends Authenticatable
{
    use Notifiable;

    protected $table = 'analysts';

    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
