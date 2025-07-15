import React from 'react';
import { usePermissionPermissions } from '@/hooks/usePermissionPermissions';

// Guard pour la liste des permissions
export const PermissionListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewList } = usePermissionPermissions();
  return canViewList ? <>{children}</> : null;
};

// Guard pour créer une permission
export const PermissionCreateGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canCreate } = usePermissionPermissions();
  return canCreate ? <>{children}</> : null;
};

// Guard pour éditer une permission
export const PermissionEditGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canEdit } = usePermissionPermissions();
  return canEdit ? <>{children}</> : null;
};

// Guard pour supprimer une permission
export const PermissionDeleteGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canDelete } = usePermissionPermissions();
  return canDelete ? <>{children}</> : null;
};

// Guard pour voir les détails d'une permission
export const PermissionDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewDetails } = usePermissionPermissions();
  return canViewDetails ? <>{children}</> : null;
};

// Guard pour voir les utilisateurs d'une permission
export const PermissionUsersGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewUsers } = usePermissionPermissions();
  return canViewUsers ? <>{children}</> : null;
};

// Guard pour voir les rôles d'une permission
export const PermissionRolesGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewRoles } = usePermissionPermissions();
  return canViewRoles ? <>{children}</> : null;
};

// Guard pour voir les statistiques d'une permission
export const PermissionStatsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewStats } = usePermissionPermissions();
  return canViewStats ? <>{children}</> : null;
}; 