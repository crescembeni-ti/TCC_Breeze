<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailWithCode;
use App\Models\Contact; // Importando o modelo Contact

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'email_verification_code', // <-- CORRIGIDO
        'email_verification_code_expires_at', // <-- CORRIGIDO
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Envia a notificação de verificação de e-mail customizada.
     */
    public function sendEmailVerificationNotification()
    {
        // Certifique-se de que o nome da sua notificação está correto.
        // Se você a chamou de SendVerificationCode, mude abaixo:
        $this->notify(new VerifyEmailWithCode); 
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}