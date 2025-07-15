import React from 'react';
import { PermissionGuard } from '../PermissionGuard';
import { usePermissions } from '../../hooks/use-permissions';

export const ViewOrganigrammeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_organigramme" fallback={null}>{children}</PermissionGuard>
);

export const ExportOrganigrammeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="export_organigramme" fallback={null}>{children}</PermissionGuard>
);

export const PrintOrganigrammeGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="print_organigramme" fallback={null}>{children}</PermissionGuard>
);

export const ViewOrganigrammeDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <PermissionGuard permission="view_organigramme_details" fallback={null}>{children}</PermissionGuard>
);

export const useOrganigrammePermissions = () => {
  const { hasPermission } = usePermissions();
  return {
    canView: hasPermission('view_organigramme'),
    canExport: hasPermission('export_organigramme'),
    canPrint: hasPermission('print_organigramme'),
    canViewDetails: hasPermission('view_organigramme_details'),
  };
}; 