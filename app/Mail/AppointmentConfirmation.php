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
            subject: 'Confirmation de votre rendez-vous - MedCabinet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment', 
        );
    }

    /**
     * Hna fin drna l'modification bach n-attachiw l'PDF
     */
    public function attachments(): array
    {
        // 1. Kan-génériw l'PDF mn wahed l'fichier Blade jdid (ghadi ncreyiwh)
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