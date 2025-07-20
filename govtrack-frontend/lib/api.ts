import axios, { AxiosInstance, AxiosResponse } from 'axios';
import { ProjectAttachment, TaskAttachment, AttachmentStats } from '@/types/attachment';
import { ProjectDiscussion, TaskDiscussion, DiscussionCreateRequest, DiscussionUpdateRequest, DiscussionStats } from '@/types/discussion';

const DEV__API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'https://localhost:8000/api';
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'https://govtrackbackend.camtechssolutions.com/api';

export interface AuditLog {
  id: number;
  action: string;
  table_name: string;
  record_id: number;
  record_type: string;
  deleted_data: any;
  deleted_data_summary: string;
  user_id: number;
  user_name: string;
  user_email: string;
  ip_address: string;
  user_agent: string;
  request_url: string;
  request_method: string;
  reason: string;
  metadata: any;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
}

export interface AuditStats {
  total_logs: number;
  deletions: number;
  force_deletions: number;
  restorations: number;
}

export interface TopTable {
  table_name: string;
  count: number;
}

export interface TopUser {
  user_id: number;
  user_name: string;
  count: number;
}

export interface AuditResponse {
  data: AuditLog[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  statistiques: AuditStats;
  top_tables: TopTable[];
  top_users: TopUser[];
}

export interface AuditFilters {
  action?: string;
  table_name?: string;
  user_id?: string;
  search?: string;
  date_debut?: string;
  date_fin?: string;
  page?: number;
  per_page?: number;
}

export interface User {
  id: number;
  matricule: string;
  nom: string;
  prenom: string;
  name: string;
  email: string;
  telephone?: string;
  adresse?: string;
  photo?: string;
  statut: boolean;
  affectation_actuelle?: {
    poste: string;
    entite: string;
    date_debut: string;
  };
  entites_dirigees: Array<{
    entite_id: number;
    entite_nom: string;
    date_debut: string;
  }>;
  roles: string[]; 
  permissions: string[];
  roles_avec_cette_permission?: Array<{
    id: number;
    nom: string;
    date_assignation_role?: string;
  }>;
}

export interface UserDetailed {
  id: number;
  matricule: string;
  nom: string;
  prenom: string;
  name: string;
  email: string;
  telephone?: string;
  adresse?: string;
  photo?: string;
  statut: boolean;
  affectation_actuelle?: {
    poste: string;
    entite: string;
    date_debut: string;
  };
  entites_dirigees: Array<{
    entite_id: number;
    entite_nom: string;
    date_debut: string;
  }>;
  roles: Array<{
    id: number;
    nom: string;
    description?: string;
    permissions: string[];
  }>;
  permissions: string[];
  historique_affectations?: Array<{
    poste: string;
    entite: string;
    date_debut: string;
    date_fin: string;
  }>;
  statistiques?: {
    total_affectations: number;
    entites_dirigees_actuellement: number;
    roles_actifs: number;
  };
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  error?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  permissions?: ProjectPermissions;
}

export interface Project {
  id: number;
  titre: string;
  description: string;
  statut: string;
  statut_libelle: string;
  niveau_execution: number;
  priorite: string;
  priorite_libelle: string;
  priorite_couleur: string;
  priorite_icone: string;
  est_favori_utilisateur: boolean;
  date_debut_previsionnelle: string;
  date_fin_previsionnelle?: string;
  date_debut_reelle?: string;
  date_fin_reelle?: string;
  est_en_retard: boolean;
  justification_modification_dates?: string;
  taches_count: number;
  type_projet: {
    id: number;
    nom: string;
    description?: string;
  };
  // Porteurs multiples (nouveau système)
  porteurs?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
    pivot: {
      date_assignation: string;
      date_fin_assignation?: string;
      statut: boolean;
      commentaire?: string;
    };
  }[];
  // Porteur principal (pour compatibilité avec l'ancien système)
  porteur?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
  };
  donneur_ordre: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
  };
  taches?: Task[];
  pieces_jointes?: any[];
  discussions?: any[];
  historique_statuts?: any[];
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface ProjectCreateRequest {
  titre: string;
  description: string;
  type_projet_id: number;
  porteur_ids: number[]; // Nouveau : tableau d'IDs des porteurs
  donneur_ordre_id: number;
  priorite?: string;
  date_debut_previsionnelle: string;
  date_fin_previsionnelle?: string;
  justification_modification_dates?: string;
}

export interface ProjectUpdateRequest {
  titre?: string;
  description?: string;
  type_projet_id?: number;
  donneur_ordre_id?: number;
  priorite?: string;
  date_debut_previsionnelle?: string;
  date_fin_previsionnelle?: string;
  justification_modification_dates?: string;
}

export interface ProjectStatutChangeRequest {
  nouveau_statut: string;
  commentaire?: string;
  justificatif?: File;
}

export interface ProjectExecutionLevelRequest {
  niveau_execution: number;
  commentaire?: string;
}

export interface ProjectDashboard {
  total_projets: number;
  projets_par_statut: {
    [key: string]: {
      libelle: string;
      count: number;
      pourcentage: number;
    };
  };
  projets_en_retard: number;
  niveau_execution_moyen: number;
  projets_recents: Project[];
  permissions_info: {
    level: 'all_projects' | 'entity_projects' | 'my_projects';
    description: string;
    scope: string;
  };
}

// Import des types de tâches
import type { 
  Tache, 
  CreateTacheRequest, 
  UpdateTacheRequest, 
  ChangeTacheStatutRequest,
  TacheFilters,
  TachePieceJointe,
  TacheDiscussion,
  TacheHistoriqueStatut
} from '@/types/tache';

export interface Task {
  id: number;
  titre: string;
  description: string;
  statut: string;
  niveau_execution: number;
  date_debut_previsionnelle?: string;
  date_fin_previsionnelle?: string;
  date_debut_reelle?: string;
  date_fin_reelle?: string;
  projet_id: number;
  // Responsables multiples (nouveau système)
  responsables?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
    pivot: {
      date_assignation: string;
      date_fin_assignation?: string;
      statut: boolean;
      commentaire?: string;
    };
  }[];
  // Responsable principal (pour compatibilité avec l'ancien système)
  responsable?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
  };
  type_tache_id?: number;
  type_tache?: {
    id: number;
    nom: string;
    couleur: string;
  };
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

// ========================================
// INTERFACES GESTION UTILISATEURS
// ========================================

export interface TypeEntite {
  id: number;
  nom: string;
  description?: string;
  entites?: Entite[];
  date_creation: string;
  date_modification: string;
  creer_par: string;
  modifier_par?: string;
}

