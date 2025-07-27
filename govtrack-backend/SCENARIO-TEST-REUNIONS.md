# 🧪 Scénario de Test - Module Réunions

## 📋 **Scénario : CODIR Mensuel - Suivi des Projets Stratégiques**

### 🎯 **Objectif du Test**
Tester le cycle complet de gestion d'une réunion CODIR avec suivi des projets stratégiques, incluant la création, la planification, l'exécution et le suivi des décisions.

### 👥 **Personnages de Test**
- **Admin** (ID: 97) : `admin@govtrack.com` / `password123`
- **Directeur** (ID: 98) : `directeur@govtrack.com` / `password123`
- **Chef Projet** (ID: 99) : `chef-projet@govtrack.com` / `password123`
- **Analyste** (ID: 100) : `analyste@govtrack.com` / `password123`

---

## 🔄 **Étapes du Scénario**

### **Étape 1 : Configuration Initiale (Admin)**
**POST** `/api/reunion/types`

```json
{
    "nom": "CODIR",
    "description": "Comité de Direction",
    "couleur": "#1f2937",
    "icone": "users",
    "actif": true,
    "ordre": 1,
    "niveau_complexite": "COMPLEXE",
    "fonctionnalites_actives": {
        "objectifs_multiples": true,
        "difficultes_par_entite": true,
        "workflow_validation": true,
        "pv_validation": true,
        "quorum_obligatoire": true
    },
    "configuration_notifications": {
        "rappel_24h": true,
        "rappel_1h": true,
        "confirmation_presence": true,
        "pv_disponible": true
    }
}
```

**Résultat attendu :** Type de réunion créé avec ID retourné

---

### **Étape 2 : Création de la Série de Réunions (Admin)**
**POST** `/api/reunion/series`

```json
{
    "nom": "CODIR Mensuel 2025",
    "description": "Série de réunions CODIR mensuelles pour le suivi des projets stratégiques",
    "type_reunion_id": 1,
    "periodicite": "MENSUELLE",
    "jour_mois": 6,
    "heure_debut": "09:00:00",
    "duree_minutes": 120,
    "lieu_defaut": "Salle de réunion principale",
    "actif": true,
    "date_debut_serie": "2025-01-06",
    "date_fin_serie": "2025-12-29",
    "suspendue": false,
    "configuration_recurrence": {
        "exclusion_jours_feries": true,
        "report_automatique": true
    }
}
```

**Résultat attendu :** Série créée avec ID retourné

---

### **Étape 3 : Planification de la Réunion (Directeur)**
**POST** `/api/reunions`

```json
{
    "titre": "CODIR Janvier 2025 - Suivi Projets Stratégiques",
    "description": "Réunion mensuelle de suivi des projets stratégiques de l'organisation",
    "type_reunion_id": 1,
    "niveau_complexite_actuel": "COMPLEXE",
    "date_debut": "2025-01-06 09:00:00",
    "date_fin": "2025-01-06 11:00:00",
    "lieu": "Salle de réunion principale",
    "type_lieu": "PHYSIQUE",
    "lien_virtuel": null,
    "periodicite": "MENSUELLE",
    "serie_id": 1,
    "suspendue": false,
    "fonctionnalites_actives": {
        "objectifs_multiples": true,
        "difficultes_par_entite": true
    },
    "quorum_minimum": 3,
    "ordre_du_jour_type": "EXPLICITE",
    "statut": "PLANIFIEE"
}
```

**Résultat attendu :** Réunion créée avec ID retourné

---

### **Étape 4 : Ajout des Participants (Directeur)**
**POST** `/api/reunions/{reunion_id}/participants`

