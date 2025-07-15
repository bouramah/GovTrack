"use client";

import { useState } from "react";
import { Sidebar } from "./sidebar";
import { Button } from "@/components/ui/button";
import { LayoutGrid, ListIcon, Kanban } from "lucide-react";
import { Toaster } from "@/components/ui/toaster";
import Topbar from "./Shared/Topbar";
import MesTachesKanban from "./mes-taches-kanban";
import MesTachesGrid from "./mes-taches-grid";
import MesTachesList from "./mes-taches-list";
import TaskFilters, { TaskFilters as TaskFiltersType } from "./Shared/TaskFilters";
import { cn } from "@/lib/utils";

type ViewMode = "grid" | "list" | "kanban";

export default function MesTachesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [filters, setFilters] = useState<TaskFiltersType>({});
  const [viewMode, setViewMode] = useState<ViewMode>("grid");

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
                <h1 className="text-2xl font-bold text-gray-900">Mes Tâches</h1>
                <p className="text-sm text-gray-600 mt-1">
                  Gérez vos tâches assignées avec différents modes d'affichage
                </p>
              </div>
            </div>
          </div>

        </div>

        <main className="flex-1 overflow-y-auto bg-gray-50">
          <div className="px-4 sm:px-6 lg:px-8 py-6">
            {/* Filtres */}
            <TaskFilters
              filters={filters}
              onFiltersChange={handleFiltersChange}
              onReset={handleFiltersReset}
            />

            {/* Barre de modes d'affichage */}
            <div className="mb-6">
              <div className="flex items-center justify-between">
                <div className="bg-gray-100 rounded-lg p-1 flex items-center">
                  <Button
                    variant="ghost"
                    size="sm"
                    className={cn(
                      "h-8 px-3 text-sm font-medium",
                      viewMode === "grid"
                        ? "bg-white text-gray-900 shadow-sm"
                        : "text-gray-600 hover:text-gray-900 hover:bg-gray-200/50"
                    )}
                    onClick={() => setViewMode("grid")}
                  >
                    <LayoutGrid className="h-4 w-4 mr-2" />
                    Grille
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    className={cn(
                      "h-8 px-3 text-sm font-medium",
                      viewMode === "list"
                        ? "bg-white text-gray-900 shadow-sm"
                        : "text-gray-600 hover:text-gray-900 hover:bg-gray-200/50"
                      )}
                    onClick={() => setViewMode("list")}
                  >
                    <ListIcon className="h-4 w-4 mr-2" />
                    Liste
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    className={cn(
                      "h-8 px-3 text-sm font-medium",
                      viewMode === "kanban"
                        ? "bg-white text-gray-900 shadow-sm"
                        : "text-gray-600 hover:text-gray-900 hover:bg-gray-200/50"
                    )}
                    onClick={() => setViewMode("kanban")}
                  >
                    <Kanban className="h-4 w-4 mr-2" />
                    Kanban
                  </Button>
                </div>
              </div>
            </div>

            {/* Contenu selon le mode d'affichage */}
            {viewMode === "grid" && (
              <MesTachesGrid filters={filters} />
            )}
            {viewMode === "list" && (
              <MesTachesList filters={filters} />
            )}
            {viewMode === "kanban" && (
              <MesTachesKanban filters={filters} />
            )}
          </div>
        </main>
      </div>

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 