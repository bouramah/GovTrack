# 🏛️ GovTrack Backend - API Laravel

## 📋 Description
API Laravel pour le système de gestion et suivi d'instructions et recommandations gouvernementales. Ce projet permet de suivre l'exécution des directives données lors de conseils de cabinet ou autres instances décisionnelles.

## 🚀 Fonctionnalités principales

### ✅ Partie 1 - Gestion des utilisateurs (Implémentée)
- **Gestion des entités organisationnelles** avec hiérarchie
- **Gestion des types d'entités** (Direction, Service, Division)
- **Gestion des postes et affectations**
- **Système complet de rôles et permissions**
- **Historique des affectations et responsabilités**
- **API REST complète** pour toutes les entités

### 🔄 Prochaines parties
- **Partie 2** : Gestion des instructions et recommandations
- **Partie 3** : Tableaux de bord et notifications

## 🛠️ Technologies utilisées
- **Framework** : Laravel 12
- **Base de données** : MySQL
- **Authentification** : Laravel Sanctum
- **API** : RESTful API avec ressources

## 📦 Installation

### Prérequis
- PHP >= 8.2
- Composer
- MySQL
- Node.js (pour le frontend React à venir)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone <repository-url>
cd govtrack-backend
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configuration de la base de données**
Modifier le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=govtrack_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

5. **Créer la base de données**
```sql
CREATE DATABASE govtrack_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. **Exécuter les migrations et seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Lancer le serveur de développement**
```bash
php artisan serve
```

## 📊 Structure de la base de données

### Tables principales
- **users** : Utilisateurs avec informations étendues
- **type_entites** : Types d'entités organisationnelles
- **entites** : Entités avec hiérarchie
- **postes** : Postes disponibles
- **roles** : Rôles système
- **permissions** : Permissions granulaires

### Tables de liaison et historiques
- **utilisateur_entite_histories** : Historique des affectations
- **entite_chef_histories** : Historique des responsables
- **role_permission** : Liaison rôles-permissions
- **utilisateur_role** : Liaison utilisateurs-rôles

## 🔌 API Endpoints

Toutes les routes sont préfixées par `/api/v1`

### Types d'entité
- `GET /api/v1/type-entites` - Liste des types
- `POST /api/v1/type-entites` - Créer un type
- `GET /api/v1/type-entites/{id}` - Détails d'un type
- `PUT /api/v1/type-entites/{id}` - Modifier un type
- `DELETE /api/v1/type-entites/{id}` - Supprimer un type

### Entités
- `GET /api/v1/entites` - Liste des entités
- `POST /api/v1/entites` - Créer une entité
- `GET /api/v1/entites/{id}` - Détails d'une entité
- `PUT /api/v1/entites/{id}` - Modifier une entité
- `DELETE /api/v1/entites/{id}` - Supprimer une entité
- `GET /api/v1/entites/{id}/enfants` - Entités enfants
- `GET /api/v1/entites/{id}/hierarchy` - Hiérarchie complète

### Utilisateurs
- `GET /api/v1/users` - Liste des utilisateurs
- `POST /api/v1/users` - Créer un utilisateur
- `GET /api/v1/users/{id}` - Détails d'un utilisateur
- `PUT /api/v1/users/{id}` - Modifier un utilisateur
- `DELETE /api/v1/users/{id}` - Supprimer un utilisateur
- `GET /api/v1/users/{id}/affectations` - Affectations d'un utilisateur
- `POST /api/v1/users/{id}/affecter` - Affecter un utilisateur
- `POST /api/v1/users/{id}/roles` - Assigner un rôle
- `DELETE /api/v1/users/{id}/roles/{roleId}` - Retirer un rôle

### Rôles et Permissions
- Routes CRUD complètes pour rôles et permissions
- Gestion des associations rôles-permissions

## 👥 Utilisateurs de test

Après avoir exécuté les seeders, vous aurez accès aux comptes suivants :

| Email | Mot de passe | Rôle | Description |
|-------|--------------|------|-------------|
| admin@govtrack.gov | password | Administrateur | Accès complet au système |
| amadou.diop@govtrack.gov | password | Directeur | Directeur DSI |
| fatou.fall@govtrack.gov | password | Employé | Développeur |

## 🔐 Authentification

L'API utilise Laravel Sanctum pour l'authentification. Pour accéder aux endpoints protégés :

1. **Connexion** : `POST /api/login`
2. **Utiliser le token** dans l'en-tête : `Authorization: Bearer {token}`
3. **Déconnexion** : `POST /api/logout`

## 🧪 Tests

```bash
# Exécuter tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📁 Organisation du code

```
app/
├── Http/Controllers/Api/     # Contrôleurs API
├── Models/                   # Modèles Eloquent
database/
├── migrations/               # Migrations de base de données
├── seeders/                  # Seeders de données
routes/
├── api.php                   # Routes API
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📝 Licence

Ce projet est sous licence [MIT](LICENSE).

## 📞 Support

Pour toute question ou problème :
- Créer une issue sur GitHub
- Contacter l'équipe de développement

---

**Développé avec ❤️ pour l'amélioration de la gouvernance publique**
