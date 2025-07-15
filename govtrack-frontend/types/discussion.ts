export interface ProjectDiscussion {
  id: number;
  projet_id: number;
  user_id: number;
  parent_id?: number;
  message: string;
  est_modifie: boolean;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
  };
  parent?: ProjectDiscussion;
  reponses?: ProjectDiscussion[];
}

export interface TaskDiscussion {
  id: number;
  tache_id: number;
  user_id: number;
  parent_id?: number;
  message: string;
  est_modifie: boolean;
  date_creation: string;
  date_modification?: string;
  creer_par: string;
  modifier_par?: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
  };
  parent?: TaskDiscussion;
  reponses?: TaskDiscussion[];
}

export interface DiscussionCreateRequest {
  message: string;
  parent_id?: number;
}

export interface DiscussionUpdateRequest {
  message: string;
}

export interface DiscussionStats {
  total_messages: number;
  messages_racine: number;
  reponses: number;
  participants: number;
  dernier_message?: ProjectDiscussion | TaskDiscussion;
} 