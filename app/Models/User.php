<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailWithCode;
use App\Models\Contact; 
use App\Notifications\SendVerificationCode;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// Imports para a foto
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Facades\Storage; 

// Adicionado "implements MustVerifyEmail" para garantir que a verificação funcione
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // Mantido por compatibilidade (pode remover no futuro)
        'role',     // <-- O CAMPO NOVO (admin, analista, user)
        'email_verification_code',
        'email_verification_code_expires_at',
        'profile_photo_path',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token', 
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // =======================================================
    //  MÉTODOS DE PERMISSÃO (ATUALIZADOS)
    // =======================================================
    
    /**
     * Verifica se é Admin olhando o novo campo 'role'
     */
    public function isAdmin(): bool
    {
        // Verifica se a role é 'admin' OU se o booleano antigo é true
        return $this->role === 'admin' || $this->is_admin === true;
    }

    /**
     * Verifica se é Analista (Funcionário)
     */
    public function isAnalyst(): bool
    {
        return $this->role === 'analista';
    }

    // =======================================================

    public function sendEmailVerificationNotification()
    {
        $this->notify(new SendVerificationCode); 
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Acessor da Foto
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['profile_photo_path']
                ? Storage::url($attributes['profile_photo_path'])
                : null,
        );
    }
}