import { ReactNode } from 'react';
import { useTypeTachePermissions } from '@/hooks/useTypeTachePermissions';

interface GuardProps {
  children: ReactNode;
  fallback?: ReactNode;
}

export const TypeTacheListGuard = ({ children, fallback }: GuardProps) => {
  const { canViewList } = useTypeTachePermissions();
  return canViewList ? <>{children}</> : <>{fallback}</>;
};

export const TypeTacheCreateGuard = ({ children, fallback }: GuardProps) => {
  const { canCreate } = useTypeTachePermissions();
  return canCreate ? <>{children}</> : <>{fallback}</>;
};

export const TypeTacheEditGuard = ({ children, fallback }: GuardProps) => {
  const { canEdit } = useTypeTachePermissions();
  return canEdit ? <>{children}</> : <>{fallback}</>;
};

export const TypeTacheDeleteGuard = ({ children, fallback }: GuardProps) => {
  const { canDelete } = useTypeTachePermissions();
  return canDelete ? <>{children}</> : <>{fallback}</>;
};

export const TypeTacheDetailsGuard = ({ children, fallback }: GuardProps) => {
  const { canViewDetails } = useTypeTachePermissions();
  return canViewDetails ? <>{children}</> : <>{fallback}</>;
};

export const TypeTacheStatsGuard = ({ children, fallback }: GuardProps) => {
  const { canViewStats } = useTypeTachePermissions();
  return canViewStats ? <>{children}</> : <>{fallback}</>;
}; 