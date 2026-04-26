<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Rendez-vous</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">

    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px;">
        <h2 style="color: #0C447C;">Bonjour {{ $appointment->patient->name }},</h2>
        
        <p>Nous vous confirmons que votre rendez-vous a été enregistré avec succès.</p>
        
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <ul style="list-style-type: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 10px;"><strong> Médecin :</strong> {{ $appointment->doctor->name }} ({{ $appointment->doctor->specialty }})</li>
                <li style="margin-bottom: 10px;"><strong> Service :</strong> {{ $appointment->service->name }}</li>
                <li style="margin-bottom: 10px;"><strong> Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</li>
                <li><strong> Heure :</strong> {{ $appointment->appointment_time }}</li>
            </ul>
        </div>

        @if($appointment->notes)
            <p><strong>Notes :</strong> {{ $appointment->notes }}</p>
        @endif

        <p>Merci pour votre confiance,<br><strong>L'équipe JamylCabinet</strong></p>
    </div>

</body>
</html>