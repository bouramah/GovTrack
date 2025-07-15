import React from 'react';
import { PermissionGuard } from '../PermissionGuard';
import { usePermissions } from '../../hooks/use-permissions';

export const ViewPostsListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_posts_list" fallback={null}>{children}</PermissionGuard>
);

export const CreatePostGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="create_post" fallback={null}>{children}</PermissionGuard>
);

export const EditPostGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="edit_post" fallback={null}>{children}</PermissionGuard>
);

export const DeletePostGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="delete_post" fallback={null}>{children}</PermissionGuard>
);

export const ViewPostDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_post_details" fallback={null}>{children}</PermissionGuard>
);

export const ViewPostsStatsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_posts_stats" fallback={null}>{children}</PermissionGuard>
);

export const usePostPermissions = () => {
  const { hasPermission } = usePermissions();
  return {
    canViewList: hasPermission('view_posts_list'),
    canCreate: hasPermission('create_post'),
    canEdit: hasPermission('edit_post'),
    canDelete: hasPermission('delete_post'),
    canViewDetails: hasPermission('view_post_details'),
    canViewStats: hasPermission('view_posts_stats'),
  };
}; 