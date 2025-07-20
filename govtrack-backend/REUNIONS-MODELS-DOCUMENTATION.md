# 📋 Documentation des Modèles Eloquent - Module Réunions

## 🎯 Vue d'ensemble

Ce document décrit tous les modèles Eloquent créés pour le module de gestion des réunions dans GovTrack. Chaque modèle inclut des relations complètes, des constantes ENUM, des scopes de requête, et des accesseurs/mutateurs utiles.

## 📊 Modèles Créés (17 modèles)

### 1. **TypeReunion** - Types de réunions
**Fichier:** `app/Models/TypeReunion.php`

**Description:** Définit les types de réunions avec leurs niveaux de complexité et configurations.

**Constantes ENUM:**
- `NIVEAU_SIMPLE`, `NIVEAU_INTERMEDIAIRE`, `NIVEAU_COMPLEXE`
- `NIVEAUX` - Mapping des libellés

**Relations principales:**
- `gestionnaires()` - BelongsToMany avec User
- `membresPermanents()` - BelongsToMany avec User
- `series()` - HasMany vers ReunionSerie
- `reunions()` - HasMany vers Reunion
- `workflowConfigs()` - HasMany vers ReunionWorkflowConfig
- `validateursPV()` - HasMany vers TypeReunionValidateurPV
- `notificationConfigs()` - HasMany vers ReunionNotificationConfig

**Fonctionnalités:**
- Scopes: `actifs()`, `byNiveauComplexite()`, `ordered()`
- Accesseurs: `niveauComplexiteLibelle`, `niveauComplexiteCouleur`, `niveauComplexiteIcone`
- Méthodes: `hasFonctionnalite()`, `hasNotification()`

---

### 2. **ReunionSerie** - Séries de réunions récurrentes
**Fichier:** `app/Models/ReunionSerie.php`

**Description:** Gère les séries de réunions récurrentes avec périodicités.

**Constantes ENUM:**
- `PERIODICITE_HEBDOMADAIRE`, `PERIODICITE_BIHEBDOMADAIRE`, `PERIODICITE_MENSUELLE`
- `PERIODICITES` - Mapping des libellés

**Relations principales:**
- `typeReunion()` - BelongsTo vers TypeReunion
- `reunionsGenerees()` - HasMany vers ReunionGeneree
- `reunions()` - HasMany vers Reunion
- `createur()` - BelongsTo vers User
- `modificateur()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `actives()`, `nonSuspendues()`, `byPeriodicite()`, `byTypeReunion()`
- Accesseurs: `periodiciteLibelle`, `estEnCours`, `estTerminee`, `estAVenir`
- Méthodes: `dureeFormatee`, `heureFin`, `generationAutomatiqueActive`

---

### 3. **Reunion** - Réunions principales
**Fichier:** `app/Models/Reunion.php`

**Description:** Modèle principal pour les réunions avec toutes leurs propriétés.

**Constantes ENUM:**
- `NIVEAU_SIMPLE`, `NIVEAU_INTERMEDIAIRE`, `NIVEAU_COMPLEXE`
- `TYPE_LIEU_PHYSIQUE`, `TYPE_LIEU_VIRTUEL`, `TYPE_LIEU_HYBRIDE`
- `PERIODICITE_PONCTUELLE`, `PERIODICITE_HEBDOMADAIRE`, etc.
- `ORDRE_JOUR_EXPLICITE`, `ORDRE_JOUR_IMPLICITE`, `ORDRE_JOUR_HYBRIDE`
- `STATUT_PLANIFIEE`, `STATUT_EN_COURS`, `STATUT_TERMINEE`, `STATUT_ANNULEE`

**Relations principales:**
- `typeReunion()` - BelongsTo vers TypeReunion
- `serie()` - BelongsTo vers ReunionSerie
- `participants()` - HasMany vers ReunionParticipant
- `ordreJours()` - HasMany vers ReunionOrdreJour
- `decisions()` - HasMany vers ReunionDecision
- `pvs()` - HasMany vers ReunionPV
- `notifications()` - HasMany vers ReunionNotification
- `workflowExecutions()` - HasMany vers ReunionWorkflowExecution
- `participantsUsers()` - BelongsToMany vers User

**Fonctionnalités:**
- Scopes: `planifiees()`, `enCours()`, `terminees()`, `annulees()`, `aVenir()`, `passees()`, `aujourdhui()`
- Accesseurs: `statutLibelle`, `niveauComplexiteLibelle`, `typeLieuLibelle`, `dureeFormatee`
- Méthodes: `quorumAtteint`, `nombreParticipantsPresents`, `hasFonctionnalite()`

---

### 4. **ReunionParticipant** - Participants aux réunions
**Fichier:** `app/Models/ReunionParticipant.php`

**Description:** Gère les participants avec leurs rôles et statuts de présence.

**Constantes ENUM:**
- `ROLE_PRESIDENT`, `ROLE_SECRETAIRE`, `ROLE_PARTICIPANT`, `ROLE_OBSERVATEUR`, `ROLE_INVITE`
- `TYPE_INTERNE`, `TYPE_EXTERNE`
- `STATUT_PRESENCE_INVITE`, `STATUT_PRESENCE_CONFIRME`, `STATUT_PRESENCE_REFUSE`, `STATUT_PRESENCE_ABSENT`, `STATUT_PRESENCE_PRESENT`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `user()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `confirmes()`, `presents()`, `absents()`, `refuses()`, `byRole()`, `byType()`
- Accesseurs: `roleLibelle`, `typeLibelle`, `statutPresenceLibelle`
- Méthodes: `estPresent`, `estConfirme`, `estPresident`, `estSecretaire`

