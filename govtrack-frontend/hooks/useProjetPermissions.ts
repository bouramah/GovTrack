import { useAuth } from '@/contexts/AuthContext';

export const useProjetPermissions = () => {
  const { user } = useAuth();
  const permissions = user?.permissions || [];

  return {
    // Permissions de base pour les projets
    canViewList: permissions.includes('view_projects_list'),
    canCreate: permissions.includes('create_project'),
    canEdit: permissions.includes('edit_project'),
    canDelete: permissions.includes('delete_project'),
    canViewDetails: permissions.includes('view_project_details'),
    canUpdateExecutionLevel: permissions.includes('update_project_execution_level'),
    canChangeStatus: permissions.includes('change_project_status'),

    // Permissions pour les tâches
    canViewTasks: permissions.includes('view_project_tasks'),
    canCreateTask: permissions.includes('create_project_task'),
    canEditTask: permissions.includes('edit_project_task'),
    canDeleteTask: permissions.includes('delete_project_task'),
    canViewTaskDetails: permissions.includes('view_project_task_details'),

    // Permissions pour les pièces jointes
    canAddAttachment: permissions.includes('add_project_attachment'),
    canViewAttachments: permissions.includes('view_project_attachments'),
    canDownloadAttachment: permissions.includes('download_project_attachment'),
    canEditAttachment: permissions.includes('edit_project_attachment'),
    canDeleteAttachment: permissions.includes('delete_project_attachment'),

    // Permissions pour l'historique
    canViewHistory: permissions.includes('view_project_history'),

    // Permissions pour les commentaires
    canAddComment: permissions.includes('add_project_comment'),
    canViewComments: permissions.includes('view_project_comments'),
    canEditComment: permissions.includes('edit_project_comment'),
    canDeleteComment: permissions.includes('delete_project_comment'),
    canViewCommentStats: permissions.includes('view_project_comment_stats'),

    // Permissions combinées pour les onglets
    canAccessTasksTab: permissions.includes('view_project_tasks'),
    canAccessAttachmentsTab: permissions.includes('view_project_attachments'),
    canAccessHistoryTab: permissions.includes('view_project_history'),
    canAccessCommentsTab: permissions.includes('view_project_comments'),

    // Permissions pour les actions principales
    canManageProject: permissions.includes('edit_project') || permissions.includes('delete_project'),
    canManageTasks: permissions.includes('create_project_task') || permissions.includes('edit_project_task') || permissions.includes('delete_project_task'),
    canManageAttachments: permissions.includes('add_project_attachment') || permissions.includes('edit_project_attachment') || permissions.includes('delete_project_attachment'),
    canManageComments: permissions.includes('add_project_comment') || permissions.includes('edit_project_comment') || permissions.includes('delete_project_comment'),
  };
}; 