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
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
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
        .task-info {
            background-color: #d4edda;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #218838;
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
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $appName }}</h1>
            <p>{{ $isReply ? 'Réponse à votre commentaire' : 'Nouveau commentaire sur une tâche' }}</p>
        </div>

        <div class="content">
            <p>Bonjour <span class="highlight">{{ $recipient->prenom }} {{ $recipient->nom }}</span>,</p>

            @if($isReply)
                <p><strong>{{ $author->prenom }} {{ $author->nom }}</strong> a répondu à votre commentaire sur la tâche <strong>{{ $discussion->tache->titre }}</strong>.</p>
            @else
                <p><strong>{{ $author->prenom }} {{ $author->nom }}</strong> a posté un nouveau commentaire sur la tâche <strong>{{ $discussion->tache->titre }}</strong>.</p>
            @endif

            <div class="task-info">
                <h3>✅ Tâche : {{ $discussion->tache->titre }}</h3>
                <p><strong>Statut :</strong> {{ $discussion->tache->statut_libelle ?? $discussion->tache->statut }}</p>
                <p><strong>Niveau d'exécution :</strong> {{ $discussion->tache->niveau_execution }}%</p>
                @if($discussion->tache->responsable)
                    <p><strong>Responsable :</strong> {{ $discussion->tache->responsable->prenom }} {{ $discussion->tache->responsable->nom }}</p>
                @endif
            </div>

            <div class="project-info">
                <h3>📋 Instruction parente : {{ $discussion->tache->projet->titre }}</h3>
                <p><strong>Type d'instruction :</strong> {{ $discussion->tache->projet->typeProjet->nom ?? 'Non défini' }}</p>
                <p><strong>Statut :</strong> {{ $discussion->tache->projet->statut_libelle ?? $discussion->tache->projet->statut }}</p>
                <p><strong>Porteur :</strong> {{ $discussion->tache->projet->porteur->prenom ?? '' }} {{ $discussion->tache->projet->porteur->nom ?? '' }}</p>
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

            <a href="{{ $appUrl }}/projects/{{ $discussion->tache->projet_id }}?task={{ $discussion->tache_id }}" class="btn">
                Voir la tâche et répondre
            </a>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par {{ $appName }}.</p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, veuillez contacter votre administrateur.</p>
        </div>
    </div>
</body>
</html>
