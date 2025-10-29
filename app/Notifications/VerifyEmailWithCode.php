<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailWithCode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Gerar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Definir tempo de expiração (ex: 10 minutos)
        $expiresAt = now()->addMinutes(10);

        // Salvar o código e a expiração no usuário
        // IMPORTANTE: Isso assume que você já rodou a migração do Passo 1
        $notifiable->email_verification_code = $code;
        $notifiable->email_verification_code_expires_at = $expiresAt;
        $notifiable->save();

        // Enviar o e-mail
        return (new MailMessage)
                    ->subject('Seu Código de Verificação de E-mail')
                    ->line('Use o código abaixo para verificar seu endereço de e-mail.')
                    ->line('Seu código de verificação é:')
                    ->line($code) // Substituímos o ->panel() por ->line()
                    ->line('Este código expira em 10 minutos.')
                    ->line('Se você não criou esta conta, nenhuma ação é necessária.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}