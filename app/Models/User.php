<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailWithCode; // Seu código de verificação
use App\Models\Contact; // Importando o modelo Contact
use App\Notifications\SendVerificationCode;
use Laravel\Sanctum\HasApiTokens; // <-- 1. ADICIONADO PARA O SANCTUM
use Spatie\Permission\Traits\HasRoles; // <-- ADICIONADO PARA O SPATIE/PERMISSION

// --- IMPORTS ADICIONADOS PARA A FOTO DE PERFIL ---
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Facades\Storage; 

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles; 

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
        'email_verification_code',
        'email_verification_code_expires_at',
        'profile_photo_path', // <-- 1. ADICIONADO AQUI
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
        $this->notify(new SendVerificationCode); 
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // =======================================================
    //  2. MÉTODO ADICIONADO (ACCESSOR PARA A URL DA FOTO)
    // =======================================================
    /**
     * Retorna a URL completa da foto de perfil.
     * O app Android vai ler isso como 'profile_photo_url'
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['profile_photo_path']
                // Se a foto existir no banco, retorna a URL completa
                ? Storage::url($attributes['profile_photo_path'])
                // Senão, retorna null
                : null,
        );
    }
}