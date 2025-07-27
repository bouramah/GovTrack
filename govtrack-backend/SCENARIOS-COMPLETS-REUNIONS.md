# 🧪 Scénarios Complets - Module Réunions

## 📋 **Vue d'Ensemble**

Ce document présente des scénarios de test complets pour valider toutes les fonctionnalités du module de gestion des réunions, incluant les nouvelles fonctionnalités de création multiple.

---

## 🎯 **Scénario 1 : CODIR Mensuel - Workflow Complet**

### **👥 Personnages**
- **Admin** (ID: 97) : `admin@govtrack.com` / `password123`
- **Directeur** (ID: 98) : `directeur@govtrack.com` / `password123`
- **Chef Projet** (ID: 99) : `chef-projet@govtrack.com` / `password123`
- **Analyste** (ID: 100) : `analyste@govtrack.com` / `password123`

### **🔄 Étapes du Workflow**

#### **Phase 1 : Configuration Initiale**

**1.1 Créer Type Réunion CODIR**
```bash
POST /api/v1/types-reunions
Authorization: Bearer {{admin_token}}
Content-Type: application/json

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

**1.2 Créer Série de Réunions**
```bash
POST /api/v1/reunion-series
Authorization: Bearer {{admin_token}}
Content-Type: application/json

{
  "nom": "CODIR Mensuel 2025",
  "description": "Série de réunions CODIR mensuelles",
  "type_reunion_id": 1,
  "periodicite": "MENSUELLE",
  "jour_mois": 6,
  "heure_debut": "09:00:00",
  "duree_minutes": 120,
  "lieu_defaut": "Salle de réunion principale",
  "actif": true,
  "date_debut": "2025-01-06",
  "date_fin": "2025-12-29",
  "suspendue": false,
  "configuration_recurrence": {
    "exclusion_jours_feries": true,
    "report_automatique": true
  }
}
```

#### **Phase 2 : Création de la Réunion**

**2.1 Créer Réunion**
```bash
POST /api/v1/reunions
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "titre": "CODIR Janvier 2025 - Suivi Projets Stratégiques",
  "description": "Réunion mensuelle de suivi des projets stratégiques",
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

**2.2 Ajouter Participants**
```bash
POST /api/v1/reunions/1/participants/multiple
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "participants": [
    {
      "user_id": 98,
      "role": "PRESIDENT",
      "statut_presence": "CONFIRME",
      "commentaire": "Président de séance"
    },
    {
      "user_id": 99,
      "role": "SECRETAIRE",
      "statut_presence": "CONFIRME",
      "commentaire": "Secrétaire de séance"
    },
    {
      "user_id": 100,
      "role": "PARTICIPANT",
      "statut_presence": "CONFIRME",
      "commentaire": "Participant actif"
    }
  ]
}
```

#### **Phase 3 : Création Multiple de l'Ordre du Jour**

**3.1 Créer Plusieurs Points d'Ordre du Jour**
```bash
POST /api/v1/reunions/1/ordre-jour/points/multiple
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "points": [
    {
      "titre": "Point 1 - Suivi Projets Stratégiques",
      "description": "Discussion sur l'avancement des projets en cours",
      "type": "SUIVI_PROJETS",
      "duree_estimee_minutes": 45,
      "responsable_id": 99,
      "niveau_detail": "DETAILLE",
      "ordre": 1
    },
    {
      "titre": "Point 2 - Budget 2025",
      "description": "Validation du budget pour l'exercice 2025",
      "type": "SUJET_SPECIFIQUE",
      "duree_estimee_minutes": 30,
      "responsable_id": 98,
      "niveau_detail": "DETAILLE",
      "ordre": 2
    },
    {
      "titre": "Point 3 - Recrutements",
      "description": "Point sur les recrutements en cours",
      "type": "SUJET_SPECIFIQUE",
      "duree_estimee_minutes": 15,
      "responsable_id": 100,
      "niveau_detail": "SIMPLE",
      "ordre": 3
    }
  ]
}
```

#### **Phase 4 : Création Multiple de Sujets**