```json
{
    "participants": [
        {
            "user_id": 98,
            "role": "PRESIDENT",
            "type": "PERMANENT",
            "statut_presence": "CONFIRME",
            "notifications_actives": {
                "rappel_24h": true,
                "rappel_1h": true
            }
        },
        {
            "user_id": 99,
            "role": "PARTICIPANT",
            "type": "PERMANENT",
            "statut_presence": "CONFIRME",
            "notifications_actives": {
                "rappel_24h": true
            }
        },
        {
            "user_id": 100,
            "role": "SECRETAIRE",
            "type": "PERMANENT",
            "statut_presence": "CONFIRME",
            "notifications_actives": {
                "rappel_24h": true,
                "rappel_1h": true
            }
        }
    ]
}
```

**Résultat attendu :** Participants ajoutés avec succès

---

### **Étape 5 : Création de l'Ordre du Jour (Directeur)**
**POST** `/api/reunions/{reunion_id}/ordre-jour`

```json
{
    "points": [
        {
            "ordre": 1,
            "titre": "Ouverture et adoption de l'ordre du jour",
            "description": "Ouverture de la réunion et adoption de l'ordre du jour",
            "type": "POINT_DIVERS",
            "duree_estimee_minutes": 5,
            "entite_proposante_id": null,
            "responsable_id": 98,
            "projet_id": null,
            "statut": "PLANIFIE",
            "niveau_detail_requis": "SIMPLE"
        },
        {
            "ordre": 2,
            "titre": "Suivi du projet Infrastructure Numérique",
            "description": "Point de situation sur l'avancement du projet de modernisation de l'infrastructure",
            "type": "SUIVI_PROJETS",
            "duree_estimee_minutes": 45,
            "entite_proposante_id": 5,
            "responsable_id": 99,
            "projet_id": 1,
            "statut": "PLANIFIE",
            "niveau_detail_requis": "DETAILLE"
        },
        {
            "ordre": 3,
            "titre": "Questions diverses",
            "description": "Points divers et questions des participants",
            "type": "POINT_DIVERS",
            "duree_estimee_minutes": 10,
            "entite_proposante_id": null,
            "responsable_id": 98,
            "projet_id": null,
            "statut": "PLANIFIE",
            "niveau_detail_requis": "SIMPLE"
        },
        {
            "ordre": 4,
            "titre": "Clôture",
            "description": "Clôture de la réunion et prochaine réunion",
            "type": "POINT_DIVERS",
            "duree_estimee_minutes": 5,
            "entite_proposante_id": null,
            "responsable_id": 98,
            "projet_id": null,
            "statut": "PLANIFIE",
            "niveau_detail_requis": "SIMPLE"
        }
    ]
}
```

**Résultat attendu :** Ordre du jour créé avec points détaillés

---

### **Étape 6 : Création du Sujet Principal (Chef Projet)**
**POST** `/api/reunions/ordre-jour/{ordre_jour_id}/sujets`

```json
{
    "titre": "Suivi du projet Infrastructure Numérique",
    "description": "Point de situation sur l'avancement du projet de modernisation de l'infrastructure",
    "difficulte_globale": "Pénurie de développeurs seniors et contraintes budgétaires",
    "recommandation": "Accélérer le recrutement des ressources techniques et réviser le budget",
    "statut": "EN_COURS_DE_RESOLUTION",
    "commentaire": "Le projet progresse bien mais nécessite des ressources supplémentaires",
    "pieces_jointes": ["rapport_avancement.pdf", "planning_projet.xlsx"],
    "projet_id": 1,
    "entite_id": 5,
    "niveau_detail": "DETAILLE",
    "objectifs_actifs": true,
    "difficultes_actives": true
}
```

**Résultat attendu :** Sujet créé avec ID retourné

---

### **Étape 7 : Ajout d'Objectifs au Sujet (Chef Projet)**
**POST** `/api/reunions/sujets/{sujet_id}/objectifs`

```json
{
    "objectifs": [
        {
            "titre": "Finaliser la phase de conception",
            "description": "Terminer la conception technique de l'infrastructure",
            "cible": "Conception technique complète validée",
            "taux_realisation": 75,
            "pourcentage_decaissement": 60.00,
            "date_objectif": "2025-02-15",
            "statut": "EN_COURS",
            "ordre": 1,
            "actif": true
        },
        {
            "titre": "Recruter l'équipe technique",
            "description": "Compléter l'équipe avec 3 développeurs seniors",
            "cible": "3 développeurs seniors recrutés et intégrés",
            "taux_realisation": 40,
            "pourcentage_decaissement": 30.00,
            "date_objectif": "2025-01-31",
            "statut": "EN_RETARD",
            "ordre": 2,
            "actif": true
        }
    ]
}
```

