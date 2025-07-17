<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isReply ? 'Réponse à votre commentaire' : 'Nouveau commentaire' }} - {{ $appName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .author-info {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .project-info {
            background-color: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .parent-message {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .highlight {
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $appName }}</h1>
            <p>{{ $isReply ? 'Réponse à votre commentaire' : 'Nouveau commentaire sur un projet' }}</p>
        </div>

        <div class="content">
            <p>Bonjour <span class="highlight">{{ $recipient->prenom }} {{ $recipient->nom }}</span>,</p>

            @if($isReply)
                <p><strong>{{ $author->prenom }} {{ $author->nom }}</strong> a répondu à votre commentaire sur l'instruction <strong>{{ $discussion->projet->titre }}</strong>.</p>
            @else
                <p><strong>{{ $author->prenom }} {{ $author->nom }}</strong> a posté un nouveau commentaire sur l'instruction <strong>{{ $discussion->projet->titre }}</strong>.</p>
            @endif

            <div class="project-info">
                <h3>📋 Instruction : {{ $discussion->projet->titre }}</h3>
                <p><strong>Type d'instruction :</strong> {{ $discussion->projet->typeProjet->nom ?? 'Non défini' }}</p>
                <p><strong>Statut :</strong> {{ $discussion->projet->statut_libelle ?? $discussion->projet->statut }}</p>
                <p><strong>Porteur :</strong> {{ $discussion->projet->porteur->prenom ?? '' }} {{ $discussion->projet->porteur->nom ?? '' }}</p>
            </div>

            @if($isReply && $parentMessage)
                <div class="parent-message">
                    <h4>💬 Votre commentaire original :</h4>
                    <p><em>{{ $parentMessage->message }}</em></p>
                    <small>Posté le {{ \Carbon\Carbon::parse($parentMessage->date_creation)->format('d/m/Y à H:i') }}</small>
                </div>
            @endif

            <div class="author-info">
                <h4>👤 Auteur : {{ $author->prenom }} {{ $author->nom }}</h4>
                <p><strong>Matricule :</strong> {{ $author->matricule }}</p>
                <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($discussion->date_creation)->format('d/m/Y à H:i') }}</p>
            </div>

            <div class="message-box">
                <h4>💬 Message :</h4>
                <p>{{ $discussion->message }}</p>
            </div>

            <a href="{{ $appUrl }}/projects/{{ $discussion->projet_id }}" class="btn">
                Voir le projet et répondre
            </a>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par {{ $appName }}.</p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, veuillez contacter votre administrateur.</p>
        </div>
    </div>
</body>
</html>
