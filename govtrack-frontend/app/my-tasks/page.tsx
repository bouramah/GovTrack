"use client";
import DashboardMyTasks from "@/components/dashboard-my-tasks";
import Topbar from "@/components/Shared/Topbar";
import Sidebar from "@/components/sidebar";
import { useState } from "react";
import { useTachePermissions } from "@/hooks/useTachePermissions";
import { useAuth } from "@/contexts/AuthContext";
import { redirect } from "next/navigation";

export default function MyTasksPage() {
  const { user } = useAuth();
  const permissions = useTachePermissions();
  const [sidebarOpen, setSidebarOpen] = useState(false);

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
            Vous n'avez pas les permissions nécessaires pour consulter les tâches.
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
      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 pt-16">
        <Topbar
          name="Mes Tâches"
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
        />

        <DashboardMyTasks permissions={permissions} />
      </div>
    </div>
  );
}
