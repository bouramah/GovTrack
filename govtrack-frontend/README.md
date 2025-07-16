# 🏛️ GovTrack Frontend

> **Interface utilisateur moderne pour la gestion gouvernementale des projets et tâches**

[![Next.js](https://img.shields.io/badge/Next.js-15.3.2-black.svg)](https://nextjs.org)
[![React](https://img.shields.io/badge/React-19-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-blue.svg)](https://typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.4.17-38B2AC.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📋 Table des Matières

- [🎯 Vue d'Ensemble](#-vue-densemble)
- [🚀 Fonctionnalités](#-fonctionnalités)
- [🏗️ Architecture](#️-architecture)
- [⚙️ Installation](#️-installation)
- [🔧 Configuration](#-configuration)
- [📱 Interface Utilisateur](#-interface-utilisateur)
- [🔐 Authentification](#-authentification)
- [🧪 Tests](#-tests)
- [📦 Déploiement](#-déploiement)
- [🤝 Contribution](#-contribution)

## 🎯 Vue d'Ensemble

GovTrack Frontend est une application web moderne développée avec Next.js 15 et React 19 pour la gestion gouvernementale des projets et tâches. Elle offre une interface utilisateur intuitive, responsive et accessible.

### 🎯 Objectifs

- **Interface moderne** et intuitive pour la gestion gouvernementale
- **Expérience utilisateur** optimale sur tous les appareils
- **Système de permissions** intégré et sécurisé
- **Collaboration en temps réel** via discussions et notifications
- **Tableaux de bord** personnalisés selon les rôles

## 🚀 Fonctionnalités

### 📊 Tableaux de Bord Intelligents
- **Dashboard personnalisé** selon les permissions utilisateur
- **Statistiques en temps réel** des projets et tâches
- **Graphiques interactifs** avec Recharts
- **Filtres avancés** et recherche globale
- **Vue calendrier** intégrée avec FullCalendar

### 📋 Gestion des Projets
- **Vue liste** avec filtres et tri
- **Vue kanban** pour la gestion agile
- **Détails complets** avec onglets multiples
- **Timeline** et historique des modifications
- **Pièces jointes** et discussions intégrées

### ✅ Gestion des Tâches
- **Interface kanban** drag & drop
- **Mes tâches** personnalisées
- **Toutes les tâches** avec permissions
- **Détails des tâches** avec discussions
- **Suivi d'avancement** visuel

### 🏢 Gestion Organisationnelle
- **Entités** avec hiérarchie visuelle
- **Utilisateurs** et rôles
- **Postes** et affectations
- **Contacts** et annuaire

### 💬 Collaboration
- **Discussions** par projet et tâche
- **Notifications** en temps réel
- **Messages** et chat intégré
- **Partage de fichiers** sécurisé

### 📅 Calendrier et Planification
- **Vue calendrier** interactive
- **Événements** et échéances
- **Planification** des tâches
- **Intégration** avec les projets

### 🔍 Audit et Rapports
- **Page d'audit** complète
- **Historique** des actions
- **Rapports** et statistiques
- **Export** des données

## 🏗️ Architecture

### Structure du Projet

```
govtrack-frontend/
├── app/                          # App Router Next.js 15
│   ├── (auth)/                   # Routes d'authentification
│   ├── projects/                 # Gestion des projets
│   ├── tasks/                    # Gestion des tâches
│   ├── entities/                 # Gestion des entités
│   ├── users/                    # Gestion des utilisateurs
│   ├── calendar/                 # Calendrier
│   ├── kanban/                   # Vue kanban
│   ├── audit/                    # Audit et rapports
│   └── layout.tsx                # Layout principal
├── components/                   # Composants React
│   ├── ui/                       # Composants UI (shadcn/ui)
│   ├── Shared/                   # Composants partagés
│   ├── dashboard/                # Composants dashboard
│   ├── projects/                 # Composants projets
│   ├── tasks/                    # Composants tâches
│   └── forms/                    # Formulaires
├── hooks/                        # Hooks personnalisés
├── contexts/                     # Contextes React
├── lib/                          # Utilitaires et API
├── types/                        # Types TypeScript
├── styles/                       # Styles globaux
└── public/                       # Assets statiques
```

### Technologies Utilisées

| Technologie | Version | Usage |
|-------------|---------|-------|
| **Next.js** | 15.3.2 | Framework React avec App Router |
| **React** | 19 | Bibliothèque UI |
| **TypeScript** | 5 | Typage statique |
| **Tailwind CSS** | 3.4.17 | Framework CSS |
| **shadcn/ui** | Latest | Composants UI |
| **Radix UI** | Latest | Primitives UI |
| **React Hook Form** | 7.56.4 | Gestion des formulaires |
| **Zod** | 3.24.4 | Validation des schémas |
| **Axios** | 1.10.0 | Client HTTP |
| **Recharts** | Latest | Graphiques |
| **FullCalendar** | Latest | Calendrier |
| **React DnD** | 16.0.1 | Drag & Drop |

### Système de Permissions Frontend

```typescript
// Hooks de permissions
useProjetPermissions()    // Permissions projets
useTachePermissions()     // Permissions tâches
useRolePermissions()      // Permissions rôles
usePermissionPermissions() // Permissions générales
```

## ⚙️ Installation

### Prérequis

- **Node.js** 18.17 ou supérieur
- **npm** 9+ ou **pnpm** 8+ ou **yarn** 1.22+
- **Git**

### Installation Rapide

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/govtrack.git
cd govtrack/govtrack-frontend

# 2. Installer les dépendances
npm install
# ou
pnpm install
# ou
yarn install

# 3. Copier le fichier d'environnement
cp .env.example .env.local

# 4. Configurer les variables d'environnement
# Voir section Configuration

# 5. Lancer le serveur de développement
npm run dev
# ou
pnpm dev
# ou
yarn dev
```

### Installation avec Docker

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/govtrack.git
cd govtrack/govtrack-frontend

# 2. Copier le fichier d'environnement
cp .env.example .env.local

# 3. Lancer avec Docker
docker-compose up -d

# 4. Installer les dépendances
docker-compose exec frontend npm install

# 5. Lancer le serveur
docker-compose exec frontend npm run dev
```

## 🔧 Configuration

### Variables d'Environnement

```env
# Application
NEXT_PUBLIC_APP_NAME=GovTrack
NEXT_PUBLIC_APP_URL=http://localhost:3000

# API Backend
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_API_TIMEOUT=10000

# Authentification
NEXT_PUBLIC_AUTH_STORAGE_KEY=govtrack_auth
NEXT_PUBLIC_TOKEN_REFRESH_INTERVAL=300000

# Fonctionnalités
NEXT_PUBLIC_ENABLE_NOTIFICATIONS=true
NEXT_PUBLIC_ENABLE_REAL_TIME=true
NEXT_PUBLIC_ENABLE_AUDIT=true

# Analytics (optionnel)
NEXT_PUBLIC_GOOGLE_ANALYTICS_ID=
NEXT_PUBLIC_SENTRY_DSN=
```

### Configuration Tailwind CSS

```typescript
// tailwind.config.ts
import type { Config } from "tailwindcss"

const config: Config = {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        // ... autres couleurs
      },
    },
  },
  plugins: [require("tailwindcss-animate")],
}

export default config
```

### Configuration des Composants UI

```typescript
// components.json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "default",
  "rsc": true,
  "tsx": true,
  "tailwind": {
    "config": "tailwind.config.ts",
    "css": "app/globals.css",
    "baseColor": "slate",
    "cssVariables": true
  },
  "aliases": {
    "components": "@/components",
    "utils": "@/lib/utils"
  }
}
```

## 📱 Interface Utilisateur

### Design System

- **shadcn/ui** : Composants UI cohérents
- **Radix UI** : Primitives accessibles
- **Tailwind CSS** : Styling utilitaire
- **Lucide React** : Icônes modernes

### Composants Principaux

```typescript
// Composants UI réutilisables
<Button>           // Boutons avec variants
<Card>             // Cartes et conteneurs
<Dialog>           // Modales et popups
<Form>             // Formulaires avec validation
<Table>            // Tableaux de données
<Select>           // Sélecteurs
<DatePicker>       // Sélecteurs de date
<Toast>            // Notifications toast
```

### Responsive Design

- **Mobile First** : Optimisé pour mobile
- **Breakpoints** : sm, md, lg, xl, 2xl
- **Touch Friendly** : Interactions tactiles
- **Accessibilité** : WCAG 2.1 AA

### Thèmes

- **Mode clair/sombre** : Support natif
- **Couleurs gouvernementales** : Palette officielle
- **Personnalisation** : Variables CSS

## 🔐 Authentification

### Système d'Auth

```typescript
// AuthContext.tsx
const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  
  // Gestion de l'authentification
  const login = async (credentials: LoginCredentials) => { ... }
  const logout = async () => { ... }
  const refreshToken = async () => { ... }
  
  return (
    <AuthContext.Provider value={{ user, login, logout, loading }}>
      {children}
    </AuthContext.Provider>
  )
}
```

### Protection des Routes

```typescript
// ProtectedRoute.tsx
export function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { user, loading } = useAuth()
  const router = useRouter()
  
  useEffect(() => {
    if (!loading && !user) {
      router.push('/login')
    }
  }, [user, loading, router])
  
  if (loading) return <LoadingSpinner />
  if (!user) return null
  
  return <>{children}</>
}
```

### Gestion des Permissions

```typescript
// PermissionGuard.tsx
export function PermissionGuard({ 
  permission, 
  children 
}: PermissionGuardProps) {
  const { user } = useAuth()
  const hasPermission = user?.permissions?.includes(permission)
  
  if (!hasPermission) {
    return <AccessDenied />
  }
  
  return <>{children}</>
}
```

## 🧪 Tests

### Configuration des Tests

```bash
# Installation des dépendances de test
npm install --save-dev @testing-library/react @testing-library/jest-dom jest jest-environment-jsdom

# Configuration Jest
# jest.config.js
module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
  testPathIgnorePatterns: ['<rootDir>/.next/', '<rootDir>/node_modules/'],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/$1',
  },
}
```

### Exécution des Tests

```bash
# Tous les tests
npm test

# Tests en mode watch
npm run test:watch

# Tests avec couverture
npm run test:coverage

# Tests E2E (si configurés)
npm run test:e2e
```

### Exemples de Tests

```typescript
// __tests__/components/Button.test.tsx
import { render, screen } from '@testing-library/react'
import { Button } from '@/components/ui/button'

describe('Button', () => {
  it('renders correctly', () => {
    render(<Button>Click me</Button>)
    expect(screen.getByRole('button')).toBeInTheDocument()
  })
  
  it('handles click events', () => {
    const handleClick = jest.fn()
    render(<Button onClick={handleClick}>Click me</Button>)
    screen.getByRole('button').click()
    expect(handleClick).toHaveBeenCalledTimes(1)
  })
})
```

## 📦 Déploiement

### Build de Production

```bash
# Build de production
npm run build

# Vérification du build
npm run lint

# Test du build
npm run start
```

### Déploiement Vercel

```bash
# 1. Installer Vercel CLI
npm i -g vercel

# 2. Déployer
vercel

# 3. Variables d'environnement
vercel env add NEXT_PUBLIC_API_URL
vercel env add NEXT_PUBLIC_APP_URL
```

### Déploiement Docker

```dockerfile
# Dockerfile
FROM node:18-alpine AS base

# Install dependencies only when needed
FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Install dependencies based on the preferred package manager
COPY package.json package-lock.json* ./
RUN npm ci --only=production

# Rebuild the source code only when needed
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Next.js collects completely anonymous telemetry data about general usage.
# Learn more here: https://nextjs.org/telemetry
# Uncomment the following line in case you want to disable telemetry during the build.
ENV NEXT_TELEMETRY_DISABLED 1

RUN npm run build

# Production image, copy all the files and run next
FROM base AS runner
WORKDIR /app

ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

COPY --from=builder /app/public ./public

# Set the correct permission for prerender cache
RUN mkdir .next
RUN chown nextjs:nodejs .next

# Automatically leverage output traces to reduce image size
# https://nextjs.org/docs/advanced-features/output-file-tracing
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

CMD ["node", "server.js"]
```

### Variables d'Environnement Production

```env
# Production
NEXT_PUBLIC_APP_URL=https://govtrack.gov
NEXT_PUBLIC_API_URL=https://api.govtrack.gov/api/v1
NODE_ENV=production

# Performance
NEXT_TELEMETRY_DISABLED=1
NEXT_OPTIMIZE_FONTS=true
NEXT_OPTIMIZE_IMAGES=true
```

## 🤝 Contribution

### Développement

```bash
# 1. Fork le projet
git clone https://github.com/votre-fork/govtrack.git

# 2. Créer une branche
git checkout -b feature/nouvelle-fonctionnalite

# 3. Installer les dépendances
npm install

# 4. Développer et tester
npm run dev
npm test

# 5. Linter et formatter
npm run lint
npm run format

# 6. Commit et push
git commit -m "feat: ajouter nouvelle fonctionnalité"
git push origin feature/nouvelle-fonctionnalite

# 7. Créer une Pull Request
```

### Standards de Code

- **TypeScript strict** : Configuration stricte
- **ESLint** : Règles de linting
- **Prettier** : Formatage automatique
- **Husky** : Hooks Git
- **Conventional Commits** : Messages de commit

### Structure des Commits

```bash
feat: ajouter nouvelle fonctionnalité
fix: corriger bug d'authentification
docs: mettre à jour la documentation
style: améliorer le style du composant
refactor: refactoriser la logique de permissions
test: ajouter tests pour le composant Button
chore: mettre à jour les dépendances
```

### Tests Obligatoires

- **Tests unitaires** : 80% de couverture minimum
- **Tests d'intégration** : Pour les composants complexes
- **Tests E2E** : Pour les flux critiques
- **Tests d'accessibilité** : Avec axe-core

## 📞 Support

- **Documentation** : [docs.govtrack.gov](https://docs.govtrack.gov)
- **Issues** : [GitHub Issues](https://github.com/votre-org/govtrack/issues)
- **Discussions** : [GitHub Discussions](https://github.com/votre-org/govtrack/discussions)
- **Email** : support@govtrack.gov

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Développé avec ❤️ pour la gestion gouvernementale** 