export interface Entite {
  id: number;
  nom: string;
  description?: string;
  type_entite: TypeEntite;
  parent?: {
    id: number;
    nom: string;
  };
  enfants?: Entite[];
  nombre_enfants?: number;
  chef_actuel?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
    date_debut_mandat: string;
    duree_mandat_jours: number;
  };
  employes_actuels?: {
    user: {
      id: number;
      nom: string;
      prenom: string;
      matricule: string;
    };
    poste: string;
    date_debut: string;
  }[];
  effectifs?: {
    nombre_employes: number;
    employes: any[];
  };
  statistiques?: {
    nombre_enfants_directs: number;
    nombre_total_descendants: number;
    a_chef: boolean;
    niveau_hierarchique: number;
  };
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface Poste {
  id: number;
  nom: string;
  description?: string;
  affectations_count?: number;
  affectations_actuelles_count?: number;
  nombre_affectations_actives?: number;
  employes_actuels?: {
    user: {
      id: number;
      nom: string;
      prenom: string;
      matricule: string;
    };
    entite: string;
    date_debut: string;
  }[];
  affectations_actuelles?: UtilisateurEntiteHistory[];
  historique_affectations?: UtilisateurEntiteHistory[];
  statistiques?: {
    total_affectations: number;
    affectations_actives: number;
    affectations_terminees: number;
  };
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface Role {
  id: number;
  nom: string;
  description?: string;
  permissions?: Permission[];
  nombre_permissions?: number;
  utilisateurs?: User[];
  nombre_utilisateurs?: number;
  statistiques?: {
    total_permissions: number;
    total_utilisateurs: number;
    utilisateurs_actifs: number;
  };
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface Permission {
  id: number;
  nom: string;
  description?: string;
  roles?: Role[];
  nombre_roles?: number;
  nombre_utilisateurs_total?: number;
  utilisateurs_avec_permission?: User[];
  statistiques?: {
    total_roles: number;
    total_utilisateurs: number;
    utilisateurs_actifs: number;
  };
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface UtilisateurEntiteHistory {
  id: number;
  user: User;
  entite: Entite;
  poste: Poste;
  date_debut: string;
  date_fin?: string;
  statut: boolean;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface EntiteChefHistory {
  id: number;
  user: User;
  entite: Entite;
  date_debut: string;
  date_fin?: string;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

// ========================================
// INTERFACES GESTION DES PROJETS
// ========================================

export interface TypeProjet {
  id: number;
  nom: string;
  description?: string;
  duree_previsionnelle_jours: number;
  description_sla?: string;
  duree_formattee?: string;
  projets_count?: number;
  projets?: Project[];
  statistiques?: TypeProjetStatistiques;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface TypeTache {
  id: number;
  nom: string;
  description?: string;
  couleur: string;
  actif: boolean;
  ordre: number;
  taches_count?: number;
  statistiques?: TypeTacheStatistiques;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
}

export interface TypeTacheStatistiques {
  total_taches: number;
  taches_par_statut: {
    [key: string]: number;
  };
  niveau_execution_moyen: number;
  taches_en_retard: number;
}

export interface TypeTacheCreateRequest {
  nom: string;
  description?: string;
  couleur?: string;
  actif?: boolean;
  ordre?: number;
}

export interface TypeTacheUpdateRequest {
  nom?: string;
  description?: string;
  couleur?: string;
  actif?: boolean;
  ordre?: number;
}

export interface TypeProjetStatistiques {
  total_projets: number;
  projets_par_statut: {
    [key: string]: {
      libelle: string;
      count: number;
    };
  };
  niveau_execution_moyen: number;
  projets_en_retard: number;
  duree_moyenne_reelle?: number;
}

export interface TypeProjetCreateRequest {
  nom: string;
  description?: string;
  duree_previsionnelle_jours: number;
  description_sla?: string;
}

export interface TypeProjetUpdateRequest {
  nom: string;
  description?: string;
  duree_previsionnelle_jours: number;
  description_sla?: string;
}

export interface ProjectFilters {
  // Filtres de base
  search?: string;
  statut?: string;
  type_projet_id?: number;
  priorite?: string;
  favoris?: boolean;
  en_retard?: boolean;
  niveau_execution_min?: number;
  niveau_execution_max?: number;
  
  // Filtres de date
  date_debut_previsionnelle_debut?: string;
  date_debut_previsionnelle_fin?: string;
  date_fin_previsionnelle_debut?: string;
  date_fin_previsionnelle_fin?: string;
  date_creation_debut?: string;
  date_creation_fin?: string;
  
  // Filtres par utilisateur (selon permissions)
  porteur_ids?: number[]; // Nouveau : filtrage par plusieurs porteurs
  donneur_ordre_ids?: number[]; // Nouveau : filtrage par plusieurs ordonnateurs
  
  // Filtre par entité (selon permissions)
  entite_id?: number;
  
  // Tri et pagination
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}

export interface ProjectPermissions {
  level: 'all_projects' | 'entity_projects' | 'my_projects';
  can_filter_by_user: boolean;
  can_filter_by_entity: boolean;
  can_filter_by_date: boolean;
  available_filters: {
    basic: string[];
    date: string[];
    user: string[];
    entity: string[];
  };
  description: string;
}

export interface FilterEntity {
  id: number;
  nom: string;
  type: string;
}

export interface FilterUser {
  id: number;
  nom: string;
  prenom: string;
  email: string;
  matricule: string;
  display_name: string;
}

// Configuration du client API avec Axios
class ApiClient {
  private client: AxiosInstance;
  private token: string | null = null;

  constructor() {
    this.client = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    // Récupérer le token du localStorage côté client
    if (typeof window !== 'undefined') {
      this.token = localStorage.getItem('govtrack_token');
      if (this.token) {
        this.setAuthHeader(this.token);
      }
    }

    // Intercepteur pour les erreurs
    this.client.interceptors.response.use(
      (response) => response,
      async (error) => {
        // Si token expiré, déconnecter directement l'utilisateur
        if (error.response?.status === 401) {
          console.error(
            '401 → déconnexion',
            error.config?.method?.toUpperCase(),
            error.config?.url,
            error.response?.data,
          );
          this.clearToken();
          if (typeof window !== 'undefined') window.location.href = '/login';
          return Promise.reject(error);
        }
        
        // Gestion des erreurs 422 (permissions insuffisantes)
        if (error.response?.status === 422) {
          const errorData = error.response.data;
          let errorMessage = 'Erreur de validation';

          // Si le backend fournit un tableau d'erreurs Laravel, on les concatène
          if (errorData?.errors) {
            errorMessage = Object.values(errorData.errors).flat().join(', ');
          } else if (errorData?.message) {
            errorMessage = errorData.message;
          }

          // Créer une erreur personnalisée contenant les détails de validation
          const validationError: any = new Error(errorMessage);
          validationError.name = 'ValidationError';
          validationError.errors = errorData?.errors || {};
          validationError.response = error.response;
          return Promise.reject(validationError);
        }

        // Gestion des permissions insuffisantes (403)
        if (error.response?.status === 403) {
          const errorData = error.response.data || {};
          const errorMessage = errorData.message || errorData.error || 'Accès refusé';
          const permissionError: any = new Error(errorMessage);
          permissionError.name = 'PermissionError';
          permissionError.response = error.response;
          return Promise.reject(permissionError);
        }
        
        return Promise.reject(error);
      }
    );
  }

  private setAuthHeader(token: string) {
    this.client.defaults.headers.Authorization = `Bearer ${token}`;
  }

  setToken(token: string) {
    this.token = token;
    if (typeof window !== 'undefined') {
      localStorage.setItem('govtrack_token', token);
    }
    this.setAuthHeader(token);
  }

  clearToken() {
    this.token = null;
    if (typeof window !== 'undefined') {
      localStorage.removeItem('govtrack_token');
    }
    delete this.client.defaults.headers.Authorization;
  }

  // ========================================
  // AUTHENTIFICATION
  // ========================================

  async login(credentials: LoginRequest): Promise<{ user: User; token: string }> {
    try {
      const response: AxiosResponse<ApiResponse<{ user: User; token: string }>> = 
        await this.client.post('/v1/auth/login', credentials);
      
      if (response.data.success && response.data.data) {
        const { user, token } = response.data.data;
        this.setToken(token);
        return { user, token };
      }
      
      // Si la réponse n'est pas un succès mais n'a pas déclenché d'erreur HTTP
      throw new Error(response.data.message || 'Erreur de connexion');
      
    } catch (error: any) {
      // Gestion détaillée des erreurs
      if (error.response?.data) {
        const errorData = error.response.data;
        
        // Erreurs de validation Laravel
        if (errorData.errors) {
          const errorMessages = Object.values(errorData.errors)
            .flat()
            .join(', ');
          throw new Error(errorMessages);
        }
        
        // Message d'erreur simple du serveur
        if (errorData.message) {
          throw new Error(errorData.message);
        }
      }
      
      // Erreurs réseau ou autres
      if (error.message) {
        throw new Error(error.message);
      }
      
      // Erreur générique
      throw new Error('Erreur de connexion. Vérifiez vos identifiants.');
    }
  }

  async logout(): Promise<void> {
    try {
      await this.client.post('/v1/auth/logout');
    } finally {
      this.clearToken();
    }
  }

  async logoutAll(): Promise<void> {
    try {
      await this.client.post('/v1/auth/logout-all');
    } finally {
      this.clearToken();
    }
  }

  async refresh(): Promise<{ user: User; token: string }> {
    const response: AxiosResponse<ApiResponse<{ user: User; token: string }>> = 
      await this.client.post('/v1/auth/refresh');
    
    if (response.data.success && response.data.data) {
      const { user, token } = response.data.data;
      this.setToken(token);
      return { user, token };
    }
    
    throw new Error(response.data.message || 'Erreur de rafraîchissement du token');
  }

  async getProfile(): Promise<User> {
    const response: AxiosResponse<ApiResponse<{ user: User }>> = 
      await this.client.get('/v1/auth/me');
    
    if (response.data.success && response.data.data) {
      return response.data.data.user;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du profil');
  }


  // ========================================
  // PROFIL UTILISATEUR
  // ========================================

  async updateProfile(profileData: {
    matricule?: string;
    nom?: string;
    prenom?: string;
    email?: string;
    telephone?: string;
    adresse?: string;
    password?: string;
  }): Promise<User> {
    const response: AxiosResponse<ApiResponse<{ user: User }>> = 
      await this.client.put('/v1/auth/profile', profileData);
    
    if (response.data.success && response.data.data) {
      return response.data.data.user;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du profil');
  }

  async getUserProfile(userId: number): Promise<User> {
    const response: AxiosResponse<ApiResponse<User>> = 
      await this.client.get(`/v1/users/${userId}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du profil utilisateur');
  }

  async uploadProfilePhoto(formData: FormData): Promise<{ photo_url: string }> {
    const response: AxiosResponse<ApiResponse<{ photo_url: string }>> = 
      await this.client.post('/v1/auth/profile/photo', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de chargement de la photo');
  }

  private async getCurrentUserId(): Promise<number> {
    const profile = await this.getProfile();
    return profile.id;
  }

  // ========================================
  // GESTION DES TYPES D'ENTITÉ
  // ========================================

  async getTypeEntites(): Promise<TypeEntite[]> {
    const response: AxiosResponse<ApiResponse<TypeEntite[]>> = 
      await this.client.get('/v1/type-entites');
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des types d\'entité');
  }

  async getTypeEntite(id: number): Promise<TypeEntite> {
    const response: AxiosResponse<ApiResponse<TypeEntite>> = 
      await this.client.get(`/v1/type-entites/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du type d\'entité');
  }

  async createTypeEntite(data: { nom: string; description?: string }): Promise<TypeEntite> {
    const response: AxiosResponse<ApiResponse<TypeEntite>> = 
      await this.client.post('/v1/type-entites', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création du type d\'entité');
  }

  async updateTypeEntite(id: number, data: { nom: string; description?: string }): Promise<TypeEntite> {
    const response: AxiosResponse<ApiResponse<TypeEntite>> = 
      await this.client.put(`/v1/type-entites/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du type d\'entité');
  }

  async deleteTypeEntite(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/type-entites/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du type d\'entité');
    }
  }

  // ========================================
  // GESTION DES ENTITÉS
  // ========================================

  async getEntitesDetailed(params?: {
    nom?: string;
    type_entite_id?: number;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<Entite>> {
    const response: AxiosResponse<PaginatedResponse<Entite>> = 
      await this.client.get('/v1/entites', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des entités');
  }

  async getEntite(id: number): Promise<Entite> {
    const response: AxiosResponse<ApiResponse<Entite>> = 
      await this.client.get(`/v1/entites/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de l\'entité');
  }

  async createEntite(data: { 
    nom: string; 
    type_entite_id: number; 
    parent_id?: number; 
    description?: string; 
  }): Promise<Entite> {
    const response: AxiosResponse<ApiResponse<Entite>> = 
      await this.client.post('/v1/entites', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création de l\'entité');
  }

  async updateEntite(id: number, data: { 
    nom: string; 
    type_entite_id: number; 
    parent_id?: number; 
    description?: string; 
  }): Promise<Entite> {
    const response: AxiosResponse<ApiResponse<Entite>> = 
      await this.client.put(`/v1/entites/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour de l\'entité');
  }

  async deleteEntite(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/entites/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression de l\'entité');
    }
  }

  async getEntiteEnfants(id: number): Promise<{ data: Entite[]; parent: { id: number; nom: string } }> {
    const response: AxiosResponse<ApiResponse<Entite[]> & { parent: { id: number; nom: string } }> = 
      await this.client.get(`/v1/entites/${id}/enfants`);
    
    if (response.data.success && response.data.data) {
      return {
        data: response.data.data,
        parent: response.data.parent
      };
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des entités enfants');
  }

  async getEntiteHierarchy(id: number): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.get(`/v1/entites/${id}/hierarchy`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de la hiérarchie');
  }

  async getOrganigramme(): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.get('/v1/entites/organigramme');
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de l\'organigramme');
  }

  async getChefsActuels(): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.get('/v1/entites/chefs-actuels');
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des chefs actuels');
  }

  async affecterChef(entiteId: number, data: { user_id: number; date_debut: string; terminer_mandat_precedent?: boolean }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/entites/${entiteId}/affecter-chef`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur d\'affectation du chef');
  }

  async terminerMandatChef(entiteId: number, data: { date_fin: string; raison?: string }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/entites/${entiteId}/terminer-mandat-chef`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de fin de mandat du chef');
  }

  async getHistoriqueChefs(entiteId: number): Promise<EntiteChefHistory[]> {
    const response: AxiosResponse<ApiResponse<EntiteChefHistory[]>> = 
      await this.client.get(`/v1/entites/${entiteId}/historique-chefs`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de l\'historique des chefs');
  }

  async getUtilisateursEntite(entiteId: number, params?: {
    statut?: string;
    role?: string;
    include_historique?: boolean;
    per_page?: number;
    page?: number;
  }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.get(`/v1/entites/${entiteId}/utilisateurs`, { params });
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des utilisateurs de l\'entité');
  }

  // ========================================
  // GESTION DES POSTES
  // ========================================

  async getPostes(params?: {
    nom?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<Poste>> {
    const response: AxiosResponse<PaginatedResponse<Poste>> = 
      await this.client.get('/v1/postes', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des postes');
  }

  async getPoste(id: number): Promise<Poste> {
    const response: AxiosResponse<ApiResponse<Poste>> = 
      await this.client.get(`/v1/postes/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du poste');
  }

  async createPoste(data: { nom: string; description?: string }): Promise<Poste> {
    const response: AxiosResponse<ApiResponse<Poste>> = 
      await this.client.post('/v1/postes', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création du poste');
  }

  async updatePoste(id: number, data: { nom: string; description?: string }): Promise<Poste> {
    const response: AxiosResponse<ApiResponse<Poste>> = 
      await this.client.put(`/v1/postes/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du poste');
  }

  async deletePoste(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/postes/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du poste');
    }
  }

  // ========================================
  // GESTION DES UTILISATEURS (ÉTENDUE)
  // ========================================

  async getUsersDetailed(params?: {
    search?: string;
    statut?: boolean;
    role?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<User>> {
    const response: AxiosResponse<PaginatedResponse<User>> = 
      await this.client.get('/v1/users', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des utilisateurs');
  }

  async getUser(id: number): Promise<UserDetailed> {
    const response: AxiosResponse<ApiResponse<UserDetailed>> = 
      await this.client.get(`/v1/users/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de l\'utilisateur');
  }

  async createUser(data: {
    matricule: string;
    nom: string;
    prenom: string;
    email: string;
    password: string;
    telephone?: string;
    adresse?: string;
    statut?: boolean;
    roles?: number[];
  }): Promise<User> {
    const response: AxiosResponse<ApiResponse<User>> = 
      await this.client.post('/v1/users', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création de l\'utilisateur');
  }

  async updateUser(id: number, data: {
    matricule?: string;
    nom?: string;
    prenom?: string;
    email?: string;
    password?: string;
    telephone?: string;
    adresse?: string;
    statut?: boolean;
  }): Promise<User> {
    const response: AxiosResponse<ApiResponse<User>> = 
      await this.client.put(`/v1/users/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour de l\'utilisateur');
  }

  async deleteUser(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/users/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression de l\'utilisateur');
    }
  }

  async getUserAffectations(id: number): Promise<UtilisateurEntiteHistory[]> {
    const response: AxiosResponse<ApiResponse<UtilisateurEntiteHistory[]>> = 
      await this.client.get(`/v1/users/${id}/affectations`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des affectations');
  }

  async affecterUser(userId: number, data: {
    entite_id: number;
    poste_id: number;
    date_debut: string;
    terminer_affectation_precedente?: boolean;
  }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/users/${userId}/affecter`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur d\'affectation de l\'utilisateur');
  }

  async terminerAffectationUser(userId: number, data: { 
    date_fin: string; 
    raison?: string; 
  }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/users/${userId}/terminer-affectation`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de fin d\'affectation');
  }

  async assignRoleToUser(userId: number, data: { role_id: number }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/users/${userId}/assign-role`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur d\'assignation du rôle');
  }

  async assignRolesToUser(userId: number, data: { roles: number[] }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/users/${userId}/assign-roles`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur d\'assignation des rôles');
  }

  async removeRoleFromUser(userId: number, roleId: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/users/${userId}/roles/${roleId}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du rôle');
    }
  }

  // ========================================
  // GESTION DES RÔLES
  // ========================================

  async getRoles(params?: {
    nom?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<Role>> {
    const response: AxiosResponse<PaginatedResponse<Role>> = 
      await this.client.get('/v1/roles', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des rôles');
  }

  async getRole(id: number): Promise<Role> {
    const response: AxiosResponse<ApiResponse<Role>> = 
      await this.client.get(`/v1/roles/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du rôle');
  }

  async createRole(data: { 
    nom: string; 
    description?: string; 
    permissions?: number[]; 
  }): Promise<Role> {
    const response: AxiosResponse<ApiResponse<Role>> = 
      await this.client.post('/v1/roles', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création du rôle');
  }

  async updateRole(id: number, data: { nom: string; description?: string }): Promise<Role> {
    const response: AxiosResponse<ApiResponse<Role>> = 
      await this.client.put(`/v1/roles/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du rôle');
  }

  async deleteRole(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/roles/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du rôle');
    }
  }

  async assignPermissionToRole(roleId: number, data: { permission_id: number }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.post(`/v1/roles/${roleId}/assign-permission`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur d\'assignation de la permission');
  }

  async removePermissionFromRole(roleId: number, permissionId: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/roles/${roleId}/permissions/${permissionId}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression de la permission');
    }
  }

  async getAvailablePermissionsForRole(roleId: number): Promise<{
    role: { id: number; nom: string };
    permissions_disponibles: Permission[];
    permissions_deja_assignees: string[];
  }> {
    const response: AxiosResponse<ApiResponse<{
      role: { id: number; nom: string };
      permissions_disponibles: Permission[];
      permissions_deja_assignees: string[];
    }>> = await this.client.get(`/v1/roles/${roleId}/available-permissions`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des permissions disponibles');
  }

  // ========================================
  // GESTION DES PERMISSIONS
  // ========================================

  async getPermissions(params?: {
    nom?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<Permission>> {
    const response: AxiosResponse<PaginatedResponse<Permission>> = 
      await this.client.get('/v1/permissions', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des permissions');
  }

  async getPermission(id: number): Promise<Permission> {
    const response: AxiosResponse<ApiResponse<Permission>> = 
      await this.client.get(`/v1/permissions/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération de la permission');
  }

  async createPermission(data: { nom: string; description?: string }): Promise<Permission> {
    const response: AxiosResponse<ApiResponse<Permission>> = 
      await this.client.post('/v1/permissions', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création de la permission');
  }

  async updatePermission(id: number, data: { nom: string; description?: string }): Promise<Permission> {
    const response: AxiosResponse<ApiResponse<Permission>> = 
      await this.client.put(`/v1/permissions/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour de la permission');
  }

  async deletePermission(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/permissions/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression de la permission');
    }
  }

  async getPermissionUsers(id: number): Promise<{
    permission: { id: number; nom: string; description?: string };
    utilisateurs: User[];
    statistiques: { total_utilisateurs: number; utilisateurs_actifs: number; roles_avec_permission: number };
  }> {
    const response: AxiosResponse<ApiResponse<{
      permission: { id: number; nom: string; description?: string };
      utilisateurs: User[];
      statistiques: { total_utilisateurs: number; utilisateurs_actifs: number; roles_avec_permission: number };
    }>> = await this.client.get(`/v1/permissions/${id}/users`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des utilisateurs de la permission');
  }

  async getAvailableRolesForPermission(permissionId: number): Promise<{
    permission: { id: number; nom: string };
    roles_disponibles: Role[];
    roles_deja_assignes: string[];
  }> {
    const response: AxiosResponse<ApiResponse<{
      permission: { id: number; nom: string };
      roles_disponibles: Role[];
      roles_deja_assignes: string[];
    }>> = await this.client.get(`/v1/permissions/${permissionId}/available-roles`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des rôles disponibles');
  }

  // ========================================
  // GESTION DES TYPES DE PROJETS
  // ========================================

  async getTypeProjets(params?: {
    nom?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<TypeProjet>> {
    const response: AxiosResponse<PaginatedResponse<TypeProjet>> = 
      await this.client.get('/v1/type-projets', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des types de projets');
  }

  async getTypeProjet(id: number): Promise<TypeProjet> {
    const response: AxiosResponse<ApiResponse<TypeProjet>> = 
      await this.client.get(`/v1/type-projets/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || "Erreur de récupération du type d'instruction");
  }

  async createTypeProjet(data: TypeProjetCreateRequest): Promise<TypeProjet> {
    const response: AxiosResponse<ApiResponse<TypeProjet>> = 
      await this.client.post('/v1/type-projets', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || "Erreur de création du type d'instruction");
  }

  async updateTypeProjet(id: number, data: TypeProjetUpdateRequest): Promise<TypeProjet> {
    const response: AxiosResponse<ApiResponse<TypeProjet>> = 
      await this.client.put(`/v1/type-projets/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || "Erreur de mise à jour du type d'instruction");
  }

  async deleteTypeProjet(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/type-projets/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || "Erreur de suppression du type d'instruction");
    }
  }

  // =================================================================
  // TYPES DE TÂCHES - GESTION COMPLÈTE
  // =================================================================

  async getTypeTaches(params?: {
    nom?: string;
    actif?: boolean;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<TypeTache>> {
    try {
      const response = await this.client.get('/v1/type-taches', { params });
      return response.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la récupération des types de tâches');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async getTypeTache(id: number): Promise<TypeTache> {
    try {
      const response = await this.client.get(`/v1/type-taches/${id}`);
      return response.data.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la récupération du type de tâche');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async createTypeTache(data: TypeTacheCreateRequest): Promise<TypeTache> {
    try {
      const response = await this.client.post('/v1/type-taches', data);
      return response.data.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la création du type de tâche');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async updateTypeTache(id: number, data: TypeTacheUpdateRequest): Promise<TypeTache> {
    try {
      const response = await this.client.put(`/v1/type-taches/${id}`, data);
      return response.data.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la mise à jour du type de tâche');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async deleteTypeTache(id: number): Promise<void> {
    try {
      const response = await this.client.delete(`/v1/type-taches/${id}`);
      if (!response.data.success) {
        throw new Error(response.data.message || 'Erreur lors de la suppression du type de tâche');
      }
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la suppression du type de tâche');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async getTypeTacheStatistiques(id: number): Promise<{
    type_tache: TypeTache;
    statistiques: TypeTacheStatistiques;
  }> {
    try {
      const response = await this.client.get(`/v1/type-taches/${id}/statistiques`);
      return response.data.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la récupération des statistiques');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async getTypeTachesActifs(): Promise<TypeTache[]> {
    try {
      const response = await this.client.get('/v1/type-taches/actifs');
      return response.data.data;
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la récupération des types de tâches actifs');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async getTypeProjetStatistiques(id: number): Promise<{
    type_projet: TypeProjet;
    statistiques: TypeProjetStatistiques;
  }> {
    const response: AxiosResponse<ApiResponse<{
      type_projet: TypeProjet;
      statistiques: TypeProjetStatistiques;
    }>> = await this.client.get(`/v1/type-projets/${id}/statistiques`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des statistiques');
  }

  // ========================================
  // GESTION DES PROJETS
  // ========================================

  async getProjects(params?: ProjectFilters): Promise<PaginatedResponse<Project>> {
    try {
      const response = await this.client.get('/v1/projets', { params });
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des projets');
    }
  }

  async getProject(id: number): Promise<Project> {
    const response: AxiosResponse<ApiResponse<Project>> = 
      await this.client.get(`/v1/projets/${id}`);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du projet');
  }

  async createProject(data: ProjectCreateRequest): Promise<Project> {
    try {
      const response: AxiosResponse<ApiResponse<Project>> = 
        await this.client.post('/v1/projets', data);
      
      if (response.data.success && response.data.data) {
        return response.data.data;
      }
      
      throw new Error(response.data.message || 'Erreur de création du projet');
    } catch (error: any) {
      // Si c'est une erreur de validation (422), on la relance avec les détails
      if (error.response?.status === 422 && error.response?.data?.errors) {
        const validationError = new Error('Erreur de validation');
        (validationError as any).response = error.response;
        throw validationError;
      }
      throw error;
    }
  }

  async updateProject(id: number, data: ProjectUpdateRequest): Promise<Project> {
    try {
      const response: AxiosResponse<ApiResponse<Project>> = 
        await this.client.put(`/v1/projets/${id}`, data);
      
      if (response.data.success && response.data.data) {
        return response.data.data;
      }
      
      throw new Error(response.data.message || 'Erreur de mise à jour du projet');
    } catch (error: any) {
      // Si c'est une erreur de validation (422), on la relance avec les détails
      if (error.response?.status === 422 && error.response?.data?.errors) {
        const validationError = new Error('Erreur de validation');
        (validationError as any).response = error.response;
        throw validationError;
      }
      throw error;
    }
  }

  async deleteProject(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/projets/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du projet');
    }
  }

  async changeProjectStatut(
    projectId: number,
    data: {
      nouveau_statut: 'a_faire' | 'en_cours' | 'bloque' | 'demande_de_cloture' | 'termine';
      commentaire?: string;
      justificatif_path?: string;
    }
  ): Promise<ApiResponse<Project>> {
    try {
      const response = await this.client.post(`/v1/projets/${projectId}/changer-statut`, data);
      return response.data;
    } catch (error: any) {
      // Si c'est une erreur de validation (422), on la relance avec les détails
      if (error.response?.status === 422 && error.response?.data?.errors) {
        const validationError = new Error('Erreur de validation');
        (validationError as any).response = error.response;
        throw validationError;
      }
      // Pour les autres erreurs, on préserve la réponse complète
      if (error.response?.data) {
        const apiError = new Error(error.response.data.message || 'Erreur lors du changement de statut');
        (apiError as any).response = error.response;
        throw apiError;
      }
      throw error;
    }
  }

  async updateProjectExecutionLevel(id: number, data: ProjectExecutionLevelRequest): Promise<Project> {
    try {
      const response: AxiosResponse<ApiResponse<Project>> = 
        await this.client.post(`/v1/projets/${id}/niveau-execution`, data);
      
      if (response.data.success && response.data.data) {
        return response.data.data;
      }
      
      throw new Error(response.data.message || 'Erreur de mise à jour du niveau d\'exécution');
    } catch (error: any) {
      // Gestion des erreurs spécifiques du backend
      if (error.response?.status === 422) {
        // Erreur de validation ou règles métier
        const errorMessage = error.response.data.message || 'Erreur de validation';
        const validationError = new Error(errorMessage);
        (validationError as any).response = error.response;
        throw validationError;
      }
      throw error;
    }
  }

  async getProjectExecutionLevelInfo(id: number): Promise<{
    projet_id: number;
    niveau_actuel: number;
    mode: 'automatique' | 'manuel';
    nombre_taches: number;
    niveau_moyen_taches: number | null;
    peut_modifier: boolean;
  }> {
    try {
      const response: AxiosResponse<ApiResponse<{
        projet_id: number;
        niveau_actuel: number;
        mode: 'automatique' | 'manuel';
        nombre_taches: number;
        niveau_moyen_taches: number | null;
        peut_modifier: boolean;
      }>> = await this.client.get(`/v1/projets/${id}/niveau-execution-info`);
      
      if (response.data.success && response.data.data) {
        return response.data.data;
      }
      
      throw new Error(response.data.message || 'Erreur de récupération des informations du niveau d\'exécution');
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Erreur lors de la récupération des informations du niveau d\'exécution');
      }
      throw new Error('Erreur de connexion');
    }
  }

  async getProjectDashboard(params?: ProjectFilters): Promise<ProjectDashboard> {
    try {
      const response: AxiosResponse<ApiResponse<ProjectDashboard>> = 
        await this.client.get('/v1/projets/tableau-bord', { params });
      
      if (response.data.success && response.data.data) {
        return response.data.data;
      }
      
      throw new Error(response.data.message || 'Erreur de récupération du tableau de bord');
    } catch (error: any) {
      if (error.response?.status === 403) {
        throw new Error('Vous n\'avez pas les permissions nécessaires pour consulter le tableau de bord');
      }
      throw error;
    }
  }

  async getProjectFilterEntities(): Promise<FilterEntity[]> {
    try {
      const response = await this.client.get('/v1/projets/filtres/entites');
      return response.data.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des entités pour filtres');
    }
  }

  async getProjectFilterUsers(): Promise<FilterUser[]> {
    try {
      const response = await this.client.get('/v1/projets/filtres/utilisateurs');
      return response.data.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des utilisateurs pour filtres');
    }
  }

  // Gestion des favoris de projets
  async getProjectFavorites(): Promise<PaginatedResponse<Project>> {
    const response = await this.client.get('/v1/projets/favoris');
    return response.data;
  }

  async addProjectToFavorites(projectId: number): Promise<ApiResponse<void>> {
    const response = await this.client.post(`/v1/projets/${projectId}/favoris`);
    return response.data;
  }

  async removeProjectFromFavorites(projectId: number): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/projets/${projectId}/favoris`);
    return response.data;
  }

  async toggleProjectFavorite(projectId: number): Promise<ApiResponse<{ est_favori: boolean; action: string }>> {
    const response = await this.client.post(`/v1/projets/${projectId}/favoris/toggle`);
    return response.data;
  }

  // =================================================================
  // PIÈCES JOINTES DES PROJETS
  // =================================================================

  async getProjectAttachments(
    projectId: number,
    params?: {
      est_justificatif?: boolean;
      type_document?: string;
      per_page?: number;
      sort_by?: string;
      sort_order?: 'asc' | 'desc';
    }
  ): Promise<ApiResponse<ProjectAttachment[]>> {
    const queryParams = new URLSearchParams();
    if (params?.est_justificatif !== undefined) {
      queryParams.append('est_justificatif', params.est_justificatif.toString());
    }
    if (params?.type_document) {
      queryParams.append('type_document', params.type_document);
    }
    if (params?.per_page) {
      queryParams.append('per_page', params.per_page.toString());
    }
    if (params?.sort_by) {
      queryParams.append('sort_by', params.sort_by);
    }
    if (params?.sort_order) {
      queryParams.append('sort_order', params.sort_order);
    }

    const url = `/v1/projets/${projectId}/pieces-jointes${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    const response = await this.client.get(url);
    return response.data;
  }

  async uploadProjectAttachment(
    projectId: number,
    formData: FormData
  ): Promise<ApiResponse<ProjectAttachment>> {
    const response = await this.client.post(`/v1/projets/${projectId}/pieces-jointes`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async getProjectAttachmentDetails(
    projectId: number,
    attachmentId: number
  ): Promise<ApiResponse<ProjectAttachment>> {
    const response = await this.client.get(`/v1/projets/${projectId}/pieces-jointes/${attachmentId}`);
    return response.data;
  }

  async downloadProjectAttachment(
    projectId: number,
    attachmentId: number
  ): Promise<Blob> {
    const response = await this.client.get(`/v1/projets/${projectId}/pieces-jointes/${attachmentId}/download`, {
      responseType: 'blob',
    });
    return response.data;
  }

  async updateProjectAttachment(
    projectId: number,
    attachmentId: number,
    data: {
      description?: string;
      type_document?: 'rapport' | 'justificatif' | 'piece_jointe' | 'documentation' | 'autre';
    }
  ): Promise<ApiResponse<ProjectAttachment>> {
    const response = await this.client.put(`/v1/projets/${projectId}/pieces-jointes/${attachmentId}`, data);
    return response.data;
  }

  async deleteProjectAttachment(
    projectId: number,
    attachmentId: number
  ): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/projets/${projectId}/pieces-jointes/${attachmentId}`);
    return response.data;
  }

  async getProjectAttachmentsStats(
    projectId: number
  ): Promise<ApiResponse<{
    total_fichiers: number;
    total_justificatifs: number;
    taille_totale: number;
    types_documents: Array<{ type_document: string; count: number }>;
    dernier_upload: ProjectAttachment | null;
  }>> {
    const response = await this.client.get(`/v1/projets/${projectId}/pieces-jointes/statistiques`);
    return response.data;
  }

  // =================================================================
  // TÂCHES - GESTION COMPLÈTE
  // =================================================================

  // Récupérer toutes les tâches (avec filtres)
  async getTaches(filters?: TacheFilters): Promise<ApiResponse<Tache[]>> {
    const queryParams = new URLSearchParams();
    
    if (filters?.projet_id) {
      queryParams.append('projet_id', filters.projet_id.toString());
    }
    if (filters?.statut) {
      queryParams.append('statut', filters.statut);
    }
    if (filters?.type_tache_id) {
      queryParams.append('type_tache_id', filters.type_tache_id.toString());
    }
    if (filters?.responsable_ids && filters.responsable_ids.length > 0) {
      filters.responsable_ids.forEach(id => {
        queryParams.append('responsable_ids[]', id.toString());
      });
    }
    if (filters?.entite_id) {
      queryParams.append('entite_id', filters.entite_id.toString());
    }
    if (filters?.en_retard !== undefined && filters.en_retard !== null) {
      queryParams.append('en_retard', filters.en_retard.toString());
    }
    if (filters?.search) {
      queryParams.append('search', filters.search);
    }
    if (filters?.sort_by) {
      queryParams.append('sort_by', filters.sort_by);
    }
    if (filters?.sort_order) {
      queryParams.append('sort_order', filters.sort_order);
    }
    if (filters?.per_page) {
      queryParams.append('per_page', filters.per_page.toString());
    }

    const url = `/v1/taches${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    const response = await this.client.get(url);
    return response.data;
  }

  // Récupérer les tâches de l'utilisateur connecté
  async getMesTaches(filters?: Pick<TacheFilters, 'statut' | 'type_tache_id' | 'en_retard' | 'entite_id' | 'sort_by' | 'sort_order'>): Promise<ApiResponse<Tache[]>> {
    const queryParams = new URLSearchParams();
    
    if (filters?.statut) {
      queryParams.append('statut', filters.statut);
    }
    if (filters?.type_tache_id) {
      queryParams.append('type_tache_id', filters.type_tache_id.toString());
    }
    if (filters?.en_retard !== undefined && filters.en_retard !== null) {
      queryParams.append('en_retard', filters.en_retard.toString());
    }
    if (filters?.entite_id) {
      queryParams.append('entite_id', filters.entite_id.toString());
    }
    if (filters?.sort_by) {
      queryParams.append('sort_by', filters.sort_by);
    }
    if (filters?.sort_order) {
      queryParams.append('sort_order', filters.sort_order);
    }

    const url = `/v1/taches/mes-taches${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    const response = await this.client.get(url);
    return response.data;
  }

  // Récupérer une tâche spécifique
  async getTache(id: number): Promise<ApiResponse<Tache>> {
    const response = await this.client.get(`/v1/taches/${id}`);
    return response.data;
  }

  // Créer une nouvelle tâche
  async createTache(data: CreateTacheRequest): Promise<ApiResponse<Tache>> {
    const response = await this.client.post('/v1/taches', data);
    return response.data;
  }

  // Mettre à jour une tâche
  async updateTache(id: number, data: UpdateTacheRequest): Promise<ApiResponse<Tache>> {
    const response = await this.client.put(`/v1/taches/${id}`, data);
    return response.data;
  }

  // Supprimer une tâche
  async deleteTache(id: number): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/taches/${id}`);
    return response.data;
  }

  // Changer le statut d'une tâche
  async changeTacheStatut(id: number, data: ChangeTacheStatutRequest): Promise<ApiResponse<Tache>> {
    const response = await this.client.post(`/v1/taches/${id}/changer-statut`, data);
    return response.data;
  }

  // Récupérer l'historique des statuts d'une tâche
  async getTacheHistoriqueStatuts(id: number): Promise<ApiResponse<TacheHistoriqueStatut[]>> {
    const response = await this.client.get(`/v1/taches/${id}/historique-statuts`);
    return response.data;
  }

  // =================================================================
  // DISCUSSIONS DES TÂCHES
  // =================================================================

  // Récupérer les discussions d'une tâche
  async getTacheDiscussions(
    tacheId: number,
    params?: {
      sort_order?: 'asc' | 'desc';
      per_page?: number;
    }
  ): Promise<ApiResponse<TacheDiscussion[]>> {
    const queryParams = new URLSearchParams();
    if (params?.sort_order) {
      queryParams.append('sort_order', params.sort_order);
    }
    if (params?.per_page) {
      queryParams.append('per_page', params.per_page.toString());
    }

    const url = `/v1/taches/${tacheId}/discussions${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    const response = await this.client.get(url);
    return response.data;
  }

  // Créer une discussion pour une tâche
  async createTacheDiscussion(
    tacheId: number,
    data: {
      message: string;
      parent_id?: number;
    }
  ): Promise<ApiResponse<TacheDiscussion>> {
    const response = await this.client.post(`/v1/taches/${tacheId}/discussions`, data);
    return response.data;
  }

  // =================================================================
  // PIÈCES JOINTES DES TÂCHES
  // =================================================================

  async getTaskAttachments(
    taskId: number,
    params?: {
      est_justificatif?: boolean;
      type_document?: string;
      per_page?: number;
      sort_by?: string;
      sort_order?: 'asc' | 'desc';
    }
  ): Promise<ApiResponse<TachePieceJointe[]>> {
    const queryParams = new URLSearchParams();
    if (params?.est_justificatif !== undefined) {
      queryParams.append('est_justificatif', params.est_justificatif.toString());
    }
    if (params?.type_document) {
      queryParams.append('type_document', params.type_document);
    }
    if (params?.per_page) {
      queryParams.append('per_page', params.per_page.toString());
    }
    if (params?.sort_by) {
      queryParams.append('sort_by', params.sort_by);
    }
    if (params?.sort_order) {
      queryParams.append('sort_order', params.sort_order);
    }

    const url = `/v1/taches/${taskId}/pieces-jointes${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;
    const response = await this.client.get(url);
    return response.data;
  }

  async uploadTaskAttachment(
    taskId: number,
    formData: FormData
  ): Promise<ApiResponse<TaskAttachment>> {
    const response = await this.client.post(`/v1/taches/${taskId}/pieces-jointes`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async getTaskAttachmentDetails(
    taskId: number,
    attachmentId: number
  ): Promise<ApiResponse<TaskAttachment>> {
    const response = await this.client.get(`/v1/taches/${taskId}/pieces-jointes/${attachmentId}`);
    return response.data;
  }

  async downloadTaskAttachment(
    taskId: number,
    attachmentId: number
  ): Promise<Blob> {
    const response = await this.client.get(`/v1/taches/${taskId}/pieces-jointes/${attachmentId}/download`, {
      responseType: 'blob',
    });
    return response.data;
  }

  async updateTaskAttachment(
    taskId: number,
    attachmentId: number,
    data: {
      description?: string;
      type_document?: 'rapport' | 'justificatif' | 'piece_jointe' | 'documentation' | 'autre';
    }
  ): Promise<ApiResponse<TaskAttachment>> {
    const response = await this.client.put(`/v1/taches/${taskId}/pieces-jointes/${attachmentId}`, data);
    return response.data;
  }

  async deleteTaskAttachment(
    taskId: number,
    attachmentId: number
  ): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/taches/${taskId}/pieces-jointes/${attachmentId}`);
    return response.data;
  }

  async getTaskAttachmentsStats(
    taskId: number
  ): Promise<ApiResponse<{
    total_fichiers: number;
    total_justificatifs: number;
    taille_totale: number;
    types_documents: Array<{ type_document: string; count: number }>;
    dernier_upload: TaskAttachment | null;
  }>> {
    const response = await this.client.get(`/v1/taches/${taskId}/pieces-jointes/statistiques`);
    return response.data;
  }

  // =================================================================
  // DISCUSSIONS DES PROJETS
  // =================================================================

  async getProjectDiscussions(
    projectId: number,
    params?: {
      sort_order?: 'asc' | 'desc';
      per_page?: number;
      page?: number;
    }
  ): Promise<PaginatedResponse<ProjectDiscussion>> {
    const response = await this.client.get(`/v1/projets/${projectId}/discussions`, { params });
    return response.data;
  }

  async createProjectDiscussion(
    projectId: number,
    data: DiscussionCreateRequest
  ): Promise<ApiResponse<ProjectDiscussion>> {
    const response = await this.client.post(`/v1/projets/${projectId}/discussions`, data);
    return response.data;
  }

  async getProjectDiscussion(
    projectId: number,
    discussionId: number
  ): Promise<ApiResponse<ProjectDiscussion>> {
    const response = await this.client.get(`/v1/projets/${projectId}/discussions/${discussionId}`);
    return response.data;
  }

  async updateProjectDiscussion(
    projectId: number,
    discussionId: number,
    data: DiscussionUpdateRequest
  ): Promise<ApiResponse<ProjectDiscussion>> {
    const response = await this.client.put(`/v1/projets/${projectId}/discussions/${discussionId}`, data);
    return response.data;
  }

  async deleteProjectDiscussion(
    projectId: number,
    discussionId: number
  ): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/projets/${projectId}/discussions/${discussionId}`);
    return response.data;
  }

  async getProjectDiscussionsStats(
    projectId: number
  ): Promise<ApiResponse<DiscussionStats>> {
    const response = await this.client.get(`/v1/projets/${projectId}/discussions/statistiques`);
    return response.data;
  }

  // =================================================================
  // DISCUSSIONS DES TÂCHES
  // =================================================================

  async getTaskDiscussions(
    taskId: number,
    params?: {
      sort_order?: 'asc' | 'desc';
      per_page?: number;
      page?: number;
    }
  ): Promise<PaginatedResponse<TaskDiscussion>> {
    const response = await this.client.get(`/v1/taches/${taskId}/discussions`, { params });
    return response.data;
  }

  async createTaskDiscussion(
    taskId: number,
    data: DiscussionCreateRequest
  ): Promise<ApiResponse<TaskDiscussion>> {
    const response = await this.client.post(`/v1/taches/${taskId}/discussions`, data);
    return response.data;
  }

  async getTaskDiscussion(
    taskId: number,
    discussionId: number
  ): Promise<ApiResponse<TaskDiscussion>> {
    const response = await this.client.get(`/v1/taches/${taskId}/discussions/${discussionId}`);
    return response.data;
  }

  async updateTaskDiscussion(
    taskId: number,
    discussionId: number,
    data: DiscussionUpdateRequest
  ): Promise<ApiResponse<TaskDiscussion>> {
    const response = await this.client.put(`/v1/taches/${taskId}/discussions/${discussionId}`, data);
    return response.data;
  }

  async deleteTaskDiscussion(
    taskId: number,
    discussionId: number
  ): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/taches/${taskId}/discussions/${discussionId}`);
    return response.data;
  }

  async getTaskDiscussionsStats(
    taskId: number
  ): Promise<ApiResponse<DiscussionStats>> {
    const response = await this.client.get(`/v1/taches/${taskId}/discussions/statistiques`);
    return response.data;
  }

  // =================================================================
  // AUDIT - TRACABILITÉ DES ACTIONS
  // =================================================================

  // Méthodes d'API pour l'audit
  async getAuditLogs(filters: AuditFilters = {}): Promise<AuditResponse> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });
    
    const response = await this.client.get('/v1/audit/logs', { params });
    return response.data;
  }

  async getAuditLog(id: number): Promise<{ data: AuditLog }> {
    const response = await this.client.get(`/v1/audit/logs/${id}`);
    return response.data;
  }

  async getAuditStats(): Promise<{ data: AuditStats }> {
    const response = await this.client.get('/v1/audit/stats');
    return response.data;
  }

  async exportAuditLogs(filters: AuditFilters = {}): Promise<{ data: any[] }> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });
    
    const response = await this.client.get('/v1/audit/export', { params });
    return response.data;
  }

  async assignPermissionsToRoleBulk(roleId: number, permissionIds: number[]): Promise<any> {
    const response = await this.client.post(`/v1/roles/${roleId}/assign-permissions-bulk`, {
      permission_ids: permissionIds,
    });
    return response.data;
  }

  async removePermissionsFromRoleBulk(roleId: number, permissionIds: number[]): Promise<any> {
    const response = await this.client.post(`/v1/roles/${roleId}/remove-permissions-bulk`, {
      permission_ids: permissionIds,
    });
    return response.data;
  }

  /**
   * Demande de réinitialisation de mot de passe (self-service)
   */
  async forgotPassword(email: string): Promise<any> {
    const response = await this.client.post('/v1/auth/forgot-password', { email });
    return response.data;
  }

  /**
   * Réinitialiser le mot de passe avec token
   */
  async resetPassword(data: { email: string; token: string; password: string; password_confirmation: string; }): Promise<any> {
    const response = await this.client.post('/v1/auth/reset-password', data);
    return response.data;
  }

  /**
   * Réinitialisation par admin à un mot de passe par défaut
   */
  async resetUserPassword(userId: number, passwordType?: 'secure' | 'simple' | 'memorable', passwordLength?: number): Promise<ApiResponse<{
    user_id: number;
    user_email: string;
    admin_name: string;
    password_sent: boolean;
    password_type: string;
    generated_password?: string;
  }>> {
    const response = await this.client.post(`/v1/users/${userId}/reset-password`, {
      password_type: passwordType || 'secure',
      password_length: passwordLength || 12
    });
    return response.data;
  }

  // Activités de connexion
  async getUserLoginActivities(userId: number, params?: {
    action?: string;
    date_from?: string;
    date_to?: string;
    ip_address?: string;
    sort_by?: string;
    sort_order?: string;
    per_page?: number;
  }): Promise<ApiResponse<{
    id: number;
    action: string;
    ip_address: string;
    user_agent: string;
    location: string | null;
    device_type: string;
    browser: string;
    os: string;
    session_id: string | null;
    created_at: string;
    session_duration: string;
    user: {
      id: number;
      nom: string;
      prenom: string;
      email: string;
    };
  }[]>> {
    const response = await this.client.get(`/v1/login-activities/user/${userId}`, { params });
    return response.data;
  }

  async getUserLoginStats(userId: number, days?: number): Promise<ApiResponse<{
    total_logins: number;
    total_logouts: number;
    failed_logins: number;
    password_resets: number;
    session_expired: number;
    unique_ips: number;
    devices: string[];
    browsers: string[];
    os_list: string[];
    last_login: string | null;
    last_logout: string | null;
    average_session_duration: number;
    average_session_duration_formatted: string;
  }>> {
    const response = await this.client.get(`/v1/login-activities/user/${userId}/stats`, { 
      params: { days } 
    });
    return response.data;
  }

  async getGlobalLoginActivities(params?: {
    action?: string;
    user_id?: number;
    date_from?: string;
    date_to?: string;
    ip_address?: string;
    sort_by?: string;
    sort_order?: string;
    per_page?: number;
  }): Promise<ApiResponse<{
    id: number;
    action: string;
    ip_address: string;
    user_agent: string;
    location: string | null;
    device_type: string;
    browser: string;
    os: string;
    session_id: string | null;
    created_at: string;
    session_duration: string;
    user: {
      id: number;
      nom: string;
      prenom: string;
      email: string;
    };
  }[]>> {
    const response = await this.client.get('/v1/login-activities/global', { params });
    return response.data;
  }

  async getGlobalLoginStats(days?: number): Promise<ApiResponse<{
    total_logins: number;
    total_logouts: number;
    failed_logins: number;
    password_resets: number;
    session_expired: number;
    unique_users: number;
    unique_ips: number;
    top_devices: Record<string, number>;
    top_browsers: Record<string, number>;
    top_os: Record<string, number>;
    daily_activity: Record<string, {
      logins: number;
      logouts: number;
      failed_logins: number;
    }>;
  }>> {
    const response = await this.client.get('/v1/login-activities/global/stats', { 
      params: { days } 
    });
    return response.data;
  }

  async getRecentLoginActivities(hours?: number, limit?: number): Promise<ApiResponse<{
    id: number;
    action: string;
    ip_address: string;
    device_type: string;
    browser: string;
    os: string;
    created_at: string;
    session_duration: string;
    user: {
      id: number;
      nom: string;
      prenom: string;
      email: string;
    };
  }[]>> {
    const response = await this.client.get('/v1/login-activities/recent', { 
      params: { hours, limit } 
    });
    return response.data;
  }


}

export const apiClient = new ApiClient();

// Export spécifique pour l'audit
export const auditApi = {
  getAuditLogs: (filters: AuditFilters = {}) => apiClient.getAuditLogs(filters),
  getAuditLog: (id: number) => apiClient.getAuditLog(id),
  getAuditStats: () => apiClient.getAuditStats(),
  exportAuditLogs: (filters: AuditFilters = {}) => apiClient.exportAuditLogs(filters),
};