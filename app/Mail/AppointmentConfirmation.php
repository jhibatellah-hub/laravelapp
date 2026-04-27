<?php

namespace App\Mail;

use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment; 
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // 1. Beddelna MedCabinet b JamylCabinet
            subject: 'Confirmation de votre rendez-vous — JamylCabinet',
        );
    }

    public function content(): Content
    {
        return new Content(
            // 2. Beddelna smeyat l'view bash tqra l'fichier li sawbna f l'message li fat
            view: 'emails.appointment-confirmation', 
        );
    }

    /**
     * Hna fin drna l'modification bach n-attachiw l'PDF (Khlliha raha s7i7a 100%)
     */
    public function attachments(): array
    {
        // 1. Kan-génériw l'PDF mn wahed l'fichier Blade jdid
        $pdf = Pdf::loadView('pdf.appointment_receipt', [
            'appointment' => $this->appointment
        ]);

        // 2. Kan-les9ouh f l'email w nsemiwh 'Recu_Rendez_Vous.pdf'
        return [
            Attachment::fromData(fn () => $pdf->output(), 'Recu_Rendez_Vous.pdf')
                      ->withMime('application/pdf'),
        ];
    }
}