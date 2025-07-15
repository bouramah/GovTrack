import { useAuth } from '@/contexts/AuthContext';

export const useTypeTachePermissions = () => {
  const { user } = useAuth();
  const permissions = user?.permissions || [];

  return {
    canViewList: permissions.includes('view_type_taches_list'),
    canViewDetails: permissions.includes('view_type_tache_details'),
    canViewStats: permissions.includes('view_type_tache_stats'),
    canCreate: permissions.includes('create_type_tache'),
    canEdit: permissions.includes('edit_type_tache'),
    canDelete: permissions.includes('delete_type_tache'),
  };
}; 