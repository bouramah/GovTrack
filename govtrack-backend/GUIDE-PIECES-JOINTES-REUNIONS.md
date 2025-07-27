# 📎 Guide des Pièces Jointes - Module Réunions

## 📋 **Vue d'Ensemble**

Ce guide explique comment gérer les pièces jointes (fichiers) dans le module de gestion des réunions, en particulier pour la création multiple de sujets avec leurs documents associés.

---

## 🔧 **Architecture des Pièces Jointes**

### **Structure de Stockage**
```
storage/app/public/reunions/
├── {reunion_id}/
│   ├── sujets/
│   │   ├── {sujet_id}/
│   │   │   ├── 1703123456_rapport_avancement.pdf
│   │   │   ├── 1703123457_presentation.pptx
│   │   │   └── 1703123458_budget.xlsx
│   │   └── {sujet_id}/
│   │       └── 1703123459_analyse.pdf
└── {reunion_id}/
    └── sujets/
        └── {sujet_id}/
            └── 1703123460_document.docx
```

### **Format des Données**
```json
{
  "pieces_jointes": [
    {
      "nom": "rapport_avancement.pdf",
      "chemin": "reunions/1/sujets/5/1703123456_rapport_avancement.pdf",
      "taille": 1024000,
      "type": "application/pdf",
      "uploaded_at": "2025-01-06T09:00:00.000000Z"
    }
  ]
}
```

---

## 🚀 **Utilisation des Pièces Jointes**

### **1. Création Multiple de Sujets avec Fichiers**

#### **Endpoint :**
```bash
POST /api/v1/reunions/{reunionId}/sujets/multiple
Content-Type: multipart/form-data
```

#### **Structure de la Requête :**
```bash
# Données JSON
sujets[0][reunion_ordre_jour_id]: 1
sujets[0][titre]: "Projet Infrastructure"
sujets[0][description]: "Point sur l'avancement du projet"
sujets[0][difficulte_globale]: "Délais serrés"
sujets[0][recommandation]: "Accélérer le recrutement"

sujets[1][reunion_ordre_jour_id]: 2
sujets[1][titre]: "Budget Marketing"
sujets[1][description]: "Validation du budget Q1 2025"

# Fichiers pour le premier sujet
sujet_0_files[0]: [fichier PDF]
sujet_0_files[1]: [fichier Excel]

# Fichiers pour le deuxième sujet
sujet_1_files[0]: [fichier Word]
```

#### **Exemple avec cURL :**
```bash
curl -X POST "{{base_url}}/api/v1/reunions/1/sujets/multiple" \
  -H "Authorization: Bearer {{token}}" \
  -F "sujets[0][reunion_ordre_jour_id]=1" \
  -F "sujets[0][titre]=Projet Infrastructure" \
  -F "sujets[0][description]=Point sur l'avancement" \
  -F "sujets[0][difficulte_globale]=Délais serrés" \
  -F "sujets[0][recommandation]=Accélérer le recrutement" \
  -F "sujets[1][reunion_ordre_jour_id]=2" \
  -F "sujets[1][titre]=Budget Marketing" \
  -F "sujets[1][description]=Validation du budget" \
  -F "sujet_0_files[0]=@rapport_avancement.pdf" \
  -F "sujet_0_files[1]=@budget_detaille.xlsx" \
  -F "sujet_1_files[0]=@presentation_marketing.pptx"
```

---

## 📝 **Exemples Complets**

### **Scénario 1 : Création avec Pièces Jointes**

#### **Données JSON :**
```json
{
  "sujets": [
    {
      "reunion_ordre_jour_id": 1,
      "titre": "Projet Infrastructure",
      "description": "Point sur l'avancement du projet infrastructure",
      "difficulte_globale": "Délais serrés",
      "recommandation": "Accélérer le recrutement",
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
      "projet_id": 2,
      "entite_id": 2,
      "niveau_detail": "SIMPLE",
      "objectifs_actifs": false,
      "difficultes_actives": false
    }
  ]
}
```

#### **Fichiers à Joindre :**
- `sujet_0_files[0]` : `rapport_avancement.pdf`
- `sujet_0_files[1]` : `budget_detaille.xlsx`
- `sujet_1_files[0]` : `presentation_marketing.pptx`

#### **Réponse Attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "titre": "Projet Infrastructure",
      "pieces_jointes": [
        {
          "nom": "rapport_avancement.pdf",
          "chemin": "reunions/1/sujets/1/1703123456_rapport_avancement.pdf",
          "taille": 1024000,
          "type": "application/pdf",
          "uploaded_at": "2025-01-06T09:00:00.000000Z"
        },
        {
          "nom": "budget_detaille.xlsx",
          "chemin": "reunions/1/sujets/1/1703123457_budget_detaille.xlsx",
          "taille": 512000,
          "type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          "uploaded_at": "2025-01-06T09:00:01.000000Z"
        }
      ]
    },
    {
      "id": 2,
      "titre": "Budget Marketing",
      "pieces_jointes": [
        {
          "nom": "presentation_marketing.pptx",
          "chemin": "reunions/1/sujets/2/1703123458_presentation_marketing.pptx",
          "taille": 2048000,
          "type": "application/vnd.openxmlformats-officedocument.presentationml.presentation",
          "uploaded_at": "2025-01-06T09:00:02.000000Z"
        }
      ]
    }
  ],
  "message": "2 sujets créés avec succès"
}
```

---

## 🔒 **Sécurité et Validation**

### **Types de Fichiers Autorisés :**
```php
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'image/jpeg',
    'image/png',
    'image/gif',
    'text/plain'
];
```

### **Limites de Taille :**
- **Taille maximale par fichier :** 10 MB
- **Taille maximale totale :** 50 MB
- **Nombre maximum de fichiers par sujet :** 10

### **Validation des Fichiers :**
```php
// Vérification de la validité du fichier
if ($file && !$file->isValid()) {
    return response()->json([
        'success' => false,
        'message' => 'Fichier invalide détecté',
        'error' => 'Un ou plusieurs fichiers sont corrompus'
    ], 422);
}

