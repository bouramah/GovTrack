import { useAuth } from '@/contexts/AuthContext';

export const usePermissionPermissions = () => {
  const { user } = useAuth();

  if (!user) {
    return {
      canViewList: false,
      canCreate: false,
      canEdit: false,
      canDelete: false,
      canViewDetails: false,
      canViewUsers: false,
      canViewRoles: false,
      canViewStats: false,
    };
  }

  const permissions = user.permissions || [];

  return {
    canViewList: permissions.includes('view_permissions_list'),
    canCreate: permissions.includes('create_permission'),
    canEdit: permissions.includes('edit_permission'),
    canDelete: permissions.includes('delete_permission'),
    canViewDetails: permissions.includes('view_permission_details'),
    canViewUsers: permissions.includes('view_permission_users'),
    canViewRoles: permissions.includes('view_permission_roles'),
    canViewStats: permissions.includes('view_permission_stats'),
  };
}; 