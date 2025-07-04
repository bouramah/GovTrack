import React from 'react';
import { usePermissions } from '../../hooks/use-permissions';
import { PermissionGuard } from '../PermissionGuard';

// Composants de protection pour les entités
export const ViewEntitiesListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entities_list" fallback={null}>
    {children}
  </PermissionGuard>
);

export const CreateEntityGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="create_entity" fallback={null}>
    {children}
  </PermissionGuard>
);

export const EditEntityGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="edit_entity" fallback={null}>
    {children}
  </PermissionGuard>
);

export const DeleteEntityGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="delete_entity" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ViewEntityDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_details" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ViewEntityHierarchyGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_hierarchy" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ViewEntityUsersGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_users" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ManageEntityAssignmentsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="manage_entity_assignments" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ViewEntityChiefHistoryGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_chief_history" fallback={null}>
    {children}
  </PermissionGuard>
);

// Guards pour les types d'entités
export const ViewEntityTypesListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_types_list" fallback={null}>
    {children}
  </PermissionGuard>
);

export const CreateEntityTypeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="create_entity_type" fallback={null}>
    {children}
  </PermissionGuard>
);

export const EditEntityTypeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="edit_entity_type" fallback={null}>
    {children}
  </PermissionGuard>
);

export const DeleteEntityTypeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="delete_entity_type" fallback={null}>
    {children}
  </PermissionGuard>
);

export const ViewEntityTypeDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_entity_type_details" fallback={null}>
    {children}
  </PermissionGuard>
);

// Hook personnalisé pour les permissions d'entités
export const useEntityPermissions = () => {
  const { hasPermission } = usePermissions();
  
  return {
    canViewList: hasPermission('view_entities_list'),
    canCreate: hasPermission('create_entity'),
    canEdit: hasPermission('edit_entity'),
    canDelete: hasPermission('delete_entity'),
    canViewDetails: hasPermission('view_entity_details'),
    canViewHierarchy: hasPermission('view_entity_hierarchy'),
    canViewUsers: hasPermission('view_entity_users'),
    canManageAssignments: hasPermission('manage_entity_assignments'),
    canViewChiefHistory: hasPermission('view_entity_chief_history'),
    // Permissions pour les types d'entités
    canViewTypesList: hasPermission('view_entity_types_list'),
    canCreateType: hasPermission('create_entity_type'),
    canEditType: hasPermission('edit_entity_type'),
    canDeleteType: hasPermission('delete_entity_type'),
    canViewTypeDetails: hasPermission('view_entity_type_details'),
  };
}; 