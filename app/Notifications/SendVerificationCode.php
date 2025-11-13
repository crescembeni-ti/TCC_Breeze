<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationCode extends Notification 
{
    use Queueable;

    /**
     * Código de verificação temporário.
     *
     * @var string
     */
    protected string $code;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Define os canais pelos quais a notificação será enviada.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Constrói o e-mail da notificação.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu Código de Verificação')
            ->greeting('Olá, ' . ucfirst($notifiable->name) . ' 🌱')
            ->line('Aqui está o seu código de verificação:')
            ->line('')
            ->line('🔒 **' . $this->code . '**')
            ->line('')
            ->line('Este código expira em 5 minutos.')
            ->line('Se você não solicitou este código, nenhuma ação é necessária.');
    }
}
