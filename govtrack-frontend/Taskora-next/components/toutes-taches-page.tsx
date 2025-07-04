"use client";

import { useState } from "react";
import { Sidebar } from "./sidebar";
import { Button } from "@/components/ui/button";
import { RefreshCw, Plus } from "lucide-react";
import { Toaster } from "@/components/ui/toaster";
import Topbar from "./Shared/Topbar";
import ToutesTachesKanban from "./toutes-taches-kanban";
import TaskFilters, { TaskFilters as TaskFiltersType } from "./Shared/TaskFilters";

export default function ToutesTachesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [filters, setFilters] = useState<TaskFiltersType>({});

  const handleRefresh = () => {
    window.location.reload();
  };

  const handleFiltersChange = (newFilters: TaskFiltersType) => {
    setFilters(newFilters);
  };

  const handleFiltersReset = () => {
    setFilters({});
  };

  return (
    <div className="bg-gray-50">
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
        {/* Topbar */}
        <Topbar
          name="Tâches"
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
        />

        {/* En-tête */}
        <div className="bg-white border-b border-gray-200 sticky top-0 z-10">
          <div className="px-4 sm:px-6 lg:px-8 py-4">
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Toutes les Tâches</h1>
                <p className="text-sm text-gray-600 mt-1">
                  Gestion complète de toutes les tâches de l'organisation
                </p>
              </div>
              <div className="flex space-x-2">
              </div>
            </div>
          </div>
        </div>

        {/* Contenu principal */}
        <main className="flex-1 overflow-y-auto bg-gray-50">
          <div className="px-4 sm:px-6 lg:px-8 py-6">
            {/* Filtres */}
            <TaskFilters
              filters={filters}
              onFiltersChange={handleFiltersChange}
              onReset={handleFiltersReset}
            />

            {/* Kanban */}
            <ToutesTachesKanban 
              filters={filters}
              userRole="admin"
            />
          </div>
        </main>
      </div>

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 