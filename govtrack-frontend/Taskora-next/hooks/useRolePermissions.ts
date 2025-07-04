import { useAuth } from '@/contexts/AuthContext';

export const useRolePermissions = () => {
  const { user } = useAuth();

  if (!user) {
    return {
      canViewList: false,
      canCreate: false,
      canEdit: false,
      canDelete: false,
      canViewDetails: false,
      canAssignPermissions: false,
      canRemovePermissions: false,
      canViewUsers: false,
      canAssignToUser: false,
      canRemoveFromUser: false,
      canViewStats: false,
    };
  }

  const permissions = user.permissions || [];

  return {
    canViewList: permissions.includes('view_roles_list'),
    canCreate: permissions.includes('create_role'),
    canEdit: permissions.includes('edit_role'),
    canDelete: permissions.includes('delete_role'),
    canViewDetails: permissions.includes('view_role_details'),
    canAssignPermissions: permissions.includes('assign_permissions_to_role'),
    canRemovePermissions: permissions.includes('remove_permissions_from_role'),
    canViewUsers: permissions.includes('view_role_users'),
    canAssignToUser: permissions.includes('assign_role_to_user'),
    canRemoveFromUser: permissions.includes('remove_role_from_user'),
    canViewStats: permissions.includes('view_role_stats'),
  };
}; 