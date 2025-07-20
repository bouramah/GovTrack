**Documentation Complète - Système de Gestion des Réunions GovTrack** 

**Table des Matières** 

1. Vision Globale 
1. Architecture du Système 
1. Modèles de Données 
1. Workflows et Processus 

**Vision Globale** 

**Objectif du Système** 

Le système de gestion des réunions GovTrack permet de gérer tous types de réunions gouvernementales avec une flexibilité totale, de la planification automatique à la finalisation des procès-verbaux. 

**Types de Réunions Supportés** 

- **CODIR** : Comités de Direction (Complexe) 
- **Réunions Techniques** : Architecture, développement (Intermédiaire) 
- **Réunions de Projet** : Suivi, coordination (Simple) 
- **Comités Spécialisés** : Audit, validation (Configurable) 
- **Réunions Ponctuelles** : Événements spéciaux 

**Niveaux de Complexité** 



|**Niveau** |**Fonctionnalités** |**Exemple** |
| - | - | - |
|**SIMPLE** |PV, Participants, Ordre du jour basique |Réunion projet |
|**INTERMÉDIAIRE**|+ Workflow validation, Notifications |Réunion technique|
|**COMPLEXE** |+ Objectifs multiples, Difficultés par entité|` `CODIR |

**Innovations Clés** 

- **Génération automatique** des réunions récurrentes 
- **Configuration dynamique** par type de réunion 
- **Workflow de validation** configurable 
- **Interface adaptative** selon la complexité 

**Architecture du Système** 

**Composants Principaux** 

![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.001.jpeg)

**Flux de Données** 

1. **Configuration** → Types de réunions, séries, participants 
1. **Génération** → Réunions automatiques ou manuelles 
1. **Préparation** → Ordre du jour, invitations 
1. **Exécution** → Présences, discussions, décisions 
1. **Finalisation** → PV, validation, notifications 

**Technologies Utilisées** 

- **Backend** : Laravel 12 + PHP 8.2+ 
- **Base de données** : MySQL 
- **Authentification** : Laravel Sanctum 
- **Frontend** : Next.js 15 + React 19 
- **Notifications** : Système email intégré 

**Modèles de Données :**  

1. **Types de Réunions**

**Description** 

Définit les différents types de réunions avec leurs configurations spécifiques et niveaux de complexité. 

**Table : type\_reunions**



|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|nom |VARCHAR(100) |Nom du type de réunion |"CODIR" |
|description |TEXT |Description détaillée |"Comité de Direction mensuel" |
|couleur |VARCHAR(7) |Couleur hexadécimale |"#1f2937" |
|icone |VARCHAR(50) |Nom de l’icône |"users" |
|actif |BOOLEAN |Si le type est actif |true |
|ordre |INT |Ordre d’affichage |1 |
|niveau\_complexite |ENUM |SIMPLE, INTERMEDIAIRE, COMPLEXE |"COMPLEXE" |
|fonctionnalites\_actives |JSON |Fonctionnalités activées |Voir détail ci- dessous |
|configuration\_notifications |JSON |Configuration des notifications |Voir détail ci- dessous |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |



|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
| - | - | :- | :- |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

*Détail des Champs JSON* 

**fonctionnalites\_actives :** 

{ 

`  `"objectifs\_multiples": true, 

`  `"difficultes\_par\_entite": true, 

`  `"workflow\_validation": true, 

`  `"pv\_validation": true, 

`  `"quorum\_obligatoire": true, 

`  `"presence\_obligatoire": true, 

`  `"notifications\_presence": true,   "notifications\_rappel": true, 

`  `"notifications\_pv": true, 

`  `"ordre\_du\_jour\_explicite": true,   "participants\_invites": true, 

`  `"pieces\_jointes": true, 

`  `"decisions\_multiples": true, 

`  `"actions\_suivi": true 

} 

**configuration\_notifications :** 

{ 

`  `"rappel\_24h": true, 

`  `"rappel\_1h": true, 

`  `"rappel\_15min": false, 

`  `"confirmation\_presence": true, 

`  `"pv\_disponible": true, 

`  `"rappel\_actions": true, 

`  `"notifications\_absence": false, 

`  `"template\_email\_presence": "template\_presence.html",   "template\_email\_rappel": "template\_rappel.html", 

`  `"template\_email\_pv": "template\_pv.html" 

} 

*Exemples* 

**CODIR :** 