---

### 5. **ReunionPV** - Procès-verbaux
**Fichier:** `app/Models/ReunionPV.php`

**Description:** Gère les procès-verbaux avec versioning et validation.

**Constantes ENUM:**
- `STATUT_BROUILLON`, `STATUT_VALIDE`, `STATUT_PUBLIE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `redacteur()` - BelongsTo vers User
- `validateur()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `brouillons()`, `valides()`, `publies()`, `byVersion()`, `dernieresVersions()`
- Accesseurs: `statutLibelle`, `statutCouleur`, `statutIcone`
- Méthodes: `nombreMots`, `nombreCaracteres`, `extrait`, `dureeRedaction`, `tempsValidation`

---

### 6. **TypeReunionValidateurPV** - Validateurs de PV
**Fichier:** `app/Models/TypeReunionValidateurPV.php`

**Description:** Configure qui peut valider les PV par type de réunion.

**Relations principales:**
- `typeReunion()` - BelongsTo vers TypeReunion
- `user()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `actifs()`, `byNiveauValidation()`, `byTypeReunion()`

---

### 7. **ReunionNotificationConfig** - Configuration des notifications
**Fichier:** `app/Models/ReunionNotificationConfig.php`

**Description:** Configure les notifications par type de réunion.

**Constantes ENUM:**
- `TYPE_CONFIRMATION_PRESENCE`, `TYPE_RAPPEL`, `TYPE_PV_DISPONIBLE`, `TYPE_RAPPEL_ACTIONS`

**Relations principales:**
- `typeReunion()` - BelongsTo vers TypeReunion
- `createur()` - BelongsTo vers User
- `modificateur()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `actives()`, `byTypeNotification()`, `byTypeReunion()`
- Accesseurs: `typeNotificationLibelle`, `delaiFormate`, `destinatairesFormates`
- Méthodes: `hasDestinataire()`, `getConfigurationAvancee()`

---

### 8. **ReunionGeneree** - Réunions générées automatiquement
**Fichier:** `app/Models/ReunionGeneree.php`

**Description:** Traçabilité des réunions générées depuis les séries.

**Constantes ENUM:**
- `STATUT_GENERE`, `STATUT_PLANIFIEE`, `STATUT_ANNULEE`

**Relations principales:**
- `serie()` - BelongsTo vers ReunionSerie
- `reunion()` - BelongsTo vers Reunion