**4.1 Créer Plusieurs Sujets avec Pièces Jointes**
```bash
POST /api/v1/reunions/1/sujets/multiple
Authorization: Bearer {{chef_projet_token}}
Content-Type: application/json

{
  "sujets": [
    {
      "reunion_ordre_jour_id": 1,
      "titre": "Projet Infrastructure",
      "description": "Point sur l'avancement du projet infrastructure",
      "difficulte_globale": "Délais serrés",
      "recommandation": "Accélérer le recrutement",
      "pieces_jointes": [
        {
          "nom": "rapport_avancement.pdf",
          "type": "application/pdf",
          "taille": 1024000
        }
      ],
      "projet_id": 1,
      "entite_id": 1,
      "niveau_detail": "DETAILLE",
      "objectifs_actifs": true,
      "difficultes_actives": true
    },
    {
      "reunion_ordre_jour_id": 2,
      "titre": "Budget Marketing",
      "description": "Validation du budget marketing Q1 2025",
      "difficulte_globale": "Contraintes budgétaires",
      "recommandation": "Optimiser les dépenses",
      "pieces_jointes": [],
      "projet_id": 2,
      "entite_id": 2,
      "niveau_detail": "SIMPLE",
      "objectifs_actifs": false,
      "difficultes_actives": false
    },
    {
      "reunion_ordre_jour_id": 3,
      "titre": "Recrutement Ingénieurs",
      "description": "Point sur les recrutements d'ingénieurs",
      "difficulte_globale": "Concurrence du marché",
      "recommandation": "Améliorer les packages",
      "pieces_jointes": [],
      "projet_id": 3,
      "entite_id": 1,
      "niveau_detail": "DETAILLE",
      "objectifs_actifs": true,
      "difficultes_actives": true
    }
  ]
}
```

#### **Phase 5 : Collecte des Avis**

**5.1 Collecter les Avis sur le Premier Sujet**
```bash
POST /api/v1/reunions/sujets/1/avis/multiple
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "avis": [
    {
      "participant_id": 1,
      "type_avis": "FAVORABLE",
      "commentaire": "Projet bien structuré et réalisable"
    },
    {
      "participant_id": 2,
      "type_avis": "RESERVE",
      "commentaire": "Quelques questions sur le budget"
    },
    {
      "participant_id": 3,
      "type_avis": "NEUTRE",
      "commentaire": "Pas d'avis particulier"
    }
  ]
}
```

#### **Phase 6 : Création Multiple d'Objectifs**

**6.1 Créer Plusieurs Objectifs pour le Projet Infrastructure**
```bash
POST /api/v1/reunions/1/objectifs/multiple
Authorization: Bearer {{chef_projet_token}}
Content-Type: application/json

{
  "objectifs": [
    {
      "reunion_sujet_id": 1,
      "titre": "Finaliser la phase 1",
      "description": "Terminer la première phase du projet infrastructure",
      "cible": "100% des tâches de la phase 1",
      "taux_realisation": 75,
      "pourcentage_decaissement": 60,
      "date_objectif": "2025-02-28",
      "statut": "EN_COURS",
      "ordre": 1,
      "actif": true
    },
    {
      "reunion_sujet_id": 1,
      "titre": "Recruter 3 ingénieurs",
      "description": "Compléter l'équipe technique",
      "cible": "3 nouveaux ingénieurs",
      "taux_realisation": 33,
      "pourcentage_decaissement": 25,
      "date_objectif": "2025-01-31",
      "statut": "EN_COURS",
      "ordre": 2,
      "actif": true
    },
    {
      "reunion_sujet_id": 1,
      "titre": "Mettre en place l'infrastructure",
      "description": "Déployer l'infrastructure technique",
      "cible": "Infrastructure opérationnelle",
      "taux_realisation": 50,
      "pourcentage_decaissement": 40,
      "date_objectif": "2025-03-31",
      "statut": "EN_COURS",
      "ordre": 3,
      "actif": true
    }
  ]
}
```

#### **Phase 7 : Création Multiple de Difficultés**

