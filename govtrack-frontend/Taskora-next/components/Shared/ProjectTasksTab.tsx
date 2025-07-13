"use client";

import { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useToast } from "@/components/ui/use-toast";
import { 
  Plus, 
  CheckSquare, 
  Calendar, 
  User, 
  AlertTriangle, 
  Eye,
  Edit,
  Trash2,
  Loader2,
  MoreHorizontal,
  History,
  Paperclip,
  MessageSquare,
  TrendingUp
} from "lucide-react";
import { cn } from "@/lib/utils";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";
import type { Project } from "@/lib/api";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";
import NewTaskModal from "./NewTaskModal";
import TacheDetailModal from "../tache-detail-modal";
import DeleteTaskDialog from "./DeleteTaskDialog";
import TaskHistoryModal from "./TaskHistoryModal";
import TaskAttachmentsModal from "./TaskAttachmentsModal";
import TaskDiscussionsModal from "../TaskDiscussionsModal";
import TaskStatusChangeModal from "./TaskStatusChangeModal";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

interface ProjectTasksTabProps {
  project: Project;
  onProjectUpdate: () => void;
}

export default function ProjectTasksTab({ project, onProjectUpdate }: ProjectTasksTabProps) {
  const { toast } = useToast();
  const [tasks, setTasks] = useState<Tache[]>([]);
  const [loading, setLoading] = useState(true);
  const [newTaskModalOpen, setNewTaskModalOpen] = useState(false);
  const [taskDetailModalOpen, setTaskDetailModalOpen] = useState(false);
  const [deleteTaskDialogOpen, setDeleteTaskDialogOpen] = useState(false);
  const [selectedTask, setSelectedTask] = useState<Tache | null>(null);
  const [historyModalOpen, setHistoryModalOpen] = useState(false);
  const [attachmentsModalOpen, setAttachmentsModalOpen] = useState(false);
  const [discussionsModalOpen, setDiscussionsModalOpen] = useState(false);
  const [statusChangeModalOpen, setStatusChangeModalOpen] = useState(false);

  // Charger les tâches du projet
  useEffect(() => {
    const loadTasks = async () => {
      try {
        setLoading(true);
        const response = await apiClient.getTaches({ projet_id: project.id });
        if (response.success && response.data) {
          setTasks(response.data);
        }
      } catch (error: any) {
        console.error('Erreur chargement tâches:', error);
        toast({
          title: "Erreur",
          description: "Impossible de charger les tâches du projet",
          variant: "destructive",
        });
      } finally {
        setLoading(false);
      }
    };

    loadTasks();
  }, [project.id, toast]);

  // Fonction pour obtenir les initiales
  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  // Vérifier si une tâche est en retard
  const isTaskLate = (task: Tache) => {
    return task.date_fin_previsionnelle && 
           new Date(task.date_fin_previsionnelle) < new Date() && 
           task.statut !== 'termine';
  };

  // Gérer la création d'une nouvelle tâche
  const handleTaskCreate = (newTask: Tache) => {
    setTasks(prev => [...prev, newTask]);
    setNewTaskModalOpen(false);
    onProjectUpdate(); // Recharger le projet pour mettre à jour les statistiques
    toast({
      title: "Succès",
      description: "Tâche créée avec succès",
    });
  };

  // Gérer la mise à jour d'une tâche
  const handleTaskUpdate = (updatedTask: Tache) => {
    setTasks(prev => prev.map(task => 
      task.id === updatedTask.id ? updatedTask : task
    ));
    onProjectUpdate(); // Recharger le projet pour mettre à jour les statistiques
    toast({
      title: "Succès",
      description: "Tâche mise à jour avec succès",
    });
  };

  // Gérer la suppression d'une tâche
  const handleTaskDelete = (deletedTaskId: number) => {
    setTasks(prev => prev.filter(task => task.id !== deletedTaskId));
    setDeleteTaskDialogOpen(false);
    setSelectedTask(null);
    onProjectUpdate(); // Recharger le projet pour mettre à jour les statistiques
    toast({
      title: "Succès",
      description: "Tâche supprimée avec succès",
    });
  };

  // Ouvrir les détails d'une tâche
  const openTaskDetails = (task: Tache) => {
    setSelectedTask(task);
    setTaskDetailModalOpen(true);
  };

  // Ouvrir la suppression d'une tâche
  const openDeleteTask = (task: Tache) => {
    setSelectedTask(task);
    setDeleteTaskDialogOpen(true);
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Tâches du projet</CardTitle>
          <CardDescription>Gestion des tâches et sous-tâches</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center py-8">
            <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
            <span className="ml-2 text-gray-600">Chargement des tâches...</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <>
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>Tâches du projet</CardTitle>
              <CardDescription>
                Gestion des tâches et sous-tâches
              </CardDescription>
            </div>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button onClick={() => setNewTaskModalOpen(true)}>
                    <Plus className="h-4 w-4 mr-2" />
                    Nouvelle tâche
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Créer une nouvelle tâche pour ce projet</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </CardHeader>
        <CardContent>
          <div className="mb-4 text-sm text-gray-600">
            Nombre total de tâches : {tasks.length}
          </div>
          
          {tasks.length > 0 ? (
            <div className="space-y-4">
              {tasks.map((task) => (
                <div key={task.id} className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                  <div className="flex items-center space-x-3 flex-1">
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <CheckSquare className="h-5 w-5 text-gray-400 cursor-help" />
                        </TooltipTrigger>
                        <TooltipContent>
                          <p>Icône de tâche</p>
                        </TooltipContent>
                      </Tooltip>
                    </TooltipProvider>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 mb-1">
                        <h4 className="font-medium text-gray-900 truncate">{task.titre}</h4>
                        {isTaskLate(task) && (
                          <TooltipProvider>
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <AlertTriangle className="h-4 w-4 text-red-500 cursor-help" />
                              </TooltipTrigger>
                              <TooltipContent>
                                <p>Tâche en retard</p>
                              </TooltipContent>
                            </Tooltip>
                          </TooltipProvider>
                        )}
                      </div>
                      
                      {task.description && (
                        <p className="text-sm text-gray-600 mb-2 line-clamp-2">{task.description}</p>
                      )}
                      
                      <div className="flex items-center gap-4 text-xs text-gray-500">
                        {task.date_fin_previsionnelle && (
                          <div className={cn(
                            "flex items-center",
                            isTaskLate(task) ? "text-red-600" : ""
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
                            <span>Échéance : {new Date(task.date_fin_previsionnelle).toLocaleDateString('fr-FR')}</span>
                          </div>
                        )}
                        
                        {task.responsable && (
                          <div className="flex items-center">
                            <TooltipProvider>
                              <Tooltip>
                                <TooltipTrigger asChild>
                                  <User className="h-3.5 w-3.5 mr-1 cursor-help" />
                                </TooltipTrigger>
                                <TooltipContent>
                                  <p>Responsable de la tâche</p>
                                </TooltipContent>
                              </Tooltip>
                            </TooltipProvider>
                            <span>{task.responsable.prenom} {task.responsable.nom}</span>
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                  
                  <div className="flex items-center space-x-2 ml-4">
                    <Badge className={cn("font-medium", TACHE_STATUT_COLORS[task.statut])}>
                      {TACHE_STATUTS_KANBAN[task.statut]}
                    </Badge>
                    <Badge variant="outline" className="text-xs">
                      {task.niveau_execution}%
                    </Badge>
                    
                    {/* Menu d'actions */}
                                        <DropdownMenu>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <DropdownMenuTrigger asChild>
                              <Button
                                variant="ghost"
                                size="sm"
                                className="h-8 w-8 p-0 hover:bg-gray-100"
                              >
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>Menu d'actions pour cette tâche</p>
                          </TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                      <DropdownMenuContent align="end" className="w-48">
                        <DropdownMenuItem onClick={() => openTaskDetails(task)}>
                          <Eye className="h-4 w-4 mr-2" />
                          Voir les détails
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => {
                          setSelectedTask(task);
                          setNewTaskModalOpen(true);
                        }}>
                          <Edit className="h-4 w-4 mr-2" />
                          Modifier
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => {
                          setSelectedTask(task);
                          setStatusChangeModalOpen(true);
                        }}>
                          <TrendingUp className="h-4 w-4 mr-2" />
                          Changer le statut
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => {
                          setSelectedTask(task);
                          setHistoryModalOpen(true);
                        }}>
                          <History className="h-4 w-4 mr-2" />
                          Historique
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => {
                          setSelectedTask(task);
                          setAttachmentsModalOpen(true);
                        }}>
                          <Paperclip className="h-4 w-4 mr-2" />
                          Pièces jointes
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => {
                          setSelectedTask(task);
                          setDiscussionsModalOpen(true);
                        }}>
                          <MessageSquare className="h-4 w-4 mr-2" />
                          Discussions
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem 
                          onClick={() => openDeleteTask(task)}
                          className="text-red-600 focus:text-red-600"
                        >
                          <Trash2 className="h-4 w-4 mr-2" />
                          Supprimer
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <CheckSquare className="h-12 w-12 text-gray-300 mx-auto mb-4 cursor-help" />
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Aucune tâche disponible</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <p className="text-gray-500 mb-2">Aucune tâche pour ce projet</p>
              <p className="text-sm text-gray-400">
                Les tâches apparaîtront ici une fois qu'elles seront créées
              </p>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button 
                      onClick={() => setNewTaskModalOpen(true)}
                      className="mt-4"
                      variant="outline"
                    >
                      <Plus className="h-4 w-4 mr-2" />
                      Créer la première tâche
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Commencer par créer la première tâche</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Modal de création/modification de tâche */}
      <NewTaskModal
        open={newTaskModalOpen}
        onOpenChange={setNewTaskModalOpen}
        task={selectedTask}
        projet_id={project.id}
        onSuccess={selectedTask ? handleTaskUpdate : handleTaskCreate}
        context="project-detail"
      />

      {/* Modal de détails de tâche */}
      {selectedTask && (
        <Dialog open={taskDetailModalOpen} onOpenChange={setTaskDetailModalOpen}>
          <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Détails de la tâche</DialogTitle>
            </DialogHeader>
            <TacheDetailModal tache={selectedTask} />
          </DialogContent>
        </Dialog>
      )}

      {/* Dialog de suppression de tâche */}
      <DeleteTaskDialog
        isOpen={deleteTaskDialogOpen}
        onClose={() => setDeleteTaskDialogOpen(false)}
        task={selectedTask}
        onSuccess={handleTaskDelete}
      />

      {/* Modal d'historique de tâche */}
      {selectedTask && (
        <TaskHistoryModal
          open={historyModalOpen}
          onOpenChange={setHistoryModalOpen}
          task={selectedTask}
        />
      )}

      {/* Modal de pièces jointes de tâche */}
      {selectedTask && (
        <TaskAttachmentsModal
          open={attachmentsModalOpen}
          onOpenChange={setAttachmentsModalOpen}
          task={selectedTask}
          onSuccess={() => handleTaskUpdate(selectedTask)}
        />
      )}

      {/* Modal de discussions de tâche */}
      {selectedTask && (
        <TaskDiscussionsModal
          isOpen={discussionsModalOpen}
          onClose={() => setDiscussionsModalOpen(false)}
          tacheId={selectedTask.id}
          tacheTitre={selectedTask.titre}
        />
      )}

      {/* Modal de changement de statut de tâche */}
      {selectedTask && (
        <TaskStatusChangeModal
          isOpen={statusChangeModalOpen}
          onClose={() => setStatusChangeModalOpen(false)}
          task={selectedTask}
          onSuccess={(updatedTask) => {
            handleTaskUpdate(updatedTask);
            setStatusChangeModalOpen(false);
          }}
        />
      )}
    </>
  );
} 