**Fonctionnalités:**
- Scopes: `byStatut()`, `bySerie()`
- Accesseurs: `statutLibelle`

---

### 9. **ReunionNotification** - Notifications envoyées
**Fichier:** `app/Models/ReunionNotification.php`

**Description:** Traçabilité des notifications envoyées aux participants.

**Constantes ENUM:**
- `TYPE_CONFIRMATION_PRESENCE`, `TYPE_RAPPEL`, `TYPE_PV_DISPONIBLE`, `TYPE_RAPPEL_ACTIONS`
- `STATUT_ENVOYEE`, `STATUT_ECHEC`, `STATUT_LUE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `destinataire()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `byType()`, `byStatut()`, `envoyees()`, `lues()`, `echecs()`
- Accesseurs: `typeLibelle`, `statutLibelle`, `statutCouleur`, `statutIcone`
- Méthodes: `tempsLecture`

---

### 10. **ReunionWorkflowConfig** - Configuration des workflows
**Fichier:** `app/Models/ReunionWorkflowConfig.php`

**Description:** Configure les workflows par type de réunion.

**Relations principales:**
- `typeReunion()` - BelongsTo vers TypeReunion
- `executions()` - HasMany vers ReunionWorkflowExecution
- `createur()` - BelongsTo vers User
- `modificateur()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `actifs()`, `byTypeReunion()`
- Accesseurs: `nombreEtapes`, `etapesFormatees`

---

### 11. **ReunionWorkflowExecution** - Exécution des workflows
**Fichier:** `app/Models/ReunionWorkflowExecution.php`

**Description:** Suivi de l'exécution des workflows par réunion.

**Constantes ENUM:**
- `STATUT_EN_COURS`, `STATUT_TERMINE`, `STATUT_BLOQUE`, `STATUT_ANNULE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `workflowConfig()` - BelongsTo vers ReunionWorkflowConfig

**Fonctionnalités:**
- Scopes: `byStatut()`, `enCours()`, `termines()`, `bloques()`
- Accesseurs: `statutLibelle`, `statutCouleur`, `statutIcone`
- Méthodes: `dureeExecution`, `progression`, `etapeActuelleFormatee`

---

### 12. **ReunionOrdreJour** - Ordre du jour
**Fichier:** `app/Models/ReunionOrdreJour.php`

**Description:** Points de l'ordre du jour avec types et durées.

**Constantes ENUM:**
- `TYPE_PRESENTATION`, `TYPE_DISCUSSION`, `TYPE_DECISION`, `TYPE_INFORMATION`, `TYPE_PAUSE`
- `STATUT_PLANIFIE`, `STATUT_EN_COURS`, `STATUT_TERMINE`, `STATUT_REPORTE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `responsable()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `byType()`, `byStatut()`, `ordered()`
- Accesseurs: `typeLibelle`, `statutLibelle`, `dureeFormatee`, `typeCouleur`, `typeIcone`

---

### 13. **ReunionDecision** - Décisions prises
**Fichier:** `app/Models/ReunionDecision.php`

**Description:** Décisions prises lors des réunions avec responsables et priorités.

**Constantes ENUM:**
- `PRIORITE_FAIBLE`, `PRIORITE_NORMALE`, `PRIORITE_ELEVEE`, `PRIORITE_CRITIQUE`
- `STATUT_A_FAIRE`, `STATUT_EN_COURS`, `STATUT_TERMINE`, `STATUT_ANNULE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `responsable()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `byStatut()`, `byPriorite()`, `enRetard()`
- Accesseurs: `prioriteLibelle`, `statutLibelle`, `prioriteCouleur`, `prioriteIcone`
- Méthodes: `estEnRetard`

---

### 14. **ReunionAction** - Actions de suivi
**Fichier:** `app/Models/ReunionAction.php`

**Description:** Actions de suivi avec progression et pièces jointes.

