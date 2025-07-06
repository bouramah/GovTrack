<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📧 EMAILS STOCKÉS EN MÉMOIRE\n";
echo "============================\n\n";

// Récupérer les emails stockés en mémoire
$emails = \Illuminate\Support\Facades\Mail::getSymfonyTransport()->messages();

if (empty($emails)) {
    echo "Aucun email stocké en mémoire.\n";
    echo "Lancez d'abord le test des listeners pour générer des emails.\n";
} else {
    echo "Nombre d'emails trouvés : " . count($emails) . "\n\n";

    foreach ($emails as $index => $email) {
        echo "📧 EMAIL #" . ($index + 1) . "\n";
        echo "===============\n";

        // Destinataire
        $recipients = $email->getTo();
        foreach ($recipients as $address => $name) {
            echo "À : {$address}\n";
        }

        // Sujet
        echo "Sujet : " . $email->getSubject() . "\n";

        // Contenu HTML
        $htmlContent = $email->getHtmlBody();
        if ($htmlContent) {
            echo "\nContenu HTML :\n";
            echo "--------------\n";
            echo $htmlContent . "\n";
        }

        // Contenu texte
        $textContent = $email->getTextBody();
        if ($textContent) {
            echo "\nContenu texte :\n";
            echo "---------------\n";
            echo $textContent . "\n";
        }

        echo "\n" . str_repeat("=", 50) . "\n\n";
    }
}
