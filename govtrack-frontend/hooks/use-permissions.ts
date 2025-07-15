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
    return roleNames.some(role => roleNames.includes(role));
  };

  // Permissions spécifiques aux utilisateurs
  const canViewUsersList = (): boolean => hasPermission('view_users_list');
  const canCreateUser = (): boolean => hasPermission('create_user');
  const canEditUser = (): boolean => hasPermission('edit_user');
  const canDeleteUser = (): boolean => hasPermission('delete_user');
  const canViewUserDetails = (): boolean => hasPermission('view_user_details');
  const canManageUserAssignments = (): boolean => hasPermission('manage_user_assignments');
  const canViewUserStats = (): boolean => hasPermission('view_user_stats');

  // Permissions générales de gestion des utilisateurs
  const canManageUsers = (): boolean => hasPermission('manage_users');

  // Permissions pour les projets
  const canViewProjectsList = (): boolean => hasPermission('view_projects_list');
  const canViewMyProjects = (): boolean => hasPermission('view_my_projects');
  const canViewAllProjects = (): boolean => hasPermission('view_all_projects');
  const canViewMyEntityProjects = (): boolean => hasPermission('view_my_entity_projects');
  const canCreateProject = (): boolean => hasPermission('create_project');
  const canEditProject = (): boolean => hasPermission('edit_project');
  const canDeleteProject = (): boolean => hasPermission('delete_project');
  const canViewProjectDetails = (): boolean => hasPermission('view_project_details');
  const canCreateInstruction = (): boolean => hasPermission('create_instruction');
  const canEditInstruction = (): boolean => hasPermission('edit_instruction');
  const canTerminateProject = (): boolean => hasPermission('terminate_project');
  const canValidateInstruction = (): boolean => hasPermission('validate_instruction');
  const canViewAllInstructions = (): boolean => hasPermission('view_all_instructions');

  // Permissions pour les tâches
  const canViewTasksList = (): boolean => hasPermission('view_tasks_list');
  const canViewMyTasks = (): boolean => hasPermission('view_tasks_list');
  const canCreateTask = (): boolean => hasPermission('create_task');
  const canEditTask = (): boolean => hasPermission('edit_task');
  const canDeleteTask = (): boolean => hasPermission('delete_task');
  const canViewTaskDetails = (): boolean => hasPermission('view_task_details');
  const canChangeTaskStatus = (): boolean => hasPermission('change_task_status');

  // Permissions pour les entités
  const canViewEntitiesList = (): boolean => hasPermission('view_entities_list');
  const canCreateEntity = (): boolean => hasPermission('create_entity');
  const canEditEntity = (): boolean => hasPermission('edit_entity');
  const canDeleteEntity = (): boolean => hasPermission('delete_entity');
  const canViewEntityDetails = (): boolean => hasPermission('view_entity_details');
  const canViewEntityHierarchy = (): boolean => hasPermission('view_entity_hierarchy');
  const canViewEntityUsers = (): boolean => hasPermission('view_entity_users');
  const canManageEntityAssignments = (): boolean => hasPermission('manage_entity_assignments');
  const canViewEntityChiefHistory = (): boolean => hasPermission('view_entity_chief_history');
  
  // Permission générale pour gérer les entités (avoir au moins une permission d'entité)
  const canManageEntities = (): boolean => hasAnyPermission([
    'view_entities_list', 'create_entity', 'edit_entity', 'delete_entity',
    'view_entity_details', 'view_entity_hierarchy', 'view_entity_users',
    'manage_entity_assignments', 'view_entity_chief_history'
  ]);

  // Permission pour les types de projets
  const canViewTypeProjetsList = (): boolean => hasPermission('view_type_projets_list');
  const canCreateTypeProjet = (): boolean => hasPermission('create_type_projet');
  const canEditTypeProjet = (): boolean => hasPermission('edit_type_projet');
  const canDeleteTypeProjet = (): boolean => hasPermission('delete_type_projet');

  // Permissions pour les types de tâches
  const canViewTypeTachesList = (): boolean => hasPermission('view_type_taches_list');
  const canCreateTypeTache = (): boolean => hasPermission('create_type_tache');
  const canEditTypeTache = (): boolean => hasPermission('edit_type_tache');
  const canDeleteTypeTache = (): boolean => hasPermission('delete_type_tache');

  // Permissions pour l'audit
  const canViewAuditLogs = (): boolean => hasPermission('view_audit_logs');
  const canExportAuditLogs = (): boolean => hasPermission('export_audit_logs');

  // Permissions pour les rôles et permissions
  const canViewRolesList = (): boolean => hasPermission('view_roles_list');
  const canCreateRole = (): boolean => hasPermission('create_role');
  const canEditRole = (): boolean => hasPermission('edit_role');
  const canDeleteRole = (): boolean => hasPermission('delete_role');
  const canViewRoleDetails = (): boolean => hasPermission('view_role_details');
  const canAssignPermissionsToRole = (): boolean => hasPermission('assign_permissions_to_role');
  const canRemovePermissionsFromRole = (): boolean => hasPermission('remove_permissions_from_role');
  const canAssignRoleToUser = (): boolean => hasPermission('assign_role_to_user');
  const canRemoveRoleFromUser = (): boolean => hasPermission('remove_role_from_user');

  // Permission générale pour gérer les rôles (avoir au moins une permission de rôle)
  const canManageUserRoles = (): boolean => hasAnyPermission([
    'view_roles_list', 'create_role', 'edit_role', 'delete_role',
    'view_role_details', 'assign_permissions_to_role', 'remove_permissions_from_role',
    'assign_role_to_user', 'remove_role_from_user'
  ]);

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
    canViewProjectsList,
    canViewMyProjects,
    canViewAllProjects,
    canViewMyEntityProjects,
    canCreateProject,
    canEditProject,
    canDeleteProject,
    canViewProjectDetails,
    canCreateInstruction,
    canEditInstruction,
    canTerminateProject,
    canValidateInstruction,
    canViewAllInstructions,
    
    // Permissions tâches
    canViewTasksList,
    canViewMyTasks,
    canCreateTask,
    canEditTask,
    canDeleteTask,
    canViewTaskDetails,
    canChangeTaskStatus,
    
    // Permissions entités
    canViewEntitiesList,
    canCreateEntity,
    canEditEntity,
    canDeleteEntity,
    canViewEntityDetails,
    canViewEntityHierarchy,
    canViewEntityUsers,
    canManageEntityAssignments,
    canViewEntityChiefHistory,
    canManageEntities,
    
    // Permissions types de projets
    canViewTypeProjetsList,
    canCreateTypeProjet,
    canEditTypeProjet,
    canDeleteTypeProjet,
    
    // Permissions types de tâches
    canViewTypeTachesList,
    canCreateTypeTache,
    canEditTypeTache,
    canDeleteTypeTache,
    
    // Permissions audit
    canViewAuditLogs,
    canExportAuditLogs,
    
    // Permissions rôles
    canViewRolesList,
    canCreateRole,
    canEditRole,
    canDeleteRole,
    canViewRoleDetails,
    canAssignPermissionsToRole,
    canRemovePermissionsFromRole,
    canAssignRoleToUser,
    canRemoveRoleFromUser,
  };
}; 