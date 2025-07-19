"use client";

import { useState, Fragment, useEffect } from "react";
import { useDrag } from "react-dnd";
import { Calendar, Paperclip, MessageSquare, User, AlertTriangle, MoreHorizontal, Edit, Trash2, History, Plus, TrendingUp } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import { cn } from "@/lib/utils";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";
import { TACHE_STATUT_COLORS } from "@/types/tache";
import { useToast } from "@/components/ui/use-toast";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import TacheDetailModal from "./tache-detail-modal";
import NewTaskModal from "./Shared/NewTaskModal";
import DeleteTaskDialog from "./Shared/DeleteTaskDialog";
import TaskHistoryModal from "./Shared/TaskHistoryModal";
import TaskExecutionLevelModal from "./Shared/TaskExecutionLevelModal";
import TaskAttachmentsModal from "./Shared/TaskAttachmentsModal";
import TaskDiscussionsModal from "./TaskDiscussionsModal";
import TaskStatusChangeModal from "./Shared/TaskStatusChangeModal";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

interface TacheKanbanCardProps {
  tache: Tache;
  onTaskUpdate?: (task: Tache) => void;
  onTaskDelete?: (taskId: number) => void;
}

export default function TacheKanbanCard({ tache, onTaskUpdate, onTaskDelete }: TacheKanbanCardProps) {
  const { toast } = useToast();
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [historyModalOpen, setHistoryModalOpen] = useState(false);
  const [executionLevelModalOpen, setExecutionLevelModalOpen] = useState(false);
  const [attachmentsModalOpen, setAttachmentsModalOpen] = useState(false);
  const [discussionsModalOpen, setDiscussionsModalOpen] = useState(false);
  const [detailModalOpen, setDetailModalOpen] = useState(false);
  const [statusChangeModalOpen, setStatusChangeModalOpen] = useState(false);
  const [tacheDetail, setTacheDetail] = useState<Tache | null>(null);
  const [loadingDetail, setLoadingDetail] = useState(false);
  // Configuration du drag source
  const [{ isDragging }, drag] = useDrag({
    type: "tache-card",
    item: { id: tache.id },
    collect: (monitor) => ({
      isDragging: !!monitor.isDragging(),
    }),
  });

  // Fonction pour obtenir les initiales
  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  // Vérifier si la tâche est en retard
  const isEnRetard = tache.est_en_retard || 
    (tache.date_fin_previsionnelle && 
     new Date(tache.date_fin_previsionnelle) < new Date() && 
     tache.statut !== 'termine');

  // Charger les détails complets de la tâche
  const loadTacheDetail = async () => {
    try {
      setLoadingDetail(true);
      const response = await apiClient.getTache(tache.id);
      if (response.success && response.data) {
        setTacheDetail(response.data);
      }
    } catch (error: any) {
      console.error('Erreur chargement détails tâche:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les détails de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoadingDetail(false);
    }
  };

  // Charger les détails quand le modal s'ouvre
  useEffect(() => {
    if (detailModalOpen && !tacheDetail) {
      loadTacheDetail();
    }
  }, [detailModalOpen]);

  return (
    <div>
      <Dialog open={detailModalOpen} onOpenChange={setDetailModalOpen}>
        <DialogTrigger asChild>
        <div
          ref={drag as any}
          className={cn(
            "bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow cursor-grab",
            isDragging ? "opacity-50" : "opacity-100"
          )}
          style={{ opacity: isDragging ? 0.5 : 1 }}
        >
          <div className="p-3">
            {/* En-tête avec projet, statut et menu d'actions */}
            <div className="flex flex-wrap gap-2 items-center justify-between mb-2">
              <div className="flex flex-wrap gap-2 items-center">
                <Badge
                  variant="outline"
                  className="text-xs font-medium text-gray-700 bg-gray-50"
                >
                  {tache.projet?.titre || 'Instruction inconnue'}
                </Badge>
                <Badge className={cn("text-xs font-medium border", TACHE_STATUT_COLORS[tache.statut])}>
                  {tache.niveau_execution}%
                </Badge>
              </div>
              
              {/* Menu d'actions */}
              <DropdownMenu>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <DropdownMenuTrigger asChild>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-6 w-6 p-0 hover:bg-gray-100"
                          onClick={(e: React.MouseEvent) => e.stopPropagation()}
                        >
                          <MoreHorizontal className="h-3 w-3" />
                        </Button>
                      </DropdownMenuTrigger>
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>Menu d'actions pour cette tâche</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <DropdownMenuContent align="end" className="w-48">
                  <DropdownMenuItem onClick={(e: React.MouseEvent) => {
                    e.stopPropagation();
                    setEditModalOpen(true);
                  }}>
                    <Edit className="h-4 w-4 mr-2" />
                    Modifier
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={(e: React.MouseEvent) => {
                    e.stopPropagation();
                    setStatusChangeModalOpen(true);
                  }}>
                    <TrendingUp className="h-4 w-4 mr-2" />
                    Changer le statut
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={(e: React.MouseEvent) => {
                    e.stopPropagation();
                    setHistoryModalOpen(true);
                  }}>
                    <History className="h-4 w-4 mr-2" />
                    Historique
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={(e: React.MouseEvent) => {
                    e.stopPropagation();
                    setAttachmentsModalOpen(true);
                  }}>
                    <Paperclip className="h-4 w-4 mr-2" />
                    Pièces jointes
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={(e: React.MouseEvent) => {
                    e.stopPropagation();
                    setDiscussionsModalOpen(true);
                  }}>
                    <MessageSquare className="h-4 w-4 mr-2" />
                    Discussions
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem 
                    onClick={(e: React.MouseEvent) => {
                      e.stopPropagation();
                      setDeleteDialogOpen(true);
                    }}
                    className="text-red-600 focus:text-red-600"
                  >
                    <Trash2 className="h-4 w-4 mr-2" />
                    Supprimer
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>

            {/* Titre de la tâche */}
            <h4 className="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
              {tache.titre}
            </h4>

            {/* Description (optionnelle) */}
            {tache.description && (
              <p className="text-xs text-gray-600 mb-2 line-clamp-2">
                {tache.description}
              </p>
            )}

            {/* Métadonnées */}
            <div className="flex flex-wrap gap-3 items-center text-xs text-gray-500 mb-3">
              {/* Type de tâche */}
              {tache.type_tache && (
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
                  <span className="text-gray-600">{tache.type_tache.nom}</span>
                </div>
              )}

              {/* Date d'échéance */}
              {tache.date_fin_previsionnelle && (
                <div className={cn(
                  "flex items-center",
                  isEnRetard ? "text-red-600" : ""
                )}>
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <Calendar className="h-3.5 w-3.5 mr-1 cursor-help" />
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>Date d'échéance de la tâche</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                  <span>
                    {format(new Date(tache.date_fin_previsionnelle), "dd MMM", { locale: fr })}
                  </span>
                  {isEnRetard && (
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <AlertTriangle className="h-3.5 w-3.5 ml-1 cursor-help" />
                        </TooltipTrigger>
                        <TooltipContent>
                          <p>Tâche en retard</p>
                        </TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                  )}
                </div>
              )}

              {/* Pièces jointes */}
              {tache.pieces_jointes && tache.pieces_jointes.length > 0 && (
                <div className="flex items-center">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <Paperclip className="h-3.5 w-3.5 cursor-help" />
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>Nombre de pièces jointes</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                  <span>{tache.pieces_jointes.length}</span>
                </div>
              )}

              {/* Discussions */}
              {tache.discussions && tache.discussions.length > 0 && (
                <div className="flex items-center">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <MessageSquare className="h-3.5 w-3.5 cursor-help" />
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>Nombre de discussions</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                  <span>{tache.discussions.length}</span>
                </div>
              )}
            </div>

            {/* Responsables */}
            <div className="flex items-center justify-between">
              {tache.responsables && tache.responsables.length > 0 ? (
                <div className="flex items-center space-x-1">
                  {tache.responsables.slice(0, 2).map((responsable) => (
                    <Avatar key={responsable.id} className="h-6 w-6">
                      <AvatarFallback className="bg-blue-100 text-blue-700 text-xs">
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
                <div className="flex items-center">
                  <Avatar className="h-6 w-6 mr-2">
                    <AvatarFallback className="bg-blue-100 text-blue-700 text-xs">
                      {getInitials(`${tache.responsable.prenom} ${tache.responsable.nom}`)}
                    </AvatarFallback>
                  </Avatar>
                  <div className="text-xs text-gray-600">
                    {tache.responsable.prenom} {tache.responsable.nom}
                  </div>
                </div>
              ) : (
                <div className="flex items-center text-xs text-gray-500">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <User className="h-3.5 w-3.5 mr-1 cursor-help" />
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>Aucun responsable assigné</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                  <span>Non assignée</span>
                </div>
              )}
            </div>

            {/* Indicateur de retard */}
            {isEnRetard && (
              <div className="mt-2 p-1 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                ⚠️ En retard
              </div>
            )}
          </div>
        </div>
      </DialogTrigger>
      
      <DialogContent className="sm:max-w-2xl max-h-[90vh] overflow-hidden">
        <DialogHeader>
          <DialogTitle>{tache.titre}</DialogTitle>
          <DialogDescription>
            {tache.projet?.titre} • {tache.responsables && tache.responsables.length > 0 
              ? `${tache.responsables.length} responsable${tache.responsables.length > 1 ? 's' : ''}` 
              : tache.responsable 
                ? `${tache.responsable.prenom} ${tache.responsable.nom}` 
                : 'Non assignée'}
          </DialogDescription>
        </DialogHeader>
        {loadingDetail ? (
          <div className="flex items-center justify-center py-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <TacheDetailModal tache={tacheDetail || tache} />
        )}
      </DialogContent>
    </Dialog>

    {/* Modals d'actions */}
    <NewTaskModal
      open={editModalOpen}
      onOpenChange={setEditModalOpen}
      task={tache}
      onSuccess={(task) => onTaskUpdate?.(task)}
      context="kanban"
    />

    <DeleteTaskDialog
      isOpen={deleteDialogOpen}
      onClose={() => setDeleteDialogOpen(false)}
      task={tache}
      onSuccess={(taskId) => onTaskDelete?.(taskId)}
    />

    <TaskHistoryModal
      open={historyModalOpen}
      onOpenChange={setHistoryModalOpen}
      task={tache}
    />

    <TaskExecutionLevelModal
      open={executionLevelModalOpen}
      onOpenChange={setExecutionLevelModalOpen}
      task={tache}
      onSuccess={onTaskUpdate}
    />

    <TaskAttachmentsModal
      open={attachmentsModalOpen}
      onOpenChange={setAttachmentsModalOpen}
      task={tache}
      onSuccess={() => onTaskUpdate?.(tache)}
    />

    <TaskDiscussionsModal
      isOpen={discussionsModalOpen}
      onClose={() => setDiscussionsModalOpen(false)}
      tacheId={tache.id}
      tacheTitre={tache.titre}
    />

    <TaskStatusChangeModal
      isOpen={statusChangeModalOpen}
      onClose={() => setStatusChangeModalOpen(false)}
      task={tache}
      onSuccess={(updatedTask) => {
        onTaskUpdate?.(updatedTask);
        setStatusChangeModalOpen(false);
      }}
    />
    </div>
  );
}

 