"use client";

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { apiClient, User, LoginRequest } from '@/lib/api';
import { useRouter } from 'next/navigation';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (credentials: LoginRequest) => Promise<void>;
  logout: () => Promise<void>;
  logoutAll: () => Promise<void>;
  isAuthenticated: boolean;
  hasPermission: (permission: string) => boolean;
  getUserRole: () => string;
  getUserRoleLabel: () => string;
  refreshUser: () => Promise<void>;
  updateProfile: (profileData: {
    matricule?: string;
    nom?: string;
    prenom?: string;
    email?: string;
    telephone?: string;
    adresse?: string;
    password?: string;
  }) => Promise<void>;
  uploadProfilePhoto: (formData: FormData) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  // Vérifier l'authentification au démarrage
  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const token = localStorage.getItem('govtrack_token');
      if (token) {
        apiClient.setToken(token);
        const profile = await apiClient.getProfile();
        setUser(profile);
      }
    } catch (error) {
      console.error('Erreur de vérification auth:', error);
      // Token invalide, le supprimer et déconnecter
      apiClient.clearToken();
      setUser(null);
      
      // Afficher une notification si on est côté client
      if (typeof window !== 'undefined') {
        // Utiliser une notification simple car toast n'est pas encore disponible
        const event = new CustomEvent('showNotification', {
          detail: {
            title: 'Session expirée',
            message: 'Votre session a expiré. Veuillez vous reconnecter.',
            type: 'warning'
          }
        });
        window.dispatchEvent(event);
      }
    } finally {
      setLoading(false);
    }
  };

  const login = async (credentials: LoginRequest) => {
    try {
      setLoading(true);
      const response = await apiClient.login(credentials);
      setUser(response.user);
      router.push('/'); // Rediriger vers le dashboard
    } catch (error) {
      console.error('Erreur de connexion:', error);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await apiClient.logout();
    } catch (error) {
      console.error('Erreur de déconnexion:', error);
    } finally {
      setUser(null);
      apiClient.clearToken();
      router.push('/login');
    }
  };

  const logoutAll = async () => {
    try {
      await apiClient.logoutAll();
    } catch (error) {
      console.error('Erreur de déconnexion de tous les appareils:', error);
    } finally {
      setUser(null);
      apiClient.clearToken();
      router.push('/login');
    }
  };

  const hasPermission = (permission: string): boolean => {
    if (!user) return false;
    
    // Vérifier dans les permissions directes (array de strings)
    if (user.permissions && user.permissions.includes(permission)) {
      return true;
    }
    
    // Vérifier dans les rôles/permissions (user.roles est un array de strings)
    return user.roles.some(role => 
      typeof role === 'string' && role.includes(permission)
    );
  };

  const getUserRole = (): string => {
    if (!user || !user.roles.length) return 'employee';
    
    // Prioriser les rôles par ordre d'importance (user.roles est un array de strings)
    if (user.roles.some(role => role === 'administrateur')) return 'admin';
    if (user.roles.some(role => role === 'directeur_entite')) return 'director';
    return 'employee';
  };

  const getUserRoleLabel = (): string => {
    const role = getUserRole();
    switch (role) {
      case 'admin':
        return 'Administrateur';
      case 'director':
        return 'Directeur d\'entité';
      default:
        return 'Employé';
    }
  };

  const refreshUser = async () => {
    try {
      const token = localStorage.getItem('govtrack_token');
      if (token) {
        apiClient.setToken(token);
        const profile = await apiClient.getProfile();
        setUser(profile);
      }
    } catch (error) {
      console.error('Erreur de rafraîchissement de l\'utilisateur:', error);
      // Token invalide, le supprimer
      apiClient.clearToken();
    }
  };

  const updateProfile = async (profileData: {
    matricule?: string;
    nom?: string;
    prenom?: string;
    email?: string;
    telephone?: string;
    adresse?: string;
    password?: string;
  }) => {
    if (!user) return;
    
    try {
      const updatedUser = await apiClient.updateProfile(profileData);
      setUser(updatedUser);
    } catch (error) {
      console.error('Erreur lors de la mise à jour du profil:', error);
      throw error;
    }
  };

  const uploadProfilePhoto = async (formData: FormData) => {
    if (!user) return;
    
    try {
      await apiClient.uploadProfilePhoto(formData);
      // Rafraîchir les données utilisateur pour obtenir la nouvelle URL de photo
      await refreshUser();
    } catch (error) {
      console.error('Erreur lors du chargement de la photo:', error);
      throw error;
    }
  };

  const value: AuthContextType = {
    user,
    loading,
    login,
    logout,
    logoutAll,
    isAuthenticated: !!user,
    hasPermission,
    getUserRole,
    getUserRoleLabel,
    refreshUser,
    updateProfile,
    uploadProfilePhoto,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
