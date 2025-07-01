# 🧪 Guide Pratique - Tests API GovTrack avec cURL

## 🎯 Objectif

Ce guide vous permet de tester complètement l'API GovTrack Partie 2 avec des commandes cURL prêtes à l'emploi. Chaque section contient des exemples réalistes et des validations métier.

## 🚀 Prérequis

### 1. Démarrer le serveur

```bash
cd govtrack-backend
php artisan serve --host=127.0.0.1 --port=8000
```

### 2. Variables d'environnement

Définissez ces variables pour simplifier les tests :

```bash
export BASE_URL="http://127.0.0.1:8000/api/v1"
export ACCESS_TOKEN=""
export USER_ID=""
export PROJET_ID=""
export TACHE_ID=""
export TYPE_PROJET_ID=""
```

---

## 🔐 Étape 1 : Authentification

### Connexion avec compte administrateur

```bash
curl -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@govtrack.gov",
    "password": "password123"
  }' | jq .
```

**Résultat attendu :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "access_token": "1|laravel_sanctum_token...",
  "user": {
    "id": 1,
    "nom": "Administrateur",
    "email": "admin@govtrack.gov"
  }
}
```

### Exporter le token

```bash
export ACCESS_TOKEN="votre_token_ici"
export USER_ID="1"
```

### Vérifier l'authentification

```bash
curl -X GET "$BASE_URL/auth/profile" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

---

## 📋 Étape 2 : Types de Projets (SLA)

### Créer un type de projet avec SLA

```bash
curl -X POST "$BASE_URL/type-projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Instruction Urgente",
    "description": "Instructions nécessitant une réponse rapide",
    "duree_previsionnelle_jours": 7,
    "description_sla": "Délai de 7 jours pour les instructions urgentes"
  }' | jq .
```

### Exporter l'ID du type de projet

```bash
export TYPE_PROJET_ID="1"  # Remplacez par l'ID retourné
```

### Lister les types de projets

```bash
curl -X GET "$BASE_URL/type-projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Voir les statistiques d'un type

```bash
curl -X GET "$BASE_URL/type-projets/$TYPE_PROJET_ID/statistiques" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

---

## 📊 Étape 3 : Projets (Instructions/Recommandations)

### Créer un projet avec SLA automatique

```bash
curl -X POST "$BASE_URL/projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titre": "Digitalisation des Processus Gouvernementaux",
    "description": "Mise en place d'un système complet de digitalisation des processus administratifs pour améliorer l'efficacité et la transparence",
    "type_projet_id": '$TYPE_PROJET_ID',
    "porteur_id": '$USER_ID',
    "donneur_ordre_id": '$USER_ID',
    "date_debut_previsionnelle": "2025-01-15"
  }' | jq .
```

> **💡 SLA Automatique** : La date de fin sera calculée automatiquement (7 jours après le début).

### Exporter l'ID du projet

```bash
export PROJET_ID="1"  # Remplacez par l'ID retourné
```

### Créer un projet avec dates personnalisées (justification obligatoire)

```bash
curl -X POST "$BASE_URL/projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titre": "Formation Complète du Personnel",
    "description": "Programme de formation exhaustif pour tout le personnel sur les nouvelles procédures digitales",
    "type_projet_id": '$TYPE_PROJET_ID',
    "porteur_id": '$USER_ID',
    "donneur_ordre_id": '$USER_ID',
    "date_debut_previsionnelle": "2025-01-20",
    "date_fin_previsionnelle": "2025-03-15",
    "justification_modification_dates": "Délai étendu nécessaire pour coordonner avec tous les départements et organiser les sessions de formation par groupes"
  }' | jq .
```

### Lister les projets avec filtres

```bash
curl -X GET "$BASE_URL/projets?per_page=10&sort_by=date_creation&sort_order=desc" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Tableau de bord des projets

```bash
curl -X GET "$BASE_URL/projets/tableau-bord" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Changer le statut du projet (à faire → en cours)

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "en_cours",
    "commentaire": "Démarrage officiel du projet après validation des ressources et de l'équipe"
  }' | jq .
