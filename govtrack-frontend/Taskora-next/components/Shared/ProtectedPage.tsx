import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { usePermissions } from '@/hooks/use-permissions';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Loader2, Shield, AlertTriangle } from 'lucide-react';

interface ProtectedPageProps {
  requiredPermissions: string[];
  children: React.ReactNode;
  fallbackMessage?: string;
  redirectTo?: string;
}

export const ProtectedPage: React.FC<ProtectedPageProps> = ({
  requiredPermissions,
  children,
  fallbackMessage = "Vous n'avez pas les permissions nécessaires pour accéder à cette page.",
  redirectTo = "/"
}) => {
  const { user, loading: authLoading } = useAuth();
  const { hasPermission } = usePermissions();
  const router = useRouter();

  // Vérifier si l'utilisateur a au moins une des permissions requises
  const hasRequiredPermission = requiredPermissions.some(permission => hasPermission(permission));

  useEffect(() => {
    // Si l'authentification est terminée et l'utilisateur n'a pas les permissions
    if (!authLoading && (!user || !hasRequiredPermission)) {
      router.push(redirectTo);
    }
  }, [authLoading, user, hasRequiredPermission, router, redirectTo]);

  // Afficher un loader pendant le chargement de l'authentification
  if (authLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Card className="w-96">
          <CardHeader className="text-center">
            <Loader2 className="h-8 w-8 animate-spin mx-auto mb-4" />
            <CardTitle>Chargement...</CardTitle>
            <CardDescription>
              Vérification des permissions en cours
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    );
  }

  // Si pas d'utilisateur connecté
  if (!user) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Card className="w-96">
          <CardHeader className="text-center">
            <AlertTriangle className="h-8 w-8 text-destructive mx-auto mb-4" />
            <CardTitle>Accès refusé</CardTitle>
            <CardDescription>
              Vous devez être connecté pour accéder à cette page.
            </CardDescription>
          </CardHeader>
          <CardContent className="text-center">
            <Button onClick={() => router.push('/login')}>
              Se connecter
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  // Si l'utilisateur n'a pas les permissions requises
  if (!hasRequiredPermission) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Card className="w-96">
          <CardHeader className="text-center">
            <Shield className="h-8 w-8 text-destructive mx-auto mb-4" />
            <CardTitle>Permissions insuffisantes</CardTitle>
            <CardDescription>
              {fallbackMessage}
            </CardDescription>
          </CardHeader>
          <CardContent className="text-center">
            <Button onClick={() => router.push(redirectTo)}>
              Retour à l'accueil
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  // Si tout est OK, afficher le contenu
  return <>{children}</>;
}; 