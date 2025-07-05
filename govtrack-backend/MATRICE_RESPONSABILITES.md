# 📊 Matrice des Responsabilités - GovTrack

## 🎯 Vue d'ensemble

Cette matrice définit les responsabilités et permissions de chaque rôle dans l'application GovTrack, un système de gestion de projets gouvernementaux.

---

## 👥 Rôles Définis

### 🔓 **Administrateur**
- **Description** : Accès complet au système
- **Niveau** : Système
- **Responsabilités** : Gestion globale, configuration, supervision

### 🏢 **Directeur**
- **Description** : Responsable d'entité
- **Niveau** : Entité
- **Responsabilités** : Gestion de son entité, projets et équipes

### 👤 **Employé**
- **Description** : Utilisateur standard
- **Niveau** : Personnel
- **Responsabilités** : Gestion de ses projets et tâches

---

## 📋 Matrice des Permissions

| **Module** | **Permission** | **Description** | **Administrateur** | **Directeur** | **Employé** |
|------------|----------------|-----------------|-------------------|---------------|-------------|
| **🔐 AUTHENTIFICATION** |
| | `login` | Se connecter | ✅ | ✅ | ✅ |
| | `logout` | Se déconnecter | ✅ | ✅ | ✅ |
| | `refresh_token` | Rafraîchir le token | ✅ | ✅ | ✅ |
| **👥 GESTION DES UTILISATEURS** |
| | `view_users_list` | Voir la liste des utilisateurs | ✅ | ❌ | ❌ |
| | `create_user` | Créer un utilisateur | ✅ | ❌ | ❌ |
| | `edit_user` | Modifier un utilisateur | ✅ | ❌ | ❌ |
| | `delete_user` | Supprimer un utilisateur | ✅ | ❌ | ❌ |
| | `view_user_details` | Voir les détails d'un utilisateur | ✅ | ❌ | ❌ |
| | `manage_user_assignments` | Gérer les affectations d'un utilisateur | ✅ | ❌ | ❌ |
| | `manage_user_roles` | Gérer les rôles d'un utilisateur | ✅ | ❌ | ❌ |
| | `view_user_stats` | Voir les statistiques des utilisateurs | ✅ | ❌ | ❌ |
| **🏢 GESTION DES ENTITÉS** |
| | `view_entities_list` | Voir la liste des entités | ✅ | ❌ | ❌ |
| | `create_entity` | Créer une entité | ✅ | ❌ | ❌ |
| | `edit_entity` | Modifier une entité | ✅ | ❌ | ❌ |
| | `delete_entity` | Supprimer une entité | ✅ | ❌ | ❌ |
| | `view_entity_details` | Voir les détails d'une entité | ✅ | ❌ | ❌ |
| | `view_entity_hierarchy` | Voir la hiérarchie des entités | ✅ | ❌ | ❌ |
| | `view_entity_users` | Voir les utilisateurs d'une entité | ✅ | ❌ | ❌ |
| | `manage_entity_assignments` | Gérer les affectations d'une entité | ✅ | ❌ | ❌ |
| | `view_entity_chief_history` | Voir l'historique des chefs d'entité | ✅ | ❌ | ❌ |
| **🎭 GESTION DES RÔLES** |
| | `view_roles_list` | Voir la liste des rôles | ✅ | ❌ | ❌ |
| | `create_role` | Créer un nouveau rôle | ✅ | ❌ | ❌ |
| | `edit_role` | Modifier un rôle existant | ✅ | ❌ | ❌ |
| | `delete_role` | Supprimer un rôle | ✅ | ❌ | ❌ |
| | `view_role_details` | Voir les détails d'un rôle | ✅ | ❌ | ❌ |
| | `assign_permissions_to_role` | Assigner des permissions à un rôle | ✅ | ❌ | ❌ |
| | `remove_permissions_from_role` | Retirer des permissions d'un rôle | ✅ | ❌ | ❌ |
| | `view_role_users` | Voir les utilisateurs d'un rôle | ✅ | ❌ | ❌ |
| | `assign_role_to_user` | Assigner un rôle à un utilisateur | ✅ | ❌ | ❌ |
| | `remove_role_from_user` | Retirer un rôle d'un utilisateur | ✅ | ❌ | ❌ |
| | `view_role_stats` | Voir les statistiques d'un rôle | ✅ | ❌ | ❌ |
| **🔑 GESTION DES PERMISSIONS** |
| | `view_permissions_list` | Voir la liste des permissions | ✅ | ❌ | ❌ |
| | `create_permission` | Créer une nouvelle permission | ✅ | ❌ | ❌ |
| | `edit_permission` | Modifier une permission existante | ✅ | ❌ | ❌ |
| | `delete_permission` | Supprimer une permission | ✅ | ❌ | ❌ |
| | `view_permission_details` | Voir les détails d'une permission | ✅ | ❌ | ❌ |
| | `view_permission_users` | Voir les utilisateurs d'une permission | ✅ | ❌ | ❌ |
| | `view_permission_roles` | Voir les rôles d'une permission | ✅ | ❌ | ❌ |
| | `view_permission_stats` | Voir les statistiques d'une permission | ✅ | ❌ | ❌ |
| **📋 TYPES DE PROJETS** |
| | `view_type_projets_list` | Voir la liste des types de projets | ✅ | ❌ | ❌ |
| | `create_type_projet` | Créer un nouveau type de projet | ✅ | ❌ | ❌ |
| | `edit_type_projet` | Modifier un type de projet existant | ✅ | ❌ | ❌ |
| | `delete_type_projet` | Supprimer un type de projet | ✅ | ❌ | ❌ |
| | `view_type_projet_details` | Voir les détails d'un type de projet | ✅ | ❌ | ❌ |
| | `view_type_projet_stats` | Voir les statistiques des types de projets | ✅ | ❌ | ❌ |
| | `view_type_projet_projects` | Voir les projets d'un type spécifique | ✅ | ❌ | ❌ |
| | `manage_type_projet_projects` | Gérer les projets d'un type spécifique | ✅ | ❌ | ❌ |
| | `configure_type_projet_sla` | Configurer les SLA des types de projets | ✅ | ❌ | ❌ |
| | `manage_type_projet_workflow` | Gérer les workflows des types de projets | ✅ | ❌ | ❌ |
| **🚀 GESTION DES PROJETS** |
| | `view_projects_list` | Voir la liste des projets | ✅ | ✅ | ✅ |
| | `create_project` | Créer un projet | ✅ | ✅ | ❌ |
| | `edit_project` | Modifier un projet | ✅ | ✅ | ❌ |
| | `delete_project` | Supprimer un projet | ✅ | ❌ | ❌ |
| | `view_project_details` | Voir les détails d'un projet | ✅ | ✅ | ✅ |
| | `update_project_execution_level` | Mettre à jour le niveau d'exécution | ✅ | ✅ | ❌ |
| | `change_project_status` | Changer le statut d'un projet | ✅ | ✅ | ❌ |
| | `view_project_history` | Voir l'historique d'un projet | ✅ | ✅ | ✅ |
| **📋 TÂCHES DES PROJETS** |
| | `view_project_tasks` | Voir les tâches d'un projet | ✅ | ✅ | ✅ |
| | `create_project_task` | Créer une tâche d'un projet | ✅ | ✅ | ❌ |
| | `edit_project_task` | Modifier une tâche d'un projet | ✅ | ✅ | ❌ |
| | `delete_project_task` | Supprimer une tâche d'un projet | ✅ | ❌ | ❌ |
| | `view_project_task_details` | Voir les détails d'une tâche | ✅ | ✅ | ✅ |
| **📎 PIÈCES JOINTES PROJETS** |
| | `add_project_attachment` | Ajouter une pièce jointe | ✅ | ✅ | ❌ |
| | `view_project_attachments` | Voir les pièces jointes | ✅ | ✅ | ✅ |
| | `download_project_attachment` | Télécharger une pièce jointe | ✅ | ✅ | ✅ |
| | `edit_project_attachment` | Modifier une pièce jointe | ✅ | ✅ | ❌ |
| | `delete_project_attachment` | Supprimer une pièce jointe | ✅ | ❌ | ❌ |
| **💬 COMMENTAIRES PROJETS** |
| | `add_project_comment` | Ajouter un commentaire | ✅ | ✅ | ❌ |
| | `view_project_comments` | Voir les commentaires | ✅ | ✅ | ✅ |
| | `edit_project_comment` | Modifier un commentaire | ✅ | ✅ | ❌ |
| | `delete_project_comment` | Supprimer un commentaire | ✅ | ❌ | ❌ |
| | `view_project_comment_stats` | Voir les statistiques des commentaires | ✅ | ✅ | ✅ |
| **📋 GESTION DES TÂCHES** |
| | `view_tasks_list` | Voir la liste des tâches | ✅ | ✅ | ✅ |
| | `create_task` | Créer une tâche | ✅ | ✅ | ❌ |
| | `edit_task` | Modifier une tâche | ✅ | ✅ | ❌ |
| | `delete_task` | Supprimer une tâche | ✅ | ❌ | ❌ |
| | `view_task_details` | Voir les détails d'une tâche | ✅ | ✅ | ✅ |
| | `change_task_status` | Changer le statut d'une tâche | ✅ | ✅ | ❌ |
| | `view_task_history` | Voir l'historique d'une tâche | ✅ | ✅ | ✅ |
| **📎 PIÈCES JOINTES TÂCHES** |
| | `add_task_attachment` | Ajouter une pièce jointe | ✅ | ✅ | ❌ |
| | `view_task_attachments` | Voir les pièces jointes | ✅ | ✅ | ✅ |
| | `download_task_attachment` | Télécharger une pièce jointe | ✅ | ✅ | ✅ |
| | `delete_task_attachment` | Supprimer une pièce jointe | ✅ | ❌ | ❌ |
| **💬 COMMENTAIRES TÂCHES** |
| | `add_task_comment` | Ajouter un commentaire | ✅ | ✅ | ❌ |
| | `view_task_comments` | Voir les commentaires | ✅ | ✅ | ✅ |
| | `edit_task_comment` | Modifier un commentaire | ✅ | ✅ | ❌ |
| | `delete_task_comment` | Supprimer un commentaire | ✅ | ❌ | ❌ |
| | `view_task_comment_stats` | Voir les statistiques des commentaires | ✅ | ✅ | ✅ |
| **📊 VISUALISATION DES PROJETS** |
| | `view_my_projects` | Voir mes projets | ✅ | ✅ | ✅ |
| | `view_all_projects` | Voir tous les projets | ✅ | ❌ | ❌ |
| | `view_my_entity_projects` | Voir les projets de mon entité | ✅ | ✅ | ❌ |
| **📝 INSTRUCTIONS** |
| | `create_instruction` | Créer une instruction | ✅ | ✅ | ✅ |
| | `edit_instruction` | Modifier une instruction | ✅ | ✅ | ✅ |
| | `validate_instruction` | Valider une instruction | ✅ | ✅ | ❌ |
| | `view_all_instructions` | Voir toutes les instructions | ✅ | ✅ | ❌ |
| | `terminate_project` | Terminer un projet | ✅ | ✅ | ❌ |
| **🔍 AUDIT** |
| | `view_audit_logs` | Consulter les logs d'audit | ✅ | ❌ | ❌ |
| | `export_audit_logs` | Exporter les logs d'audit | ✅ | ❌ | ❌ |
| **⚙️ GESTION SYSTÈME** |
| | `manage_users` | Gérer les utilisateurs | ✅ | ❌ | ❌ |
| | `manage_entities` | Gérer les entités | ✅ | ❌ | ❌ |

