"use client";

import { useState, useEffect } from "react";
import { DndProvider } from "react-dnd";
import { HTML5Backend } from "react-dnd-html5-backend";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, AlertCircle, Plus, RefreshCw } from "lucide-react";
import { Button } from "@/components/ui/button";
import { apiClient } from "@/lib/api";
import type { Tache, TacheStatut, TacheFilters } from "@/types/tache";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";
import TacheKanbanColumn from "./tache-kanban-column";
import TacheKanbanCard from "./tache-kanban-card";
import NewTaskModal from "./Shared/NewTaskModal";

interface ToutesTachesKanbanProps {
  filters?: TacheFilters;
  userRole?: 'admin' | 'responsable' | 'user';
}

export default function ToutesTachesKanban({ filters, userRole = 'user' }: ToutesTachesKanbanProps) {
  const { toast } = useToast();
  const [taches, setTaches] = useState<Tache[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newTaskModalOpen, setNewTaskModalOpen] = useState(false);

  // Charger toutes les tâches
  const loadToutesTaches = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await apiClient.getTaches(filters);
      if (response.success && response.data) {
        setTaches(response.data);
      } else {
        setError(response.message || 'Erreur lors du chargement des tâches');
      }
    } catch (err: any) {
      console.error('Erreur chargement toutes les tâches:', err);
      setError(err.message || 'Erreur lors du chargement des tâches');
      toast({
        title: "Erreur",
        description: "Impossible de charger les tâches",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadToutesTaches();
  }, [filters]);

  // Gérer le changement de statut d'une tâche
  const handleTacheStatusChange = async (tacheId: number, nouveauStatut: TacheStatut) => {
    try {
      const response = await apiClient.changeTacheStatut(tacheId, {
        nouveau_statut: nouveauStatut,
        commentaire: `Statut changé vers ${TACHE_STATUTS_KANBAN[nouveauStatut]}`
      });

      if (response.success && response.data) {
        // Mettre à jour la tâche dans l'état local
        setTaches(prevTaches => 
          prevTaches.map(tache => 
            tache.id === tacheId ? response.data as Tache : tache
          )
        );

        toast({
          title: "Succès",
          description: `Tâche déplacée vers ${TACHE_STATUTS_KANBAN[nouveauStatut]}`,
        });
      }
    } catch (err: any) {
      console.error('Erreur changement statut tâche:', err);
      toast({
        title: "Erreur",
        description: err.response?.data?.message || "Impossible de changer le statut de la tâche",
        variant: "destructive",
      });
    }
  };

  // Gérer la mise à jour d'une tâche
  const handleTacheUpdate = (updatedTask: Tache) => {
    setTaches(prevTaches => 
      prevTaches.map(tache => 
        tache.id === updatedTask.id ? updatedTask : tache
      )
    );
  };

  // Gérer la suppression d'une tâche
  const handleTacheDelete = (deletedTaskId: number) => {
    setTaches(prevTaches => 
      prevTaches.filter(tache => tache.id !== deletedTaskId)
    );
  };

  // Grouper les tâches par statut
  const getTachesByStatut = (statut: TacheStatut) => {
    return taches.filter(tache => tache.statut === statut);
  };

  // Statuts à afficher dans le Kanban
  const statutsKanban: TacheStatut[] = ['a_faire', 'en_cours', 'bloque', 'demande_de_cloture', 'termine'];

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto text-blue-600" />
          <p className="mt-2 text-gray-600">Chargement des tâches...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <AlertCircle className="h-8 w-8 mx-auto text-red-500 mb-2" />
          <p className="text-gray-600 mb-4">{error}</p>
          <button 
            onClick={loadToutesTaches}
            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Réessayer
          </button>
        </div>
      </div>
    );
  }

  return (
    <DndProvider backend={HTML5Backend}>
      <div className="p-6">
        {/* En-tête */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-lg font-semibold text-gray-900">
              Toutes les Tâches ({taches.length})
            </h2>
            <p className="text-sm text-gray-600">
              {userRole === 'admin' ? 'Gestion complète de toutes les tâches' : 
               userRole === 'responsable' ? 'Gestion des tâches de votre équipe' : 
               'Visualisation des tâches'}
            </p>
          </div>
          
          {/* Boutons d'action */}
          <div className="flex gap-2">
            <Button 
              onClick={() => setNewTaskModalOpen(true)}
              size="sm"
            >
              <Plus className="h-4 w-4 mr-2" />
              Nouvelle Tâche
            </Button>
            <Button 
              onClick={loadToutesTaches} 
              variant="outline" 
              size="sm"
              disabled={loading}
            >
              <RefreshCw className={`h-4 w-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
              Actualiser
            </Button>
          </div>
        </div>

        {/* Tableau Kanban */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
          {statutsKanban.map((statut) => (
            <TacheKanbanColumn
              key={statut}
              title={TACHE_STATUTS_KANBAN[statut]}
              statut={statut}
              taches={getTachesByStatut(statut)}
              onTacheMove={handleTacheStatusChange}
              onTaskUpdate={handleTacheUpdate}
              onTaskDelete={handleTacheDelete}
              colorClass={TACHE_STATUT_COLORS[statut]}
            />
          ))}
        </div>
        
        {taches.length === 0 && (
          <div className="text-center py-12">
            <div className="text-gray-500">
              <p className="text-lg font-medium mb-2">Aucune tâche trouvée</p>
              <p className="text-sm">
                {filters?.statut 
                  ? `Aucune tâche avec le statut "${TACHE_STATUTS_KANBAN[filters.statut]}"`
                  : "Aucune tâche n'a été créée"
                }
              </p>
            </div>
          </div>
        )}
      </div>

      {/* Modal de nouvelle tâche */}
      <NewTaskModal
        open={newTaskModalOpen}
        onOpenChange={setNewTaskModalOpen}
        onSuccess={(newTask) => {
          setTaches(prev => [...prev, newTask]);
          toast({
            title: "Succès",
            description: "Tâche créée avec succès",
          });
        }}
        context="kanban"
      />
    </DndProvider>
  );
} 