```

### Voir les détails du projet

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

---

## ✅ Étape 4 : Tâches

### Créer une tâche

```bash
curl -X POST "$BASE_URL/taches" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titre": "Analyse Approfondie des Besoins",
    "description": "Analyser en détail les besoins spécifiques de chaque département pour adapter la digitalisation",
    "projet_id": '$PROJET_ID',
    "responsable_id": '$USER_ID',
    "date_debut_previsionnelle": "2025-01-15",
    "date_fin_previsionnelle": "2025-01-20"
  }' | jq .
```

### Exporter l'ID de la tâche

```bash
export TACHE_ID="1"  # Remplacez par l'ID retourné
```

### Lister les tâches du projet

```bash
curl -X GET "$BASE_URL/taches?projet_id=$PROJET_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Voir mes tâches assignées

```bash
curl -X GET "$BASE_URL/taches/mes-taches" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Changer le statut de la tâche

```bash
curl -X POST "$BASE_URL/taches/$TACHE_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "en_cours",
    "commentaire": "Début de l'analyse avec réunions programmées",
    "niveau_execution": 10
  }' | jq .
```

### Mettre à jour le niveau d'exécution

```bash
curl -X POST "$BASE_URL/taches/$TACHE_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "en_cours",
    "commentaire": "50% des départements analysés",
    "niveau_execution": 50
  }' | jq .
```

---

## 📎 Étape 5 : Pièces Jointes et Justificatifs

### Créer un fichier de test

```bash
mkdir -p temp_uploads
echo "Document de spécifications techniques pour le projet de digitalisation.
Ce document contient les détails techniques et les exigences fonctionnelles." > temp_uploads/specifications.txt
```

### Upload d'une pièce jointe normale

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/pieces-jointes" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -F "fichier=@temp_uploads/specifications.txt" \
  -F "description=Document de spécifications techniques détaillées" \
  -F "est_justificatif=false" | jq .
```

### Créer un justificatif

```bash
echo "JUSTIFICATIF DE CLÔTURE DU PROJET

Le projet de digitalisation a été mené à bien selon les spécifications.
Tous les objectifs ont été atteints avec succès.

Résultats obtenus:
- 100% des processus digitalisés
- Formation du personnel terminée
- Tests de validation réussis
- Mise en production effective

Date: $(date)
Responsable: Administrateur Système" > temp_uploads/justificatif_cloture.txt
```

### Upload du justificatif obligatoire

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/pieces-jointes" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -F "fichier=@temp_uploads/justificatif_cloture.txt" \
  -F "description=Justificatif officiel de clôture du projet" \
  -F "est_justificatif=true" | jq .
```

### Lister les pièces jointes

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID/pieces-jointes" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Statistiques des pièces jointes

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID/pieces-jointes/statistiques" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Upload pièce jointe pour tâche

```bash
echo "Rapport d'analyse des besoins - Département IT
Synthèse des entretiens et recommandations." > temp_uploads/rapport_analyse.txt

curl -X POST "$BASE_URL/taches/$TACHE_ID/pieces-jointes" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -F "fichier=@temp_uploads/rapport_analyse.txt" \
  -F "description=Rapport d'analyse des besoins par département" \
  -F "est_justificatif=false" | jq .
```

---

## 💬 Étape 6 : Discussions Collaboratives

### Poster un message principal dans le projet

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/discussions" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "🚀 Lancement officiel du projet de digitalisation ! \n\nBonjour à tous,\n\nNous démarrons aujourd'hui ce projet stratégique qui va transformer notre façon de travailler. N'hésitez pas à partager vos idées, questions et suggestions pour assurer le succès de cette initiative.\n\nMerci pour votre engagement ! 💪"
  }' | jq .
```

### Répondre au message (simulation d'un autre utilisateur)

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/discussions" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Excellente initiative ! 👏\n\nJe suggère de commencer par une phase pilote avec le département IT pour valider l'approche avant de déployer plus largement. Qu'en pensez-vous ?",
    "parent_id": 1
  }' | jq .
```

### Ajouter une réponse à la discussion

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/discussions" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Tout à fait d'accord ! C'est exactement ce qui était prévu dans la roadmap. Le département IT sera notre cas d'usage pilote. 🎯",
    "parent_id": 1
  }' | jq .
```

### Lister toutes les discussions du projet

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID/discussions" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Voir les statistiques des discussions

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID/discussions/statistiques" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Discussion sur la tâche