**Résultat attendu :** Objectifs ajoutés au sujet

---

### **Étape 8 : Ajout de Difficultés (Chef Projet)**
**POST** `/api/reunions/objectifs/{objectif_id}/difficultes`

```json
{
    "difficultes": [
        {
            "entite_id": 5,
            "description_difficulte": "Difficulté à recruter des développeurs expérimentés",
            "niveau_difficulte": "ELEVE",
            "impact": "Retard de 2 semaines sur le planning de recrutement",
            "solution_proposee": "Utiliser LinkedIn et les réseaux professionnels",
            "statut": "EN_COURS_RESOLUTION"
        },
        {
            "entite_id": 5,
            "description_difficulte": "Contraintes budgétaires sur les salaires",
            "niveau_difficulte": "MOYEN",
            "impact": "Limitation des offres salariales",
            "solution_proposee": "Révision du budget avec la direction",
            "statut": "IDENTIFIEE"
        }
    ]
}
```

**Résultat attendu :** Difficultés ajoutées à l'objectif

---

### **Étape 9 : Prise de Décisions (Directeur)**
**POST** `/api/reunions/sujets/{sujet_id}/decisions`

```json
{
    "decisions": [
        {
            "reunion_id": 1,
            "texte_decision": "Autoriser le recrutement de 3 développeurs seniors avec un budget supplémentaire de 50k€",
            "type": "DEFINITIVE",
            "responsables_ids": [99],
            "date_limite": "2025-01-20",
            "statut": "EN_COURS",
            "priorite": "ELEVEE",
            "commentaire": "Budget supplémentaire approuvé pour accélérer le projet"
        },
        {
            "reunion_id": 1,
            "texte_decision": "Ajuster le planning pour tenir compte des retards identifiés",
            "type": "DEFINITIVE",
            "responsables_ids": [99, 100],
            "date_limite": "2025-02-01",
            "statut": "EN_ATTENTE",
            "priorite": "NORMALE",
            "commentaire": "Nouveau planning à présenter dans 2 semaines"
        }
    ]
}
```

**Résultat attendu :** Décisions prises et enregistrées

---

### **Étape 10 : Création d'Actions (Chef Projet)**
**POST** `/api/reunions/decisions/{decision_id}/actions`

```json
{
    "actions": [
        {
            "titre": "Lancer les offres d'emploi",
            "description": "Publier les annonces pour les postes de développeurs",
            "responsable_id": 99,
            "date_limite": "2025-01-15",
            "statut": "EN_COURS",
            "commentaire": "Utiliser LinkedIn et les réseaux professionnels",
            "pieces_jointes": ["offre_emploi_dev_senior.pdf"],
            "priorite": "CRITIQUE",
            "progression": 25
        },
        {
            "titre": "Réviser le budget",
            "description": "Préparer la demande de budget supplémentaire",
            "responsable_id": 99,
            "date_limite": "2025-01-20",
            "statut": "A_FAIRE",
            "commentaire": "Documenter les coûts supplémentaires",
            "pieces_jointes": [],
            "priorite": "ELEVEE",
            "progression": 0
        }
    ]
}
```

**Résultat attendu :** Actions créées et assignées

---

### **Étape 11 : Simulation de la Réunion (Directeur)**
**PUT** `/api/reunions/{reunion_id}`

```json
{
    "statut": "EN_COURS",
    "date_debut": "2025-01-06 09:05:00"
}
```

**Résultat attendu :** Statut de la réunion mis à jour

---

### **Étape 12 : Rédaction du PV (Analyste)**
**POST** `/api/reunions/{reunion_id}/pv`

