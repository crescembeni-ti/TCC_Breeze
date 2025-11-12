<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; // O código de 6 dígitos

    /**
     * Create a new message instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu Código de Redefinição de Senha',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Este é um template de e-mail simples.
        // Ele vai procurar por um arquivo 'resources/views/emails/password-reset-code.blade.php'
        // Se você não quiser criar um .blade.php, pode usar a função 'text()'
        
        return new Content(
            text: 'emails.password-reset-code' // Referência ao arquivo de texto
        );
    }
}