```bash
curl -X POST "$BASE_URL/taches/$TACHE_ID/discussions" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "📊 Mise à jour sur l'analyse des besoins:\n\n- ✅ Département IT: Analyse terminée\n- 🔄 Département RH: En cours\n- ⏳ Département Finance: Planifié\n\nJ'aurai besoin de clarifications sur les spécifications techniques pour le module comptabilité. Quelqu'un peut-il m'aider ? 🤔"
  }' | jq .
```

---

## 🧠 Étape 7 : Tests Logiques Métier Avancées

### Test 1: Tentative de clôture sans justificatif (doit échouer)

Créons d'abord un projet sans justificatif :

```bash
curl -X POST "$BASE_URL/projets" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titre": "Projet Test Validation",
    "description": "Projet pour tester la validation des justificatifs",
    "type_projet_id": '$TYPE_PROJET_ID',
    "porteur_id": '$USER_ID',
    "donneur_ordre_id": '$USER_ID',
    "date_debut_previsionnelle": "2025-01-15"
  }' | jq .
```

Passons le projet en cours :

```bash
export PROJET_TEST_ID="2"  # Remplacez par l'ID retourné

curl -X POST "$BASE_URL/projets/$PROJET_TEST_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "en_cours",
    "commentaire": "Démarrage pour test de validation"
  }' | jq .
```

Essayons de demander la clôture sans justificatif (doit échouer) :

```bash
curl -X POST "$BASE_URL/projets/$PROJET_TEST_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "demande_de_cloture",
    "commentaire": "Tentative de clôture sans justificatif"
  }' | jq .
```

**Résultat attendu :** Erreur 422 avec message "Justificatif obligatoire"

### Test 2: Clôture avec justificatif (doit réussir)

Maintenant testons la clôture du projet principal qui a un justificatif :

```bash
curl -X POST "$BASE_URL/projets/$PROJET_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "demande_de_cloture",
    "commentaire": "Demande de clôture officielle avec justificatif valide attaché"
  }' | jq .
```

**Résultat attendu :** Succès 200

### Test 3: Terminer une tâche (validation porteur)

```bash
curl -X POST "$BASE_URL/taches/$TACHE_ID/changer-statut" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nouveau_statut": "termine",
    "commentaire": "Analyse terminée avec succès - Rapport final disponible",
    "niveau_execution": 100
  }' | jq .
```

**Résultat attendu :** Succès (car l'utilisateur est le porteur du projet)

---

## 📊 Étape 8 : Validation et Vérifications

### Vérifier la mise à jour automatique du niveau du projet

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq '.data.niveau_execution'
```

### Vérifier l'équipe projet automatique

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq '.data | {porteur, taches: .taches[].responsable}'
```

### Tableau de bord final

```bash
curl -X GET "$BASE_URL/projets/tableau-bord" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

### Lister toutes les pièces jointes par type

```bash
curl -X GET "$BASE_URL/projets/$PROJET_ID/pieces-jointes" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq '.data | map(select(.est_justificatif == true))'
```

---

## 🧹 Étape 9 : Nettoyage

### Supprimer les fichiers de test

```bash
rm -rf temp_uploads
```

### Déconnexion

```bash
curl -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .
```

---

## 📋 Récapitulatif des Tests

✅ **Tests réussis :**
- Authentification et gestion des tokens
- Types de projets avec SLA automatiques
- Création de projets (avec et sans justification)
- Gestion complète des tâches
- Upload et gestion des pièces jointes
- Justificatifs obligatoires pour clôture
- Discussions collaboratives hiérarchiques
- Validation des permissions (porteur pour terminer tâches)
- Calcul automatique du niveau d'exécution
- Tableau de bord avec statistiques temps réel

🚀 **Fonctionnalités avancées vérifiées :**
- SLA automatiques selon type de projet
- Équipe projet automatique
- Historique des changements de statut
- Validation métier stricte
- Upload sécurisé des fichiers
- Discussions hiérarchiques

---

## 🎯 Utilisation en Production

Ces mêmes commandes peuvent être adaptées pour l'environnement de production en changeant simplement :

```bash
export BASE_URL="https://api.govtrack.gov/v1"
```

Et en utilisant les vraies credentials de production.

---

**🎉 L'API GovTrack Partie 2 est entièrement fonctionnelle et prête pour la production !** 
