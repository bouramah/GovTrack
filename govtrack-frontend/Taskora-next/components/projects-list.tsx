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
import { apiClient, Project, PaginatedResponse } from "@/lib/api";
import { ProjectViewDetailsModal } from "./project-view-details-modal";
import { ProjectTimelineModal } from "./project-timeline-modal";
import { ProjectStatusModal } from "./project-status-modal";
import { ProjectArchiveModal } from "./project-archive-modal";

interface ProjectsListProps {
  viewMode: "grid" | "list";
  filterStatus: string | null;
}

export default function ProjectsList({
  viewMode,
  filterStatus,
}: ProjectsListProps) {
  const [projects, setProjects] = useState<Project[]>([]);
  const [filteredProjects, setFilteredProjects] = useState<Project[]>([]);
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [viewDetailsOpen, setViewDetailsOpen] = useState(false);
  const [timelineOpen, setTimelineOpen] = useState(false);
  const [statusModalOpen, setStatusModalOpen] = useState(false);
  const [statusAction, setStatusAction] = useState<
    "complete" | "hold" | "change"
  >("change");
  const [archiveModalOpen, setArchiveModalOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const [filters, setFilters] = useState({
    search: "",
    statut: "",
    type_projet_id: undefined as number | undefined,
    en_retard: false,
    sort_by: "date_creation",
    sort_order: "desc" as "asc" | "desc",
  });
  const { toast } = useToast();

  // Fonction pour charger les projets
  const loadProjects = async (page: number = 1) => {
    try {
      setLoading(true);
      setError(null);
      
      const params: any = {
        ...filters,
        page,
        per_page: pagination.per_page,
      };

      // Exclure les valeurs vides des paramètres
      Object.keys(params).forEach(key => {
        if (params[key] === "" || params[key] === undefined || params[key] === null) {
          delete params[key];
        }
      });

      // Appliquer le filtre de statut du parent si présent
      if (filterStatus) {
        params.statut = filterStatus;
      }

      const response: PaginatedResponse<Project> = await apiClient.getProjects(params);
      
      console.log('API Response:', response);
      console.log('Pagination:', response.pagination);
      
      setProjects(response.data || []);
      setFilteredProjects(response.data || []);
      setPagination(response.pagination);
      
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

  const handleViewDetails = (project: Project) => {
    setSelectedProject(project);
    setViewDetailsOpen(true);
  };

  const handleViewTimeline = (project: Project) => {
    setSelectedProject(project);
    setTimelineOpen(true);
  };

  const handleToggleStar = (project: Project) => {
    // TODO: Implémenter la fonctionnalité de favoris
    toast({
      title: "Fonctionnalité à venir",
      description: "La gestion des favoris sera bientôt disponible",
    });
  };

  const handleStatusChange = (
    project: Project,
    action: "complete" | "hold" | "change"
  ) => {
    setSelectedProject(project);
    setStatusAction(action);
    setStatusModalOpen(true);
  };

  const handleUpdateStatus = async (
    status: string,
    note: string
  ) => {
    if (!selectedProject) return;

    try {
      await apiClient.changeProjectStatut(selectedProject.id, {
        nouveau_statut: status,
        commentaire: note,
      });

      toast({
        title: "Statut mis à jour",
        description: "Le statut du projet a été modifié avec succès",
      });

      // Recharger les projets
      loadProjects(pagination.current_page);
      setStatusModalOpen(false);
    } catch (err: any) {
      toast({
        title: "Erreur",
        description: err.message || "Impossible de modifier le statut",
        variant: "destructive",
      });
    }
  };

  const handleArchiveProject = (project: Project) => {
    setSelectedProject(project);
    setArchiveModalOpen(true);
  };

  const handleConfirmArchive = async (notifyTeam: boolean) => {
    if (!selectedProject) return;

    try {
      await apiClient.deleteProject(selectedProject.id);
      
      toast({
        title: "Projet archivé",
        description: "Le projet a été archivé avec succès",
      });

      // Recharger les projets
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

  const handleFilterChange = (key: string, value: any) => {
    setFilters(prev => ({
      ...prev,
      [key]: value,
    }));
  };

  const handleSearch = (value: string) => {
    setFilters(prev => ({
      ...prev,
      search: value,
    }));
  };

  const handlePageChange = (page: number) => {
    loadProjects(page);
  };

  // Fonction pour obtenir la couleur du statut
  const getStatusColor = (statut: string) => {
    switch (statut) {
      case "a_faire":
        return "bg-gray-100 text-gray-800";
      case "en_cours":
        return "bg-blue-100 text-blue-800";
      case "demande_cloture":
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
      demande_cloture: "Demande de clôture",
      termine: "Terminé",
      bloque: "Bloqué",
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

  if (loading && projects.length === 0) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin" />
        <span className="ml-2">Chargement des projets...</span>
      </div>
    );
  }

  if (error && projects.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center h-64">
        <AlertTriangle className="h-12 w-12 text-red-500 mb-4" />
        <h3 className="text-lg font-semibold mb-2">Erreur de chargement</h3>
        <p className="text-gray-600 mb-4">{error}</p>
        <Button onClick={() => loadProjects()}>
          Réessayer
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Filtres et recherche */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="flex-1">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              placeholder="Rechercher des projets..."
              value={filters.search}
              onChange={(e) => handleSearch(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>
        <div className="flex gap-2">
          <Select
            value={filters.statut}
            onValueChange={(value) => handleFilterChange("statut", value)}
          >
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="a_faire">À faire</SelectItem>
              <SelectItem value="en_cours">En cours</SelectItem>
              <SelectItem value="demande_de_cloture">Demande de clôture</SelectItem>
              <SelectItem value="termine">Terminé</SelectItem>
              <SelectItem value="bloque">Bloqué</SelectItem>
            </SelectContent>
          </Select>
          <Select
            value={filters.sort_by}
            onValueChange={(value) => handleFilterChange("sort_by", value)}
          >
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Trier par" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="date_creation">Date de création</SelectItem>
              <SelectItem value="titre">Titre</SelectItem>
              <SelectItem value="statut">Statut</SelectItem>
              <SelectItem value="niveau_execution">Niveau d'exécution</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* Statistiques par statut */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total</p>
                <p className="text-2xl font-bold">{pagination.total}</p>
              </div>
              <BarChart2 className="h-8 w-8 text-blue-500" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">À faire</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.statut === "a_faire").length}
                </p>
              </div>
              <div className="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                <span className="text-gray-600 text-sm font-bold">À</span>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">En cours</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.statut === "en_cours").length}
                </p>
              </div>
              <Clock className="h-8 w-8 text-blue-500" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Demande clôture</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.statut === "demande_de_cloture").length}
                </p>
              </div>
              <div className="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <span className="text-yellow-600 text-sm font-bold">DC</span>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Terminés</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.statut === "termine").length}
                </p>
              </div>
              <CheckCircle className="h-8 w-8 text-green-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Statistiques supplémentaires */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Bloqués</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.statut === "bloque").length}
                </p>
              </div>
              <div className="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                <span className="text-red-600 text-sm font-bold">B</span>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">En retard</p>
                <p className="text-2xl font-bold">
                  {projects.filter(p => p.est_en_retard).length}
                </p>
              </div>
              <AlertTriangle className="h-8 w-8 text-red-500" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Niveau moyen</p>
                <p className="text-2xl font-bold">
                  {projects.length > 0 
                    ? Math.round(projects.reduce((sum, p) => sum + p.niveau_execution, 0) / projects.length)
                    : 0}%
                </p>
              </div>
              <BarChart2 className="h-8 w-8 text-purple-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Liste des projets */}
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
              getStatusColor={getStatusColor}
              getStatusLabel={getStatusLabel}
              getInitials={getInitials}
            />
          ))}
        </div>
      )}



      {/* Pagination */}
      {pagination.total > 0 && (
        <div className="flex justify-center mt-8">
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => handlePageChange(1)}
              disabled={pagination.current_page === 1}
              size="sm"
            >
              Première
            </Button>
            <Button
              variant="outline"
              onClick={() => handlePageChange(pagination.current_page - 1)}
              disabled={pagination.current_page === 1}
              size="sm"
            >
              Précédent
            </Button>
            
            {/* Pages numérotées */}
            <div className="flex gap-1">
              {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                let pageNum;
                if (pagination.last_page <= 5) {
                  pageNum = i + 1;
                } else if (pagination.current_page <= 3) {
                  pageNum = i + 1;
                } else if (pagination.current_page >= pagination.last_page - 2) {
                  pageNum = pagination.last_page - 4 + i;
                } else {
                  pageNum = pagination.current_page - 2 + i;
                }
                
                return (
                  <Button
                    key={pageNum}
                    variant={pagination.current_page === pageNum ? "default" : "outline"}
                    onClick={() => handlePageChange(pageNum)}
                    size="sm"
                    className="w-10 h-10"
                  >
                    {pageNum}
                  </Button>
                );
              })}
            </div>
            
            <Button
              variant="outline"
              onClick={() => handlePageChange(pagination.current_page + 1)}
              disabled={pagination.current_page === pagination.last_page}
              size="sm"
            >
              Suivant
            </Button>
            <Button
              variant="outline"
              onClick={() => handlePageChange(pagination.last_page)}
              disabled={pagination.current_page === pagination.last_page}
              size="sm"
            >
              Dernière
            </Button>
            
            <span className="flex items-center px-4 text-sm text-gray-600">
              {pagination.total} projets • Page {pagination.current_page} sur {pagination.last_page}
            </span>
          </div>
        </div>
      )}

      {/* Modals */}
      {selectedProject && (
        <>
          <ProjectViewDetailsModal
            project={selectedProject}
            open={viewDetailsOpen}
            onOpenChange={setViewDetailsOpen}
          />
          <ProjectTimelineModal
            project={selectedProject}
            open={timelineOpen}
            onOpenChange={setTimelineOpen}
          />
          <ProjectStatusModal
            project={selectedProject}
            action={statusAction}
            open={statusModalOpen}
            onOpenChange={setStatusModalOpen}
            onUpdateStatus={handleUpdateStatus}
          />
          <ProjectArchiveModal
            project={selectedProject}
            open={archiveModalOpen}
            onOpenChange={setArchiveModalOpen}
            onConfirmArchive={handleConfirmArchive}
          />
        </>
      )}
    </div>
  );
}