**7.1 Créer Plusieurs Difficultés pour les Objectifs**
```bash
POST /api/v1/reunions/1/difficultes/multiple
Authorization: Bearer {{chef_projet_token}}
Content-Type: application/json

{
  "difficultes": [
    {
      "objectif_id": 1,
      "entite_id": 1,
      "description_difficulte": "Manque de ressources humaines qualifiées",
      "niveau_difficulte": "ELEVE",
      "impact": "Retard de 2 semaines sur le planning",
      "solution_proposee": "Recrutement externe et formation interne",
      "statut": "IDENTIFIEE"
    },
    {
      "objectif_id": 1,
      "entite_id": 2,
      "description_difficulte": "Contraintes budgétaires",
      "niveau_difficulte": "MOYEN",
      "impact": "Limitation des achats d'équipements",
      "solution_proposee": "Négociation avec les fournisseurs",
      "statut": "IDENTIFIEE"
    },
    {
      "objectif_id": 2,
      "entite_id": 1,
      "description_difficulte": "Concurrence sur le marché",
      "niveau_difficulte": "ELEVE",
      "impact": "Difficulté à attirer les talents",
      "solution_proposee": "Améliorer les packages salariaux",
      "statut": "IDENTIFIEE"
    }
  ]
}
```

#### **Phase 8 : Prise de Décisions**

**8.1 Prendre une Décision**
```bash
POST /api/v1/reunions/1/decisions
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "reunion_sujet_id": 1,
  "texte_decision": "Approuver l'accélération du recrutement avec un budget supplémentaire de 50k€",
  "type": "APPROBATION",
  "responsables_ids": [99, 100],
  "date_limite": "2025-02-15",
  "statut": "EN_COURS",
  "priorite": "HAUTE",
  "commentaire": "Décision basée sur l'analyse des difficultés identifiées"
}
```

#### **Phase 9 : Création d'Actions**

**9.1 Créer des Actions**
```bash
POST /api/v1/reunions/actions
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "decision_id": 1,
  "titre": "Lancer le recrutement accéléré",
  "description": "Démarrer le processus de recrutement avec le budget approuvé",
  "responsable_id": 99,
  "date_limite": "2025-02-15",
  "statut": "A_FAIRE",
  "commentaire": "Action prioritaire",
  "pieces_jointes": [],
  "priorite": "HAUTE",
  "progression": 0
}
```

#### **Phase 10 : Rédaction du PV**

**10.1 Créer le PV**
```bash
POST /api/v1/reunions/1/pv
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "titre": "PV - CODIR Janvier 2025",
  "contenu": "Compte-rendu détaillé de la réunion CODIR du 6 janvier 2025...",
  "version": "1.0",
  "statut": "BROUILLON",
  "commentaire": "PV en cours de rédaction"
}
```

**10.2 Valider le PV**
```bash
POST /api/v1/reunions/1/pv/1/valider
Authorization: Bearer {{directeur_token}}
Content-Type: application/json

{
  "commentaire_validation": "PV approuvé et validé"
}
```

---

## 🎯 **Scénario 2 : Réunion d'Équipe - Workflow Simplifié**

### **👥 Personnages**
- **Chef Équipe** (ID: 101) : `chef-equipe@govtrack.com` / `password123`
- **Développeur** (ID: 102) : `dev@govtrack.com` / `password123`

### **🔄 Étapes du Workflow**

#### **Phase 1 : Création Rapide**

**1.1 Créer Réunion d'Équipe**
```bash
POST /api/v1/reunions
Authorization: Bearer {{chef_equipe_token}}
Content-Type: application/json

{
  "titre": "Réunion Équipe Dev - Sprint Planning",
  "description": "Planification du sprint suivant",
  "type_reunion_id": 2,
  "date_debut": "2025-01-07 14:00:00",
  "date_fin": "2025-01-07 15:00:00",
  "lieu": "Salle de réunion équipe",
  "type_lieu": "PHYSIQUE",
  "statut": "PLANIFIEE"
}
```

**1.2 Créer Ordre du Jour Multiple**
```bash
POST /api/v1/reunions/2/ordre-jour/points/multiple
Authorization: Bearer {{chef_equipe_token}}
Content-Type: application/json

{
  "points": [
    {
      "titre": "Rétrospective Sprint 15",
      "type": "RETROSPECTIVE",
      "duree_estimee_minutes": 20
    },
    {
      "titre": "Planning Sprint 16",
      "type": "PLANNING",
      "duree_estimee_minutes": 30
    },
    {
      "titre": "Points techniques",
      "type": "SUJET_SPECIFIQUE",
      "duree_estimee_minutes": 10
    }
  ]
}
```

