"use client";

import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredPermission?: string;
}

export function ProtectedRoute({ children, requiredPermission }: ProtectedRouteProps) {
  const { isAuthenticated, loading, hasPermission } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading) {
      if (!isAuthenticated) {
        router.push('/login');
        return;
      }
      
      if (requiredPermission && !hasPermission(requiredPermission)) {
        // Rediriger vers une page d'erreur ou le dashboard
        router.push('/');
        return;
      }
    }
  }, [isAuthenticated, loading, hasPermission, requiredPermission, router]);

  // Afficher un loader pendant la vérification
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  // Afficher le contenu si authentifié et autorisé
  if (isAuthenticated && (!requiredPermission || hasPermission(requiredPermission))) {
    return <>{children}</>;
  }

  // Ne rien afficher pendant la redirection
  return null;
}