// Vérification du type MIME
if (!in_array($file->getMimeType(), $allowedTypes)) {
    return response()->json([
        'success' => false,
        'message' => 'Type de fichier non autorisé',
        'error' => 'Le type ' . $file->getMimeType() . ' n\'est pas supporté'
    ], 422);
}
```

---

## 📊 **Gestion des Erreurs**

### **Erreurs Courantes :**

1. **Fichier Trop Volumineux :**
```json
{
  "success": false,
  "message": "Fichier trop volumineux",
  "error": "La taille maximale autorisée est de 10 MB"
}
```

2. **Type de Fichier Non Autorisé :**
```json
{
  "success": false,
  "message": "Type de fichier non autorisé",
  "error": "Le type application/octet-stream n'est pas supporté"
}
```

3. **Fichier Corrompu :**
```json
{
  "success": false,
  "message": "Fichier invalide détecté",
  "error": "Un ou plusieurs fichiers sont corrompus"
}
```

4. **Erreur de Stockage :**
```json
{
  "success": false,
  "message": "Erreur lors du stockage des fichiers",
  "error": "Impossible de sauvegarder le fichier sur le serveur"
}
```

---

## 🔄 **Workflow Complet**

### **Étapes de Création :**

1. **Validation des Données :**
   - Vérification des champs obligatoires
   - Validation des relations (reunion_ordre_jour_id, projet_id, etc.)

2. **Validation des Fichiers :**
   - Vérification de la validité des fichiers
   - Contrôle des types MIME autorisés
   - Vérification des tailles

3. **Création des Sujets :**
   - Insertion en base de données sans pièces jointes
   - Récupération des IDs générés

4. **Traitement des Fichiers :**
   - Upload vers le stockage
   - Génération des chemins uniques
   - Mise à jour des sujets avec les métadonnées

5. **Finalisation :**
   - Commit de la transaction
   - Retour des données complètes

---

## 🛠️ **Intégration Frontend**

### **Exemple JavaScript :**
```javascript
async function createMultipleSujets(reunionId, sujets, files) {
    const formData = new FormData();
    
    // Ajouter les données JSON
    formData.append('sujets', JSON.stringify(sujets));
    
    // Ajouter les fichiers
    files.forEach((fileGroup, sujetIndex) => {
        fileGroup.forEach((file, fileIndex) => {
            formData.append(`sujet_${sujetIndex}_files[${fileIndex}]`, file);
        });
    });
    
    const response = await fetch(`/api/v1/reunions/${reunionId}/sujets/multiple`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData
    });
    
    return await response.json();
}

// Utilisation
const sujets = [
    {
        reunion_ordre_jour_id: 1,
        titre: "Projet Infrastructure",
        description: "Point sur l'avancement"
    }
];

const files = [
    [file1, file2], // Fichiers pour le premier sujet
    [file3]         // Fichiers pour le deuxième sujet
];

const result = await createMultipleSujets(1, sujets, files);
```

---

## 📞 **Support et Dépannage**

### **Problèmes Courants :**

1. **Fichiers Non Uploadés :**
   - Vérifier la taille des fichiers
   - Contrôler les types MIME
   - S'assurer que les fichiers ne sont pas corrompus

2. **Erreurs de Permissions :**
   - Vérifier les permissions du dossier storage
   - S'assurer que le lien symbolique public est créé

3. **Problèmes de Performance :**
   - Limiter le nombre de fichiers simultanés
   - Utiliser des uploads asynchrones pour de gros volumes

### **Commandes de Maintenance :**
```bash
# Créer le lien symbolique pour le stockage public
php artisan storage:link

# Nettoyer les fichiers orphelins
php artisan storage:clean

# Vérifier les permissions
chmod -R 755 storage/app/public
```

---

## ✅ **Bonnes Pratiques**

1. **Nommage des Fichiers :**
   - Utiliser des noms descriptifs
   - Éviter les caractères spéciaux
   - Inclure la date dans le nom si nécessaire

2. **Organisation :**
   - Grouper les fichiers par sujet
   - Utiliser des dossiers structurés
   - Maintenir une hiérarchie claire

3. **Sécurité :**
   - Valider tous les fichiers uploadés
   - Limiter les types de fichiers autorisés
   - Surveiller l'espace disque utilisé

4. **Performance :**
   - Compresser les images si possible
   - Utiliser des formats optimisés
   - Implémenter un système de cache si nécessaire 