**Constantes ENUM:**
- `PRIORITE_FAIBLE`, `PRIORITE_NORMALE`, `PRIORITE_ELEVEE`, `PRIORITE_CRITIQUE`
- `STATUT_A_FAIRE`, `STATUT_EN_COURS`, `STATUT_TERMINE`, `STATUT_ANNULE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `responsable()` - BelongsTo vers User

**Fonctionnalités:**
- Scopes: `byStatut()`, `byPriorite()`, `enRetard()`
- Accesseurs: `prioriteLibelle`, `statutLibelle`, `progressionFormatee`
- Méthodes: `estEnRetard`, `estTerminee`

---

### 15. **ReunionSujet** - Sujets discutés
**Fichier:** `app/Models/ReunionSujet.php`

**Description:** Sujets discutés lors des réunions avec niveaux de difficulté.

**Constantes ENUM:**
- `DIFFICULTE_FACILE`, `DIFFICULTE_MOYENNE`, `DIFFICULTE_DIFFICILE`
- `STATUT_A_DISCUTER`, `STATUT_EN_DISCUSSION`, `STATUT_RESOLU`, `STATUT_REPORTE`

**Relations principales:**
- `reunion()` - BelongsTo vers Reunion
- `objectifs()` - HasMany vers ReunionSujetObjectif

**Fonctionnalités:**
- Scopes: `byDifficulte()`, `byStatut()`, `avecObjectifs()`, `avecDifficultes()`
- Accesseurs: `difficulteLibelle`, `statutLibelle`, `difficulteCouleur`, `difficulteIcone`

---

### 16. **ReunionSujetObjectif** - Objectifs par sujet
**Fichier:** `app/Models/ReunionSujetObjectif.php`

**Description:** Objectifs associés aux sujets avec progression.

**Constantes ENUM:**
- `STATUT_A_ATTEINDRE`, `STATUT_EN_COURS`, `STATUT_ATTEINT`, `STATUT_ANNULE`

**Relations principales:**
- `sujet()` - BelongsTo vers ReunionSujet
- `difficultes()` - HasMany vers ReunionObjectifDifficulte

**Fonctionnalités:**
- Scopes: `byStatut()`, `enRetard()`
- Accesseurs: `statutLibelle`, `progressionFormatee`, `statutCouleur`, `statutIcone`
- Méthodes: `estEnRetard`, `estAtteint`

---

### 17. **ReunionObjectifDifficulte** - Difficultés par objectif
**Fichier:** `app/Models/ReunionObjectifDifficulte.php`

**Description:** Difficultés rencontrées pour atteindre les objectifs.

**Constantes ENUM:**
- `IMPACT_FAIBLE`, `IMPACT_MOYEN`, `IMPACT_ELEVE`, `IMPACT_CRITIQUE`
- `STATUT_A_RESOUDRE`, `STATUT_EN_COURS`, `STATUT_RESOLU`, `STATUT_ANNULE`

**Relations principales:**
- `objectif()` - BelongsTo vers ReunionSujetObjectif
- `entite()` - BelongsTo vers Entite

**Fonctionnalités:**
- Scopes: `byImpact()`, `byStatutResolution()`, `nonResolues()`
- Accesseurs: `impactLibelle`, `statutResolutionLibelle`, `impactCouleur`, `impactIcone`
- Méthodes: `estResolue`, `estCritique`

---

## 🔗 Relations Ajoutées au Modèle User

Le modèle `User` a été enrichi avec de nombreuses relations vers les réunions :

### Relations Many-to-Many
- `typesReunionsGeres()` - Types de réunions gérés
- `typesReunionsMembrePermanent()` - Types où membre permanent

### Relations One-to-Many
- `typesReunionsValidateurPV()` - Types où peut valider PV
- `seriesReunionsCreees()` - Séries créées
- `reunionsCreees()` - Réunions créées
- `reunionsModifiees()` - Réunions modifiées
- `reunionsParticipant()` - Participations
- `reunionsPresident()` - Présidences
- `reunionsSecretaire()` - Secrétariats
- `pvsRediges()` - PV rédigés
- `pvsValides()` - PV validés
- `reunionsPVValidees()` - Réunions avec PV validés
- `notificationsReunions()` - Notifications reçues
- `configsNotificationsCreees()` - Configs créées
- `configsNotificationsModifiees()` - Configs modifiées
- `workflowsReunionsCrees()` - Workflows créés
- `workflowsReunionsModifies()` - Workflows modifiés
- `ordresJourResponsable()` - Ordres du jour responsables
- `decisionsReunionsResponsable()` - Décisions responsables
- `actionsReunionsResponsable()` - Actions responsables

### Méthodes Utilitaires
- `reunionsImpliquees()` - Toutes les réunions impliquées
- `peutGererTypeReunion()` - Vérification permissions gestion
- `estMembrePermanentTypeReunion()` - Vérification membre permanent
- `peutValiderPVTypeReunion()` - Vérification validation PV

## 🎨 Fonctionnalités Communes

### Traits Utilisés
- `HasFactory` - Pour les factories de test
- `Auditable` - Pour la traçabilité des modifications

### Patterns Communs
- **Timestamps personnalisés:** `date_creation`, `date_modification`
- **Audit:** `creer_par`, `modifier_par`
- **Scopes de requête:** Pour filtrer facilement
- **Accesseurs:** Pour formater les données
- **Constantes ENUM:** Pour les valeurs prédéfinies
- **Couleurs et icônes:** Pour l'interface utilisateur

### Casts JSON
- `fonctionnalites_actives` - Configuration flexible
- `configuration_recurrence` - Paramètres de récurrence
- `notifications_actives` - Préférences de notification
- `pieces_jointes` - Fichiers attachés
- `commentaires` - Commentaires structurés

## 🚀 Utilisation

### Exemple d'utilisation basique
```php
// Créer un type de réunion
$typeReunion = TypeReunion::create([
    'nom' => 'Comité de Direction',
    'niveau_complexite' => TypeReunion::NIVEAU_COMPLEXE,
    'actif' => true
]);

