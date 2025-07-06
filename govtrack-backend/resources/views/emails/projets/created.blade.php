<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau projet créé</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .title {
            color: #28a745;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #6c757d;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .project-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .project-title {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .creator-info {
            background-color: #e7f3ff;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        .creator-name {
            font-weight: bold;
            color: #17a2b8;
        }
        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-a-faire {
            background-color: #e9ecef;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
            <div class="title">Nouveau projet créé</div>
            <div class="subtitle">Un nouveau projet a été créé dans votre organisation</div>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $recipient->prenom }} {{ $recipient->nom }}</strong>,</p>

            <p>Un nouveau projet a été créé par <strong>{{ $creator->prenom }} {{ $creator->nom }}</strong>.</p>

            <div class="project-info">
                <div class="project-title">{{ $projet->titre }}</div>

                <div class="info-row">
                    <span class="info-label">Description :</span>
                    <span class="info-value">{{ Str::limit($projet->description, 100) }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Type de projet :</span>
                    <span class="info-value">{{ $projet->typeProjet->nom ?? 'Non défini' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Porteur :</span>
                    <span class="info-value">{{ $projet->porteur->prenom }} {{ $projet->porteur->nom }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Donneur d'ordre :</span>
                    <span class="info-value">{{ $projet->donneurOrdre->prenom }} {{ $projet->donneurOrdre->nom }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Date de début prévisionnelle :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($projet->date_debut_previsionnelle)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Date de fin prévisionnelle :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($projet->date_fin_previsionnelle)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Statut :</span>
                    <span class="info-value">
                        <span class="status-badge status-a-faire">À faire</span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Niveau d'exécution :</span>
                    <span class="info-value">{{ $projet->niveau_execution }}%</span>
                </div>
            </div>

            <div class="creator-info">
                <p><strong>Créé par :</strong> <span class="creator-name">{{ $creator->prenom }} {{ $creator->nom }}</span></p>
                <p><strong>Date de création :</strong> {{ \Carbon\Carbon::parse($projet->date_creation)->format('d/m/Y à H:i') }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ $appUrl }}/projects/{{ $projet->id }}" class="cta-button">
                    Voir le projet
                </a>
            </div>

            <p>Vous recevez cette notification car vous êtes impliqué dans ce projet (porteur, donneur d'ordre ou membre de l'équipe).</p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par {{ $appName }}</p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, vous pouvez modifier vos préférences dans votre profil.</p>
        </div>
    </div>
</body>
</html>
