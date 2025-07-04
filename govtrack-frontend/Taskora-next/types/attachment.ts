export interface ProjectAttachment {
  id: number;
  projet_id: number;
  user_id: number;
  fichier_path: string;
  nom_original: string;
  mime_type: string | null;
  taille: number;
  description: string | null;
  est_justificatif: boolean;
  type_document: 'rapport' | 'justificatif' | 'piece_jointe' | 'documentation' | 'autre' | null;
  date_creation: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    name: string;
    email: string;
  };
  taille_formattee?: string;
}

export interface TaskAttachment {
  id: number;
  tache_id: number;
  user_id: number;
  fichier_path: string;
  nom_original: string;
  mime_type: string | null;
  taille: number;
  description: string | null;
  est_justificatif: boolean;
  type_document: 'rapport' | 'justificatif' | 'piece_jointe' | 'documentation' | 'autre' | null;
  date_creation: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    name: string;
    email: string;
  };
  taille_formattee?: string;
}

export interface AttachmentStats {
  total_fichiers: number;
  total_justificatifs: number;
  taille_totale: number;
  types_documents: Array<{
    type_document: string;
    count: number;
  }>;
  dernier_upload: ProjectAttachment | TaskAttachment | null;
}

export type AttachmentType = 'rapport' | 'justificatif' | 'piece_jointe' | 'documentation' | 'autre';

export const ATTACHMENT_TYPES: { value: AttachmentType; label: string }[] = [
  { value: 'piece_jointe', label: 'Pièce jointe' },
  { value: 'justificatif', label: 'Justificatif' },
  { value: 'rapport', label: 'Rapport' },
  { value: 'documentation', label: 'Documentation' },
  { value: 'autre', label: 'Autre' },
]; 