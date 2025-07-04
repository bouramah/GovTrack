import React from 'react';
import { useTypeProjetPermissions } from '@/hooks/useTypeProjetPermissions';

export const TypeProjetListGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewList } = useTypeProjetPermissions();
  return canViewList ? <>{children}</> : null;
};

export const TypeProjetCreateGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canCreate } = useTypeProjetPermissions();
  return canCreate ? <>{children}</> : null;
};

export const TypeProjetEditGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canEdit } = useTypeProjetPermissions();
  return canEdit ? <>{children}</> : null;
};

export const TypeProjetDeleteGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canDelete } = useTypeProjetPermissions();
  return canDelete ? <>{children}</> : null;
};

export const TypeProjetDetailsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewDetails } = useTypeProjetPermissions();
  return canViewDetails ? <>{children}</> : null;
};

export const TypeProjetStatsGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { canViewStats } = useTypeProjetPermissions();
  return canViewStats ? <>{children}</> : null;
}; 