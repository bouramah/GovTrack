"use client";
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Sidebar } from "./sidebar";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useToast } from "@/components/ui/use-toast";
import { Toaster } from "@/components/ui/toaster";
import {
  Calendar,
  Clock,
  ArrowLeft,
  Star,
  FileText,
  Users,
  BarChart2,
  Settings,
  MessageSquare,
  CheckSquare,
  Menu,
  Plus,
  AlertTriangle,
  Loader2,
  Edit,
  Trash2,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { apiClient, Project } from "@/lib/api";
import ProjectModal from "@/components/Shared/ProjectModal";
import DeleteProjectDialog from "@/components/Shared/DeleteProjectDialog";
import ProjectExecutionLevelModal from "@/components/Shared/ProjectExecutionLevelModal";
import ProjectStatusChangeModal from "@/components/Shared/ProjectStatusChangeModal";
import ProjectAttachmentsModal from "@/components/Shared/ProjectAttachmentsModal";
import ProjectAttachmentsList from "@/components/Shared/ProjectAttachmentsList";
import ProjectAttachmentUploadModal from "@/components/Shared/ProjectAttachmentUploadModal";
import ProjectDiscussionsList from "@/components/Shared/ProjectDiscussionsList";
import ProjectTasksTab from "@/components/Shared/ProjectTasksTab";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

interface ProjectDetailPageProps {
  id: string;
}

export default function ProjectDetailPage({ id }: ProjectDetailPageProps) {
  const router = useRouter();
  const { toast } = useToast();
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [executionLevelModalOpen, setExecutionLevelModalOpen] = useState(false);
  const [statusChangeModalOpen, setStatusChangeModalOpen] = useState(false);
  const [attachmentsModalOpen, setAttachmentsModalOpen] = useState(false);
  const [uploadModalOpen, setUploadModalOpen] = useState(false);

  useEffect(() => {
    const fetchProject = async () => {
      try {
      setLoading(true);
        setError(null);
        
        const projectData = await apiClient.getProject(parseInt(id));
        setProject(projectData);
      } catch (err: any) {
        setError(err.message || "Erreur lors du chargement de l'instruction");
        toast({
          title: "Erreur",
          description: err.message || "Impossible de charger l'instruction",
          variant: "destructive",
        });
        // Rediriger vers la liste des instructions après un délai
        setTimeout(() => {
        router.push("/projects");
        }, 3000);
      } finally {
        setLoading(false);
      }
    };

    if (id) {
    fetchProject();
    }
  }, [id, router, toast]);

  if (loading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-12 w-12 animate-spin mx-auto text-blue-600" />
          <p className="mt-4 text-gray-600">Chargement des détails de l'instruction...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="text-center">
          <AlertTriangle className="h-12 w-12 mx-auto text-red-500 mb-4" />
          <h3 className="text-lg font-semibold mb-2">Erreur de chargement</h3>
          <p className="text-gray-600 mb-4">{error}</p>
          <Button onClick={() => router.push("/projects")}>
            Retour aux instructions
          </Button>
        </div>
      </div>
    );
  }

  if (!project) {
    return null;
  }

  // Fonction pour obtenir la couleur du statut
  const getStatusColor = (statut: string) => {
    switch (statut) {
      case "a_faire":
        return "bg-gray-100 text-gray-800";
      case "en_cours":
        return "bg-blue-100 text-blue-800";
      case "demande_de_cloture":
        return "bg-yellow-100 text-yellow-800";
      case "termine":
        return "bg-green-100 text-green-800";
      case "bloque":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  // Fonction pour obtenir le libellé du statut
  const getStatusLabel = (statut: string) => {
    const statuts: Record<string, string> = {
      a_faire: "À faire",
      en_cours: "En cours",
      demande_de_cloture: "Demande de clôture",
      termine: "Terminé",
      bloque: "Bloqué",
    };
    return statuts[statut] || statut;
  };

  // Fonction pour obtenir le libellé du statut des tâches
  const getTaskStatusLabel = (statut: string) => {
    const statuts: Record<string, string> = {
      a_faire: "À faire",
      en_cours: "En cours",
      termine: "Terminé",
      bloque: "Bloqué",
      annule: "Annulé",
      en_attente: "En attente",
      demande_de_cloture: "Demande de clôture",
    };
    return statuts[statut] || statut;
  };

  // Fonction pour obtenir les initiales
  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  // Fonction pour recharger l'instruction après modification
  const handleProjectUpdate = async () => {
    try {
      const projectData = await apiClient.getProject(parseInt(id));
      setProject(projectData);
    } catch (err: any) {
      toast({
        title: "Erreur",
        description: "Impossible de recharger l'instruction",
        variant: "destructive",
      });
    }
  };

  // Fonction pour rediriger après suppression
  const handleProjectDelete = () => {
    router.push("/projects");
  };

  return (
    <div className="bg-gray-50">
      {/* Sidebar */}
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

      {/* Main Content */}
      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col pt-16">
        {/* Top Bar */}
        <header className="bg-white border-b border-gray-200 sticky top-0 z-10">
          <div className="h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div className="flex items-center">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button
                      variant="ghost"
                      size="icon"
                      className="mr-4 lg:hidden"
                      onClick={() => setSidebarOpen(!sidebarOpen)}
                    >
                      <Menu className="h-5 w-5" />
                      <span className="sr-only">Toggle menu</span>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Ouvrir/Fermer le menu</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button
                      variant="ghost"
                      size="sm"
                      className="mr-2"
                      onClick={() => router.push("/projects")}
                    >
                      <ArrowLeft className="h-4 w-4 mr-2" />
                      Retour aux instructions
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Retourner à la liste des instructions</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>

            <div className="flex items-center space-x-2">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button 
                      className="bg-blue-600 hover:bg-blue-700"
                      onClick={() => setEditModalOpen(true)}
                    >
                      <Edit className="h-4 w-4 mr-2" />
                      Modifier l'instruction
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Modifier les informations de l'instruction</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button 
                      variant="outline"
                      onClick={() => setStatusChangeModalOpen(true)}
                    >
                      <Settings className="h-4 w-4 mr-2" />
                      Changer statut
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Modifier le statut de l'instruction</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button 
                      variant="outline"
                      onClick={() => setAttachmentsModalOpen(true)}
                    >
                      <FileText className="h-4 w-4 mr-2" />
                      Pièces jointes
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Gérer les pièces jointes de l'instruction</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button 
                      variant="destructive"
                      onClick={() => setDeleteDialogOpen(true)}
                      disabled={project?.statut === 'en_cours'}
                    >
                      <Trash2 className="h-4 w-4 mr-2" />
                      Supprimer
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Supprimer définitivement l'instruction</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
          </div>
        </header>

        {/* Project Header */}
        <div className="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between">
            <div className="flex items-start space-x-3">
              <div
                className={cn(
                  "w-2 h-16 rounded-full",
                  getStatusColor(project.statut)
                )}
              />
              <div>
                <div className="flex items-center">
                  <h1 className="text-2xl font-bold text-gray-900">
                    {project.titre}
                  </h1>
                  {/* TODO: Implémenter les favoris */}
                </div>
                <p className="text-gray-500 mt-1">{project.description}</p>
                <div className="flex items-center mt-2 space-x-4">
                  <Badge
                    className={cn(
                      "font-medium",
                      getStatusColor(project.statut)
                    )}
                  >
                    {getStatusLabel(project.statut)}
                  </Badge>
                  {project.est_en_retard && (
                    <Badge className="bg-red-100 text-red-800 hover:bg-red-100">
                      En retard
                    </Badge>
                  )}
                </div>
              </div>
            </div>

            <div className="mt-4 md:mt-0 flex flex-col items-start md:items-end">
              <div className="flex items-center text-gray-500 mb-2">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Calendar className="h-4 w-4 mr-1 cursor-help" />
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>Date d'échéance prévisionnelle</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <span>
                  Échéance: {project.date_fin_previsionnelle 
                    ? new Date(project.date_fin_previsionnelle).toLocaleDateString('fr-FR')
                    : 'Non définie'
                  }
                </span>
              </div>
              <div className="flex items-center text-gray-500">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Clock className="h-4 w-4 mr-1 cursor-help" />
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>Date de début prévisionnelle</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <span>
                  Début: {new Date(project.date_debut_previsionnelle).toLocaleDateString('fr-FR')}
                </span>
              </div>
            </div>
          </div>

          <div className="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="bg-gray-50 rounded-lg p-4">
              <div className="text-sm text-gray-500 mb-1">Niveau d'exécution</div>
              <div className="flex items-center justify-between mb-2">
                <div className="text-xl font-bold">{project.niveau_execution}%</div>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => setExecutionLevelModalOpen(true)}
                        className="ml-2"
                        disabled={project.statut !== 'en_cours'}
                      >
                        Mettre à jour
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>Modifier le niveau d'exécution de l'instruction</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <div className="text-xs text-gray-500 ml-2">
                  {project.taches_count || 0} tâches
                </div>
              </div>
              <Progress value={project.niveau_execution} className="h-2" />
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="text-sm text-gray-500 mb-1">Porteur</div>
              <div className="flex items-center">
                <Avatar className="h-8 w-8 mr-2">
                  <AvatarFallback className="bg-blue-100 text-blue-700 text-xs">
                    {getInitials(`${project.porteur.prenom} ${project.porteur.nom}`)}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <div className="font-medium text-sm">
                    {project.porteur.prenom} {project.porteur.nom}
                  </div>
                  <div className="text-xs text-gray-500">{project.porteur.email}</div>
                </div>
              </div>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="text-sm text-gray-500 mb-1">Donneur d'ordre</div>
              <div className="flex items-center">
                <Avatar className="h-8 w-8 mr-2">
                  <AvatarFallback className="bg-green-100 text-green-700 text-xs">
                    {getInitials(`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`)}
                      </AvatarFallback>
                    </Avatar>
                <div>
                  <div className="font-medium text-sm">
                    {project.donneur_ordre.prenom} {project.donneur_ordre.nom}
                  </div>
                  <div className="text-xs text-gray-500">{project.donneur_ordre.email}</div>
              </div>
              </div>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="text-sm text-gray-500 mb-1"> Type d'instruction</div>
              <div className="font-medium">{project.type_projet.nom}</div>
              {project.type_projet.description && (
              <div className="text-xs text-gray-500 mt-1">
                  {project.type_projet.description}
              </div>
              )}
            </div>
          </div>
        </div>

        {/* Main Content */}
        <main className="flex-1 overflow-y-auto">
          <div className="px-4 sm:px-6 lg:px-8 py-6">
            <Tabs defaultValue="overview" className="space-y-6">
              <TabsList>
                <TabsTrigger value="overview">Vue d'ensemble</TabsTrigger>
                <TabsTrigger value="tasks">Tâches</TabsTrigger>
                <TabsTrigger value="attachments">Pièces jointes</TabsTrigger>
                <TabsTrigger value="timeline">Historique</TabsTrigger>
                <TabsTrigger value="discussions">Discussions</TabsTrigger>
                <TabsTrigger value="analytics">Analyses</TabsTrigger>
            </TabsList>

              <TabsContent value="overview">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Project Details */}
              <Card>
                <CardHeader>
                      <CardTitle>Détails de l'instruction</CardTitle>
                </CardHeader>
                    <CardContent className="space-y-4">
                    <div>
                        <label className="text-sm font-medium text-gray-500">Description</label>
                        <p className="mt-1 text-sm">{project.description}</p>
                  </div>
                      
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <label className="text-sm font-medium text-gray-500">Date de création</label>
                          <p className="mt-1 text-sm">
                            {new Date(project.date_creation).toLocaleDateString('fr-FR')}
                          </p>
                        </div>
                        <div>
                          <label className="text-sm font-medium text-gray-500">Dernière modification</label>
                          <p className="mt-1 text-sm">
                            {project.date_modification 
                              ? new Date(project.date_modification).toLocaleDateString('fr-FR')
                              : 'Non modifié'
                            }
                          </p>
                        </div>
                      </div>

                      {project.justification_modification_dates && (
                        <div>
                          <label className="text-sm font-medium text-gray-500">Justification des dates</label>
                          <p className="mt-1 text-sm">{project.justification_modification_dates}</p>
                        </div>
                      )}
                  </CardContent>
                </Card>

                  {/* Project Timeline */}
                <Card>
                  <CardHeader>
                      <CardTitle>Timeline de l'instruction</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-4">
                        <div className="flex items-center">
                          <div className="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                          <div>
                            <div className="font-medium text-sm">Début prévisionnel</div>
                            <div className="text-sm text-gray-500">
                              {new Date(project.date_debut_previsionnelle).toLocaleDateString('fr-FR')}
                            </div>
                          </div>
                        </div>
                        
                        {project.date_debut_reelle && (
                          <div className="flex items-center">
                            <div className="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                              <div className="font-medium text-sm">Début réel</div>
                              <div className="text-sm text-gray-500">
                                {new Date(project.date_debut_reelle).toLocaleDateString('fr-FR')}
                        </div>
                      </div>
                        </div>
                        )}

                        {project.date_fin_previsionnelle && (
                          <div className="flex items-center">
                            <div className="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                        <div>
                              <div className="font-medium text-sm">Fin prévisionnelle</div>
                              <div className="text-sm text-gray-500">
                                {new Date(project.date_fin_previsionnelle).toLocaleDateString('fr-FR')}
                        </div>
                      </div>
                        </div>
                        )}

                        {project.date_fin_reelle && (
                          <div className="flex items-center">
                            <div className="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                        <div>
                              <div className="font-medium text-sm">Fin réelle</div>
                              <div className="text-sm text-gray-500">
                                {new Date(project.date_fin_reelle).toLocaleDateString('fr-FR')}
                        </div>
                      </div>
                          </div>
                        )}
                    </div>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="tasks">
                <ProjectTasksTab 
                  project={project} 
                  onProjectUpdate={handleProjectUpdate}
                />
              </TabsContent>

              <TabsContent value="attachments">
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <div>
                        <CardTitle>Pièces jointes de l'instruction</CardTitle>
                      <CardDescription>
                          Fichiers et documents attachés à l'instruction
                      </CardDescription>
                    </div>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <Button onClick={() => setUploadModalOpen(true)}>
                              <Plus className="h-4 w-4 mr-2" />
                              Ajouter un fichier
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>Ajouter une nouvelle pièce jointe</p>
                          </TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                  </div>
                </CardHeader>
                <CardContent>
                    <ProjectAttachmentsList 
                      projectId={parseInt(id)} 
                      onRefresh={handleProjectUpdate}
                    />
                </CardContent>
              </Card>
            </TabsContent>

              <TabsContent value="timeline">
              <Card>
                <CardHeader>
                    <CardTitle>Historique des statuts</CardTitle>
                      <CardDescription>
                      Évolution du statut de l'instruction
                      </CardDescription>
                </CardHeader>
                <CardContent>
                    {project.historique_statuts && project.historique_statuts.length > 0 ? (
                      <div className="space-y-4">
                        {project.historique_statuts.map((historique, index) => (
                          <div key={index} className="flex items-start space-x-3">
                            <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div className="flex-1">
                              <div className="flex items-center justify-between">
                                <div className="font-medium">
                                  {historique.ancien_statut ? (
                                    <span>
                                      <span className="text-gray-500">{getStatusLabel(historique.ancien_statut)}</span>
                                      <span className="mx-2">→</span>
                                      <span className="text-blue-600">{getStatusLabel(historique.nouveau_statut)}</span>
                                    </span>
                                  ) : (
                                    <span className="text-green-600">
                                      Création : {getStatusLabel(historique.nouveau_statut)}
                                    </span>
                                  )}
                                </div>
                                <div className="text-sm text-gray-500">
                                  {new Date(historique.date_changement).toLocaleDateString('fr-FR')}
                                </div>
                              </div>
                              {historique.commentaire && (
                                <div className="text-sm text-gray-600 mt-1">
                                  {historique.commentaire}
                                </div>
                              )}
                              {historique.user && (
                                <div className="text-xs text-gray-500 mt-1">
                                  Par {historique.user.prenom} {historique.user.nom}
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                  <p className="text-gray-500 text-center py-8">
                        Aucun historique disponible
                  </p>
                    )}
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="discussions">
                <ProjectDiscussionsList 
                  projectId={project.id} 
                  onRefresh={handleProjectUpdate}
                />
            </TabsContent>

            <TabsContent value="analytics">
              <Card>
                <CardHeader>
                    <CardTitle>Analyses de l'instruction</CardTitle>
                  <CardDescription>
                      Métriques de performance et insights
                  </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div className="bg-blue-50 rounded-lg p-4">
                        <div className="text-sm text-blue-600 mb-1">Niveau d'exécution</div>
                        <div className="text-2xl font-bold text-blue-900">{project.niveau_execution}%</div>
                      </div>
                      
                      <div className="bg-green-50 rounded-lg p-4">
                        <div className="text-sm text-green-600 mb-1">Tâches</div>
                        <div className="text-2xl font-bold text-green-900">{project.taches_count || 0}</div>
                      </div>
                      
                      <div className="bg-purple-50 rounded-lg p-4">
                        <div className="text-sm text-purple-600 mb-1">Statut</div>
                        <div className="text-lg font-bold text-purple-900">{getStatusLabel(project.statut)}</div>
                      </div>
                    </div>
                    
                    {project.est_en_retard && (
                      <div className="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div className="flex items-center">
                          <TooltipProvider>
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <AlertTriangle className="h-5 w-5 text-red-500 mr-2 cursor-help" />
                              </TooltipTrigger>
                              <TooltipContent>
                                <p>L'instruction a dépassé sa date d'échéance</p>
                              </TooltipContent>
                            </Tooltip>
                          </TooltipProvider>
                          <div>
                            <div className="font-medium text-red-900">Instruction en retard</div>
                            <div className="text-sm text-red-700">
                              L'échéance prévisionnelle a été dépassée
                            </div>
                          </div>
                        </div>
                      </div>
                    )}
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
          </div>
        </main>
      </div>

      {/* Modals */}
      <ProjectModal
        isOpen={editModalOpen}
        onClose={() => setEditModalOpen(false)}
        project={project}
        onSuccess={handleProjectUpdate}
      />

      <DeleteProjectDialog
        isOpen={deleteDialogOpen}
        onClose={() => setDeleteDialogOpen(false)}
        project={project}
        onSuccess={handleProjectDelete}
      />

      <ProjectExecutionLevelModal
        isOpen={executionLevelModalOpen}
        onClose={() => setExecutionLevelModalOpen(false)}
        project={project}
        onSuccess={handleProjectUpdate}
      />

      <ProjectStatusChangeModal
        isOpen={statusChangeModalOpen}
        onClose={() => setStatusChangeModalOpen(false)}
        project={project}
        onSuccess={handleProjectUpdate}
      />

      <ProjectAttachmentsModal
        projectId={project.id}
        projectTitle={project.titre}
        open={attachmentsModalOpen}
        onOpenChange={setAttachmentsModalOpen}
        onRefresh={handleProjectUpdate}
      />

      <ProjectAttachmentUploadModal
        projectId={project.id}
        projectTitle={project.titre}
        open={uploadModalOpen}
        onOpenChange={setUploadModalOpen}
        onRefresh={handleProjectUpdate}
      />

      {/* Toast notifications */}
      <Toaster />
    </div>
  );
}
