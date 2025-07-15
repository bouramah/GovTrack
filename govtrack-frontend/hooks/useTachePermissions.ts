import { useAuth } from '@/contexts/AuthContext';

export const useTachePermissions = () => {
  const { user } = useAuth();
  const permissions = user?.permissions || [];

  return {
    // Permissions de base pour les tâches
    canViewList: permissions.includes('view_tasks_list'),
    canCreate: permissions.includes('create_task'),
    canEdit: permissions.includes('edit_task'),
    canDelete: permissions.includes('delete_task'),
    canViewDetails: permissions.includes('view_task_details'),
    canChangeStatus: permissions.includes('change_task_status'),
    canViewHistory: permissions.includes('view_task_history'),

    // Permissions pour les pièces jointes des tâches
    canAddAttachment: permissions.includes('add_task_attachment'),
    canViewAttachments: permissions.includes('view_task_attachments'),
    canDownloadAttachment: permissions.includes('download_task_attachment'),
    canDeleteAttachment: permissions.includes('delete_task_attachment'),

    // Permissions pour les commentaires des tâches
    canAddComment: permissions.includes('add_task_comment'),
    canViewComments: permissions.includes('view_task_comments'),
    canEditComment: permissions.includes('edit_task_comment'),
    canDeleteComment: permissions.includes('delete_task_comment'),
    canViewCommentStats: permissions.includes('view_task_comment_stats'),

    // Permissions combinées pour les onglets
    canAccessAttachmentsTab: permissions.includes('view_task_attachments'),
    canAccessCommentsTab: permissions.includes('view_task_comments'),
    canAccessHistoryTab: permissions.includes('view_task_history'),

    // Permissions pour les actions principales
    canManageTask: permissions.includes('edit_task') || permissions.includes('delete_task'),
    canManageAttachments: permissions.includes('add_task_attachment') || permissions.includes('delete_task_attachment'),
    canManageComments: permissions.includes('add_task_comment') || permissions.includes('edit_task_comment') || permissions.includes('delete_task_comment'),
  };
}; 