```json
{
    "contenu": "PV de la réunion CODIR du 6 janvier 2025\n\nPrésents : Directeur, Chef Projet, Analyste\n\nPoints abordés :\n1. Suivi du projet Infrastructure Numérique\n2. Décisions prises :\n   - Approbation du recrutement de 3 développeurs\n   - Révision du planning projet\n\nProchaine réunion : 3 février 2025",
    "redige_par_id": 100,
    "redige_le": "2025-01-06 11:30:00",
    "version": 1,
    "statut": "BROUILLON",
    "commentaire_validation": "PV en cours de finalisation"
}
```

**Résultat attendu :** PV créé avec statut brouillon

---

### **Étape 13 : Validation du PV (Directeur)**
**PUT** `/api/reunions/{reunion_id}/pv/{pv_id}`

```json
{
    "statut": "VALIDE",
    "valide_par_id": 98,
    "valide_le": "2025-01-06 14:00:00",
    "commentaire_validation": "PV approuvé et validé"
}
```

**Résultat attendu :** PV validé par le directeur

---

### **Étape 14 : Clôture de la Réunion (Directeur)**
**PUT** `/api/reunions/{reunion_id}`

```json
{
    "statut": "TERMINEE",
    "date_fin": "2025-01-06 11:15:00"
}
```

**Résultat attendu :** Réunion marquée comme terminée

---

### **Étape 15 : Vérification des Analytics (Admin)**
**GET** `/api/reunions/analytics`

**Résultat attendu :** Statistiques de la réunion avec :
- Durée réelle vs prévue
- Nombre de décisions prises
- Nombre d'actions créées
- Statut des participants

---

## ✅ **Critères de Validation**

### **Fonctionnels**
- ✅ Toutes les étapes s'exécutent sans erreur
- ✅ Les données sont correctement enregistrées
- ✅ Les relations entre entités sont respectées
- ✅ Les permissions sont vérifiées

### **Techniques**
- ✅ Codes de retour HTTP appropriés (200, 201, 400, 401, 403)
- ✅ Validation des données d'entrée
- ✅ Gestion des erreurs
- ✅ Performance acceptable (< 2s par requête)

### **Métier**
- ✅ Workflow de réunion respecté
- ✅ Traçabilité des décisions
- ✅ Suivi des actions
- ✅ Génération du PV

---

## 🚀 **Instructions d'Exécution**

1. **Démarrer le serveur Laravel :**
   ```bash
   php artisan serve
   ```

2. **Vérifier les données de test :**
   ```bash
   php scripts/prepare-test-data.php
   ```

3. **Importer la collection Postman :**
   - Utiliser `GovTrack-API-Complete.postman_collection.json`

4. **Configurer l'environnement Postman :**
   - Base URL: `http://localhost:8000`
   - Token: Obtenir via login avec `admin@govtrack.com`

5. **Exécuter le scénario étape par étape**

---

## 📊 **Métriques de Succès**

- **Temps total d'exécution :** < 30 minutes
- **Taux de succès :** 100% des étapes
- **Couvrage fonctionnel :** 100% des fonctionnalités testées
- **Qualité des données :** Cohérence et intégrité vérifiées

---

## 🔧 **Dépannage**

### **Erreurs Courantes**
- **401 Unauthorized :** Vérifier le token d'authentification
- **422 Validation Error :** Vérifier le format des données JSON
- **404 Not Found :** Vérifier les IDs des ressources
- **500 Server Error :** Vérifier les logs Laravel

### **Logs à Surveiller**
```bash
tail -f storage/logs/laravel.log
```

---

## 📝 **Notes de Test**

- Tous les IDs utilisés correspondent aux données créées par le script
- Les dates sont cohérentes avec le scénario (janvier 2025)
- Les permissions sont configurées selon les rôles définis
- Le projet de test "Infrastructure Numérique" est référencé

**🎯 Objectif :** Valider le cycle complet de gestion des réunions avec un scénario réaliste et complet. 
 