// Créer une réunion
$reunion = Reunion::create([
    'titre' => 'Réunion mensuelle',
    'type_reunion_id' => $typeReunion->id,
    'date_debut' => now()->addDays(7),
    'date_fin' => now()->addDays(7)->addHours(2),
    'statut' => Reunion::STATUT_PLANIFIEE
]);

// Ajouter des participants
$reunion->participants()->create([
    'user_id' => $user->id,
    'role' => ReunionParticipant::ROLE_PRESIDENT,
    'statut_presence' => ReunionParticipant::STATUT_INVITE
]);
```

### Exemple de requêtes complexes
```php
// Réunions à venir pour un utilisateur
$reunionsAVenir = $user->reunionsParticipant()
    ->with('reunion')
    ->whereHas('reunion', function($q) {
        $q->aVenir()->nonSuspendues();
    })
    ->get();

// PV en attente de validation
$pvEnAttente = ReunionPV::brouillons()
    ->whereHas('reunion.typeReunion.validateursPV', function($q) use ($user) {
        $q->where('user_id', $user->id)->actifs();
    })
    ->get();
```

## 📝 Notes Importantes

1. **Tous les modèles utilisent des timestamps personnalisés** (`date_creation`, `date_modification`)
2. **Tous les modèles incluent l'audit** (`creer_par`, `modifier_par`)
3. **Les relations sont optimisées** avec des clés étrangères appropriées
4. **Les scopes facilitent les requêtes** courantes
5. **Les accesseurs formatent automatiquement** les données pour l'affichage
6. **Les constantes ENUM** assurent la cohérence des données
7. **Les couleurs et icônes** sont prêtes pour l'interface utilisateur

## 🔄 Prochaines Étapes

1. **Créer les contrôleurs** pour chaque modèle
2. **Implémenter les services** métier
3. **Créer les seeders** pour les données de test
4. **Développer l'interface frontend**
5. **Ajouter les tests unitaires**
6. **Implémenter les notifications** automatiques
7. **Créer les rapports** et statistiques 
