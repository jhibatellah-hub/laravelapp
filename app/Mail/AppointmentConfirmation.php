<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    // L'constructeur kayched les informations dial RDV
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    // L'objet (Subject) dial l'email
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de votre rendez-vous - MedCabinet',
        );
    }

    // L'fichier Blade li fih le design dial l'email
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment', // Gha ncreyiwha daba
        );
    }

    public function attachments(): array
    {
        return [];
    }
}