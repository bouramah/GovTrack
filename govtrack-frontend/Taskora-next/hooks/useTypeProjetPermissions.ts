import { useAuth } from '@/contexts/AuthContext';

export const useTypeProjetPermissions = () => {
  const { user } = useAuth();
  const permissions = user?.permissions || [];

  return {
    canViewList: permissions.includes('view_type_projets_list'),
    canViewDetails: permissions.includes('view_type_projet_details'),
    canViewStats: permissions.includes('view_type_projet_stats'),
    canCreate: permissions.includes('create_type_projet'),
    canEdit: permissions.includes('edit_type_projet'),
    canDelete: permissions.includes('delete_type_projet'),
  };
}; 