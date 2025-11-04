<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationCode extends Notification
{
    use Queueable;

    protected $code;

    /**
     * Crie uma nova instância da notificação.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Obtenha os canais de entrega da notificação.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtenha a representação por e-mail da notificação.
     * (MÉTODO CORRIGIDO)
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Seu Código de Verificação')
                    ->line('Olá, ' . $notifiable->name . '!')
                    ->line('Seu código de verificação é:')
                    ->line('')
                    // --- CORREÇÃO AQUI ---
                    // Trocamos ->panel() por ->line() com negrito
                    ->line('**' . $this->code . '**')
                    // --- FIM DA CORREÇÃO ---
                    ->line('')
                    ->line('Este código expira em 5 minutos.')
                    ->line('Se você não criou esta conta, nenhuma ação é necessária.');
    }
}