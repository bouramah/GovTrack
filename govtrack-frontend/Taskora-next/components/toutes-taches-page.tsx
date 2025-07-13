"use client";

import { useState, useEffect } from "react";
import { Sidebar } from "./sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Search, Filter, RefreshCw, LayoutGrid, ListIcon, Kanban } from "lucide-react";
import { Toaster } from "@/components/ui/toaster";
import Topbar from "./Shared/Topbar";
import ToutesTachesKanban from "./toutes-taches-kanban";
import ToutesTachesGrid from "./toutes-taches-grid";
import ToutesTachesList from "./toutes-taches-list";
import TaskFilters, { TaskFilters as TaskFiltersType } from "./Shared/TaskFilters";
import { cn } from "@/lib/utils";

type ViewMode = "grid" | "list" | "kanban";

export default function ToutesTachesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [filters, setFilters] = useState<TaskFiltersType>({});
  const [viewMode, setViewMode] = useState<ViewMode>("grid");

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
                  Gestion complète de toutes les tâches de l'organisation avec différents modes d'affichage
                </p>
              </div>
              <div className="flex space-x-2">
              </div>
            </div>
          </div>
          
          {/* Barre de modes d'affichage */}
          <div className="px-4 sm:px-6 lg:px-8 py-3 border-t border-gray-100">
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

            {/* Contenu selon le mode d'affichage */}
            {viewMode === "grid" && (
              <ToutesTachesGrid 
                filters={filters}
                userRole="admin"
              />
            )}
            {viewMode === "list" && (
              <ToutesTachesList 
                filters={filters}
                userRole="admin"
              />
            )}
            {viewMode === "kanban" && (
              <ToutesTachesKanban 
                filters={filters}
                userRole="admin"
              />
            )}
          </div>
        </main>
      </div>

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 