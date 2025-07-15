import React from 'react';
import { usePermissions } from '@/hooks/use-permissions';

interface PermissionGuardProps {
  children: React.ReactNode;
  permission?: string;
  permissions?: string[];
  requireAll?: boolean;
  fallback?: React.ReactNode;
}

export const PermissionGuard: React.FC<PermissionGuardProps> = ({
  children,
  permission,
  permissions,
  requireAll = false,
  fallback = null,
}) => {
  const { hasPermission, hasAnyPermission, hasAllPermissions } = usePermissions();

  let hasAccess = false;

  if (permission) {
    hasAccess = hasPermission(permission);
  } else if (permissions && permissions.length > 0) {
    if (requireAll) {
      hasAccess = hasAllPermissions(permissions);
    } else {
      hasAccess = hasAnyPermission(permissions);
    }
  } else {
    // Si aucune permission n'est spécifiée, on autorise l'accès
    hasAccess = true;
  }

  if (!hasAccess) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
};

// Composants spécialisés pour les permissions utilisateurs
export const UsersListGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="view_users_list" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const CreateUserGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="create_user" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const EditUserGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="edit_user" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const DeleteUserGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="delete_user" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const UserDetailsGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="view_user_details" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const UserAssignmentsGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="manage_user_assignments" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const UserRolesGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="manage_user_roles" fallback={fallback}>
    {children}
  </PermissionGuard>
);

export const UserStatsGuard: React.FC<{ children: React.ReactNode; fallback?: React.ReactNode }> = ({ 
  children, 
  fallback = null
}) => (
  <PermissionGuard permission="view_user_stats" fallback={fallback}>
    {children}
  </PermissionGuard>
); 