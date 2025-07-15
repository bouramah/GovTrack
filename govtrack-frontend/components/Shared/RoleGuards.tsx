import React from 'react';
import { useRolePermissions } from '@/hooks/useRolePermissions';

// Guard pour la liste des rôles
export const RoleListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewList } = useRolePermissions();
  return canViewList ? <>{children}</> : null;
};

// Guard pour créer un rôle
export const RoleCreateGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canCreate } = useRolePermissions();
  return canCreate ? <>{children}</> : null;
};

// Guard pour éditer un rôle
export const RoleEditGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canEdit } = useRolePermissions();
  return canEdit ? <>{children}</> : null;
};

// Guard pour supprimer un rôle
export const RoleDeleteGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canDelete } = useRolePermissions();
  return canDelete ? <>{children}</> : null;
};

// Guard pour voir les détails d'un rôle
export const RoleDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewDetails } = useRolePermissions();
  return canViewDetails ? <>{children}</> : null;
};

// Guard pour assigner des permissions à un rôle
export const RoleAssignPermissionsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canAssignPermissions } = useRolePermissions();
  return canAssignPermissions ? <>{children}</> : null;
};

// Guard pour retirer des permissions d'un rôle
export const RoleRemovePermissionsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canRemovePermissions } = useRolePermissions();
  return canRemovePermissions ? <>{children}</> : null;
};

// Guard pour voir les utilisateurs d'un rôle
export const RoleUsersGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewUsers } = useRolePermissions();
  return canViewUsers ? <>{children}</> : null;
};

// Guard pour assigner un rôle à un utilisateur
export const RoleAssignToUserGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canAssignToUser } = useRolePermissions();
  return canAssignToUser ? <>{children}</> : null;
};

// Guard pour retirer un rôle d'un utilisateur
export const RoleRemoveFromUserGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canRemoveFromUser } = useRolePermissions();
  return canRemoveFromUser ? <>{children}</> : null;
};

// Guard pour voir les statistiques d'un rôle
export const RoleStatsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewStats } = useRolePermissions();
  return canViewStats ? <>{children}</> : null;
}; 