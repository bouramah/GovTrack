"use client";
import { useState, useEffect } from "react";
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
import {
  Calendar,
  MoreHorizontal,
  Star,
  FileText,
  BarChart2,
  Clock,
  AlertTriangle,
  CheckCircle,
  PauseCircle,
  Loader2,
  Search,
  Filter,
  Settings,
  Trash2,
  Plus,
  TrendingUp,
} from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";
import { apiClient, Project as ApiProject, PaginatedResponse, ProjectFilters, ProjectPermissions } from "@/lib/api";
import { ProjectViewDetailsModal } from "./project-view-details-modal";
import { ProjectTimelineModal } from "./project-timeline-modal";
import ProjectStatusChangeModal from "@/components/Shared/ProjectStatusChangeModal";
import { ProjectArchiveModal } from "./project-archive-modal";
import ProjectModal from "@/components/Shared/ProjectModal";
import DeleteProjectDialog from "@/components/Shared/DeleteProjectDialog";
import ProjectExecutionLevelModal from "@/components/Shared/ProjectExecutionLevelModal";
import ProjectsAdvancedFilters from "./projects-advanced-filters";

interface ProjectsListProps {
  viewMode: "grid" | "list";
  filterStatus: string | null;
}

export default function ProjectsList({
  viewMode,
  filterStatus,
}: ProjectsListProps) {
  const [projects, setProjects] = useState<ApiProject[]>([]);
  const [filteredProjects, setFilteredProjects] = useState<ApiProject[]>([]);
  const [selectedProject, setSelectedProject] = useState<ApiProject | null>(null);
  const [viewDetailsOpen, setViewDetailsOpen] = useState(false);
  const [timelineOpen, setTimelineOpen] = useState(false);
  const [statusChangeModalOpen, setStatusChangeModalOpen] = useState(false);
  const [archiveModalOpen, setArchiveModalOpen] = useState(false);
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [executionLevelModalOpen, setExecutionLevelModalOpen] = useState(false);
  const [selectedProjectForAction, setSelectedProjectForAction] = useState<ApiProject | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [permissions, setPermissions] = useState<ProjectPermissions | null>(null);
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const [filters, setFilters] = useState<ProjectFilters>({
    search: "",
    statut: "",
    type_projet_id: undefined,
    en_retard: false,
    sort_by: "date_creation",
    sort_order: "desc",
  });
  const { toast } = useToast();

  // Fonction pour charger les projets
  const loadProjects = async (page: number = 1) => {
    try {
      setLoading(true);
      setError(null);
      
      const params: ProjectFilters = {
        ...filters,
        page,
        per_page: pagination.per_page,
      };

      // Exclure les valeurs vides des paramètres
      Object.keys(params).forEach(key => {
        if (params[key as keyof ProjectFilters] === "" || params[key as keyof ProjectFilters] === undefined || params[key as keyof ProjectFilters] === null) {
          delete params[key as keyof ProjectFilters];
        }
      });

      // Appliquer le filtre de statut du parent si présent
      if (filterStatus) {
        params.statut = filterStatus;
      }

      const response: PaginatedResponse<ApiProject> = await apiClient.getProjects(params);
      
      console.log('API Response:', response);
      console.log('Pagination:', response.pagination);
      console.log('Permissions:', response.permissions);
      
      setProjects(response.data || []);
      setFilteredProjects(response.data || []);
      setPagination(response.pagination);
      setPermissions(response.permissions || null);
      
    } catch (err: any) {
      setError(err.message || "Erreur lors du chargement des projets");
      toast({
        title: "Erreur",
        description: err.message || "Impossible de charger les projets",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  // Charger les projets au montage et quand les filtres changent
  useEffect(() => {
    loadProjects();
  }, [filters, filterStatus]);

  // Appliquer les filtres locaux
  useEffect(() => {
    if (filterStatus) {
      setFilteredProjects(
        projects.filter((project) => project.statut === filterStatus)
      );
    } else {
      setFilteredProjects(projects);
    }
  }, [filterStatus, projects]);

  const handleViewDetails = (project: ApiProject) => {
    // Naviguer vers la page de détails du projet
    window.location.href = `/projects/${project.id}`;
  };

  const handleViewTimeline = (project: ApiProject) => {
    // Naviguer vers la page de détails du projet avec l'onglet timeline
    window.location.href = `/projects/${project.id}?tab=timeline`;
  };

  const handleToggleStar = (project: ApiProject) => {
    // TODO: Implémenter la fonctionnalité de favoris
    toast({
      title: "Fonctionnalité à venir",
      description: "La gestion des favoris sera bientôt disponible",
    });
  };

  const handleStatusChange = (project: ApiProject) => {
    setSelectedProject(project);
    setStatusChangeModalOpen(true);
  };

  const handleStatusChanged = () => {
    loadProjects(pagination.current_page);
    setStatusChangeModalOpen(false);
    setSelectedProject(null);
  };

  const handleArchiveProject = (project: ApiProject) => {
    setSelectedProject(project);
    setArchiveModalOpen(true);
  };

  const handleConfirmArchive = async (notifyTeam: boolean) => {
    if (!selectedProject) return;

    try {
      // TODO: Implémenter l'archivage
      toast({
        title: "Projet archivé",
        description: "Le projet a été archivé avec succès",
      });

      loadProjects(pagination.current_page);
      setArchiveModalOpen(false);
    } catch (err: any) {
      toast({
        title: "Erreur",
        description: err.message || "Impossible d'archiver le projet",
        variant: "destructive",
      });
    }
  };

  const handlePageChange = (page: number) => {
    loadProjects(page);
  };

  const handleProjectCreated = () => {
    loadProjects(1); // Recharger depuis la première page
    toast({
      title: 'Projet créé',
      description: 'Le projet a été créé avec succès.',
      variant: 'success',
    });
  };

  const handleEditProject = (project: ApiProject) => {
    setSelectedProjectForAction(project);
    setEditModalOpen(true);
  };

  const handleDeleteProject = (project: ApiProject) => {
    setSelectedProjectForAction(project);
    setDeleteDialogOpen(true);
  };

  const handleUpdateExecutionLevel = (project: ApiProject) => {
    setSelectedProjectForAction(project);
    setExecutionLevelModalOpen(true);
  };

  const handleProjectEdited = () => {
    loadProjects(pagination.current_page);
    setEditModalOpen(false);
    setSelectedProjectForAction(null);
    toast({
      title: 'Projet modifié',
      description: 'Le projet a été modifié avec succès.',
      variant: 'success',
    });
  };

  const handleProjectDeleted = () => {
    loadProjects(pagination.current_page);
    setDeleteDialogOpen(false);
    setSelectedProjectForAction(null);
    toast({
      title: 'Projet supprimé',
      description: 'Le projet a été supprimé avec succès.',
      variant: 'success',
    });
  };

  const handleExecutionLevelUpdated = () => {
    loadProjects(pagination.current_page);
    setExecutionLevelModalOpen(false);
    setSelectedProjectForAction(null);
    toast({
      title: 'Niveau d\'exécution mis à jour',
      description: 'Le niveau d\'exécution a été mis à jour avec succès.',
      variant: 'success',
    });
  };

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

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  return (
    <>
      {/* Informations de permissions */}
      {permissions && (
        <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-blue-800 font-medium">{permissions.description}</p>
              <p className="text-xs text-blue-600 mt-1">
                Filtres disponibles: {permissions.available_filters.basic.length + permissions.available_filters.date.length + permissions.available_filters.user.length + permissions.available_filters.entity.length}
              </p>
            </div>
            <Badge variant="outline" className="text-blue-700 border-blue-300">
              {permissions.level === 'all_projects' ? 'Administrateur' : 
               permissions.level === 'entity_projects' ? 'Chef d\'entité' : 'Utilisateur'}
            </Badge>
          </div>
        </div>
      )}

      {/* Filtres avancés */}
      <ProjectsAdvancedFilters
        filters={filters}
        onFiltersChange={setFilters}
        permissions={permissions || undefined}
        className="mb-6"
      />

      {/* Bouton nouveau projet */}
      <div className="flex justify-end mb-6">
        <Button
          onClick={() => setCreateModalOpen(true)}
          className="bg-blue-600 hover:bg-blue-700"
        >
          <Plus className="h-4 w-4 mr-2" />
          Nouveau projet
        </Button>
      </div>

      {/* Liste des projets */}
      {loading ? (
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
          <span className="ml-2 text-gray-600">Chargement des projets...</span>
        </div>
      ) : error ? (
        <div className="text-center py-12">
          <AlertTriangle className="h-12 w-12 mx-auto text-red-500 mb-4" />
          <h3 className="text-lg font-semibold mb-2">Erreur de chargement</h3>
          <p className="text-gray-600">{error}</p>
        </div>
      ) : filteredProjects.length === 0 ? (
        <div className="text-center py-12">
          <FileText className="h-12 w-12 mx-auto text-gray-400 mb-4" />
          <h3 className="text-lg font-semibold mb-2">Aucun projet trouvé</h3>
          <p className="text-gray-600 mb-4">
            {filters.search || filters.statut
              ? "Aucun projet ne correspond à vos critères de recherche."
              : "Aucun projet n'a encore été créé."}
          </p>
          <Button onClick={() => setCreateModalOpen(true)}>
            <Plus className="h-4 w-4 mr-2" />
            Créer le premier projet
          </Button>
        </div>
      ) : (
        <>
          {viewMode === "grid" ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredProjects.map((project) => (
                <ProjectCard
                  key={project.id}
                  project={project}
                  onViewDetails={handleViewDetails}
                  onViewTimeline={handleViewTimeline}
                  onToggleStar={handleToggleStar}
                  onStatusChange={handleStatusChange}
                  onArchive={handleArchiveProject}
                  onEdit={handleEditProject}
                  onDelete={handleDeleteProject}
                  onUpdateExecutionLevel={handleUpdateExecutionLevel}
                  getStatusColor={getStatusColor}
                  getStatusLabel={getStatusLabel}
                  getInitials={getInitials}
                />
              ))}
            </div>
          ) : (
            <div className="space-y-4">
              {filteredProjects.map((project) => (
                <ProjectRow
                  key={project.id}
                  project={project}
                  onViewDetails={handleViewDetails}
                  onViewTimeline={handleViewTimeline}
                  onToggleStar={handleToggleStar}
                  onStatusChange={handleStatusChange}
                  onArchive={handleArchiveProject}
                  onEdit={handleEditProject}
                  onDelete={handleDeleteProject}
                  onUpdateExecutionLevel={handleUpdateExecutionLevel}
                  getStatusColor={getStatusColor}
                  getStatusLabel={getStatusLabel}
                  getInitials={getInitials}
                />
              ))}
            </div>
          )}

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <div className="flex items-center justify-center space-x-2 pt-6">
              <Button
                variant="outline"
                size="sm"
                onClick={() => handlePageChange(pagination.current_page - 1)}
                disabled={pagination.current_page === 1}
              >
                Précédent
              </Button>
              <span className="text-sm text-gray-600">
                Page {pagination.current_page} sur {pagination.last_page}
              </span>
              <Button
                variant="outline"
                size="sm"
                onClick={() => handlePageChange(pagination.current_page + 1)}
                disabled={pagination.current_page === pagination.last_page}
              >
                Suivant
              </Button>
            </div>
          )}
        </>
      )}

      {/* Modals */}
      <ProjectModal
        isOpen={createModalOpen}
        onClose={() => setCreateModalOpen(false)}
        onSuccess={handleProjectCreated}
      />

      <ProjectModal
        isOpen={editModalOpen}
        onClose={() => {
          setEditModalOpen(false);
          setSelectedProjectForAction(null);
        }}
        project={selectedProjectForAction}
        onSuccess={handleProjectEdited}
      />

      <DeleteProjectDialog
        isOpen={deleteDialogOpen}
        onClose={() => {
          setDeleteDialogOpen(false);
          setSelectedProjectForAction(null);
        }}
        project={selectedProjectForAction}
        onSuccess={handleProjectDeleted}
      />

      <ProjectExecutionLevelModal
        isOpen={executionLevelModalOpen}
        onClose={() => {
          setExecutionLevelModalOpen(false);
          setSelectedProjectForAction(null);
        }}
        project={selectedProjectForAction}
        onSuccess={handleExecutionLevelUpdated}
      />

      <ProjectStatusChangeModal
        isOpen={statusChangeModalOpen}
        onClose={() => {
          setStatusChangeModalOpen(false);
          setSelectedProject(null);
        }}
        project={selectedProject}
        onSuccess={handleStatusChanged}
      />

      {/* Modals existants temporairement désactivés pour éviter les conflits de types */}
      {/* TODO: Corriger les interfaces des modals existants */}
    </>
  );
}

