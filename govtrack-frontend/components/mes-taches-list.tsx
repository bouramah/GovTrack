"use client";

import { useState, useEffect } from "react";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, AlertCircle, RefreshCw, Plus, Calendar, Clock, User, Building, MoreHorizontal, Edit, Trash2, History, Paperclip, MessageSquare, TrendingUp } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Progress } from "@/components/ui/progress";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { apiClient } from "@/lib/api";
import type { Tache, TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";
import NewTaskModal from "./Shared/NewTaskModal";
import TacheModal from "./Shared/TacheModal";
import DeleteTaskDialog from "./Shared/DeleteTaskDialog";
import TaskHistoryModal from "./Shared/TaskHistoryModal";
import TaskExecutionLevelModal from "./Shared/TaskExecutionLevelModal";
import TaskAttachmentsModal from "./Shared/TaskAttachmentsModal";
import TaskDiscussionsModal from "./TaskDiscussionsModal";
import TaskStatusChangeModal from "./Shared/TaskStatusChangeModal";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import { cn } from "@/lib/utils";

interface MesTachesListProps {
  filters?: {
    statut?: TacheStatut;
    type_tache_id?: number;
    en_retard?: boolean;
    entite_id?: number;
    search?: string;
  };
}

export default function MesTachesList({ filters }: MesTachesListProps) {
  const { toast } = useToast();
  const [taches, setTaches] = useState<Tache[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newTaskModalOpen, setNewTaskModalOpen] = useState(false);
  const [selectedTache, setSelectedTache] = useState<Tache | null>(null);
  const [detailModalOpen, setDetailModalOpen] = useState(false);
  
  // Modals d'actions
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [historyModalOpen, setHistoryModalOpen] = useState(false);
  const [executionLevelModalOpen, setExecutionLevelModalOpen] = useState(false);
  const [attachmentsModalOpen, setAttachmentsModalOpen] = useState(false);
  const [discussionsModalOpen, setDiscussionsModalOpen] = useState(false);
  const [statusChangeModalOpen, setStatusChangeModalOpen] = useState(false);

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

  // Ouvrir le modal de détails
  const handleOpenDetailModal = (tache: Tache) => {
    setSelectedTache(tache);
    setDetailModalOpen(true);
  };

  // Vérifier si une tâche est en retard
  const isEnRetard = (tache: Tache) => {
    if (!tache.date_fin_previsionnelle) return false;
    const dateLimite = new Date(tache.date_fin_previsionnelle);
    const aujourdhui = new Date();
    return dateLimite < aujourdhui && tache.statut !== 'termine';
  };

  // Obtenir les initiales d'un nom
  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  // Formater la date
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR');
  };

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
                Affichage en liste de vos tâches assignées
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
        </div>
      </div>

      {/* Liste des tâches */}
      {taches.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-gray-400 mb-4">
            <Calendar className="h-12 w-12 mx-auto" />
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">Aucune tâche trouvée</h3>
          <p className="text-gray-600 mb-4">
            {filters && Object.keys(filters).length > 0 
              ? "Aucune tâche ne correspond aux filtres appliqués."
              : "Vous n'avez pas encore de tâches assignées."
            }
          </p>
          <Button onClick={() => setNewTaskModalOpen(true)}>
            <Plus className="h-4 w-4 mr-2" />
            Créer une tâche
          </Button>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-[300px]">Tâche</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead>Progression</TableHead>
                <TableHead>Date limite</TableHead>
                <TableHead>Assigné à</TableHead>
                <TableHead>Instruction</TableHead>
                <TableHead className="w-[50px]"></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {taches.map((tache) => (
                <TableRow 
                  key={tache.id}
                  className="hover:bg-gray-50 cursor-pointer"
                  onClick={() => handleOpenDetailModal(tache)}
                >
                  <TableCell>
                    <div className="flex items-start space-x-3">
                      <div className="flex-1 min-w-0">
                        <div className="text-sm font-medium text-gray-900 truncate">
                          {tache.titre}
                        </div>
                        {tache.description && (
                          <div className="text-xs text-gray-500 mt-1 line-clamp-2">
                            {tache.description}
                          </div>
                        )}
                      </div>
                    </div>
                  </TableCell>
                  
                  <TableCell>
                    {tache.type_tache ? (
                      <div className="flex items-center">
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <div
                                className="w-3 h-3 rounded-full mr-1 cursor-help"
                                style={{ backgroundColor: tache.type_tache.couleur }}
                              />
                            </TooltipTrigger>
                            <TooltipContent>
                              <p>Type de tâche : {tache.type_tache.nom}</p>
                            </TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <span className="text-sm text-gray-900">{tache.type_tache.nom}</span>
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </TableCell>
                  
                  <TableCell>
                    <Badge 
                      variant="secondary" 
                      className={`text-xs ${TACHE_STATUT_COLORS[tache.statut]}`}
                    >
                      {TACHE_STATUTS_KANBAN[tache.statut]}
                    </Badge>
                  </TableCell>
                  
                  <TableCell>
                    <div className="w-20">
                      <div className="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>{tache.niveau_execution}%</span>
                      </div>
                      <Progress value={tache.niveau_execution} className="h-2" />
                    </div>
                  </TableCell>
                  
                  <TableCell>
                    {tache.date_fin_previsionnelle ? (
                      <div className="flex items-center text-sm">
                        <Calendar className="h-3 w-3 mr-1 text-gray-400" />
                        <span className={isEnRetard(tache) ? "text-red-500 font-medium" : "text-gray-900"}>
                          {format(new Date(tache.date_fin_previsionnelle), "dd MMM", { locale: fr })}
                        </span>
                        {isEnRetard(tache) && (
                          <TooltipProvider>
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <AlertCircle className="h-3 w-3 ml-1 cursor-help text-red-500" />
                              </TooltipTrigger>
                              <TooltipContent>
                                <p>Tâche en retard</p>
                              </TooltipContent>
                            </Tooltip>
                          </TooltipProvider>
                        )}
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </TableCell>
                  
                  <TableCell>
                    {tache.responsables && tache.responsables.length > 0 ? (
                      <div className="flex items-center space-x-1">
                        {tache.responsables.slice(0, 2).map((responsable) => (
                          <Avatar key={responsable.id} className="h-6 w-6">
                            <AvatarFallback className="text-xs">
                              {getInitials(`${responsable.prenom} ${responsable.nom}`)}
                            </AvatarFallback>
                          </Avatar>
                        ))}
                        {tache.responsables.length > 2 && (
                          <div className="h-6 w-6 bg-gray-100 rounded-full flex items-center justify-center">
                            <span className="text-xs text-gray-600">+{tache.responsables.length - 2}</span>
                          </div>
                        )}
                      </div>
                    ) : tache.responsable ? (
                      <div className="flex items-center space-x-2">
                        <Avatar className="h-6 w-6">
                          <AvatarFallback className="text-xs">
                            {getInitials(`${tache.responsable.prenom} ${tache.responsable.nom}`)}
                          </AvatarFallback>
                        </Avatar>
                        <span className="text-sm text-gray-900">
                          {tache.responsable.prenom} {tache.responsable.nom}
                        </span>
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </TableCell>
                  
                  <TableCell>
                    {tache.projet ? (
                      <div className="flex items-center text-sm">
                        <Building className="h-3 w-3 mr-1 text-gray-400" />
                        <span className="text-gray-900">{tache.projet.titre}</span>
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </TableCell>
                  
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild onClick={(e) => e.stopPropagation()}>
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                          <MoreHorizontal className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" className="w-48">
                        <DropdownMenuItem onClick={(e) => {
                          e.stopPropagation();
                          setSelectedTache(tache);
                          setEditModalOpen(true);
                        }}>
                          <Edit className="h-4 w-4 mr-2" />
                          Modifier
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={(e) => {
                          e.stopPropagation();
                          setSelectedTache(tache);
                          setStatusChangeModalOpen(true);
                        }}>
                          <TrendingUp className="h-4 w-4 mr-2" />
                          Changer le statut
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={(e) => {
                          e.stopPropagation();
                          setSelectedTache(tache);
                          setHistoryModalOpen(true);
                        }}>
                          <History className="h-4 w-4 mr-2" />
                          Historique
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={(e) => {
                          e.stopPropagation();
                          setSelectedTache(tache);
                          setAttachmentsModalOpen(true);
                        }}>
                          <Paperclip className="h-4 w-4 mr-2" />
                          Pièces jointes
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={(e) => {
                          e.stopPropagation();
                          setSelectedTache(tache);
                          setDiscussionsModalOpen(true);
                        }}>
                          <MessageSquare className="h-4 w-4 mr-2" />
                          Discussions
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem 
                          onClick={(e) => {
                            e.stopPropagation();
                            setSelectedTache(tache);
                            setDeleteDialogOpen(true);
                          }}
                          className="text-red-600 focus:text-red-600"
                        >
                          <Trash2 className="h-4 w-4 mr-2" />
                          Supprimer
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}

      {/* Modals */}
      <NewTaskModal
        open={newTaskModalOpen}
        onOpenChange={setNewTaskModalOpen}
        onSuccess={handleTacheUpdate}
      />

      {selectedTache && (
        <>
          <TacheModal
            open={detailModalOpen}
            onOpenChange={setDetailModalOpen}
            tache={selectedTache}
            onTacheUpdate={handleTacheUpdate}
            onTacheDelete={handleTacheDelete}
          />

          <NewTaskModal
            open={editModalOpen}
            onOpenChange={setEditModalOpen}
            task={selectedTache}
            onSuccess={handleTacheUpdate}
            context="kanban"
          />

          <DeleteTaskDialog
            isOpen={deleteDialogOpen}
            onClose={() => setDeleteDialogOpen(false)}
            task={selectedTache}
            onSuccess={handleTacheDelete}
          />

          <TaskHistoryModal
            open={historyModalOpen}
            onOpenChange={setHistoryModalOpen}
            task={selectedTache}
          />

          <TaskExecutionLevelModal
            open={executionLevelModalOpen}
            onOpenChange={setExecutionLevelModalOpen}
            task={selectedTache}
            onSuccess={handleTacheUpdate}
          />

          <TaskAttachmentsModal
            open={attachmentsModalOpen}
            onOpenChange={setAttachmentsModalOpen}
            task={selectedTache}
            onSuccess={() => handleTacheUpdate(selectedTache)}
          />

          <TaskDiscussionsModal
            isOpen={discussionsModalOpen}
            onClose={() => setDiscussionsModalOpen(false)}
            tacheId={selectedTache.id}
            tacheTitre={selectedTache.titre}
          />

          <TaskStatusChangeModal
            isOpen={statusChangeModalOpen}
            onClose={() => setStatusChangeModalOpen(false)}
            task={selectedTache}
            onSuccess={(updatedTask) => {
              handleTacheUpdate(updatedTask);
              setStatusChangeModalOpen(false);
            }}
          />
        </>
      )}
    </div>
  );
} 