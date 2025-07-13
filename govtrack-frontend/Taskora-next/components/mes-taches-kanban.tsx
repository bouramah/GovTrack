"use client";

import { useState, useEffect } from "react";
import { DndProvider } from "react-dnd";
import { HTML5Backend } from "react-dnd-html5-backend";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, AlertCircle, RefreshCw, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { apiClient } from "@/lib/api";
import type { Tache, TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";
import TacheKanbanColumn from "./tache-kanban-column";
import TacheKanbanCard from "./tache-kanban-card";
import NewTaskModal from "./Shared/NewTaskModal";

interface MesTachesKanbanProps {
  filters?: {
    statut?: TacheStatut;
    en_retard?: boolean;
  };
}

export default function MesTachesKanban({ filters }: MesTachesKanbanProps) {
  const { toast } = useToast();
  const [taches, setTaches] = useState<Tache[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newTaskModalOpen, setNewTaskModalOpen] = useState(false);

  // Charger les tâches de l'utilisateur connecté
  const loadMesTaches = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await apiClient.getMesTaches({
        ...filters,
        sort_by: 'date_creation',
        sort_order: 'desc'
      });
      if (response.success && response.data) {
        setTaches(response.data);
      } else {
        setError(response.message || 'Erreur lors du chargement des tâches');
      }
    } catch (err: any) {
      console.error('Erreur chargement mes tâches:', err);
      setError(err.message || 'Erreur lors du chargement des tâches');
      toast({
        title: "Erreur",
        description: "Impossible de charger vos tâches",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadMesTaches();
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
          <p className="mt-2 text-gray-600">Chargement de vos tâches...</p>
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
            onClick={loadMesTaches}
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
        {/* Header avec statistiques */}
        <div className="mb-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="flex items-center gap-3">
              <div>
                <h2 className="text-lg font-semibold text-gray-900 flex items-center gap-2">
                  Mes Tâches ({taches.length})
                  {loading && <Loader2 className="h-4 w-4 animate-spin text-blue-600" />}
                </h2>
                <p className="text-sm text-gray-600 mt-1">
                  Gérez vos tâches assignées avec le tableau Kanban
                </p>
              </div>
              <div className="flex gap-2">
                <Button 
                  onClick={() => setNewTaskModalOpen(true)}
                  size="sm"
                >
                  <Plus className="h-4 w-4 mr-2" />
                  Nouvelle Tâche
                </Button>
                <Button 
                  onClick={loadMesTaches} 
                  variant="outline" 
                  size="sm"
                  disabled={loading}
                >
                  <RefreshCw className={`h-4 w-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
                  Actualiser
                </Button>
              </div>
            </div>
            
            {/* Statistiques rapides */}
            <div className="flex flex-wrap gap-3 text-sm">
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-blue-600 text-lg">
                  {getTachesByStatut('a_faire').length}
                </div>
                <div className="text-gray-500 text-xs">À faire</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((getTachesByStatut('a_faire').length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-blue-600 text-lg">
                  {getTachesByStatut('en_cours').length}
                </div>
                <div className="text-gray-500 text-xs">En cours</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((getTachesByStatut('en_cours').length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-red-600 text-lg">
                  {getTachesByStatut('bloque').length}
                </div>
                <div className="text-gray-500 text-xs">Bloquées</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((getTachesByStatut('bloque').length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-yellow-600 text-lg">
                  {getTachesByStatut('demande_de_cloture').length}
                </div>
                <div className="text-gray-500 text-xs">En validation</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((getTachesByStatut('demande_de_cloture').length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-green-600 text-lg">
                  {getTachesByStatut('termine').length}
                </div>
                <div className="text-gray-500 text-xs">Terminées</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((getTachesByStatut('termine').length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-orange-600 text-lg">
                  {taches.filter(tache => 
                    tache.est_en_retard || 
                    (tache.date_fin_previsionnelle && 
                     new Date(tache.date_fin_previsionnelle) < new Date() && 
                     tache.statut !== 'termine')
                  ).length}
                </div>
                <div className="text-gray-500 text-xs">En retard</div>
                <div className="text-gray-400 text-xs">
                  {taches.length > 0 
                    ? `${Math.round((taches.filter(tache => 
                        tache.est_en_retard || 
                        (tache.date_fin_previsionnelle && 
                         new Date(tache.date_fin_previsionnelle) < new Date() && 
                         tache.statut !== 'termine')
                      ).length / taches.length) * 100)}%`
                    : '0%'
                  }
                </div>
              </div>
              <div className="text-center bg-gray-50 px-3 py-2 rounded-lg">
                <div className="font-semibold text-purple-600 text-lg">
                  {taches.length > 0 
                    ? Math.round(taches.reduce((total, tache) => total + tache.niveau_execution, 0) / taches.length)
                    : 0
                  }%
                </div>
                <div className="text-gray-500 text-xs">Progression</div>
                <div className="text-gray-400 text-xs">Moyenne</div>
              </div>
            </div>
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
                  ? `Vous n'avez pas de tâches avec le statut "${TACHE_STATUTS_KANBAN[filters.statut]}"`
                  : "Vous n'avez pas encore de tâches assignées"
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