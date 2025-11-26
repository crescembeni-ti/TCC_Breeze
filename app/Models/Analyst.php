<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Analyst extends Authenticatable
{
    use Notifiable;

    protected $table = 'analysts';

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
