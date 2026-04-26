<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Rendez-vous</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #007BFF; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #007BFF; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .details th { background-color: #f8f9fa; width: 40%; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; }
    </style>
</head>
<body>

    <div class="header">
        <h1>JamylCabinet</h1>
        <p>Reçu de Confirmation de Rendez-vous</p>
    </div>

    <div class="details">
        <p>Bonjour <strong>{{ $appointment->patient->name }}</strong>,</p>
        <p>Voici les détails de votre rendez-vous :</p>
        
        <table>
            <tr>
                <th>N° de Référence</th>
                <td>#RDV-{{ $appointment->id }}</td>
            </tr>
            <tr>
                <th>Médecin</th>
                <td>Dr. {{ $appointment->doctor->name }}</td>
            </tr>
            <tr>
                <th>Service</th>
                <td>{{ $appointment->service->name ?? 'Consultation' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Heure</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Merci de vous présenter à l'accueil 10 minutes avant l'heure indiquée.</p>
        <p>JamylCabinet - Tél: +212 5XX XX XX XX - Email: contact@JamylCabinet.ma</p>
    </div>

</body>
</html>