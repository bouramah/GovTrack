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
} 