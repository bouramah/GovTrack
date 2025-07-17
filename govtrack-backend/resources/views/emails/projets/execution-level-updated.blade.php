<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour du niveau d'exécution - {{ $projet->titre }}</title>
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
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .project-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .project-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .progress-section {
            background-color: #e8f5e8;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .progress-bar-container {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 15px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }
        .progress-change {
            font-size: 16px;
            font-weight: 600;
            color: #28a745;
            margin: 10px 0;
        }
        .details {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .details h3 {
            margin-top: 0;
            color: #856404;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .info-value {
            color: #2c3e50;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <span class="icon">📊</span>
            <h1>Mise à jour du niveau d'exécution</h1>
        </div>

        <div class="content">
            <div class="greeting">
                Bonjour {{ $recipient->prenom }} {{ $recipient->nom }},
            </div>

            <p>Le niveau d'exécution de l'instruction a été mis à jour par <strong>{{ $updater->prenom }} {{ $updater->nom }}</strong>.</p>

            <div class="project-info">
                <div class="project-title">{{ $projet->titre }}</div>
                <div class="info-row">
                    <span class="info-label">Type d'instruction :</span>
                    <span class="info-value">{{ $projet->typeProjet->nom ?? 'Non défini' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut :</span>
                    <span class="info-value">{{ \App\Models\Projet::STATUTS[$projet->statut] ?? $projet->statut }}</span>
                </div>
            </div>

            <div class="progress-section">
                <h3 style="margin-top: 0; color: #155724;">📈 Progression de l'instruction</h3>

                <div class="progress-change">
                    {{ $ancienNiveau }}% → {{ $nouveauNiveau }}%
                    @if($nouveauNiveau > $ancienNiveau)
                        <span style="color: #28a745;">(+{{ $nouveauNiveau - $ancienNiveau }}%)</span>
                    @elseif($nouveauNiveau < $ancienNiveau)
                        <span style="color: #dc3545;">({{ $nouveauNiveau - $ancienNiveau }}%)</span>
                    @else
                        <span style="color: #6c757d;">(aucun changement)</span>
                    @endif
                </div>

                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: {{ $nouveauNiveau }}%;">
                        {{ $nouveauNiveau }}%
                    </div>
                </div>
            </div>

            @if($commentaire)
            <div class="details">
                <h3>💬 Commentaire</h3>
                <p>{{ $commentaire }}</p>
            </div>
            @endif

            <div class="details">
                <h3>📋 Détails de l'instruction</h3>
                <div class="info-row">
                    <span class="info-label">Porteur :</span>
                    <span class="info-value">{{ $projet->porteur->prenom ?? 'Non défini' }} {{ $projet->porteur->nom ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ordonnateur de l'instruction :</span>
                    <span class="info-value">{{ $projet->donneurOrdre->prenom ?? 'Non défini' }} {{ $projet->donneurOrdre->nom ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de début prév. :</span>
                    <span class="info-value">{{ $projet->date_debut_previsionnelle ? \Carbon\Carbon::parse($projet->date_debut_previsionnelle)->format('d/m/Y') : 'Non définie' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de fin prév. :</span>
                    <span class="info-value">{{ $projet->date_fin_previsionnelle ? \Carbon\Carbon::parse($projet->date_fin_previsionnelle)->format('d/m/Y') : 'Non définie' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mis à jour par :</span>
                    <span class="info-value">{{ $updater->prenom }} {{ $updater->nom }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de mise à jour :</span>
                    <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="#" class="btn">Voir l'instruction</a>
            </div>

            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                Cette notification a été générée automatiquement par le système GovTrack.
            </p>
        </div>

        <div class="footer">
            <p><strong>GovTrack</strong> - Système de gestion de projets gouvernementaux</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