interface ProjectCardProps {
  project: Project;
  onViewDetails: (project: Project) => void;
  onViewTimeline: (project: Project) => void;
  onToggleStar: (project: Project) => void;
  onStatusChange: (
    project: Project,
    action: "complete" | "hold" | "change"
  ) => void;
  onArchive: (project: Project) => void;
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
  getStatusColor,
  getStatusLabel,
  getInitials,
}: ProjectCardProps) {
  return (
    <Card className="overflow-hidden transition-all hover:shadow-md">
      <CardHeader className="pb-2">
        <div className="flex flex-wrap gap-2 justify-between items-start">
          <div className="flex items-start space-x-2">
            <div
              className={cn(
                "w-1 h-12 rounded-full",
                getStatusColor(project.statut)
              )}
            />
            <div>
              <CardTitle className="text-lg flex items-center">
                {project.titre}
                {/* TODO: Implémenter les favoris */}
              </CardTitle>
              <CardDescription>{project.description}</CardDescription>
            </div>
          </div>
          <Badge
            className={cn(
              "font-medium text-nowrap",
              getStatusColor(project.statut)
            )}
          >
            {getStatusLabel(project.statut)}
          </Badge>
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex justify-between items-center mb-3">
          <div className="flex items-center text-gray-500 text-sm">
            <Calendar className="h-4 w-4 mr-1" />
            <span>
              Échéance: {project.date_fin_previsionnelle 
                ? new Date(project.date_fin_previsionnelle).toLocaleDateString('fr-FR')
                : 'Non définie'
              }
            </span>
          </div>
          <ProjectActions
            project={project}
            onViewDetails={onViewDetails}
            onViewTimeline={onViewTimeline}
            onToggleStar={onToggleStar}
            onStatusChange={onStatusChange}
            onArchive={onArchive}
          />
        </div>
        <div className="space-y-3">
          <div className="flex justify-between text-sm">
            <span className="text-gray-500">Niveau d'exécution</span>
            <span className="font-medium">{project.niveau_execution}%</span>
          </div>
          <Progress value={project.niveau_execution} className="h-2" />
        </div>
        <div className="mt-4 flex justify-between items-center">
          <div className="flex -space-x-2">
            {/* Porteur du projet */}
            <Avatar className="h-8 w-8 border-2 border-white">
              <AvatarImage
                src={`/avatars/${project.porteur.nom.toLowerCase()}-${project.porteur.prenom.toLowerCase()}.png`}
                alt={`${project.porteur.prenom} ${project.porteur.nom}`}
              />
              <AvatarFallback>{getInitials(`${project.porteur.prenom} ${project.porteur.nom}`)}</AvatarFallback>
            </Avatar>
            {/* Donneur d'ordre */}
            <Avatar className="h-8 w-8 border-2 border-white">
              <AvatarImage
                src={`/avatars/${project.donneur_ordre.nom.toLowerCase()}-${project.donneur_ordre.prenom.toLowerCase()}.png`}
                alt={`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`}
              />
              <AvatarFallback>{getInitials(`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`)}</AvatarFallback>
            </Avatar>
          </div>
          <div className="flex items-center space-x-3 text-xs text-gray-500">
            <div className="flex items-center">
              <FileText className="h-3.5 w-3.5 mr-1" />
              <span>{project.taches_count || 0} tâches</span>
            </div>
            <div className="flex items-center">
              <BarChart2 className="h-3.5 w-3.5 mr-1" />
              <span>{project.type_projet.nom}</span>
            </div>
          </div>
        </div>
        <div className="mt-4 pt-4 border-t border-gray-100">
          <div className="grid grid-cols-2 gap-4 text-xs">
            <div>
              <p className="text-gray-500 mb-1">Porteur</p>
              <p className="font-medium">{project.porteur.prenom} {project.porteur.nom}</p>
            </div>
            <div>
              <p className="text-gray-500 mb-1">Donneur d'ordre</p>
              <p className="font-medium">{project.donneur_ordre.prenom} {project.donneur_ordre.nom}</p>
            </div>
            <div>
              <p className="text-gray-500 mb-1">Date de début</p>
              <p className="font-medium">{new Date(project.date_debut_previsionnelle).toLocaleDateString('fr-FR')}</p>
            </div>
            <div>
              <p className="text-gray-500 mb-1">Statut</p>
              <Badge
                className={cn(
                  "font-medium",
                  getStatusColor(project.statut)
                )}
              >
                {getStatusLabel(project.statut)}
              </Badge>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

interface ProjectRowProps {
  project: Project;
  onViewDetails: (project: Project) => void;
  onViewTimeline: (project: Project) => void;
  onToggleStar: (project: Project) => void;
  onStatusChange: (
    project: Project,
    action: "complete" | "hold" | "change"
  ) => void;
  onArchive: (project: Project) => void;
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
  getStatusColor,
  getStatusLabel,
  getInitials,
}: ProjectRowProps) {
  return (
    <tr className="border-b border-gray-200 hover:bg-gray-50">
      <td className="py-3 px-4">
        <div className="flex items-center">
          <div
            className={cn(
              "w-1 h-8 rounded-full mr-3",
              getStatusColor(project.statut)
            )}
          />
          <div>
            <div className="font-medium text-gray-900 flex items-center text-nowrap">
              {project.titre}
              {/* TODO: Implémenter les favoris */}
            </div>
            <div className="text-xs text-gray-500">{project.description}</div>
          </div>
        </div>
      </td>
      <td className="py-3 px-4">
        <Badge
          className={cn(
            "font-medium text-nowrap",
            getStatusColor(project.statut)
          )}
        >
          {getStatusLabel(project.statut)}
        </Badge>
      </td>
      <td className="py-3 px-4">
        <div className="w-32">
          <div className="flex justify-between text-xs mb-1">
            <span className="text-gray-500">Exécution</span>
            <span className="font-medium">{project.niveau_execution}%</span>
          </div>
          <Progress value={project.niveau_execution} className="h-1.5" />
        </div>
      </td>
      <td className="py-3 px-4">
        <div className="flex items-center text-gray-500 text-sm">
          <Calendar className="h-3.5 w-3.5 mr-1.5" />
          <span className="text-nowrap">
            {project.date_fin_previsionnelle 
              ? new Date(project.date_fin_previsionnelle).toLocaleDateString('fr-FR')
              : 'Non définie'
            }
          </span>
        </div>
      </td>
      <td className="py-3 px-4">
        <div className="flex -space-x-2">
          {/* Porteur du projet */}
          <Avatar className="h-7 w-7 border-2 border-white">
            <AvatarImage
              src={`/avatars/${project.porteur.nom.toLowerCase()}-${project.porteur.prenom.toLowerCase()}.png`}
              alt={`${project.porteur.prenom} ${project.porteur.nom}`}
            />
            <AvatarFallback>{getInitials(`${project.porteur.prenom} ${project.porteur.nom}`)}</AvatarFallback>
          </Avatar>
          {/* Donneur d'ordre */}
          <Avatar className="h-7 w-7 border-2 border-white">
            <AvatarImage
              src={`/avatars/${project.donneur_ordre.nom.toLowerCase()}-${project.donneur_ordre.prenom.toLowerCase()}.png`}
              alt={`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`}
            />
            <AvatarFallback>{getInitials(`${project.donneur_ordre.prenom} ${project.donneur_ordre.nom}`)}</AvatarFallback>
          </Avatar>
        </div>
      </td>
      <td className="py-3 px-4  flex justify-end cursor-pointer">
        <MoreHorizontal className="text-end" />
      </td>
    </tr>
  );
}

interface ProjectActionsProps {
  project: Project;
  onViewDetails: (project: Project) => void;
  onViewTimeline: (project: Project) => void;
  onToggleStar: (project: Project) => void;
  onStatusChange: (
    project: Project,
    action: "complete" | "hold" | "change"
  ) => void;
  onArchive: (project: Project) => void;
}

function ProjectActions({
  project,
  onViewDetails,
  onViewTimeline,
  onToggleStar,
  onStatusChange,
  onArchive,
}: ProjectActionsProps) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="icon" className="h-8 w-8">
          <MoreHorizontal className="h-4 w-4" />
          <span className="sr-only">Project actions</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56">
        <DropdownMenuLabel>Project Actions</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={() => onViewDetails(project)}>
          <FileText className="h-4 w-4 mr-2" />
          <span>View Details</span>
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onViewTimeline(project)}>
          <Clock className="h-4 w-4 mr-2" />
          <span>View Timeline</span>
        </DropdownMenuItem>
        <DropdownMenuItem onClick={() => onToggleStar(project)}>
          <Star className="h-4 w-4 mr-2" />
          <span>Marquer comme favori</span>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem
          onClick={() => onStatusChange(project, "change")}
          disabled={project.statut === "termine"}
        >
          <Clock className="h-4 w-4 mr-2" />
          <span>Change Status</span>
        </DropdownMenuItem>
        <DropdownMenuItem
          onClick={() => onStatusChange(project, "complete")}
          disabled={project.statut === "termine"}
        >
          <CheckCircle className="h-4 w-4 mr-2" />
          <span>Mark as Completed</span>
        </DropdownMenuItem>
        <DropdownMenuItem
          onClick={() => onStatusChange(project, "hold")}
          disabled={project.statut === "bloque"}
        >
          <PauseCircle className="h-4 w-4 mr-2" />
          <span>Put on Hold</span>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem
          onClick={() => onArchive(project)}
          className="text-red-600"
        >
          <AlertTriangle className="h-4 w-4 mr-2" />
          <span>Archive Project</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
