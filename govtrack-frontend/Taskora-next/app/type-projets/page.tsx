'use client';

import { useState, useEffect } from 'react';
import { useDebounce } from '@/hooks/use-debounce';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient, TypeProjet, TypeProjetCreateRequest, TypeProjetUpdateRequest } from '@/lib/api';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/use-toast';
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  FolderOpen,
  Plus,
  Search,
  Edit,
  Trash2,
  Eye,
  BarChart3,
  Calendar,
  Clock,
  FileText,
  MoreHorizontal,
  Loader2,
  AlertTriangle,
  CheckCircle,
  XCircle,
  TrendingUp,
  TrendingDown
} from 'lucide-react';
import { useTypeProjetPermissions } from '@/hooks/useTypeProjetPermissions';
import {
  TypeProjetListGuard,
  TypeProjetCreateGuard,
  TypeProjetEditGuard,
  TypeProjetDeleteGuard,
  TypeProjetDetailsGuard,
  TypeProjetStatsGuard
} from '@/components/Shared/TypeProjetGuards';
import { formatBackendErrors } from '@/lib/utils';

interface TypeProjetWithDetails extends TypeProjet {
  projets_count?: number;
}

export default function TypeProjetsPage() {
  const { user, hasPermission } = useAuth();
  const { toast } = useToast();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [typeProjets, setTypeProjets] = useState<TypeProjet[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Valeur debounced pour éviter les appels API trop fréquents
  const debouncedSearchTerm = useDebounce(searchTerm, 500);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  // États pour les modales
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [showStatistiquesModal, setShowStatistiquesModal] = useState(false);

  // États pour les données sélectionnées
  const [selectedTypeProjet, setSelectedTypeProjet] = useState<TypeProjet | null>(null);
  const [typeProjetToDelete, setTypeProjetToDelete] = useState<TypeProjet | null>(null);
  const [selectedTypeProjetStatistiques, setSelectedTypeProjetStatistiques] = useState<any>(null);

  // États pour les formulaires
  const [formData, setFormData] = useState<TypeProjetCreateRequest>({
    nom: '',
    description: '',
    duree_previsionnelle_jours: 30,
    description_sla: ''
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const permissions = useTypeProjetPermissions();

  // Charger les données
  const loadData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getTypeProjets({
        nom: debouncedSearchTerm || undefined,
        page: currentPage,
        per_page: 15
      });
      
      setTypeProjets(response.data || []);
      setTotalPages(response.pagination?.last_page || 1);
      setTotalItems(response.pagination?.total || 0);
    } catch (error: any) {
      console.error('Erreur chargement types de projets:', error);
      
      // Si c'est une erreur de permission, rediriger vers la page d'accueil
      if (error.name === 'PermissionError' || error.message?.includes('permission')) {
        toast({
          title: "❌ Accès refusé",
          description: formatBackendErrors(error),
          variant: "destructive"
        });
        // Rediriger vers la page d'accueil après un court délai
        setTimeout(() => {
          window.location.href = '/';
        }, 2000);
        return;
      }
      
      // Pour les autres erreurs, afficher le toast normalement
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [debouncedSearchTerm, currentPage]);

  // Gestionnaires CRUD
  const handleCreateTypeProjet = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    try {
      await apiClient.createTypeProjet(formData);
      await loadData();
      setShowCreateModal(false);
      setFormData({ nom: '', description: '', duree_previsionnelle_jours: 30, description_sla: '' });
      toast({
        title: "✅ Succès",
        description: "Type de projet créé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur création type projet:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleUpdateTypeProjet = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTypeProjet) return;
    
    setIsSubmitting(true);
    try {
      await apiClient.updateTypeProjet(selectedTypeProjet.id, formData);
      await loadData();
      setShowEditModal(false);
      setSelectedTypeProjet(null);
      setFormData({ nom: '', description: '', duree_previsionnelle_jours: 30, description_sla: '' });
      toast({
        title: "✅ Succès",
        description: "Type de projet modifié avec succès"
      });
    } catch (error: any) {
      console.error('Erreur modification type projet:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDeleteTypeProjet = async () => {
    if (!typeProjetToDelete) return;
    
    try {
      await apiClient.deleteTypeProjet(typeProjetToDelete.id);
      await loadData();
      setShowDeleteDialog(false);
      setTypeProjetToDelete(null);
      toast({
        title: "✅ Succès",
        description: "Type de projet supprimé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur suppression type projet:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  // Gestionnaires d'ouverture des modales
  const openCreateModal = () => {
    setFormData({ nom: '', description: '', duree_previsionnelle_jours: 30, description_sla: '' });
    setShowCreateModal(true);
  };

  const openEditModal = (typeProjet: TypeProjet) => {
    setSelectedTypeProjet(typeProjet);
    setFormData({
      nom: typeProjet.nom,
      description: typeProjet.description || '',
      duree_previsionnelle_jours: typeProjet.duree_previsionnelle_jours,
      description_sla: typeProjet.description_sla || ''
    });
    setShowEditModal(true);
  };

  const openDeleteDialog = (typeProjet: TypeProjet) => {
    setTypeProjetToDelete(typeProjet);
    setShowDeleteDialog(true);
  };

  const openDetailModal = async (typeProjet: TypeProjet) => {
    try {
      const details = await apiClient.getTypeProjet(typeProjet.id);
      setSelectedTypeProjet(details);
      setShowDetailModal(true);
    } catch (error: any) {
      console.error('Erreur détails type projet:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openStatistiquesModal = async (typeProjet: TypeProjet) => {
    try {
      const stats = await apiClient.getTypeProjetStatistiques(typeProjet.id);
      setSelectedTypeProjetStatistiques(stats);
      setShowStatistiquesModal(true);
    } catch (error: any) {
      console.error('Erreur statistiques type projet:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: name === 'duree_previsionnelle_jours' ? parseInt(value) || 0 : value
    }));
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  };

  const getStatutColor = (statut: string) => {
    switch (statut) {
      case 'en_cours': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'termine': return 'bg-green-100 text-green-800 border-green-200';
      case 'en_attente': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'annule': return 'bg-red-100 text-red-800 border-red-200';
      default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  // Protection de la page entière
  if (!permissions.canViewList) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Card className="w-96">
          <CardHeader className="text-center">
            <AlertTriangle className="h-8 w-8 text-destructive mx-auto mb-4" />
            <CardTitle>Accès refusé</CardTitle>
            <CardDescription>
              Vous n'avez pas la permission de voir les types de projets.
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    );
  }

  if (loading && typeProjets.length === 0) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar name="Types de Projets" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <div className="flex items-center justify-center min-h-[400px]">
              <div className="text-center">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-4 text-gray-600">Chargement des types de projets...</p>
              </div>
            </div>
          </main>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-gray-50">
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
        <Topbar name="Types de Projets" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
        <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="max-w-7xl mx-auto">
            {/* En-tête */}
            <div className="flex justify-between items-center mb-6">
              <div>
                <h1 className="text-3xl font-bold text-gray-900">Types de Projets</h1>
                <p className="text-gray-600">Gérer les catégories et types de projets du système</p>
              </div>
              {permissions.canCreate && (
                <TypeProjetCreateGuard>
                  <Button onClick={openCreateModal}>
                    <Plus className="h-4 w-4 mr-2" />
                    Nouveau Type
                  </Button>
                </TypeProjetCreateGuard>
              )}
            </div>

            {/* Statistiques */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Types</CardTitle>
                  <FolderOpen className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{totalItems}</div>
                  <p className="text-xs text-muted-foreground">types configurés</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Types Actifs</CardTitle>
                  <CheckCircle className="h-4 w-4 text-green-600" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {typeProjets.filter(tp => (tp.projets_count || 0) > 0).length}
                  </div>
                  <p className="text-xs text-muted-foreground">avec projets</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Durée Moyenne</CardTitle>
                  <Clock className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {typeProjets.length > 0 
                      ? Math.round(typeProjets.reduce((acc, tp) => acc + tp.duree_previsionnelle_jours, 0) / typeProjets.length)
                      : 0
                    } jours
                  </div>
                  <p className="text-xs text-muted-foreground">durée prévisionnelle</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Types Récents</CardTitle>
                  <TrendingUp className="h-4 w-4 text-blue-600" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {typeProjets.filter(tp => {
                      const date = new Date(tp.date_creation);
                      const now = new Date();
                      const diffDays = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24));
                      return diffDays <= 30;
                    }).length}
                  </div>
                  <p className="text-xs text-muted-foreground">créés ce mois</p>
                </CardContent>
              </Card>
            </div>

            {/* Recherche et filtres */}
            <Card className="mb-6">
              <CardContent className="pt-6">
                <div className="flex flex-col sm:flex-row gap-4">
                  <div className="flex-1">
                    <div className="relative">
                      <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                      <Input
                        placeholder="Rechercher un type de projet..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-9"
                      />
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Liste des types de projets */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FolderOpen className="h-5 w-5" />
                  Types de Projets ({totalItems})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {typeProjets.length > 0 ? (
                  <div className="space-y-4">
                    {typeProjets.map((typeProjet) => (
                      <Card key={typeProjet.id} className="hover:shadow-md transition-shadow">
                        <CardContent className="pt-4">
                          <div className="flex justify-between items-start">
                            <div className="flex-1">
                              <div className="flex items-center gap-2 mb-2">
                                <h3 className="font-semibold text-lg">{typeProjet.nom}</h3>
                                <Badge variant={(typeProjet.projets_count || 0) > 0 ? "default" : "secondary"}>
                                  {typeProjet.projets_count || 0} projets
                                </Badge>
                                <Badge variant="outline">
                                  {typeProjet.duree_formattee || `${typeProjet.duree_previsionnelle_jours} jours`}
                                </Badge>
                              </div>
                              
                              {typeProjet.description && (
                                <p className="text-gray-600 mb-3">{typeProjet.description}</p>
                              )}

                              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                <div className="flex items-center gap-1">
                                  <Calendar className="h-4 w-4" />
                                  Créé le {formatDate(typeProjet.date_creation)}
                                </div>
                                {typeProjet.date_modification && (
                                  <div className="flex items-center gap-1">
                                    <Edit className="h-4 w-4" />
                                    Modifié le {formatDate(typeProjet.date_modification)}
                                  </div>
                                )}
                                <div className="flex items-center gap-1">
                                  <FileText className="h-4 w-4" />
                                  {typeProjet.creer_par}
                                </div>
                              </div>
                            </div>

                            <DropdownMenu>
                              <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="sm">
                                  <MoreHorizontal className="h-4 w-4" />
                                </Button>
                              </DropdownMenuTrigger>
                              <DropdownMenuContent align="end">
                                <TypeProjetDetailsGuard>
                                  <DropdownMenuItem onClick={() => openDetailModal(typeProjet)}>
                                    <Eye className="h-4 w-4 mr-2" />
                                    Voir détails
                                  </DropdownMenuItem>
                                </TypeProjetDetailsGuard>
                                <TypeProjetStatsGuard>
                                  <DropdownMenuItem onClick={() => openStatistiquesModal(typeProjet)}>
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    Statistiques
                                  </DropdownMenuItem>
                                </TypeProjetStatsGuard>
                                {(permissions.canEdit || permissions.canDelete) && (
                                  <>
                                    <TypeProjetEditGuard>
                                      <DropdownMenuItem onClick={() => openEditModal(typeProjet)}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Modifier
                                      </DropdownMenuItem>
                                    </TypeProjetEditGuard>
                                    <TypeProjetDeleteGuard>
                                      <DropdownMenuItem 
                                        onClick={() => openDeleteDialog(typeProjet)}
                                        className="text-red-600"
                                      >
                                        <Trash2 className="h-4 w-4 mr-2" />
                                        Supprimer
                                      </DropdownMenuItem>
                                    </TypeProjetDeleteGuard>
                                  </>
                                )}
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    <FolderOpen className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                    <p>Aucun type de projet trouvé</p>
                    {searchTerm && (
                      <p className="text-sm">Essayez de modifier vos critères de recherche</p>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex justify-center mt-6">
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                    disabled={currentPage === 1}
                  >
                    Précédent
                  </Button>
                  <span className="text-sm text-muted-foreground">
                    Page {currentPage} sur {totalPages}
                  </span>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                    disabled={currentPage === totalPages}
                  >
                    Suivant
                  </Button>
                </div>
              </div>
            )}
          </div>

          {/* Modal Création */}
          <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <Plus className="h-5 w-5" />
                  Nouveau Type de Projet
                </DialogTitle>
                <DialogDescription>
                  Créer un nouveau type de projet avec ses caractéristiques
                </DialogDescription>
              </DialogHeader>
              <form onSubmit={handleCreateTypeProjet}>
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="nom">Nom *</Label>
                      <Input
                        id="nom"
                        name="nom"
                        value={formData.nom}
                        onChange={handleInputChange}
                        required
                        placeholder="Ex: Projet d'infrastructure"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="duree_previsionnelle_jours">Durée prévisionnelle (jours) *</Label>
                      <Input
                        id="duree_previsionnelle_jours"
                        name="duree_previsionnelle_jours"
                        type="number"
                        min="1"
                        max="365"
                        value={formData.duree_previsionnelle_jours}
                        onChange={handleInputChange}
                        required
                        placeholder="30"
                      />
                    </div>
                  </div>
                  
                  <div className="space-y-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                      id="description"
                      name="description"
                      value={formData.description}
                      onChange={handleInputChange}
                      placeholder="Description détaillée du type de projet..."
                      rows={3}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="description_sla">Description SLA</Label>
                    <Textarea
                      id="description_sla"
                      name="description_sla"
                      value={formData.description_sla}
                      onChange={handleInputChange}
                      placeholder="Description des niveaux de service (SLA)..."
                      rows={3}
                    />
                  </div>
                </div>
                <DialogFooter className="mt-6">
                  <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                    Annuler
                  </Button>
                  <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? (
                      <>
                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        Création...
                      </>
                    ) : (
                      <>
                        <Plus className="h-4 w-4 mr-2" />
                        Créer
                      </>
                    )}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>

          {/* Modal Modification */}
          <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <Edit className="h-5 w-5" />
                  Modifier le Type de Projet
                </DialogTitle>
                <DialogDescription>
                  Modifier les informations du type de projet
                </DialogDescription>
              </DialogHeader>
              <form onSubmit={handleUpdateTypeProjet}>
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="edit-nom">Nom *</Label>
                      <Input
                        id="edit-nom"
                        name="nom"
                        value={formData.nom}
                        onChange={handleInputChange}
                        required
                        placeholder="Ex: Projet d'infrastructure"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="edit-duree_previsionnelle_jours">Durée prévisionnelle (jours) *</Label>
                      <Input
                        id="edit-duree_previsionnelle_jours"
                        name="duree_previsionnelle_jours"
                        type="number"
                        min="1"
                        max="365"
                        value={formData.duree_previsionnelle_jours}
                        onChange={handleInputChange}
                        required
                        placeholder="30"
                      />
                    </div>
                  </div>
                  
                  <div className="space-y-2">
                    <Label htmlFor="edit-description">Description</Label>
                    <Textarea
                      id="edit-description"
                      name="description"
                      value={formData.description}
                      onChange={handleInputChange}
                      placeholder="Description détaillée du type de projet..."
                      rows={3}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="edit-description_sla">Description SLA</Label>
                    <Textarea
                      id="edit-description_sla"
                      name="description_sla"
                      value={formData.description_sla}
                      onChange={handleInputChange}
                      placeholder="Description des niveaux de service (SLA)..."
                      rows={3}
                    />
                  </div>
                </div>
                <DialogFooter className="mt-6">
                  <Button type="button" variant="outline" onClick={() => setShowEditModal(false)}>
                    Annuler
                  </Button>
                  <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? (
                      <>
                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        Modification...
                      </>
                    ) : (
                      <>
                        <Edit className="h-4 w-4 mr-2" />
                        Modifier
                      </>
                    )}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>

          {/* Modal Détails */}
          <Dialog open={showDetailModal} onOpenChange={setShowDetailModal}>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <Eye className="h-5 w-5" />
                  Détails du Type de Projet : {selectedTypeProjet?.nom}
                </DialogTitle>
              </DialogHeader>
              {selectedTypeProjet && (
                <div className="space-y-6">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">Nom</Label>
                      <p className="text-lg font-semibold">{selectedTypeProjet.nom}</p>
                    </div>
                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">Durée prévisionnelle</Label>
                      <p className="text-lg font-semibold">
                        {selectedTypeProjet.duree_formattee || `${selectedTypeProjet.duree_previsionnelle_jours} jours`}
                      </p>
                    </div>
                  </div>
                  
                  {selectedTypeProjet.description && (
                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">Description</Label>
                      <p className="mt-1">{selectedTypeProjet.description}</p>
                    </div>
                  )}

                  {selectedTypeProjet.description_sla && (
                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">Description SLA</Label>
                      <p className="mt-1">{selectedTypeProjet.description_sla}</p>
                    </div>
                  )}

                  <div className="flex items-center gap-4 text-sm text-muted-foreground">
                    <div>Créé le {formatDate(selectedTypeProjet.date_creation)}</div>
                    {selectedTypeProjet.date_modification && (
                      <div>Modifié le {formatDate(selectedTypeProjet.date_modification)}</div>
                    )}
                  </div>
                </div>
              )}
              <DialogFooter>
                <Button onClick={() => setShowDetailModal(false)}>Fermer</Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          {/* Modal Statistiques */}
          <Dialog open={showStatistiquesModal} onOpenChange={setShowStatistiquesModal}>
            <DialogContent className="max-w-3xl">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <BarChart3 className="h-5 w-5" />
                  Statistiques : {selectedTypeProjetStatistiques?.type_projet?.nom}
                </DialogTitle>
                <DialogDescription>
                  Statistiques détaillées des projets de ce type
                </DialogDescription>
              </DialogHeader>
              {selectedTypeProjetStatistiques && (
                <div className="space-y-6">
                  {/* Statistiques générales */}
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Card>
                      <CardContent className="pt-4">
                        <div className="text-2xl font-bold">{selectedTypeProjetStatistiques.statistiques.total_projets}</div>
                        <p className="text-xs text-muted-foreground">Total projets</p>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4">
                        <div className="text-2xl font-bold">{selectedTypeProjetStatistiques.statistiques.niveau_execution_moyen}%</div>
                        <p className="text-xs text-muted-foreground">Niveau d'exécution moyen</p>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4">
                        <div className="text-2xl font-bold text-red-600">{selectedTypeProjetStatistiques.statistiques.projets_en_retard}</div>
                        <p className="text-xs text-muted-foreground">Projets en retard</p>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardContent className="pt-4">
                        <div className="text-2xl font-bold text-green-600">
                          {selectedTypeProjetStatistiques.statistiques.total_projets - selectedTypeProjetStatistiques.statistiques.projets_en_retard}
                        </div>
                        <p className="text-xs text-muted-foreground">Projets à jour</p>
                      </CardContent>
                    </Card>
                  </div>

                  {/* Répartition par statut */}
                  <div>
                    <h4 className="font-medium mb-3">Répartition par statut</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {Object.entries(selectedTypeProjetStatistiques.statistiques.projets_par_statut).map(([statut, data]) => (
                        <div key={statut} className="flex items-center justify-between p-3 border rounded">
                          <div className="flex items-center gap-2">
                            <Badge className={getStatutColor(statut)}>
                              {(data as any).libelle}
                            </Badge>
                          </div>
                          <span className="font-semibold">{(data as any).count}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}
              <DialogFooter>
                <Button onClick={() => setShowStatistiquesModal(false)}>Fermer</Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          {/* Dialog Suppression */}
          <AlertDialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Supprimer le type de projet</AlertDialogTitle>
                <AlertDialogDescription>
                  Êtes-vous sûr de vouloir supprimer le type de projet "{typeProjetToDelete?.nom}" ?
                  {typeProjetToDelete?.projets_count && typeProjetToDelete.projets_count > 0 && (
                    <div className="mt-2 p-3 bg-orange-50 border border-orange-200 rounded">
                      <p className="text-orange-800 text-sm">
                        ⚠️ Attention : Ce type de projet est associé à {typeProjetToDelete.projets_count} projet(s). 
                        La suppression n'est possible que s'il n'y a aucun projet associé.
                      </p>
                    </div>
                  )}
                  Cette action est irréversible.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Annuler</AlertDialogCancel>
                <AlertDialogAction onClick={handleDeleteTypeProjet} className="bg-destructive hover:bg-destructive/90">
                  Supprimer
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </main>
      </div>
    </div>
  );
} 