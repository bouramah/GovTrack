import axios, { AxiosInstance, AxiosResponse } from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

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
  permissions?: {
    level: string;
    can_filter_by_user: boolean;
    description: string;
  };
}

export interface Project {
  id: number;
  titre: string;
  description: string;
  statut: string;
  statut_libelle: string;
  niveau_execution: number;
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
  porteur: {
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
  porteur_id: number;
  donneur_ordre_id: number;
  date_debut_previsionnelle: string;
  date_fin_previsionnelle?: string;
  justification_modification_dates?: string;
}

export interface ProjectUpdateRequest {
  titre?: string;
  description?: string;
  type_projet_id?: number;
  porteur_id?: number;
  donneur_ordre_id?: number;
  date_debut_previsionnelle?: string;
  date_fin_previsionnelle?: string;
  justification_modification_dates?: string;
}

export interface ProjectStatutChangeRequest {
  nouveau_statut: string;
  commentaire?: string;
  justificatif?: File;
}

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
  responsable_id: number;
  responsable?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
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
        // Si token expiré, essayer de le rafraîchir
        if (error.response?.status === 401) {
          const originalRequest = error.config;
          
          // Éviter les boucles infinies
          if (!originalRequest._retry) {
            originalRequest._retry = true;
            
            try {
              // Essayer de rafraîchir le token
              const response = await this.refresh();
              // Retenter la requête originale avec le nouveau token
              originalRequest.headers.Authorization = `Bearer ${response.token}`;
              return this.client(originalRequest);
            } catch (refreshError) {
              // Si le refresh échoue, déconnecter
              this.clearToken();
              window.location.href = '/login';
            }
          } else {
            // Si on a déjà essayé de rafraîchir, déconnecter
            this.clearToken();
            window.location.href = '/login';
          }
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
    
    throw new Error(response.data.message || 'Erreur d\'upload de la photo');
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
    
    throw new Error(response.data.message || 'Erreur de récupération du type de projet');
  }

  async createTypeProjet(data: TypeProjetCreateRequest): Promise<TypeProjet> {
    const response: AxiosResponse<ApiResponse<TypeProjet>> = 
      await this.client.post('/v1/type-projets', data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de création du type de projet');
  }

  async updateTypeProjet(id: number, data: TypeProjetUpdateRequest): Promise<TypeProjet> {
    const response: AxiosResponse<ApiResponse<TypeProjet>> = 
      await this.client.put(`/v1/type-projets/${id}`, data);
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du type de projet');
  }

  async deleteTypeProjet(id: number): Promise<void> {
    const response: AxiosResponse<ApiResponse<void>> = 
      await this.client.delete(`/v1/type-projets/${id}`);
    
    if (!response.data.success) {
      throw new Error(response.data.message || 'Erreur de suppression du type de projet');
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

  async getProjects(params?: {
    search?: string;
    statut?: string;
    porteur_id?: number;
    donneur_ordre_id?: number;
    type_projet_id?: number;
    en_retard?: boolean;
    niveau_execution_min?: number;
    niveau_execution_max?: number;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  }): Promise<PaginatedResponse<Project>> {
    const response: AxiosResponse<PaginatedResponse<Project>> = 
      await this.client.get('/v1/projets', { params });
    
    if (response.data.success && response.data.data) {
      return response.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération des projets');
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

  async changeProjectStatut(id: number, data: ProjectStatutChangeRequest): Promise<Project> {
    const formData = new FormData();
    formData.append('nouveau_statut', data.nouveau_statut);
    if (data.commentaire) formData.append('commentaire', data.commentaire);
    if (data.justificatif) formData.append('justificatif', data.justificatif);

    const response: AxiosResponse<ApiResponse<Project>> = 
      await this.client.post(`/v1/projets/${id}/changer-statut`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de changement de statut');
  }

  async updateProjectExecutionLevel(id: number, niveau_execution: number): Promise<Project> {
    const response: AxiosResponse<ApiResponse<Project>> = 
      await this.client.put(`/v1/projets/${id}/niveau-execution`, { niveau_execution });
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de mise à jour du niveau d\'exécution');
  }

  async getProjectDashboard(params?: {
    statut?: string;
    type_projet_id?: number;
    en_retard?: boolean;
  }): Promise<any> {
    const response: AxiosResponse<ApiResponse<any>> = 
      await this.client.get('/v1/projets/tableau-bord', { params });
    
    if (response.data.success && response.data.data) {
      return response.data.data;
    }
    
    throw new Error(response.data.message || 'Erreur de récupération du tableau de bord');
  }
}

export const apiClient = new ApiClient();
