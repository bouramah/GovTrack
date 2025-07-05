"use client";

import { useState } from "react";
import { Sidebar } from "./sidebar";
import ProjectsHeader from "./projects-header";
import ProjectsList from "./projects-list";
import { Toaster } from "@/components/ui/toaster";
import { useProjetPermissions } from "@/hooks/useProjetPermissions";
import { useAuth } from "@/contexts/AuthContext";
import { redirect } from "next/navigation";

export default function ProjectsPage() {
  const { user } = useAuth();
  const permissions = useProjetPermissions();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [filterStatus, setFilterStatus] = useState<string | null>(null);

  // Vérifier les permissions d'accès
  if (!user) {
    redirect('/login');
  }

  if (!permissions.canViewList) {
    return (
      <div className="bg-gray-50 min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Accès refusé
          </h1>
          <p className="text-gray-600 mb-4">
            Vous n'avez pas les permissions nécessaires pour consulter les projets.
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
        <ProjectsHeader
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
          viewMode={viewMode}
          onViewModeChange={setViewMode}
          filterStatus={filterStatus}
          onFilterStatusChange={setFilterStatus}
          permissions={permissions}
        />

        {/* Main Content Area */}
        <main className="flex-1 overflow-y-auto min-h-0 bg-gray-50 p-3 lg:p-6">
          <ProjectsList 
            viewMode={viewMode} 
            filterStatus={filterStatus} 
            permissions={permissions}
          />
        </main>
      </div>
      {/* Toast notifications */}
      <Toaster />
    </div>
  );
}
