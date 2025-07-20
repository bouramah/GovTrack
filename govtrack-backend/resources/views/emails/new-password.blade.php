<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - GovTrack</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
        }
        .password-box {
            background-color: #dbeafe;
            border: 2px solid #3b82f6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Nouveau mot de passe</h1>
        <p>GovTrack - Plateforme de gestion des instructions</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $user->prenom }} {{ $user->nom }}</strong>,</p>

        <p>Votre mot de passe a été réinitialisé par l'administrateur <strong>{{ $adminName }}</strong>.</p>

        <p>Voici votre nouveau mot de passe temporaire :</p>

        <div class="password-box">
            {{ $newPassword }}
        </div>

        <div class="warning">
            <strong>⚠️ Important :</strong>
            <ul>
                <li>Ce mot de passe est temporaire et doit être changé lors de votre prochaine connexion</li>
                <li>Ne partagez jamais ce mot de passe avec qui que ce soit</li>
                <li>Pour des raisons de sécurité, changez ce mot de passe dès que possible</li>
            </ul>
        </div>

        <p>Pour vous connecter :</p>
        <ol>
            <li>Allez sur la plateforme GovTrack</li>
            <li>Utilisez votre email : <strong>{{ $user->email }}</strong></li>
            <li>Utilisez le mot de passe temporaire ci-dessus</li>
            <li>Vous serez automatiquement redirigé vers la page de changement de mot de passe</li>
        </ol>

        <p>Si vous n'avez pas demandé cette réinitialisation, veuillez contacter immédiatement l'administrateur système.</p>

        <p>Cordialement,<br>
        <strong>L'équipe GovTrack</strong></p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Veuillez ne pas y répondre.</p>
        <p>Si vous avez des questions, contactez votre administrateur système.</p>
    </div>
</body>
</html>