// Composant ProjectCard (vue grille)
interface ProjectCardProps {
  project: ApiProject;
  onViewDetails: (project: ApiProject) => void;
  onViewTimeline: (project: ApiProject) => void;
  onToggleStar: (project: ApiProject) => void;
  onStatusChange: (project: ApiProject) => void;
  onArchive: (project: ApiProject) => void;
  onEdit: (project: ApiProject) => void;
  onDelete: (project: ApiProject) => void;
  onUpdateExecutionLevel: (project: ApiProject) => void;
  getStatusColor: (statut: string) => string;
  getStatusLabel: (statut: string) => string;
  getInitials: (name: string) => string;
}

function ProjectCard({
  project,
  onViewDetails,
  onViewTimeline,
  onToggleStar,
  onStatusChange,
  onArchive,
  onEdit,
  onDelete,
  onUpdateExecutionLevel,
  getStatusColor,
  getStatusLabel,
  getInitials,
}: ProjectCardProps) {
  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardHeader className="pb-3">
        <div className="flex items-start justify-between">
          <div className="flex-1 min-w-0">
            <CardTitle 
              className="text-lg cursor-pointer hover:text-blue-600 transition-colors truncate"
              onClick={() => onViewDetails(project)}
            >
              {project.titre}
            </CardTitle>
            <CardDescription className="text-sm text-gray-600 line-clamp-2 mt-1">
              {project.description || "Aucune description disponible"}
            </CardDescription>
          </div>
          <div className="flex items-center space-x-1 ml-2">
            <Button
              variant="ghost"
              size="sm"
              className="h-6 w-6 p-0"
              onClick={() => onToggleStar(project)}
            >
              <Star className="h-4 w-4 text-gray-400 hover:text-yellow-500" />
            </Button>
            <ProjectActions
              project={project}
              onViewDetails={onViewDetails}
              onViewTimeline={onViewTimeline}
              onToggleStar={onToggleStar}
              onStatusChange={onStatusChange}
              onArchive={onArchive}
              onEdit={onEdit}
              onDelete={onDelete}
              onUpdateExecutionLevel={onUpdateExecutionLevel}
            />
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-4">
        {/* Statut et progression */}
        <div className="space-y-2">
          <div className="flex items-center justify-between">
            <Badge
              className={cn(
                "font-medium",
                getStatusColor(project.statut)
              )}
            >
              {getStatusLabel(project.statut)}
            </Badge>
            <span className="text-sm font-medium text-gray-700">
              {project.niveau_execution}%
            </span>
          </div>
          <Progress value={project.niveau_execution} className="h-2" />
        </div>

        {/* Dates */}
        <div className="flex items-center text-sm text-gray-500">
          <Calendar className="h-4 w-4 mr-1" />
          <span>
            {new Date(project.date_debut_previsionnelle).toLocaleDateString('fr-FR')}
            {project.date_fin_previsionnelle && (
              <> - {new Date(project.date_fin_previsionnelle).toLocaleDateString('fr-FR')}</>
            )}
          </span>
        </div>

        {/* Type de projet */}
        <div className="text-sm text-gray-600">
          <span className="font-medium">Type :</span> {project.type_projet.nom}
        </div>

        {/* Porteur et Donneur d'ordre */}
        <div className="flex items-center justify-between">
          <div className="flex -space-x-2">
            <Avatar className="h-8 w-8 border-2 border-white">
              <AvatarFallback className="text-xs bg-blue-100 text-blue-700">
                {getInitials(`${project.porteur.prenom} ${project.porteur.nom}`)}
              </AvatarFallback>
            </Avatar>
            <Avatar className="h-8 w-8 border-2 border-white">
              <AvatarFallback className="text-xs bg-green-100 text-green-700">
                {getInitials(`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`)}
              </AvatarFallback>
            </Avatar>
          </div>
          <span className="text-xs text-gray-500">
            Porteur & Donneur d'ordre
          </span>
        </div>

        {/* Bouton Voir détails */}
        <div className="mt-4 pt-4 border-t border-gray-100">
          <Button
            variant="outline"
            size="sm"
            className="w-full"
            onClick={() => onViewDetails(project)}
          >
            Voir détails
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}

