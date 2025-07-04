"use client";

import { useState } from "react";
import { Sidebar } from "./sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Search, Filter, RefreshCw, Plus } from "lucide-react";
import { Toaster } from "@/components/ui/toaster";
import Topbar from "./Shared/Topbar";
import ToutesTachesKanban from "./toutes-taches-kanban";
import type { TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN } from "@/types/tache";

export default function ToutesTachesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<TacheStatut | "all">("all");
  const [enRetardFilter, setEnRetardFilter] = useState<boolean | null>(null);
  const [projetFilter, setProjetFilter] = useState<number | null>(null);

  const handleRefresh = () => {
    window.location.reload();
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
                <Button onClick={handleRefresh} variant="outline" size="sm">
                  <RefreshCw className="h-4 w-4 mr-2" />
                  Actualiser
                </Button>
                <Button size="sm">
                  <Plus className="h-4 w-4 mr-2" />
                  Nouvelle Tâche
                </Button>
              </div>
            </div>
          </div>

          {/* Barre de filtres */}
          <div className="px-4 sm:px-6 lg:px-8 py-3 border-t border-gray-100">
            <div className="flex flex-col sm:flex-row gap-3">
              {/* Recherche */}
              <div className="relative flex-1 max-w-sm">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
                <Input
                  type="search"
                  placeholder="Rechercher dans les tâches..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-8 h-9"
                />
              </div>

              {/* Filtre par statut */}
              <Select value={statusFilter} onValueChange={(value) => setStatusFilter(value as TacheStatut | "all")}>
                <SelectTrigger className="w-full sm:w-48 h-9">
                  <SelectValue placeholder="Tous les statuts" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Tous les statuts</SelectItem>
                  {Object.entries(TACHE_STATUTS_KANBAN).map(([key, value]) => (
                    <SelectItem key={key} value={key}>
                      {value}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>

              {/* Filtre en retard */}
              <Select 
                value={enRetardFilter === null ? "all" : enRetardFilter.toString()} 
                onValueChange={(value) => setEnRetardFilter(value === "all" ? null : value === "true")}
              >
                <SelectTrigger className="w-full sm:w-48 h-9">
                  <SelectValue placeholder="Toutes les tâches" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes les tâches</SelectItem>
                  <SelectItem value="true">En retard</SelectItem>
                  <SelectItem value="false">À jour</SelectItem>
                </SelectContent>
              </Select>

              {/* Filtre par projet (à implémenter) */}
              <Select 
                value={projetFilter?.toString() || "all"} 
                onValueChange={(value) => setProjetFilter(value === "all" ? null : parseInt(value))}
              >
                <SelectTrigger className="w-full sm:w-48 h-9">
                  <SelectValue placeholder="Tous les projets" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Tous les projets</SelectItem>
                  {/* À remplir avec les projets disponibles */}
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        {/* Contenu principal */}
        <main className="flex-1 overflow-y-auto bg-gray-50">
          <ToutesTachesKanban 
            filters={{
              statut: statusFilter === "all" ? undefined : statusFilter,
              en_retard: enRetardFilter,
              projet_id: projetFilter || undefined,
              search: searchTerm || undefined
            }}
            userRole="admin"
          />
        </main>
      </div>

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 