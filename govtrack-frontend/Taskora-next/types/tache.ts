// Types pour les tâches basés sur le modèle Laravel
export type TacheStatut = 'a_faire' | 'en_cours' | 'bloque' | 'demande_de_cloture' | 'termine';

export interface Tache {
  id: number;
  titre: string;
  description: string | null;
  projet_id: number;
  responsable_id: number | null;
  statut: TacheStatut;
  niveau_execution: number;
  date_debut_previsionnelle: string | null;
  date_fin_previsionnelle: string | null;
  date_debut_reelle: string | null;
  date_fin_reelle: string | null;
  date_creation: string;
  date_modification: string;
  creer_par: string;
  modifier_par: string | null;
  
  // Relations chargées
  projet?: {
    id: number;
    titre: string;
    typeProjet?: {
      id: number;
      nom: string;
    };
    porteur?: {
      id: number;
      nom: string;
      prenom: string;
      email: string;
    };
  };
  responsable?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    matricule: string;
  };
  pieces_jointes?: TachePieceJointe[];
  discussions?: TacheDiscussion[];
  historique_statuts?: TacheHistoriqueStatut[];
  
  // Attributs calculés
  statut_libelle?: string;
  est_en_retard?: boolean;
}

export interface TachePieceJointe {
  id: number;
  tache_id: number;
  user_id: number;
  fichier_path: string;
  nom_original: string;
  mime_type: string;
  taille: number;
  description: string | null;
  est_justificatif: boolean;
  type_document: string;
  date_creation: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
}

export interface TacheDiscussion {
  id: number;
  tache_id: number;
  user_id: number;
  parent_id: number | null;
  message: string;
  est_modifie: boolean;
  date_creation: string;
  creer_par: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
  reponses?: TacheDiscussion[];
}

export interface TacheHistoriqueStatut {
  id: number;
  tache_id: number;
  user_id: number;
  ancien_statut: TacheStatut;
  nouveau_statut: TacheStatut;
  commentaire: string | null;
  justificatif_path: string | null;
  date_changement: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
}

// Types pour les requêtes API
export interface CreateTacheRequest {
  titre: string;
  description?: string;
  projet_id: number;
  responsable_id?: number;
  date_debut_previsionnelle?: string;
  date_fin_previsionnelle?: string;
}

export interface UpdateTacheRequest {
  titre?: string;
  description?: string;
  responsable_id?: number;
  date_debut_previsionnelle?: string;
  date_fin_previsionnelle?: string;
  niveau_execution?: number;
}

export interface ChangeTacheStatutRequest {
  nouveau_statut: TacheStatut;
  niveau_execution?: number;
  commentaire?: string;
  justificatif_path?: string;
}

// Types pour les filtres
export interface TacheFilters {
  projet_id?: number;
  statut?: TacheStatut;
  responsable_id?: number;
  entite_id?: number;
  en_retard?: boolean;
  search?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  per_page?: number;
}

// Mapping des statuts pour le Kanban
export const TACHE_STATUTS_KANBAN = {
  a_faire: 'À faire',
  en_cours: 'En cours',
  bloque: 'Bloqué',
  demande_de_cloture: 'Demande de clôture',
  termine: 'Terminé'
} as const;

// Couleurs pour les statuts dans le Kanban
export const TACHE_STATUT_COLORS = {
  a_faire: 'bg-gray-100 text-gray-800 border-gray-200',
  en_cours: 'bg-blue-100 text-blue-800 border-blue-200',
  bloque: 'bg-red-100 text-red-800 border-red-200',
  demande_de_cloture: 'bg-yellow-100 text-yellow-800 border-yellow-200',
  termine: 'bg-green-100 text-green-800 border-green-200'
} as const;