<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    // Data yang dikirim ke email (Request & Status)
    public function __construct(public $drugRequest, public $statusLabel) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Pesanan E-Pharma: #' . $this->drugRequest->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_notification', // Merujuk ke file blade
        );
    }
}