<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle tâche créée - {{ $appName }}</title>
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
            background-color: white;
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
        .task-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .task-title {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .info-row {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .project-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .creator-info {
            background-color: #f3e5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #9c27b0;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-a_faire {
            background-color: #ffc107;
            color: #000;
        }
        .status-en_cours {
            background-color: #17a2b8;
            color: white;
        }
        .status-bloque {
            background-color: #dc3545;
            color: white;
        }
        .status-demande_de_cloture {
            background-color: #fd7e14;
            color: white;
        }
        .status-termine {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Nouvelle tâche créée</h1>
            <p>Une nouvelle tâche a été créée dans le projet</p>
        </div>

        <div class="task-info">
            <div class="task-title">{{ $tache->titre }}</div>

            @if($tache->description)
                <div style="margin: 15px 0;">
                    <strong>Description :</strong><br>
                    {{ $tache->description }}
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">Statut :</span>
                <span class="status-badge status-{{ $tache->statut }}">
                    {{ $tache->statut_libelle }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Niveau d'exécution :</span>
                <span class="info-value">{{ $tache->niveau_execution }}%</span>
            </div>

            @if($tache->date_debut_previsionnelle)
                <div class="info-row">
                    <span class="info-label">Date de début prévisionnelle :</span>
                    <span class="info-value">{{ $tache->date_debut_previsionnelle->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($tache->date_fin_previsionnelle)
                <div class="info-row">
                    <span class="info-label">Date de fin prévisionnelle :</span>
                    <span class="info-value">{{ $tache->date_fin_previsionnelle->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($tache->responsables && $tache->responsables->count() > 0)
                <div class="info-row">
                    <span class="info-label">Responsables :</span>
                    <span class="info-value">
                        {{ $tache->responsables->pluck('prenom')->implode(', ') }} {{ $tache->responsables->pluck('nom')->implode(', ') }}
                    </span>
                </div>
            @endif
        </div>

        <div class="project-info">
            <h3 style="margin-top: 0; color: #2196f3;">📋 Projet associé</h3>
            <div class="info-row">
                <span class="info-label">Projet :</span>
                <span class="info-value">{{ $tache->projet->titre }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Porteurs :</span>
                <span class="info-value">
                    @if($tache->projet->porteurs && $tache->projet->porteurs->count() > 0)
                        {{ $tache->projet->porteurs->pluck('prenom')->implode(', ') }} {{ $tache->projet->porteurs->pluck('nom')->implode(', ') }}
                    @else
                        Non assigné
                    @endif
                </span>
            </div>
            @if($tache->projet->donneurOrdre)
                <div class="info-row">
                    <span class="info-label">Ordonnateur de l'instruction :</span>
                    <span class="info-value">{{ $tache->projet->donneurOrdre->prenom }} {{ $tache->projet->donneurOrdre->nom }}</span>
                </div>
            @endif
        </div>

        <div class="creator-info">
            <h3 style="margin-top: 0; color: #9c27b0;">👤 Créé par</h3>
            <div class="info-row">
                <span class="info-label">Nom :</span>
                <span class="info-value">{{ $creator->prenom }} {{ $creator->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value">{{ $creator->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de création :</span>
                <span class="info-value">{{ $tache->date_creation->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $appUrl }}/taches/{{ $tache->id }}" class="btn">
                Voir la tâche
            </a>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par {{ $appName }}</p>
            <p>Si vous ne souhaitez plus recevoir ces notifications, veuillez contacter votre administrateur.</p>
        </div>
    </div>
</body>
</html>
