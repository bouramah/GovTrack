import { useContext } from 'react';
import { useAuth } from '@/contexts/AuthContext';

export interface Permission {
  id: number;
  nom: string;
  description?: string;
}

export const usePermissions = () => {
  const { user } = useAuth();

  const hasPermission = (permissionName: string): boolean => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permissionName);
  };

  const hasAnyPermission = (permissionNames: string[]): boolean => {
    if (!user || !user.permissions) return false;
    return permissionNames.some(permission => user.permissions.includes(permission));
  };

  const hasAllPermissions = (permissionNames: string[]): boolean => {
    if (!user || !user.permissions) return false;
    return permissionNames.every(permission => user.permissions.includes(permission));
  };

  const hasRole = (roleName: string): boolean => {
    if (!user || !user.roles) return false;
    return user.roles.includes(roleName);
  };

  const hasAnyRole = (roleNames: string[]): boolean => {
    if (!user || !user.roles) return false;
    return user.roles.some(role => roleNames.includes(role));
  };

  // Permissions spécifiques aux utilisateurs
  const canViewUsersList = (): boolean => hasPermission('view_users_list');
  const canCreateUser = (): boolean => hasPermission('create_user');
  const canEditUser = (): boolean => hasPermission('edit_user');
  const canDeleteUser = (): boolean => hasPermission('delete_user');
  const canViewUserDetails = (): boolean => hasPermission('view_user_details');
  const canManageUserAssignments = (): boolean => hasPermission('manage_user_assignments');
  const canManageUserRoles = (): boolean => hasPermission('manage_user_roles');
  const canViewUserStats = (): boolean => hasPermission('view_user_stats');

  // Permissions générales de gestion des utilisateurs
  const canManageUsers = (): boolean => hasPermission('manage_users');

  // Permissions pour les projets
  const canViewMyProjects = (): boolean => hasPermission('view_my_projects');
  const canViewAllProjects = (): boolean => hasPermission('view_all_projects');
  const canViewMyEntityProjects = (): boolean => hasPermission('view_my_entity_projects');
  const canCreateInstruction = (): boolean => hasPermission('create_instruction');
  const canEditInstruction = (): boolean => hasPermission('edit_instruction');
  const canTerminateProject = (): boolean => hasPermission('terminate_project');
  const canValidateInstruction = (): boolean => hasPermission('validate_instruction');
  const canViewAllInstructions = (): boolean => hasPermission('view_all_instructions');

  // Permissions pour les entités
  const canManageEntities = (): boolean => hasPermission('manage_entities');

  // Permission pour les types de projets
  const canViewTypeProjetsList = (): boolean => hasPermission('view_type_projets_list');

  return {
    // Méthodes générales
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
    
    // Permissions utilisateurs
    canViewUsersList,
    canCreateUser,
    canEditUser,
    canDeleteUser,
    canViewUserDetails,
    canManageUserAssignments,
    canManageUserRoles,
    canViewUserStats,
    
    // Permissions générales
    canManageUsers,
    
    // Permissions projets
    canViewMyProjects,
    canViewAllProjects,
    canViewMyEntityProjects,
    canCreateInstruction,
    canEditInstruction,
    canTerminateProject,
    canValidateInstruction,
    canViewAllInstructions,
    
    // Permissions entités
    canManageEntities,
    canViewTypeProjetsList,
  };
}; 