<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut du projet modifié</title>
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
            color: #ffc107;
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
            margin-bottom: 15px;
        }
        .status-change {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .status-arrow {
            font-size: 20px;
            color: #856404;
            margin: 0 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-a-faire { background-color: #e9ecef; color: #495057; }
        .status-en-cours { background-color: #cce5ff; color: #004085; }
        .status-bloque { background-color: #f8d7da; color: #721c24; }
        .status-demande-cloture { background-color: #fff3cd; color: #856404; }
        .status-termine { background-color: #d4edda; color: #155724; }
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
        .changer-info {
            background-color: #e7f3ff;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        .changer-name {
            font-weight: bold;
            color: #17a2b8;
        }
        .commentaire {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
            <div class="title">Statut du projet modifié</div>
            <div class="subtitle">Le statut d'un projet a été mis à jour</div>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $recipient->prenom }} {{ $recipient->nom }}</strong>,</p>

            <p>Le statut du projet <strong>{{ $projet->titre }}</strong> a été modifié par <strong>{{ $changer->prenom }} {{ $changer->nom }}</strong>.</p>

            <div class="project-info">
                <div class="project-title">{{ $projet->titre }}</div>

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
                    <span class="info-label">Niveau d'exécution :</span>
                    <span class="info-value">{{ $projet->niveau_execution }}%</span>
                </div>
            </div>

            <div class="status-change">
                <span class="status-badge status-{{ $ancienStatut }}">
                    {{ $ancienStatut === 'a_faire' ? 'À faire' :
                       ($ancienStatut === 'en_cours' ? 'En cours' :
                       ($ancienStatut === 'bloque' ? 'Bloqué' :
                       ($ancienStatut === 'demande_de_cloture' ? 'Demande de clôture' :
                       ($ancienStatut === 'termine' ? 'Terminé' : $ancienStatut)))) }}
                </span>
                <span class="status-arrow">→</span>
                <span class="status-badge status-{{ $nouveauStatut }}">
                    {{ $nouveauStatut === 'a_faire' ? 'À faire' :
                       ($nouveauStatut === 'en_cours' ? 'En cours' :
                       ($nouveauStatut === 'bloque' ? 'Bloqué' :
                       ($nouveauStatut === 'demande_de_cloture' ? 'Demande de clôture' :
                       ($nouveauStatut === 'termine' ? 'Terminé' : $nouveauStatut)))) }}
                </span>
            </div>

            @if($commentaire)
            <div class="commentaire">
                <strong>Commentaire :</strong><br>
                {{ $commentaire }}
            </div>
            @endif

            <div class="changer-info">
                <p><strong>Modifié par :</strong> <span class="changer-name">{{ $changer->prenom }} {{ $changer->nom }}</span></p>
                <p><strong>Date de modification :</strong> {{ \Carbon\Carbon::parse($projet->date_modification)->format('d/m/Y à H:i') }}</p>
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
