<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { color: #185FA5; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; }
        .details { background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="header">JamylCabinet</h2>
        
        <p>Bonjour <strong>{{ $appointment->patient->name }}</strong>,</p>
        
        <p>Nous vous confirmons que votre rendez-vous a bien été enregistré dans notre système. Voici les détails :</p>
        
        <div class="details">
            <p><strong>Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</p>
            <p> <strong>Heure :</strong> {{ $appointment->appointment_time }}</p>
            <p><strong>Service :</strong> {{ $appointment->service->name }}</p>
            <p><strong>Médecin :</strong> {{ $appointment->doctor->name }}</p>
        </div>

        <p>Si vous souhaitez annuler ou modifier ce rendez-vous, veuillez vous connecter à votre espace patient ou nous contacter directement.</p>
        
        <p>Cordialement,<br>L'équipe JamylCabinet</p>

        <div class="footer">
            Ceci est un email automatique, merci de ne pas y répondre.
        </div>
    </div>
</body>
</html>