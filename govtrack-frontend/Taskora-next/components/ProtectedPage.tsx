import React from 'react';
import { usePermissions } from '@/hooks/use-permissions';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { Loader2, AlertTriangle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface ProtectedPageProps {
  children: React.ReactNode;
  permission?: string;
  permissions?: string[];
  requireAll?: boolean;
  fallback?: React.ReactNode;
  redirectTo?: string;
}

export const ProtectedPage: React.FC<ProtectedPageProps> = ({
  children,
  permission,
  permissions,
  requireAll = false,
  fallback,
  redirectTo = '/',
}) => {
  const { hasPermission, hasAnyPermission, hasAllPermissions } = usePermissions();
  const { user, loading } = useAuth();
  const router = useRouter();

  let hasAccess = false;

  if (permission) {
    hasAccess = hasPermission(permission);
  } else if (permissions && permissions.length > 0) {
    if (requireAll) {
      hasAccess = hasAllPermissions(permissions);
    } else {
      hasAccess = hasAnyPermission(permissions);
    }
  } else {
    // Si aucune permission n'est spécifiée, on autorise l'accès
    hasAccess = true;
  }

  // Hook useEffect doit être appelé avant tous les returns
  useEffect(() => {
    if (!hasAccess && redirectTo && !loading && user) {
      router.push(redirectTo);
    }
  }, [hasAccess, redirectTo, router, loading, user]);

  // Afficher un loader pendant le chargement de l'utilisateur
  if (loading || !user) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <div className="mx-auto w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <Loader2 className="h-6 w-6 text-blue-600 animate-spin" />
            </div>
            <CardTitle className="text-xl text-gray-900">Chargement...</CardTitle>
          </CardHeader>
          <CardContent className="text-center">
            <p className="text-gray-600 mb-4">
              Vérification des permissions en cours...
            </p>
          </CardContent>
        </Card>
      </div>
    );
  }

  if (!hasAccess) {
    if (fallback) {
      return <>{fallback}</>;
    }

    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <div className="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
              <AlertTriangle className="h-6 w-6 text-red-600" />
            </div>
            <CardTitle className="text-xl text-gray-900">Accès refusé</CardTitle>
          </CardHeader>
          <CardContent className="text-center">
            <p className="text-gray-600 mb-4">
              Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            </p>
            <div className="flex items-center justify-center">
              <Loader2 className="h-4 w-4 animate-spin mr-2" />
              <span className="text-sm text-gray-500">Redirection en cours...</span>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  return <>{children}</>;
};

// Composants spécialisés pour les pages utilisateurs
export const UsersListPage: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <ProtectedPage permission="view_users_list" redirectTo="/">
    {children}
  </ProtectedPage>
);

export const UserManagementPage: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <ProtectedPage 
    permissions={['view_users_list', 'create_user', 'edit_user', 'delete_user']} 
    redirectTo="/"
  >
    {children}
  </ProtectedPage>
); 