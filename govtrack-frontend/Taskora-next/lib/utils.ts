import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Fonction pour formater les erreurs du backend
export function formatBackendErrors(error: any): string {
  // 1️⃣ Erreurs personnalisées émises par apiClient
  if (error?.name === 'ValidationError') {
    if (error.errors && typeof error.errors === 'object') {
      const messages = Object.values(error.errors).flat();
      if (messages.length) {
        return messages.join(', ');
      }
    }
    return error.message || 'Erreur de validation';
  }

  if (error?.name === 'PermissionError') {
    return error.message || 'Accès refusé';
  }

  // 2️⃣ Analyse de la réponse HTTP
  const responseData = error?.response?.data;
  if (responseData) {
    // Tableau d'erreurs Laravel
    if (responseData.errors && typeof responseData.errors === 'object') {
      const messages = Object.values(responseData.errors).flat();
      if (messages.length) {
        return messages.join(', ');
      }
    }

    if (responseData.message) {
      return responseData.message;
    }

    if (responseData.error) {
      return responseData.error;
    }
  }

  // 3️⃣ Fallbacks génériques
  if (error?.message) {
    return error.message;
  }

  if (typeof error === 'string') {
    return error;
  }

  return "Une erreur inattendue s'est produite";
}

// Fonction pour formater la taille des fichiers
export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 B';
  
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Fonction pour obtenir le nom de l'icône appropriée selon le type MIME
export function getFileIconName(mimeType: string): string {
  if (mimeType.startsWith('image/')) {
    return 'Image';
  } else if (mimeType.startsWith('video/')) {
    return 'Video';
  } else if (mimeType.startsWith('audio/')) {
    return 'Music';
  } else if (mimeType.includes('pdf') || mimeType.includes('document') || mimeType.includes('text/')) {
    return 'FileText';
  } else if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('tar') || mimeType.includes('7z')) {
    return 'Archive';
  } else {
    return 'File';
  }
}