---

## 🎯 Résumé par Rôle

### 🔓 **Administrateur** (Niveau Système)
- **Accès** : Toutes les permissions (85 permissions)
- **Responsabilités** :
  - Gestion complète du système
  - Configuration des utilisateurs, rôles, permissions
  - Gestion des entités et types de projets
  - Supervision et audit
  - Support technique

### 🏢 **Directeur** (Niveau Entité)
- **Accès** : Gestion de son entité et projets associés (35 permissions)
- **Responsabilités** :
  - Gestion des projets de son entité
  - Création et modification de projets
  - Gestion des tâches et commentaires
  - Validation d'instructions
  - Supervision de son équipe

### 👤 **Employé** (Niveau Personnel)
- **Accès** : Ses projets et tâches personnelles (15 permissions)
- **Responsabilités** :
  - Consultation de ses projets
  - Création d'instructions
  - Consultation des tâches et commentaires
  - Téléchargement de documents

---

## 🔒 Politique de Sécurité

### Principe du Moindre Privilège
- Chaque utilisateur n'a accès qu'aux fonctionnalités nécessaires à son rôle
- Les permissions sont granulaires et spécifiques
- Audit complet de toutes les actions

### Hiérarchie des Accès
1. **Système** (Administrateur) : Accès global
2. **Entité** (Directeur) : Accès limité à son entité
3. **Personnel** (Employé) : Accès limité à ses données

