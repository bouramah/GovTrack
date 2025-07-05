"use client";

import { useState } from "react";
import { Sidebar } from "@/components/sidebar";
import { useAuth } from "@/contexts/AuthContext";
import { usePermissions } from "@/hooks/use-permissions";
import { redirect } from "next/navigation";
import { Toaster } from "@/components/ui/toaster";
import AuditPageContent from "@/components/audit-page-content";

export default function AuditPage() {
  const { user } = useAuth();
  const permissions = usePermissions();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  // Vérifier les permissions d'accès
  if (!user) {
    redirect('/login');
  }

  if (!permissions.canViewAuditLogs) {
    return (
      <div className="bg-gray-50 min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Accès refusé
          </h1>
          <p className="text-gray-600 mb-4">
            Vous n'avez pas les permissions nécessaires pour consulter les logs d'audit.
          </p>
          <p className="text-sm text-gray-500">
            Contactez votre administrateur pour obtenir les permissions appropriées.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-gray-50">
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

      {/* Main Content */}
      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col pt-16">
        {/* Header */}
        <header className="bg-white shadow-sm border-b border-gray-200 px-4 py-4 lg:px-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Audit et Traçabilité</h1>
              <p className="text-sm text-gray-600 mt-1">
                Consultez l'historique complet des actions de suppression dans l'application
              </p>
            </div>
          </div>
        </header>

        {/* Main Content Area */}
        <main className="flex-1 overflow-y-auto min-h-0 bg-gray-50">
          <AuditPageContent />
        </main>
      </div>
      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 