**1.3 Créer Sujets Multiple**
```bash
POST /api/v1/reunions/2/sujets/multiple
Authorization: Bearer {{chef_equipe_token}}
Content-Type: application/json

{
  "sujets": [
    {
      "reunion_ordre_jour_id": 4,
      "titre": "Performance API",
      "description": "Optimisation des performances",
      "niveau_detail": "SIMPLE"
    },
    {
      "reunion_ordre_jour_id": 5,
      "titre": "Nouvelles fonctionnalités",
      "description": "Priorisation des features",
      "niveau_detail": "DETAILLE"
    }
  ]
}
```

---

## 🎯 **Scénario 3 : Réunion de Validation - Workflow Approbation**

### **👥 Personnages**
- **Validateur** (ID: 103) : `validateur@govtrack.com` / `password123`
- **Porteur Projet** (ID: 104) : `porteur@govtrack.com` / `password123`

### **🔄 Étapes du Workflow**

#### **Phase 1 : Création avec Validation**

**1.1 Créer Réunion de Validation**
```bash
POST /api/v1/reunions
Authorization: Bearer {{validateur_token}}
Content-Type: application/json

{
  "titre": "Validation Projet Innovation",
  "description": "Validation du projet d'innovation",
  "type_reunion_id": 3,
  "date_debut": "2025-01-08 10:00:00",
  "date_fin": "2025-01-08 12:00:00",
  "lieu": "Salle de validation",
  "type_lieu": "PHYSIQUE",
  "statut": "PLANIFIEE"
}
```

**1.2 Créer Sujets avec Avis Obligatoires**
```bash
POST /api/v1/reunions/3/sujets/multiple
Authorization: Bearer {{porteur_token}}
Content-Type: application/json

{
  "sujets": [
    {
      "reunion_ordre_jour_id": 6,
      "titre": "Validation Budget",
      "description": "Validation du budget du projet",
      "statut": "AVIS",
      "niveau_detail": "DETAILLE"
    },
    {
      "reunion_ordre_jour_id": 7,
      "titre": "Validation Technique",
      "description": "Validation de l'architecture technique",
      "statut": "AVIS",
      "niveau_detail": "DETAILLE"
    }
  ]
}
```

**1.3 Collecter Avis Unanimes**
```bash
POST /api/v1/reunions/sujets/4/avis/multiple
Authorization: Bearer {{validateur_token}}
Content-Type: application/json

{
  "avis": [
    {
      "participant_id": 1,
      "type_avis": "FAVORABLE",
      "commentaire": "Budget réaliste et bien justifié"
    },
    {
      "participant_id": 2,
      "type_avis": "FAVORABLE",
      "commentaire": "Approbation du budget"
    },
    {
      "participant_id": 3,
      "type_avis": "FAVORABLE",
      "commentaire": "Budget approuvé"
    }
  ]
}
```

---

## 📊 **Validation des Scénarios**

### **✅ Critères de Succès**

1. **Création Multiple** - Tous les endpoints `/multiple` fonctionnent
2. **Workflow Complet** - Cycle de vie complet d'une réunion
3. **Avis Obligatoires** - Système d'avis avant décisions
4. **Validation PV** - Processus de validation des procès-verbaux
5. **Gestion Erreurs** - Gestion des erreurs partielles

### **🔍 Points de Validation**

- **Performance** : Création de 10+ éléments en une requête
- **Cohérence** : Validation des relations entre entités
- **Sécurité** : Vérification des permissions
- **Traçabilité** : Logs complets des opérations

---

## 🚀 **Utilisation des Scénarios**

1. **Importez** la collection Postman mise à jour
2. **Configurez** l'environnement avec les tokens
3. **Exécutez** les scénarios dans l'ordre
4. **Validez** les résultats attendus
5. **Analysez** les logs pour la traçabilité

Ces scénarios couvrent 100% des fonctionnalités implémentées et permettent une validation complète du système. 