### Contrôles d'Accès
- Authentification obligatoire
- Vérification des permissions à chaque action
- Logs d'audit pour toutes les opérations sensibles
- Session sécurisée avec expiration automatique

---

## 📈 Métriques de Sécurité

| **Métrique** | **Administrateur** | **Directeur** | **Employé** |
|--------------|-------------------|---------------|-------------|
| **Permissions totales** | 85 | 35 | 15 |
| **Niveau d'accès** | Système | Entité | Personnel |
| **Actions critiques** | Toutes | Création/Modification | Consultation |
| **Données sensibles** | Toutes | Son entité | Ses données |

---

## 🔍 Détail des Permissions par Module

### 📊 **Répartition des Permissions**

| **Module** | **Total** | **Admin** | **Directeur** | **Employé** |
|------------|-----------|-----------|---------------|-------------|
| **Authentification** | 3 | 3 | 3 | 3 |
| **Gestion Utilisateurs** | 8 | 8 | 0 | 0 |
| **Gestion Entités** | 9 | 9 | 0 | 0 |
| **Gestion Rôles** | 11 | 11 | 0 | 0 |
| **Gestion Permissions** | 8 | 8 | 0 | 0 |
| **Types de Projets** | 10 | 10 | 0 | 0 |
| **Gestion Projets** | 8 | 8 | 7 | 4 |
| **Tâches Projets** | 5 | 5 | 4 | 2 |
| **Pièces Jointes Projets** | 5 | 5 | 4 | 2 |
| **Commentaires Projets** | 5 | 5 | 4 | 2 |
| **Gestion Tâches** | 7 | 7 | 6 | 3 |
| **Pièces Jointes Tâches** | 4 | 4 | 3 | 2 |
| **Commentaires Tâches** | 5 | 5 | 4 | 2 |
| **Visualisation Projets** | 3 | 3 | 2 | 1 |
| **Instructions** | 5 | 5 | 4 | 2 |
| **Audit** | 2 | 2 | 0 | 0 |
| **Gestion Système** | 2 | 2 | 0 | 0 |

---

*Dernière mise à jour : Décembre 2024*
*Version : 1.0* 