// Composant ProjectRow (vue liste)
interface ProjectRowProps {
  project: ApiProject;
  onViewDetails: (project: ApiProject) => void;
  onViewTimeline: (project: ApiProject) => void;
  onToggleStar: (project: ApiProject) => void;
  onStatusChange: (project: ApiProject) => void;
  onArchive: (project: ApiProject) => void;
  onEdit: (project: ApiProject) => void;
  onDelete: (project: ApiProject) => void;
  onUpdateExecutionLevel: (project: ApiProject) => void;
  getStatusColor: (statut: string) => string;
  getStatusLabel: (statut: string) => string;
  getInitials: (name: string) => string;
}

function ProjectRow({
  project,
  onViewDetails,
  onViewTimeline,
  onToggleStar,
  onStatusChange,
  onArchive,
  onEdit,
  onDelete,
  onUpdateExecutionLevel,
  getStatusColor,
  getStatusLabel,
  getInitials,
}: ProjectRowProps) {
  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardContent className="p-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 flex-1">
            <div
              className={cn(
                "w-2 h-12 rounded-full",
                getStatusColor(project.statut)
              )}
            />
            <div className="flex-1 min-w-0">
              <div className="flex items-center space-x-2">
                <h3 
                  className="text-lg font-semibold text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                  onClick={() => onViewDetails(project)}
                >
                  {project.titre}
                </h3>
                <Button
                  variant="ghost"
                  size="sm"
                  className="h-6 w-6 p-0"
                  onClick={() => onToggleStar(project)}
                >
                  <Star className="h-4 w-4 text-gray-400 hover:text-yellow-500" />
                </Button>
              </div>
              <p className="text-sm text-gray-600 truncate">
                {project.description || "Aucune description disponible"}
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="text-right">
              <div className="text-sm font-medium text-gray-900">
                {project.niveau_execution}%
              </div>
              <div className="text-xs text-gray-500">Progression</div>
            </div>
            
            <Badge
              className={cn(
                "font-medium",
                getStatusColor(project.statut)
              )}
            >
              {getStatusLabel(project.statut)}
            </Badge>
            
            <ProjectActions
              project={project}
              onViewDetails={onViewDetails}
              onViewTimeline={onViewTimeline}
              onToggleStar={onToggleStar}
              onStatusChange={onStatusChange}
              onArchive={onArchive}
              onEdit={onEdit}
              onDelete={onDelete}
              onUpdateExecutionLevel={onUpdateExecutionLevel}
            />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

// Composant ProjectActions
interface ProjectActionsProps {
  project: ApiProject;
  onViewDetails: (project: ApiProject) => void;
  onViewTimeline: (project: ApiProject) => void;
  onToggleStar: (project: ApiProject) => void;
  onStatusChange: (project: ApiProject) => void;
  onArchive: (project: ApiProject) => void;
  onEdit: (project: ApiProject) => void;
  onDelete: (project: ApiProject) => void;
  onUpdateExecutionLevel: (project: ApiProject) => void;
}

function ProjectActions({
  project,
  onViewDetails,
  onViewTimeline,
  onToggleStar,
  onStatusChange,
  onArchive,
  onEdit,
  onDelete,
  onUpdateExecutionLevel,
}: ProjectActionsProps) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
          <MoreHorizontal className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuLabel>Actions</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={() => onViewDetails(project)}>
          <FileText className="h-4 w-4 mr-2" />
          Voir détails
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onEdit(project)}>
          <Settings className="h-4 w-4 mr-2" />
          Modifier
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onUpdateExecutionLevel(project)}>
          <TrendingUp className="h-4 w-4 mr-2 text-blue-600" />
          Mettre à jour le niveau d'exécution
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onDelete(project)} className="text-red-600 focus:text-red-600">
          <Trash2 className="h-4 w-4 mr-2" />
          Supprimer
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={() => onStatusChange(project)}> 
          <Settings className="h-4 w-4 mr-2" />
          Changer statut
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