INSERT INTO type\_reunions VALUES ( 

`    `1, 'CODIR', 'Comité de Direction mensuel pour suivi stratégique', 

`    `'#1f2937', 'users', true, 1, 'COMPLEXE', 

`    `'{"objectifs\_multiples": true, "difficultes\_par\_entite": true, "workflow\_validation": true, "pv\_validation": true, "quorum\_obligatoire": true, "presence\_obligatoire": true, "notifications\_presence": true, "notifications\_rappel": true, "notifications\_pv": true, "ordre\_du\_jour\_explicite": true, "participants\_invites": true, "pieces\_jointes": true, "decisions\_multiples": true, "actions\_suivi": true}', 

`    `'{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "template\_email\_presence": "codir\_presence.html", "template\_email\_rappel": "codir\_rappel.html", "template\_email\_pv": "codir\_pv.html"}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

**Réunion Technique :** 

INSERT INTO type\_reunions VALUES ( 

`    `2, 'REUNION\_TECHNIQUE', 'Réunion technique et architecture', 

`    `'#059669', 'settings', true, 2, 'INTERMEDIAIRE', 

`    `'{"objectifs\_multiples": false, "difficultes\_par\_entite": false, "workflow\_validation": true, "pv\_validation": true, "quorum\_obligatoire": false, "presence\_obligatoire": false, "notifications\_presence": true, "notifications\_rappel": true, "notifications\_pv": true, "ordre\_du\_jour\_explicite": true, "participants\_invites": true, "pieces\_jointes": true, "decisions\_multiples": true, "actions\_suivi": true}', 

`    `'{"rappel\_24h": true, "rappel\_1h": false, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": false, "notifications\_absence": false, "template\_email\_presence": "tech\_presence.html", "template\_email\_rappel": "tech\_rappel.html", "template\_email\_pv": "tech\_pv.html"}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

2. Gestionnaires de Types de Réunions 

*Description* 

Définit qui peut gérer chaque type de réunion avec des permissions granulaires. **Table: type\_reunion\_gestionnaires** 

|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|type\_reunion\_id |INT |Référence vers type\_reunions |1 |
|user\_id |INT |Référence vers users |3 |
|permissions |JSON |Permissions accordées |Voir détail ci- dessous |
|actif |BOOLEAN |Si le gestionnaire est actif |true |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |



|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
| - | - | :- | :- |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

**permissions :** 

{ 

`  `"create": true, 

`  `"edit": true, 

`  `"delete": true, 

`  `"validate\_pv": true, 

`  `"manage\_participants": true,   "configure\_type": true, 

`  `"manage\_series": true, 

`  `"generate\_meetings": true, 

`  `"view\_all\_meetings": true, 

`  `"export\_data": true, 

`  `"manage\_notifications": true,   "override\_workflow": true, 

`  `"manage\_permissions": false } 

*Exemples* 

**CODIR :** 

INSERT INTO type\_reunion\_gestionnaires VALUES ( 

`    `1, 1, 1, -- Admin gère CODIR 

`    `'{"create": true, "edit": true, "delete": true, "validate\_pv": true, "manage\_participants": true, "configure\_type": true, "manage\_series": true, "generate\_meetings": true, "view\_all\_meetings": true, "export\_data": true, "manage\_notifications": true, "override\_workflow": true, "manage\_permissions": true}', 

`    `true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

**Réunion Technique :** 

INSERT INTO type\_reunion\_gestionnaires VALUES ( 

`    `2, 2, 3, -- DSI gère réunions techniques 

`    `'{"create": true, "edit": true, "delete": true, "validate\_pv": true, "manage\_participants": true, "configure\_type": false, "manage\_series": true, "generate\_meetings": true, "view\_all\_meetings": true, "export\_data": true, "manage\_notifications": true, "override\_workflow": false, "manage\_permissions": false}', 

`    `true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 ); 

3. Membres Permanents par Type de Réunion 

*Description* 

Définit les membres permanents pour chaque type de réunion avec leurs rôles par défaut. 

*Table : type\_reunion\_membres\_permanents* 



|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|type\_reunion\_id |INT |Référence vers type\_reunions |1 |
|user\_id |INT |Référence vers users |1 |
|role\_defaut |ENUM |PRESIDENT, SECRETAIRE, PARTICIPANT, OBSERVATEUR |"PRESIDENT" |
|actif |BOOLEAN |Si le membre est actif |true |
|notifications\_par\_defaut |JSON |Configuration notifications par défaut |Voir détail ci- dessous |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

*Détail du Champ JSON* **notifications\_par\_defaut :** 

{ 

`  `"rappel\_24h": true, 

`  `"rappel\_1h": true, 

`  `"rappel\_15min": false, 

`  `"confirmation\_presence": true,   "pv\_disponible": true, 

`  `"rappel\_actions": true, 

`  `"notifications\_absence": false,   "email\_prioritaire": true, 

`  `"sms\_rappel": false 

} 

*Exemples* 

**CODIR :** 

INSERT INTO type\_reunion\_membres\_permanents VALUES  

(1, 1, 1, 'PRESIDENT', true, '{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "email\_prioritaire": true, "sms\_rappel": false}', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(2, 1, 2, 'SECRETAIRE', true, '{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "email\_prioritaire": true, "sms\_rappel": false}', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 1, 3, 'PARTICIPANT', true, '{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "email\_prioritaire": false, "sms\_rappel": false}', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO type\_reunion\_membres\_permanents VALUES  

(4, 2, 3, 'PRESIDENT', true, '{"rappel\_24h": true, "rappel\_1h": false, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": false, "notifications\_absence": false, "email\_prioritaire": false, "sms\_rappel": false}', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(5, 2, 7, 'PARTICIPANT', true, '{"rappel\_24h": true, "rappel\_1h": false, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": false, "notifications\_absence": false, "email\_prioritaire": false, "sms\_rappel": false}', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

4\.Séries de Réunions 

Description

Définit les séries de réunions récurrentes avec leur configuration de génération automatique.  

Table : reunion\_series 

|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|nom |VARCHAR(100 ) |Nom de la série |"CODIR Mensuel" |
|description |TEXT |Description de la série |"CODIR mensuel pour suivi stratégique" |
|type\_reunion\_id |INT |Référence vers type\_reunions |1 |




|periodicite |ENUM |HEBDOMADAIRE, BIHEBDOMADAIRE , MENSUELLE |"MENSUELLE " |
| - | - | :- | :- |
|jour\_semaine |INT |Jour semaine (1–7) pour hebdomadaire |2 |
|jour\_mois |INT |Jour du mois (1–31) pour mensuel |15 |
|heure\_debut |TIME |Heure de début |"09:00:00" |
|duree\_minutes |INT |Durée en minutes |180 |
|lieu\_defaut |VARCHAR(200 ) |Lieu par défaut |"Salle de réunion principale" |
|actif |BOOLEAN |Si la série est active |true |
|date\_debut\_serie |DATE |Date de début de la série |"2024-01-15" |
|date\_fin\_serie |DATE |Date de fin de la série |"2024-12-31" |
|suspendue |BOOLEAN |Si la série est suspendue |false |
|prochaine\_generation |TIMESTAMP |Prochaine génération automatique |"2024-02-15 08:00:00" |
|configuration\_recurrenc e |JSON |Configuration de la récurrence |Voir détail ci- dessous |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

*Détail du Champ JSON* 

**configuration\_recurrence :** 

{ 

`  `"generer\_automatiquement": true, 

`  `"notifier\_gestionnaire": true, 

`  `"copier\_participants": true, 

`  `"template\_titre": "{nom} - {mois} {annee}",   "notifier\_creation": true, 

`  `"valider\_avant\_generation": false, 

`  `"limite\_generations": 12, 

`  `"generations\_restantes": 11, 

`  `"regles\_exception": { 

`    `"jours\_feries": "reporter\_lendemain", 

`    `"weekends": "reporter\_lundi", 

`    `"conflits": "notifier\_gestionnaire"   }, 

`  `"notifications\_generation": { 

`    `"gestionnaire": true, 

`    `"participants": false, 

`    `"admin": false 

`  `} 

} 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_series VALUES ( 

`    `1, 'CODIR Mensuel', 'CODIR mensuel pour suivi stratégique', 

`    `1, 'MENSUELLE', null, 15, '09:00:00', 180, 

`    `'Salle de réunion principale', true, 

`    `'2024-01-15', '2024-12-31', false, '2024-02-15 08:00:00', 

`    `'{"generer\_automatiquement": true, "notifier\_gestionnaire": true, "copier\_participants": true, "template\_titre": "CODIR Mensuel - {mois} {annee}", "notifier\_creation": true, "valider\_avant\_generation": false, "limite\_generations": 12, "generations\_restantes": 11, "regles\_exception": {"jours\_feries": "reporter\_lendemain", "weekends": "reporter\_lundi", "conflits": "notifier\_gestionnaire"}, "notifications\_generation": {"gestionnaire": true, "participants": false, "admin": false}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

**Réunion Technique :** 

INSERT INTO reunion\_series VALUES ( 

`    `2, 'Réunions Tech Hebdo', 'Réunions techniques hebdomadaires', 

`    `2, 'HEBDOMADAIRE', 2, null, '14:00:00', 90, 

`    `'Salle technique', true, 

`    `'2024-01-02', '2024-12-31', false, '2024-02-13 08:00:00', 

`    `'{"generer\_automatiquement": true, "notifier\_gestionnaire": true, "copier\_participants": true, "template\_titre": "Réunion Tech - Semaine {semaine} {annee}", "notifier\_creation": false, "valider\_avant\_generation": false, "limite\_generations": 52, "generations\_restantes": 50, "regles\_exception": {"jours\_feries": "annuler", "weekends": "reporter\_lundi", "conflits": "notifier\_gestionnaire"}, "notifications\_generation": {"gestionnaire": true, "participants": false, "admin": false}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

5. Réunions 

Description

Table principale contenant toutes les réunions, qu'elles soient générées automatiquement ou créées manuellement. 

*Table : reunions* 



|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|titre |VARCHAR(2 00) |Titre de la réunion |"CODIR Février 2024" |
|description |TEXT |Description de la réunion |"Suivi mensuel des projets stratégiques" |
|type\_reunion\_id |INT |Référence vers type\_reunions |1 |
|niveau\_complexite\_a ctuel |ENUM |SIMPLE, INTERMEDIAIR E, COMPLEXE |"COMPLEXE" |
|date\_debut |TIMESTAMP |Date et heure de début |"2024-02-15 09:00:00" |
|date\_fin |TIMESTAMP |Date et heure de fin |"2024-02-15 12:00:00" |
|lieu |VARCHAR(2 00) |Lieu de la réunion |"Salle de réunion principale" |
|type\_lieu |ENUM |PHYSIQUE, VIRTUEL, HYBRIDE |"PHYSIQUE" |
|lien\_virtuel |VARCHAR(5 00) |Lien pour réunion virtuelle |"https://meet.google.co m/..." |
|periodicite |ENUM |PONCTUELLE, HEBDOMADAIR E, BIHEBDOMADAI RE, MENSUELLE |"MENSUELLE" |
|serie\_id |INT |Référence vers reunion\_series |1 |
|suspendue |BOOLEAN |Si la réunion est suspendue |false |
|reprogrammee\_le |TIMESTAMP |Date de reprogrammation |null |
|fonctionnalites\_active s |JSON |Fonctionnalités activées pour cette réunion |Voir détail ci-dessous |
|quorum\_minimum |INT |Nombre minimum de présences |4 |
|ordre\_du\_jour\_type |ENUM |EXPLICITE, IMPLICITE, HYBRIDE |"EXPLICITE" |
|statut |ENUM |PLANIFIEE, EN\_COURS, |"PLANIFIEE" |



|||TERMINEE, ANNULEE ||
| :- | :- | :- | :- |
|pv\_valide\_par\_id |INT |ID utilisateur validateur du PV |1 |
|pv\_valide\_le |TIMESTAMP |Date de validation du PV |null |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

*Détail du Champ JSON* **fonctionnalites\_actives :** 

{ 

`  `"objectifs\_multiples": true, 

`  `"difficultes\_par\_entite": true, 

`  `"workflow\_validation": true, 

`  `"pv\_validation": true, 

`  `"quorum\_obligatoire": true, 

`  `"presence\_obligatoire": true, 

`  `"notifications\_presence": true,   "notifications\_rappel": true, 

`  `"notifications\_pv": true, 

`  `"ordre\_du\_jour\_explicite": true,   "participants\_invites": true, 

`  `"pieces\_jointes": true, 

`  `"decisions\_multiples": true, 

`  `"actions\_suivi": true, 

`  `"modifications\_autorisees": { 

`    `"titre": true, 

`    `"date": false, 

`    `"participants": true, 

`    `"ordre\_du\_jour": true 

`  `} 

} 

*Exemples* 

**CODIR :** 

INSERT INTO reunions VALUES ( 

`    `1, 'CODIR Mensuel - Février 2024', 

`    `'Suivi mensuel des projets stratégiques et points divers', 

`    `1, 'COMPLEXE', '2024-02-15 09:00:00', '2024-02-15 12:00:00', 

`    `'Salle de réunion principale', 'PHYSIQUE', null, 

`    `'MENSUELLE', 1, false, null, 

`    `'{"objectifs\_multiples": true, "difficultes\_par\_entite": true, "workflow\_validation": true, "pv\_validation": true, "quorum\_obligatoire": true, "presence\_obligatoire": true, "notifications\_presence": true, "notifications\_rappel": true, "notifications\_pv": true, "ordre\_du\_jour\_explicite": true, "participants\_invites": true, "pieces\_jointes": true, "decisions\_multiples": true, "actions\_suivi": true, "modifications\_autorisees": {"titre": true, "date": false, "participants": true, "ordre\_du\_jour": true}}', 

`    `4, 'EXPLICITE', 'PLANIFIEE', null, null, 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

**Réunion Technique :** 

INSERT INTO reunions VALUES ( 

`    `2, 'Architecture Système Paiement - Semaine 7', 

`    `'Discussion sur l\'architecture du nouveau système de paiement', 

`    `2, 'INTERMEDIAIRE', '2024-02-13 14:00:00', '2024-02-13 15:30:00', 

`    `'Salle technique', 'HYBRIDE', 'https://meet.google.com/abc-defg-hij', 

`    `'HEBDOMADAIRE', 2, false, null, 

`    `'{"objectifs\_multiples": false, "difficultes\_par\_entite": false, "workflow\_validation": true, "pv\_validation": true, "quorum\_obligatoire": false, "presence\_obligatoire": false, "notifications\_presence": true, "notifications\_rappel": true, "notifications\_pv": true, "ordre\_du\_jour\_explicite": true, "participants\_invites": true, "pieces\_jointes": true, "decisions\_multiples": true, "actions\_suivi": true, "modifications\_autorisees": {"titre": true, "date": true, "participants": true, "ordre\_du\_jour": true}}', 

`    `2, 'EXPLICITE', 'PLANIFIEE', null, null, 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

6. Réunions Générées 

*Description* 

Trace les réunions générées automatiquement depuis les séries. 

*Table : reunion\_generees* 



|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|serie\_id |INT |Référence vers reunion\_series |1 |
|reunion\_id |INT |Référence vers reunions |1 |
|genere\_le |TIMESTAMP |Date de génération |"2024-02-15 08:00:00" |
|statut\_generation |ENUM |SUCCES, ERREUR |"SUCCES" |
|message\_erreur |TEXT |Message d'erreur si échec |null |
|configuration\_utilisee |JSON |Configuration utilisée pour la génération |Voir détail ci- dessous |



|date\_creation |TIMESTAMP |Date de création |2024-02-15 08:00:00 |
| - | - | - | :- |

*Détail du Champ JSON* 

**configuration\_utilisee :** 

{ 

`  `"template\_titre": "CODIR Mensuel - {mois} {annee}",   "participants\_copies": 5, 

`  `"ordre\_du\_jour\_copie": true, 

`  `"notifications\_envoyees": true, 

`  `"lieu\_utilise": "Salle de réunion principale", 

`  `"duree\_utilisee": 180, 

`  `"heure\_debut\_utilisee": "09:00:00", 

`  `"modifications\_apportees": { 

`    `"titre": "CODIR Mensuel - Février 2024", 

`    `"date": "2024-02-15" 

`  `} 

} 

**Exemples** 

**CODIR :** 

INSERT INTO reunion\_generees VALUES ( 

`    `1, 1, 1, '2024-02-15 08:00:00', 'SUCCES', null, 

`    `'{"template\_titre": "CODIR Mensuel - {mois} {annee}", "participants\_copies": 5, "ordre\_du\_jour\_copie": true, "notifications\_envoyees": true, "lieu\_utilise": "Salle de réunion principale", "duree\_utilisee": 180, "heure\_debut\_utilisee": "09:00:00", "modifications\_apportees": {"titre": "CODIR Mensuel - Février 2024", "date": "2024-02- 15"}}', 

`    `'2024-02-15 08:00:00' 

); 

**Réunion Technique :** 

INSERT INTO reunion\_generees VALUES ( 

`    `2, 2, 2, '2024-02-13 08:00:00', 'SUCCES', null, 

`    `'{"template\_titre": "Réunion Tech - Semaine {semaine} {annee}", "participants\_copies": 4, "ordre\_du\_jour\_copie": false, "notifications\_envoyees": true, "lieu\_utilise": "Salle technique", "duree\_utilisee": 90, "heure\_debut\_utilisee": "14:00:00", "modifications\_apportees": {"titre": "Architecture Système Paiement - Semaine 7", "date": "2024-02-13"}}', 

`    `'2024-02-13 08:00:00' 

); 

7. Participants aux Réunions 

*Description* 

Gère les participants de chaque réunion avec leurs statuts de présence et notifications. 

*Table : reunion\_participants* 



|Champ |Type |Description |Exemple |
| - | - | - | - |
|id |INT |Identifiant unique |1 |
|reunion\_id |INT |Référence vers reunions |1 |
|user\_id |INT |Référence vers users |1 |
|role |ENUM |PRESIDENT, SECRETAIRE, PARTICIPANT, OBSERVATEUR, VALIDATEUR\_PV |"PRESIDENT" |
|type |ENUM |PERMANENT, INVITE |"PERMANENT" |
|statut\_presence |ENUM |CONFIRME, ABSENT, EN\_ATTENTE |"CONFIRME" |
|present\_le |TIMESTAMP |Date de confirmation de présence |"2024-02-14 15:30:00" |
|absent\_le |TIMESTAMP |Date de déclaration d'absence |null |
|commentaire\_absence |TEXT |Commentaire pour l'absence |null |
|notifie\_absence |BOOLEAN |Si l'absence doit être notifiée |false |
|notifications\_actives |JSON |Configuration des notifications |Voir détail ci- dessous |
|date\_creation |TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification |TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par |INT |ID utilisateur créateur |1 |
|modifier\_par |INT |ID utilisateur modificateur |1 |

*Détail du Champ JSON* **notifications\_actives :** 

{ 

`  `"rappel\_24h": true, 

`  `"rappel\_1h": true, 

`  `"rappel\_15min": false, 

`  `"confirmation\_presence": true,   "pv\_disponible": true, 

`  `"rappel\_actions": true, 

`  `"notifications\_absence": false,   "email\_prioritaire": true, 

`  `"sms\_rappel": false, 

`  `"canaux\_preferes": { 

`    `"email": true, 

`    `"sms": false, 

`    `"notification\_app": true, 

`    `"notification\_web": true 

`  `} 

} 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_participants VALUES  

(1, 1, 1, 'PRESIDENT', 'PERMANENT', 'CONFIRME', '2024-02-14 15:30:00', null, null, false, '{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "email\_prioritaire": true, "sms\_rappel": false, "canaux\_preferes": {"email": true, "sms": false, "notification\_app": true, "notification\_web": true}}', '2024-01-15 10:00:00', '2024-02-14 15:30:00', 1, 1), 

(2, 1, 2, 'SECRETAIRE', 'PERMANENT', 'ABSENT', null, '2024-02-14 16:00:00', 'Congé maladie', false, '{"rappel\_24h": true, "rappel\_1h": true, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": true, "notifications\_absence": false, "email\_prioritaire": true, "sms\_rappel": false, "canaux\_preferes": {"email": true, "sms": false, "notification\_app": true, "notification\_web": true}}', '2024-01-15 10:00:00', '2024-02-14 16:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_participants VALUES  

(3, 2, 3, 'PRESIDENT', 'PERMANENT', 'CONFIRME', '2024-02-13 13:45:00', null, null, false, '{"rappel\_24h": true, "rappel\_1h": false, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": false, "notifications\_absence": false, "email\_prioritaire": false, "sms\_rappel": false, "canaux\_preferes": {"email": true, "sms": false, "notification\_app": true, "notification\_web": true}}', '2024-01-15 10:00:00', '2024-02-13 13:45:00', 1, 1), 

(4, 2, 10, 'OBSERVATEUR', 'INVITE', 'CONFIRME', '2024-02-13 13:50:00', null, null, false, '{"rappel\_24h": true, "rappel\_1h": false, "rappel\_15min": false, "confirmation\_presence": true, "pv\_disponible": true, "rappel\_actions": false, "notifications\_absence": false, "email\_prioritaire": false, "sms\_rappel": false, "canaux\_preferes": {"email": true, "sms": false, "notification\_app": false, "notification\_web": true}}', '2024-01-15 10:00:00', '2024-02-

13 13:50:00', 1, 1); 

8. Ordre du Jour 

*Description* 

Définit les points à traiter lors de la réunion avec leur ordre et détails. **Table : reunion\_ordre\_jours** 

|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|reunion\_id|INT |<p>Référence vers </p><p>reunions</p>|1 |
|ordre|INT |Ordre de traitement |1 |
|titre|VARCHAR(200 ) |Titre du point |"Chantier PSD" |
|description|TEXT |Description du point |"Suivi du projet PSD et difficultés" |
|type|ENUM |SUJET\_SPECIFIQUE , POINT\_DIVERS, SUIVI\_PROJETS |"SUJET\_SPECIFIQUE " |
|duree\_estimee\_minute s|INT |Durée estimée en minutes |45 |
|entite\_proposante\_id|INT |<p>Référence vers </p><p>entites</p>|3 |
|responsable\_id|INT |Référence vers users|3 |
|projet\_id|INT |<p>Référence vers </p><p>projets</p>|1 |
|statut|ENUM |PLANIFIE, EN\_COURS, TERMINE, REPORTE |"PLANIFIE" |
|niveau\_detail\_requis|ENUM |SIMPLE, DETAILLE |"DETAILLE" |
|date\_creation|TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification|TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par|INT |ID utilisateur créateur |1 |
|modifier\_par|INT |ID utilisateur modificateur |1 |

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_ordre\_jours VALUES  

(1, 1, 1, 'Chantier PSD', 'Suivi du projet PSD et difficultés rencontrées', 'SUJET\_SPECIFIQUE', 45, 3, 3, 1, 'PLANIFIE', 'DETAILLE', '2024-01-15 10:00:00', '2024- 01-15 10:00:00', 1, 1), 

(2, 1, 2, 'Budget Q1', 'Validation du budget Q1 2024', 'SUJET\_SPECIFIQUE', 30, 5, 5, null, 'PLANIFIE', 'DETAILLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 1, 3, 'Points divers', 'Autres points à traiter', 'POINT\_DIVERS', 15, null, 1, null, 'PLANIFIE', 'SIMPLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_ordre\_jours VALUES  

(4, 2, 1, 'Architecture API', 'Présentation de l\'architecture API REST', 'SUJET\_SPECIFIQUE', 30, 8, 8, null, 'PLANIFIE', 'DETAILLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(5, 2, 2, 'Base de données', 'Choix de la base de données et schéma', 'SUJET\_SPECIFIQUE', 25, 9, 9, null, 'PLANIFIE', 'DETAILLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), (6, 2, 3, 'Sécurité', 'Mesures de sécurité à implémenter', 'SUJET\_SPECIFIQUE', 20, 7, 7, null, 'PLANIFIE', 'DETAILLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(7, 2, 4, 'Planning', 'Planning de développement et déploiement', 'SUJET\_SPECIFIQUE', 15, null, 3, null, 'PLANIFIE', 'SIMPLE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

9. Sujets de Réunion 

*Description* 

Détaille les sujets discutés lors de chaque point de l'ordre du jour. 

*Table : reunion\_sujets* 

**Champ  ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.002.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.003.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.004.png)**

**Type** 

**Description  Exemple** 

|INT ||
| - | :- |
|INT ||
|Identifiant unique ||
|<p>Référence vers </p><p>reunion\_ordre\_jours</p>||
|VARCHAR( 200) ||

Titre du sujet  "Gestion d’éthique" 



|TEXT |
| - |

"Discussion sur les enjeux Description détaillée 

éthiques" 

|TEXT |
| - |

Difficulté globale du sujet  "Coordination entre entités" Recommandation proposée 

|TEXT |
| - |

"Renforcer la communication" 

|ENUM |
| - |

RESOLU,  

EN\_COURS\_DE\_RESOL 

"EN\_COURS\_DE\_RESOL UTION, BLOQUE, AVIS, 

UTION" 

APPROUVE, REJETE, 

EN\_ATTENTE 

|TEXT |
| - |

"Projet en cours, suivi Commentaire additionnel 

renforcé" 

pieces\_jointes  JSON  Voir détail ci-dessous 

Liste des pièces jointes 



|**Type** |
| - |

**Description  Exemple ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.005.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.006.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.007.png)**Référence vers projets 1 

|INT |
| - |
|INT |

Référence vers entites 3 



|ENUM |
| - |

SIMPLE, DETAILLE  "DETAILLE" 

|BOOLEAN |
| - |

Si les objectifs sont activés  true 



|BOOLEAN |
| - |

Si les difficultés sont activées 

true 



|TIMESTAM P |
| :- |

Date de création Date de modification 

2024-01-15 10:00:00 2024-01-15 10:00:00 

|TIMESTAM P |
| :- |
|INT |

ID utilisateur créateur  1 ID utilisateur modificateur  1 

|INT |
| - |

*Détail du Champ JSON* 

**pieces\_jointes :** 

[ 

`  `{ 

`    `"nom": "document1.pdf", 

`    `"type": "pdf", 

`    `"taille": 1024000, 

`    `"url": "/uploads/reunions/1/document1.pdf", 

`    `"description": "Rapport éthique" 

`  `}, 

`  `{ 

`    `"nom": "rapport\_ethique.docx", 

`    `"type": "docx", 

`    `"taille": 512000, 

`    `"url": "/uploads/reunions/1/rapport\_ethique.docx",     "description": "Document Word" 

`  `} 

] 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_sujets VALUES  

(1, 1, 'Gestion d\'éthique dans le projet PSD', 

`    `'Discussion sur les enjeux éthiques du projet PSD et les difficultés rencontrées',     'Difficultés de coordination entre les entités', 

`    `'Renforcer la communication inter-services', 

`    `'EN\_COURS\_DE\_RESOLUTION', 

`    `'Projet en cours, nécessite un suivi renforcé', 

`    `'[{"nom": "document1.pdf", "type": "pdf", "taille": 1024000, "url": "/uploads/reunions/1/document1.pdf", "description": "Rapport éthique"}, {"nom": "rapport\_ethique.docx", "type": "docx", "taille": 512000, "url": "/uploads/reunions/1/rapport\_ethique.docx", "description": "Document Word"}]', 

`    `1, 3, 'DETAILLE', true, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_sujets VALUES  

(2, 4, 'Architecture API REST', 

`    `'Présentation de l\'architecture API REST pour le système de paiement', 

`    `'Complexité de l\'intégration avec les systèmes existants', 

`    `'Utiliser une approche microservices', 

`    `'RESOLU', 

`    `'Architecture validée par l\'équipe', 

`    `'[{"nom": "architecture\_api.pdf", "type": "pdf", "taille": 2048000, "url": "/uploads/reunions/2/architecture\_api.pdf", "description": "Documentation API"}, {"nom": "diagramme\_sequence.png", "type": "png", "taille": 256000, "url": "/uploads/reunions/2/diagramme\_sequence.png", "description": "Diagramme de séquence"}]',     null, 8, 'DETAILLE', false, false, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

10. Objectifs des Sujets 

*Description* 

Définit les objectifs spécifiques pour chaque sujet (principalement pour CODIR). 

*Table : reunion\_sujet\_objectifs* 

**Champ  Type  Description  Exemple ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.008.png)**

|INT |
| - |

Identifiant unique 

1 1 

|INT ||
| - | :- |
|VARCHAR(200)||
|<p>Référence vers </p><p>reunion\_sujets</p>||
|` `Titre de l’objectif ||

"Formation équipe éthique" 



|TEXT |
| - |

"Former 100% de Description de 

l’équipe aux règles 

l’objectif 

éthiques" 

|TEXT |
| - |

"Sensibilisation Cible à atteindre  complète de 

l’équipe" 

|INT |
| - |

Pourcentage de 

75 réalisation (0–100) 

|` `DECIMAL(5,2) |
| - |

Pourcentage de décaissement 

15\.50 "2024-03-15" 

date\_objectif DATE  Date objectif 



|**Type** |
| - |

**Description  Exemple ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.009.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.010.png)**"EN\_COURS" 

|ENUM |
| - |

EN\_COURS, ATTEINT, EN\_RETARD 

|INT |
| - |

Ordre d’affichage  1 Si l’objectif est actif  true 

|BOOLEAN |
| - |
|TIMESTAMP |

2024-01-15 Date de création 

10:00:00 Date de modification 

|TIMESTAMP |
| - |

2024-01-15 10:00:00 

|INT |
| - |

ID utilisateur 

1 créateur 

|INT |
| - |

ID utilisateur modificateur 

1 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_sujet\_objectifs VALUES  

(1, 1, 'Formation équipe éthique', 'Former 100% de l\'équipe aux règles éthiques', 'Sensibilisation complète de l\'équipe', 75, 15.50, '2024-03-15', 'EN\_COURS', 1, true, '2024- 01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(2, 1, 'Audit processus', 'Auditer tous les processus critiques', 'Validation des processus par comité', 60, 25.00, '2024-04-30', 'EN\_COURS', 2, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 1, 'Mise en place monitoring', 'Installer système de monitoring éthique', 'Déploiement complet du système', 30, 45.00, '2024-06-15', 'EN\_RETARD', 3, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

-- Pas d'objectifs pour les réunions techniques (objectifs\_actifs = false) 

11. Difficultés par Objectif et Entité 

*Description* 

Détaille les difficultés rencontrées par objectif et par entité (CODIR uniquement). 

*Table : reunion\_objectif\_difficultes* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|objectif\_id|INT |<p>Référence vers </p><p>reunion\_sujet\_objectifs</p>|1 |



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|entite\_id|INT |Référence vers entites|4 |
|description\_difficulte|` `TEXT |Description de la difficulté |"Manque de ressources humaines" |
|niveau\_difficulte|ENUM |FAIBLE, MOYEN, ELEVE, CRITIQUE |"ELEVE" |
|impact|TEXT |Impact de la difficulté |"Retard dans la formation" |
|solution\_proposee|TEXT |Solution proposée |"Recrutement temporaire" |
|statut|ENUM |IDENTIFIEE, EN\_COURS\_RESOLUTION, RESOLUE |"IDENTIFIEE" |
|date\_creation|TIMESTAMP|` `Date de création |2024-01-15 10:00:00 |
|date\_modification|TIMESTAMP|` `Date de modification |2024-01-15 10:00:00 |
|creer\_par|INT |ID utilisateur créateur |1 |
|modifier\_par|INT |ID utilisateur modificateur |1 |

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_objectif\_difficultes VALUES  

(1, 1, 4, 'Manque de ressources humaines', 'ELEVE', 'Retard dans la formation', 'Recrutement temporaire', 'IDENTIFIEE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(2, 1, 5, 'Budget insuffisant', 'CRITIQUE', 'Impossibilité de financer l\'audit', 'Demande de budget supplémentaire', 'EN\_COURS\_RESOLUTION', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 2, 4, 'Disponibilité des équipes', 'MOYEN', 'Difficulté à organiser les sessions', 'Planification flexible', 'RESOLUE', '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.011.png)

-- Pas de difficultés par entité pour les réunions techniques (difficultes\_actives = false) 

12. Décisions de Réunion 

*Description* 

Enregistre les décisions prises lors de la réunion avec leurs responsables. 

*Table : reunion\_decisions* 



|**Type** |
| - |

**Description  Exemple ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.012.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.013.png)**Identifiant unique 

|INT |
| - |

1 1 

|INT |
| - |

Référence vers reunions

|INT |
| - |

Référence vers 

1 

reunion\_sujets

|TEXT |
| - |

"Renforcer le suivi Texte de la décision 

éthique" PROVISOIRE, DEFINITIVE 

|ENUM |
| - |

"DEFINITIVE" "[3, 4, 5]" "2024-03-31" "EN\_COURS" 

|JSON |
| - |

Tableau des IDs des responsables 

|DATE |
| - |

Date limite pour l'exécution 

|ENUM |
| - |

EN\_ATTENTE, EN\_COURS, TERMINEE 

|ENUM |
| - |

FAIBLE, NORMALE, 

"NORMALE" ELEVEE, CRITIQUE 

|TEXT ||
| - | :- |
|TIMESTAMP||
|Commentaire additionnel ||
|` `Date de création ||

"Budget approuvé" 2024-01-15 10:00:00 2024-01-15 10:00:00 1 

|` `TIMESTAMP|
| - |

` `Date de modification 

|ID utilisateur créateur ||
| - | :- |
|ID utilisateur modificateur ||
|INT ||
|INT ||

1 ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.014.png)

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_decisions VALUES  

(1, 1, 1, 'Renforcer le suivi éthique du projet PSD', 

`    `'DEFINITIVE', 

`    `'[3, 4, 5]', -- Jean (DSI), Sophie (DRH), Pierre (DAF)     '2024-03-31', 

`    `'EN\_COURS', 

`    `'NORMALE', 

`    `'Budget approuvé pour les actions', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_decisions VALUES  

(2, 2, 2, 'Adoption de l\'architecture microservices',     'DEFINITIVE', 

`    `'[7, 8, 9]', -- Alice, Bob, Claire 

`    `'2024-03-15', 

`    `'EN\_COURS', 

`    `'ELEVEE', 

`    `'Architecture validée par l\'équipe', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), (3, 2, 2, 'Migration PostgreSQL progressive', 

`    `'DEFINITIVE', 

`    `'[9, 8]', -- Claire, Bob 

`    `'2024-04-30', 

`    `'EN\_COURS', 

`    `'NORMALE', 

`    `'Tests de performance en cours', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

13. Actions de Suivi 

*Description* 

Détaille les actions spécifiques à réaliser suite aux décisions prises. 

*Table : reunion\_actions* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|decision\_id|INT |<p>Référence vers </p><p>reunion\_decisions</p>|1 |
|titre|VARCHAR(200)|` `Titre de l'action |"Recruter consultant éthique" |
|description|TEXT |Description de l'action |"Recrutement d’un consultant spécialisé" |
|responsable\_id|INT |Référence vers users|3 |
|date\_limite|DATE |Date limite |"2024-03-15" |
|statut|ENUM |A\_FAIRE, EN\_COURS, TERMINEE |"A\_FAIRE" |
|commentaire|TEXT |Commentaire |"Budget approuvé" |
|pieces\_jointes|JSON |Pièces jointes liées |Voir détail ci-dessous |
|priorite|ENUM |FAIBLE, NORMALE, ELEVEE, CRITIQUE |"NORMALE" |
|progression|INT |Pourcentage de progression (0-100) |0 |
|date\_creation|TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification|` `TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par|INT |ID utilisateur créateur |1 |
|modifier\_par|INT |ID utilisateur modificateur |1 |

*Détail du Champ JSON* 

**pieces\_jointes :** 

[ 

`  `{ 

`    `"nom": "specifications.pdf", 

`    `"type": "pdf", 

`    `"taille": 512000, 

`    `"url": "/uploads/actions/1/specifications.pdf",     "description": "Spécifications du poste" 

`  `} 

] 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_actions VALUES  

(1, 1, 'Recruter consultant éthique', 'Recrutement d\'un consultant spécialisé', 3, '2024-03-15', 'A\_FAIRE', 'Budget approuvé', '[{"nom": "specifications.pdf", "type": "pdf", "taille": 512000, "url": "/uploads/actions/1/specifications.pdf", "description": "Spécifications du poste"}]', 'NORMALE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(2, 1, 'Organiser réunion inter-services', 'Réunion de coordination DSI-DRH-DAF', 3, '2024- 02-28', 'A\_FAIRE', 'À planifier', '[]', 'NORMALE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 1, 'Préparer rapport mensuel', 'Rapport de suivi éthique mensuel', 3, '2024-03-15', 'A\_FAIRE', 'Template à créer', '[]', 'FAIBLE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_actions VALUES  

(4, 2, 'Créer POC microservices', 'Développer un proof of concept', 7, '2024-02-28', 'A\_FAIRE', 'Utiliser Docker', '[]', 'ELEVEE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(5, 2, 'Documenter architecture', 'Rédiger la documentation technique', 8, '2024-03-07', 'A\_FAIRE', 'Format Markdown', '[]', 'NORMALE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(6, 3, 'Installer PostgreSQL', 'Installation et configuration', 9, '2024-02-20', 'A\_FAIRE', 'Version 15+', '[]', 'NORMALE', 0, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

14. Configuration des Workflows 

*Description* 

Définit les workflows de validation configurés pour chaque type de réunion. 

*Table : reunion\_workflow\_configs* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|type\_reunion\_id|INT |<p>Référence vers </p><p>type\_reunions</p>|1 |
|nom\_workflow|VARCHAR(100)|` `Nom du workflow |"Validation CODIR" |
|etapes|JSON |Configuration des étapes |Voir détail ci- dessous |
|actif|BOOLEAN |Si le workflow est actif |true |
|obligatoire|BOOLEAN |Si le workflow est obligatoire |true |
|configuration|JSON |Configuration générale |Voir détail ci- dessous |
|date\_creation|TIMESTAMP |Date de création |2024-01-15 10:00:00 |
|date\_modification|` `TIMESTAMP |Date de modification |2024-01-15 10:00:00 |
|creer\_par|INT |ID utilisateur créateur |1 |
|modifier\_par|INT |ID utilisateur modificateur |1 |

*Détail des Champs JSON* 

**etapes :** 

[ 

`  `{ 

`    `"ordre": 1, 

`    `"role\_validateur": "PORTEUR", 

`    `"user\_id": null, 

`    `"duree\_limite\_jours": 3, 

`    `"obligatoire": true, 

`    `"description": "Validation par le porteur du projet" 

`  `}, 

`  `{ 

`    `"ordre": 2, 

`    `"role\_validateur": "MEMBRE\_CODIR", 

`    `"user\_id": null, 

`    `"duree\_limite\_jours": 2, 

`    `"obligatoire": true, 

`    `"description": "Validation par les membres CODIR"   }, 

`  `{ 

`    `"ordre": 3, 

`    `"role\_validateur": "SG", 

`    `"user\_id": null, 

`    `"duree\_limite\_jours": 1, 

`    `"obligatoire": true, 

`    `"description": "Validation par le Secrétaire Général" 

`  `}, 

`  `{ 

`    `"ordre": 4, 

`    `"role\_validateur": "AG", 

`    `"user\_id": null, 

`    `"duree\_limite\_jours": 1, 

`    `"obligatoire": true, 

`    `"description": "Validation par l'Administrateur Général"   } 

] 

**configuration :** 

{ 

`  `"ordre\_fixe": true, 

`  `"validation\_parallele": false, 

`  `"notifier\_etape": true, 

`  `"escalade\_automatique": true, 

`  `"duree\_escalade\_jours": 2, 

`  `"notifications\_escalade": { 

`    `"gestionnaire": true, 

`    `"admin": true, 

`    `"participants": false 

`  `}, 

`  `"regles\_validation": { 

`    `"quorum\_minimum": 2,     "majorite\_requise": true,     "veto\_possible": true 

`  `} 

} 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_workflow\_configs VALUES ( 

`    `1, 1, 'Validation CODIR', 

`    `'[{"ordre": 1, "role\_validateur": "PORTEUR", "user\_id": null, "duree\_limite\_jours": 3, "obligatoire": true, "description": "Validation par le porteur du projet"}, {"ordre": 2, "role\_validateur": "MEMBRE\_CODIR", "user\_id": null, "duree\_limite\_jours": 2, "obligatoire": true, "description": "Validation par les membres CODIR"}, {"ordre": 3, "role\_validateur": "SG", "user\_id": null, "duree\_limite\_jours": 1, "obligatoire": true, "description": "Validation par le Secrétaire Général"}, {"ordre": 4, "role\_validateur": "AG", "user\_id": null, "duree\_limite\_jours": 1, "obligatoire": true, "description": "Validation par l\'Administrateur Général"}]', 

`    `true, true, 

`    `'{"ordre\_fixe": true, "validation\_parallele": false, "notifier\_etape": true, "escalade\_automatique": true, "duree\_escalade\_jours": 2, "notifications\_escalade": {"gestionnaire": true, "admin": true, "participants": false}, "regles\_validation": {"quorum\_minimum": 2, "majorite\_requise": true, "veto\_possible": true}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

**Réunion Technique :** 

INSERT INTO reunion\_workflow\_configs VALUES ( 

`    `2, 2, 'Validation Technique', 

`    `'[{"ordre": 1, "role\_validateur": "PRESIDENT", "user\_id": null, "duree\_limite\_jours": 2, "obligatoire": true, "description": "Validation par le DSI"}, {"ordre": 2, "role\_validateur": "PARTICIPANT", "user\_id": null, "duree\_limite\_jours": 1, "obligatoire": false, "description": "Validation par l\'équipe technique"}]', 

`    `true, false, 

`    `'{"ordre\_fixe": false, "validation\_parallele": true, "notifier\_etape": true, "escalade\_automatique": false, "duree\_escalade\_jours": 0, "notifications\_escalade": {"gestionnaire": true, "admin": false, "participants": false}, "regles\_validation": {"quorum\_minimum": 1, "majorite\_requise": false, "veto\_possible": false}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1 

); 

15. Exécution des Workflows 

*Description* 

Trace l'exécution des workflows de validation pour chaque réunion. 

*Table : reunion\_workflow\_executions* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|reunion\_id|INT |Référence vers reunions|1 |
|workflow\_config\_id|` `INT |<p>Référence vers </p><p>reunion\_workflow\_configs</p>|1 |
|etape\_actuelle|INT |Numéro de l’étape actuelle |2 |
|statut\_global|ENUM |EN\_COURS, TERMINE, BLOQUE |"EN\_COURS" |
|date\_debut|TIMESTAMP|` `Date de début du workflow |"2024-02-15 12:00:00" |
|date\_fin|TIMESTAMP|` `Date de fin du workflow |null |
|historique\_etapes|JSON |Historique des étapes |Voir détail ci- dessous |
|commentaire|TEXT |Commentaire sur l'exécution |"Workflow en cours" |
|date\_creation|TIMESTAMP|` `Date de création |2024-02-15 12:00:00 |
|date\_modification|TIMESTAMP|` `Date de modification |2024-02-15 12:00:00 |

*Détail du Champ JSON* 

**historique\_etapes :** 

[ 

`  `{ 

`    `"etape": 1, 

`    `"validateur": 3, 

`    `"statut": "VALIDE", 

`    `"date": "2024-02-15 12:00:00", 

`    `"commentaire": "Validation porteur effectuée" 

`  `}, 

`  `{ 

`    `"etape": 2, 

`    `"validateur": 4, 

`    `"statut": "EN\_ATTENTE", 

`    `"date": "2024-02-15 12:00:00", 

`    `"commentaire": "En attente validation membres"   } 

] 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_workflow\_executions VALUES ( 

`    `1, 1, 1, 2, 'EN\_COURS', '2024-02-15 12:00:00', null, 

`    `'[{"etape": 1, "validateur": 3, "statut": "VALIDE", "date": "2024-02-15 12:00:00", "commentaire": "Validation porteur effectuée"}, {"etape": 2, "validateur": 4, "statut": "EN\_ATTENTE", "date": "2024-02-15 12:00:00", "commentaire": "En attente validation membres"}]', 

`    `'Workflow en cours de validation', 

`    `'2024-02-15 12:00:00', '2024-02-15 12:00:00' 

); 

**Réunion Technique :** 

INSERT INTO reunion\_workflow\_executions VALUES ( 

`    `2, 2, 2, 1, 'EN\_COURS', '2024-02-13 15:30:00', null, 

`    `'[{"etape": 1, "validateur": 3, "statut": "EN\_ATTENTE", "date": "2024-02-13 15:30:00", "commentaire": "En attente validation DSI"}]', 

`    `'Validation technique en cours', 

`    `'2024-02-13 15:30:00', '2024-02-13 15:30:00' 

); 

16. Procès-Verbaux 

*Description* 

Gère les procès-verbaux des réunions avec leur validation. 

*Table : reunion\_pvs* 



|**Description** ||
| - | :- |
|Identifiant unique ||
|<p>Référence vers </p><p>reunions</p>||
|Contenu du procès- verbal ||
|Référence vers users||
|` `Date de rédaction ||
|` `Date de modification ||
|Numéro de version ||
|Référence vers users||
|` `Date de validation ||
|BROUILLON, VALIDE, PUBLIE ||
|1 ||
|1 ||
|"CODIR Février 2024 – Procès-Verbal..." ||
|1 ||
|"2024-02-15 10:35:00" ||
|"2024-02-15 10:35:00" ||
|1 ||
|1 ||
|"2024-02-15 11:00:00" ||
|"VALIDE" ||

**Champ  Type  ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.015.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.016.png)**

id INT  reunion\_id INT  

contenu TEXT  

redige\_par\_id INT  redige\_le TIMESTAMP modifie\_le TIMESTAMP version INT  valide\_par\_id INT  valide\_le TIMESTAMP 

statut ENUM  commentaire\_validation TEXT  notifications\_envoyees 

|Commentaire de validation |
| :- |
|Si les notifications ont été envoyées |
|` `Date de création |
|` `Date de modification |

"PV validé par le Président" 

|date\_creation|
| - |

` `BOOLEAN  TIMESTAMP 

true 

2024-02-15 10:35:00 2024-02-15 11:00:00 

date\_modification TIMESTAMP

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_pvs VALUES (     1, 1,  

`    `'CODIR Février 2024 - Procès-Verbal 

1. Ouverture de séance (09:00) 

Présents : Admin (Président), Jean (DSI), Sophie (DRH), Pierre (DAF), Invité Absent : Marie (Secrétaire) - Congé maladie 

2. Point 1 : Chantier PSD (09:05-09:50) 
   1. Sujet : Gestion d\'éthique 
   1. Objectifs discutés : Formation (75%), Audit (60%), Monitoring (30%) 
   1. Difficultés identifiées : Ressources DRH, Budget DAF 
   1. Décision : Renforcement du suivi éthique 
   1. Responsables : Jean, Sophie, Pierre 
   1. Actions : Recrutement consultant, Réunion inter-services, Rapport mensuel 
2. Point 2 : Budget Q1 (09:50-10:20) [...] 
2. Points divers (10:20-10:35) 

[...] 

Clôture : 10:35', 

`    `1, '2024-02-15 10:35:00', '2024-02-15 10:35:00', 1, 

`    `1, '2024-02-15 11:00:00', 'VALIDE', 'PV validé par le Président', true,     '2024-02-15 10:35:00', '2024-02-15 11:00:00' 

); 

**Réunion Technique :** 

INSERT INTO reunion\_pvs VALUES ( 

`    `2, 2,  

`    `'Réunion Technique - Architecture Système Paiement Date : 13/02/2024 - 14:00-15:30 

Lieu : Salle technique + Visioconférence 

Présents : Jean (DSI), Alice, Bob, Claire, Expert Sécurité Absents : Aucun 

1. Architecture API (14:00-14:30) 
   1. Présentation de l\'architecture microservices 
   1. Décision : Adoption de l\'approche microservices 
   1. Actions : POC Docker (Alice), Documentation (Bob) 
   1. Échéance : 28/02 pour POC, 07/03 pour doc 
1. Base de données (14:30-14:55) 
   1. Choix PostgreSQL validé 
   1. Migration progressive sur 3 mois 
   1. Actions : Installation (Claire), Tests (Bob) 
   1. Échéance : 20/02 installation, 30/04 migration 
1. Sécurité (14:55-15:15) 
   1. Standards AES-256 + TLS 1.3 approuvés 
   1. Audit sécurité obligatoire 
   1. Actions : Audit (Expert), Implémentation (Alice) 
   1. Échéance : 15/03 audit, 31/03 implémentation 
1. Planning (15:15-15:30) 
- Planning 6 mois avec milestones 
- Décision provisoire : Ajouter semaine de buffer 
- Action : Révision planning (Jean) 
- Échéance : 20/02 pour présentation CODIR 

Prochaine réunion : Mardi 20/02 - 14:00 

Sujet : Révision planning et début POC', 

`    `3, '2024-02-13 15:30:00', '2024-02-13 15:30:00', 1, 

`    `3, '2024-02-13 16:00:00', 'VALIDE', 'PV validé par le DSI', true,     '2024-02-13 15:30:00', '2024-02-13 16:00:00' 

); 


17. Validateurs de PV par Type 

*Description* 

Configure qui peut valider les procès-verbaux pour chaque type de réunion. 

*Table : type\_reunion\_validateur\_pvs* 



|**Type** |
| - |

**Description  Exemple ![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.017.png)![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.018.png)**Identifiant unique 

|INT |
| - |

1 1 

|INT |
| - |

Référence vers type\_reunions

|ENUM |
| - |

SECRETAIRE, PRESIDENT, 

"SECRETAIRE" 

AUTRE 

|INT |
| - |

Référence vers users (si validateur spécifique) 

2 



|INT |
| - |

Ordre de priorité  1 Si le validateur est actif  true 

|BOOLEAN ||
| - | :- |
|TIMESTAMP||
|` `TIMESTAMP||
|` `Date de création ||
|` `Date de modification ||

2024-01-15 10:00:00 

2024-01-15 10:00:00 

|INT |
| - |

ID utilisateur créateur  1 ID utilisateur modificateur  1 

modifier\_par INT 

*Exemples* 

**CODIR :** 

INSERT INTO type\_reunion\_validateur\_pvs VALUES  

(1, 1, 'SECRETAIRE', 2, 1, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), (2, 1, 'PRESIDENT', 1, 2, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); **Réunion Technique :** 

INSERT INTO type\_reunion\_validateur\_pvs VALUES  

(3, 2, 'PRESIDENT', 3, 1, true, '2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

18. Notifications 

*Description* 

Trace toutes les notifications envoyées pour les réunions. 

*Table : reunion\_notifications* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|reunion\_id|INT |Référence vers reunions|1 |



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|type|ENUM |CONFIRMATION\_PRESE NCE, RAPPEL\_24H, RAPPEL\_1H, RAPPEL\_15MIN, PV\_DISPONIBLE, RAPPEL\_ACTIONS |"CONFIRMATION\_PRESE NCE" |
|envoye\_a|INT |Référence vers users|1 |
|envoye\_le|TIMESTA MP |Date d'envoi |"2024-02-10 10:00:00" |
|statut|ENUM |ENVOYE, LU, ERREUR |"ENVOYE" |
|contenu\_email|TEXT |Contenu de l'email |"Invitation CODIR Février 2024" |
|configuration\_t ype|JSON |Configuration selon le type de réunion |Voir détail ci-dessous |
|date\_creation|TIMESTA MP |Date de création |2024-02-10 10:00:00 |

*Détail du Champ JSON* 

**configuration\_type :** 

{ 

`  `"template\_utilise": "codir\_presence.html", 

`  `"sujet\_email": "Invitation CODIR Février 2024", 

`  `"priorite": "HAUTE", 

`  `"canaux": ["email"], 

`  `"relance\_automatique": true, 

`  `"delai\_relance\_jours": 1, 

`  `"notifications\_suivantes": ["RAPPEL\_24H", "RAPPEL\_1H"] }

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_notifications VALUES  

(1, 1, 'CONFIRMATION\_PRESENCE', 1, '2024-02-10 10:00:00', 'ENVOYE', 'Invitation CODIR Février 2024', 

`    `'{"template\_utilise": "codir\_presence.html", "sujet\_email": "Invitation CODIR Février 2024", "priorite": "HAUTE", "canaux": ["email"], "relance\_automatique": true, "delai\_relance\_jours": 1, "notifications\_suivantes": ["RAPPEL\_24H", "RAPPEL\_1H"]}',     '2024-02-10 10:00:00'), 

(2, 1, 'RAPPEL\_24H', 1, '2024-02-14 09:00:00', 'ENVOYE', 'Rappel CODIR demain', 

`    `'{"template\_utilise": "codir\_rappel.html", "sujet\_email": "Rappel CODIR demain", "priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "notifications\_suivantes": []}', 

`    `'2024-02-14 09:00:00'), 

(3, 1, 'PV\_DISPONIBLE', 1, '2024-02-15 11:05:00', 'ENVOYE', 'PV CODIR Février 2024 disponible', 

`    `'{"template\_utilise": "codir\_pv.html", "sujet\_email": "PV CODIR Février 2024 disponible", "priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "notifications\_suivantes": []}', 

`    `'2024-02-15 11:05:00'); 

**Réunion Technique :** 

INSERT INTO reunion\_notifications VALUES  

(4, 2, 'CONFIRMATION\_PRESENCE', 3, '2024-02-12 10:00:00', 'ENVOYE', 'Invitation Réunion Tech - Architecture Paiement', 

`    `'{"template\_utilise": "tech\_presence.html", "sujet\_email": "Invitation Réunion Tech - Architecture Paiement", "priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "notifications\_suivantes": []}', 

`    `'2024-02-12 10:00:00'), 

(5, 2, 'PV\_DISPONIBLE', 3, '2024-02-13 16:05:00', 'ENVOYE', 'PV Réunion Tech - Architecture Paiement', 

`    `'{"template\_utilise": "tech\_pv.html", "sujet\_email": "PV Réunion Tech - Architecture Paiement", "priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "notifications\_suivantes": []}', 

`    `'2024-02-13 16:05:00'); 

19. Configuration des Notifications 

*Description* 

Configure les notifications par type de réunion avec leurs paramètres spécifiques. 

*Table : reunion\_notification\_configs* 



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|id|INT |Identifiant unique |1 |
|type\_reunion\_id|INT |<p>Référence vers </p><p>type\_reunions</p>|1 |
|type\_notification|ENUM |CONFIRMATION\_PRE SENCE, RAPPEL, PV\_DISPONIBLE, RAPPEL\_ACTIONS |"CONFIRMATION\_PRE SENCE" |
|actif|BOOLEAN |Si la notification est active |true |
|delai\_jours|INT |Délai en jours pour les rappels |1 |
|template\_email|VARCHAR( 100) |Template email à utiliser |"codir\_presence.html" |
|destinataires\_par\_ defaut|JSON |Destinataires par défaut |Voir détail ci-dessous |
|configuration\_avan cee|JSON |Configuration avancée |Voir détail ci-dessous |



|**Champ** |**Type** |**Description** |**Exemple** |
| - | - | - | - |
|date\_creation|TIMESTAM P |Date de création |2024-01-15 10:00:00 |
|date\_modification|TIMESTAM P |Date de modification |2024-01-15 10:00:00 |
|creer\_par|INT |ID utilisateur créateur |1 |
|modifier\_par|INT |ID utilisateur modificateur |1 |

*Détail des Champs JSON* 

**destinataires\_par\_defaut :** 

{ 

`  `"roles": ["PRESIDENT", "SECRETAIRE", "PARTICIPANT"],   "types": ["PERMANENT", "INVITE"], 

`  `"exclusions": ["ABSENT"], 

`  `"gestionnaire": true, 

`  `"admin": false, 

`  `"emails\_supplementaires": ["admin@govtrack.gov"] 

} 

**configuration\_avancee :** 

{ 

`  `"priorite": "HAUTE", 

`  `"canaux": ["email", "notification\_app"], 

`  `"relance\_automatique": true, 

`  `"delai\_relance\_jours": 1, 

`  `"nombre\_relances\_max": 3, 

`  `"conditions\_envoi": { 

`    `"quorum\_atteint": true, 

`    `"pv\_valide": false, 

`    `"actions\_en\_cours": true 

`  `}, 

`  `"personnalisation": { 

`    `"sujet\_dynamique": true, 

`    `"contenu\_dynamique": true, 

`    `"variables\_disponibles": ["{nom\_reunion}", "{date}", "{lieu}", "{participants}"]   } 

} 

*Exemples* 

**CODIR :** 

INSERT INTO reunion\_notification\_configs VALUES  

(1, 1, 'CONFIRMATION\_PRESENCE', true, 7, 'codir\_presence.html',     '{"roles": ["PRESIDENT", "SECRETAIRE", "PARTICIPANT"], "types": ["PERMANENT", "INVITE"], "exclusions": ["ABSENT"], "gestionnaire": true, "admin": false, "emails\_supplementaires": ["admin@govtrack.gov"]}', 

`    `'{"priorite": "HAUTE", "canaux": ["email", "notification\_app"], "relance\_automatique": true, "delai\_relance\_jours": 1, "nombre\_relances\_max": 3, "conditions\_envoi": {"quorum\_atteint": true, "pv\_valide": false, "actions\_en\_cours": true}, "personnalisation": {"sujet\_dynamique": true, "contenu\_dynamique": true, "variables\_disponibles": ["{nom\_reunion}", "{date}", "{lieu}", "{participants}"]}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(2, 1, 'RAPPEL', true, 1, 'codir\_rappel.html', 

`    `'{"roles": ["PRESIDENT", "SECRETAIRE", "PARTICIPANT"], "types": ["PERMANENT", "INVITE"], "exclusions": ["ABSENT"], "gestionnaire": false, "admin": false, "emails\_supplementaires": []}', 

`    `'{"priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "nombre\_relances\_max": 0, "conditions\_envoi": {"quorum\_atteint": false, "pv\_valide": false, "actions\_en\_cours": false}, "personnalisation": {"sujet\_dynamique": true, "contenu\_dynamique": true, "variables\_disponibles": ["{nom\_reunion}", "{date}", "{lieu}"]}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(3, 1, 'PV\_DISPONIBLE', true, 0, 'codir\_pv.html', 

`    `'{"roles": ["PRESIDENT", "SECRETAIRE", "PARTICIPANT"], "types": ["PERMANENT", "INVITE"], "exclusions": [], "gestionnaire": true, "admin": false, 

"emails\_supplementaires": []}', 

`    `'{"priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "nombre\_relances\_max": 0, "conditions\_envoi": {"quorum\_atteint": false, "pv\_valide": true, "actions\_en\_cours": false}, "personnalisation": {"sujet\_dynamique": true, "contenu\_dynamique": true, "variables\_disponibles": ["{nom\_reunion}", "{date}", "{pv\_url}"]}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

**Réunion Technique :** 

INSERT INTO reunion\_notification\_configs VALUES  

(4, 2, 'CONFIRMATION\_PRESENCE', true, 1, 'tech\_presence.html', 

`    `'{"roles": ["PRESIDENT", "PARTICIPANT"], "types": ["PERMANENT", "INVITE"], "exclusions": ["ABSENT"], "gestionnaire": false, "admin": false, "emails\_supplementaires": []}', 

`    `'{"priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "nombre\_relances\_max": 0, "conditions\_envoi": {"quorum\_atteint": false, "pv\_valide": false, "actions\_en\_cours": false}, "personnalisation": {"sujet\_dynamique": true, "contenu\_dynamique": true, "variables\_disponibles": ["{nom\_reunion}", "{date}", "{lieu}"]}}', 

`    `'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1), 

(5, 2, 'PV\_DISPONIBLE', true, 0, 'tech\_pv.html', 

`    `'{"roles": ["PRESIDENT", "PARTICIPANT"], "types": ["PERMANENT", "INVITE"], "exclusions": [], "gestionnaire": false, "admin": false, "emails\_supplementaires": []}',     '{"priorite": "NORMALE", "canaux": ["email"], "relance\_automatique": false, "delai\_relance\_jours": 0, "nombre\_relances\_max": 0, "conditions\_envoi": {"quorum\_atteint": false, "pv\_valide": true, "actions\_en\_cours": false}, "personnalisation": {"sujet\_dynamique": true, "contenu\_dynamique": true, "variables\_disponibles": ["{nom\_reunion}", "{date}", "{pv\_url}"]}}', 

'2024-01-15 10:00:00', '2024-01-15 10:00:00', 1, 1); 

🔄 Workflows et Processus 

1. Processus de Génération Automatique 

![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.019.jpeg)

2. Processus de Réunion 

![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.020.jpeg)

![](Aspose.Words.35d283bc-5ccb-4e23-85da-bca00ee8143b.021.jpeg)

🔄 Exemples Complets 

Scénario 1 : CODIR Mensuel (Complexe) 

**Configuration :** 

- Type : CODIR (COMPLEXE) 
- Périodicité : Mensuelle (15 de chaque mois) 
- Participants : 6 permanents + invités 
- Fonctionnalités : Objectifs multiples, Difficultés par entité, Workflow validation 

**Workflow :** 

1. **Génération automatique** le 15 février à 8h 
1. **Invitations** envoyées automatiquement 
1. **Confirmations** de présence (5/6 confirmés) 
1. **Réunion** avec 3 points à l'ordre du jour 
1. **Discussions** détaillées avec objectifs et difficultés 
1. **Décisions** prises avec workflow de validation 
1. **PV** rédigé et validé par le Président 
1. **Actions** créées et assignées 
1. **Notifications** envoyées aux participants 

**Tables utilisées :** 20 tables complètes 

Scénario 2 : Réunion Technique Hebdo (Intermédiaire) 

**Configuration :** 

- Type : Réunion Technique (INTERMEDIAIRE) 
- Périodicité : Hebdomadaire (Mardi 14h) 
- Participants : 4 permanents + 1 invité 
- Fonctionnalités : Workflow validation, PV validation 

**Workflow :** 

1. **Génération automatique** le mardi à 8h 
1. **Invitations** envoyées 
1. **Confirmations** de présence (5/5 confirmés) 
1. **Réunion** avec 4 points techniques 
1. **Discussions** techniques simples 
1. **Décisions** prises avec validation DSI 
1. **PV** rédigé et validé 
1. **Actions** techniques créées 
1. **Notifications** envoyées 

**Tables utilisées :** 15 tables 
