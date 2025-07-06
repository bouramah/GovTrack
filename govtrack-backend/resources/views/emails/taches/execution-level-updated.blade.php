<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niveau d'exécution de tâche mis à jour - {{ $appName }}</title>
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
        .progress-change {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
            text-align: center;
        }
        .progress-bar {
            background-color: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            margin: 15px 0;
            position: relative;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-old {
            background-color: #ffc107;
        }
        .progress-new {
            background-color: #28a745;
        }
        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
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
        .updater-info {
            background-color: #f3e5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #9c27b0;
        }
        .comment-section {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
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
        .progression-indicator {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .progression-positive {
            color: #28a745;
        }
        .progression-negative {
            color: #dc3545;
        }
        .progression-neutral {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📈 Niveau d'exécution mis à jour</h1>
            <p>Le niveau d'exécution d'une tâche a été modifié</p>
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

            @if($tache->responsable)
                <div class="info-row">
                    <span class="info-label">Responsable :</span>
                    <span class="info-value">{{ $tache->responsable->prenom }} {{ $tache->responsable->nom }}</span>
                </div>
            @endif
        </div>

        <div class="progress-change">
            <h3 style="margin-top: 0; color: #4caf50;">📊 Évolution du niveau d'exécution</h3>

            @php
                $progression = $nouveauNiveau - $ancienNiveau;
                $progressionClass = $progression > 0 ? 'progression-positive' : ($progression < 0 ? 'progression-negative' : 'progression-neutral');
                $progressionIcon = $progression > 0 ? '↗️' : ($progression < 0 ? '↘️' : '→');
                $progressionText = $progression > 0 ? 'Augmentation' : ($progression < 0 ? 'Diminution' : 'Maintien');
            @endphp

            <div class="progression-indicator {{ $progressionClass }}">
                {{ $progressionIcon }} {{ $progressionText }} de {{ abs($progression) }}%
            </div>

            <div class="progress-labels">
                <span>Ancien niveau</span>
                <span>Nouveau niveau</span>
            </div>

            <div class="progress-bar">
                <div class="progress-fill progress-old" style="width: {{ $ancienNiveau }}%;"></div>
            </div>
            <div class="progress-labels">
                <span>{{ $ancienNiveau }}%</span>
                <span>{{ $nouveauNiveau }}%</span>
            </div>
        </div>

        @if($commentaire)
            <div class="comment-section">
                <h3 style="margin-top: 0; color: #ffc107;">💬 Commentaire</h3>
                <p style="margin: 0; font-style: italic;">{{ $commentaire }}</p>
            </div>
        @endif

        <div class="project-info">
            <h3 style="margin-top: 0; color: #2196f3;">📋 Projet associé</h3>
            <div class="info-row">
                <span class="info-label">Projet :</span>
                <span class="info-value">{{ $tache->projet->titre }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Porteur :</span>
                <span class="info-value">{{ $tache->projet->porteur->prenom }} {{ $tache->projet->porteur->nom }}</span>
            </div>
            @if($tache->projet->donneurOrdre)
                <div class="info-row">
                    <span class="info-label">Donneur d'ordre :</span>
                    <span class="info-value">{{ $tache->projet->donneurOrdre->prenom }} {{ $tache->projet->donneurOrdre->nom }}</span>
                </div>
            @endif
        </div>

        <div class="updater-info">
            <h3 style="margin-top: 0; color: #9c27b0;">👤 Mis à jour par</h3>
            <div class="info-row">
                <span class="info-label">Nom :</span>
                <span class="info-value">{{ $updater->prenom }} {{ $updater->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email :</span>
                <span class="info-value">{{ $updater->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de mise à jour :</span>
                <span class="info-value">{{ $tache->date_modification->format('d/m/Y à H:i') }}</span>
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
