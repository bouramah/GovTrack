"use client";

import { useState, useEffect } from "react";
import { Sidebar } from "./sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Search, Filter, RefreshCw } from "lucide-react";
import { Toaster } from "@/components/ui/toaster";
import Topbar from "./Shared/Topbar";
import MesTachesKanban from "./mes-taches-kanban";
import type { TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN } from "@/types/tache";
import { apiClient } from "@/lib/api";
import type { Entite } from "@/lib/api";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";

export default function MesTachesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<TacheStatut | "all">("all");
  const [enRetardFilter, setEnRetardFilter] = useState<boolean | null>(null);
  const [entiteFilter, setEntiteFilter] = useState<string>("all");
  const [entites, setEntites] = useState<Entite[]>([]);
  const [loadingEntites, setLoadingEntites] = useState(true);

  // Charger les entités pour le filtre
  const loadEntites = async () => {
    try {
      setLoadingEntites(true);
      const response = await apiClient.getEntitesDetailed({ per_page: 1000 });
      if (response.success && response.data) {
        setEntites(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement entités:', error);
    } finally {
      setLoadingEntites(false);
    }
  };

  useEffect(() => {
    loadEntites();
  }, []);

  // Préparer les options pour le SearchableSelect
  const entiteOptions: SearchableSelectOption[] = [
    { value: 'all', label: 'Toutes les entités' },
    ...entites.map(entite => ({
      value: entite.id.toString(),
      label: entite.nom,
      description: entite.type_entite?.nom
    }))
  ];

  const handleRefresh = () => {
    // Recharger les données (sera géré par le composant Kanban)
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
                <h1 className="text-2xl font-bold text-gray-900">Mes Tâches</h1>
                <p className="text-sm text-gray-600 mt-1">
                  Gérez vos tâches assignées avec le tableau Kanban
                </p>
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
                  placeholder="Rechercher dans mes tâches..."
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

              {/* Filtre par entité */}
              <SearchableSelect
                options={entiteOptions}
                value={entiteFilter}
                onValueChange={setEntiteFilter}
                placeholder="Toutes les entités"
                searchPlaceholder="Rechercher une entité..."
                disabled={loadingEntites}
                className="w-full sm:w-48 h-9"
              />
            </div>
          </div>
        </div>

        {/* Contenu principal */}
        <main className="flex-1 overflow-y-auto bg-gray-50">
          <MesTachesKanban 
            filters={{
              statut: statusFilter === "all" ? undefined : statusFilter,
              en_retard: enRetardFilter,
              entite_id: entiteFilter === "all" ? undefined : parseInt(entiteFilter)
            }}
          />
        </main>
      </div>

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
} 