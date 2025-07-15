'use client';

import { useState, useEffect } from 'react';
import { useDebounce } from '@/hooks/use-debounce';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient, TypeTache, TypeTacheCreateRequest, TypeTacheUpdateRequest } from '@/lib/api';
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
  CheckSquare,
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
  Palette
} from 'lucide-react';
import { useTypeTachePermissions } from '@/hooks/useTypeTachePermissions';
import {
  TypeTacheListGuard,
  TypeTacheCreateGuard,
  TypeTacheEditGuard,
  TypeTacheDeleteGuard,
  TypeTacheDetailsGuard,
  TypeTacheStatsGuard
} from '@/components/Shared/TypeTacheGuards';
import { formatBackendErrors } from '@/lib/utils';
import { CreateTypeTacheDialog } from '@/components/Shared/CreateTypeTacheDialog';
import { EditTypeTacheDialog } from '@/components/Shared/EditTypeTacheDialog';
import { TypeTacheStatsDialog } from '@/components/Shared/TypeTacheStatsDialog';

export default function TypeTachesPage() {
  const { user, hasPermission } = useAuth();
  const { toast } = useToast();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [typeTaches, setTypeTaches] = useState<TypeTache[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Valeur debounced pour éviter les appels API trop fréquents
  const debouncedSearchTerm = useDebounce(searchTerm, 500);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  // États pour les modales
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [showStatsDialog, setShowStatsDialog] = useState(false);

  // États pour les données sélectionnées
  const [selectedTypeTache, setSelectedTypeTache] = useState<TypeTache | null>(null);
  const [typeTacheToDelete, setTypeTacheToDelete] = useState<TypeTache | null>(null);

  const permissions = useTypeTachePermissions();

  // Charger les données
  const loadData = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getTypeTaches({
        nom: debouncedSearchTerm || undefined,
        page: currentPage,
        per_page: 15,
        sort_by: 'ordre',
        sort_order: 'asc'
      });
      
      setTypeTaches(response.data || []);
      setTotalPages(response.pagination?.last_page || 1);
      setTotalItems(response.pagination?.total || 0);
    } catch (error: any) {
      console.error('Erreur chargement types de tâches:', error);
      
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
  const handleCreateTypeTache = async (data: TypeTacheCreateRequest) => {
    try {
      await apiClient.createTypeTache(data);
      await loadData();
      setShowCreateDialog(false);
      toast({
        title: "✅ Succès",
        description: "Type de tâche créé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur création type tâche:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdateTypeTache = async (data: TypeTacheUpdateRequest) => {
    if (!selectedTypeTache) return;
    
    try {
      await apiClient.updateTypeTache(selectedTypeTache.id, data);
      await loadData();
      setShowEditDialog(false);
      setSelectedTypeTache(null);
      toast({
        title: "✅ Succès",
        description: "Type de tâche modifié avec succès"
      });
    } catch (error: any) {
      console.error('Erreur modification type tâche:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeleteTypeTache = async () => {
    if (!typeTacheToDelete) return;
    
    try {
      await apiClient.deleteTypeTache(typeTacheToDelete.id);
      await loadData();
      setShowDeleteDialog(false);
      setTypeTacheToDelete(null);
      toast({
        title: "✅ Succès",
        description: "Type de tâche supprimé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur suppression type tâche:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openCreateModal = () => {
    setShowCreateDialog(true);
  };

  const openEditModal = (typeTache: TypeTache) => {
    setSelectedTypeTache(typeTache);
    setShowEditDialog(true);
  };

  const openDeleteDialog = (typeTache: TypeTache) => {
    setTypeTacheToDelete(typeTache);
    setShowDeleteDialog(true);
  };

  const openStatsModal = (typeTache: TypeTache) => {
    setSelectedTypeTache(typeTache);
    setShowStatsDialog(true);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
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
              Vous n'avez pas la permission de voir les types de tâches.
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    );
  }

  if (loading && typeTaches.length === 0) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar name="Types de Tâches" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <div className="flex items-center justify-center min-h-[400px]">
              <div className="text-center">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-4 text-gray-600">Chargement des types de tâches...</p>
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
        <Topbar name="Types de Tâches" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
        <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="max-w-7xl mx-auto">
            {/* En-tête */}
            <div className="flex justify-between items-center mb-6">
              <div>
                <h1 className="text-3xl font-bold text-gray-900">Types de Tâches</h1>
                <p className="text-gray-600">Gérer les catégories et types de tâches du système</p>
              </div>
              {permissions.canCreate && (
                <TypeTacheCreateGuard>
                  <Button onClick={openCreateModal}>
                    <Plus className="h-4 w-4 mr-2" />
                    Nouveau Type
                  </Button>
                </TypeTacheCreateGuard>
              )}
            </div>

            {/* Statistiques */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Types</CardTitle>
                  <CheckSquare className="h-4 w-4 text-muted-foreground" />
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
                    {typeTaches.filter(tt => tt.actif).length}
                  </div>
                  <p className="text-xs text-muted-foreground">actuellement actifs</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Types Utilisés</CardTitle>
                  <TrendingUp className="h-4 w-4 text-blue-600" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {typeTaches.filter(tt => (tt.taches_count || 0) > 0).length}
                  </div>
                  <p className="text-xs text-muted-foreground">avec des tâches</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Types Récents</CardTitle>
                  <Clock className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {typeTaches.filter(tt => {
                      const date = new Date(tt.date_creation);
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
                        placeholder="Rechercher un type de tâche..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-9"
                      />
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Liste des types de tâches */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CheckSquare className="h-5 w-5" />
                  Types de Tâches ({totalItems})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {typeTaches.length > 0 ? (
                  <div className="space-y-4">
                    {typeTaches.map((typeTache) => (
                      <Card key={typeTache.id} className="hover:shadow-md transition-shadow">
                        <CardContent className="pt-4">
                          <div className="flex justify-between items-start">
                            <div className="flex-1">
                              <div className="flex items-center gap-2 mb-2">
                                <div
                                  className="w-4 h-4 rounded-full"
                                  style={{ backgroundColor: typeTache.couleur }}
                                />
                                <h3 className="font-semibold text-lg">{typeTache.nom}</h3>
                                <Badge variant={typeTache.actif ? "default" : "secondary"}>
                                  {typeTache.actif ? 'Actif' : 'Inactif'}
                                </Badge>
                                <Badge variant="outline">
                                  Ordre: {typeTache.ordre}
                                </Badge>
                                {(typeTache.taches_count || 0) > 0 && (
                                  <Badge variant="outline">
                                    {typeTache.taches_count} tâche(s)
                                  </Badge>
                                )}
                              </div>
                              
                              {typeTache.description && (
                                <p className="text-gray-600 mb-3">{typeTache.description}</p>
                              )}

                              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                <div className="flex items-center gap-1">
                                  <Calendar className="h-4 w-4" />
                                  Créé le {formatDate(typeTache.date_creation)}
                                </div>
                                {typeTache.date_modification && (
                                  <div className="flex items-center gap-1">
                                    <Edit className="h-4 w-4" />
                                    Modifié le {formatDate(typeTache.date_modification)}
                                  </div>
                                )}
                                <div className="flex items-center gap-1">
                                  <FileText className="h-4 w-4" />
                                  {typeTache.creer_par}
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
                                <TypeTacheStatsGuard>
                                  <DropdownMenuItem onClick={() => openStatsModal(typeTache)}>
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    Statistiques
                                  </DropdownMenuItem>
                                </TypeTacheStatsGuard>
                                
                                <TypeTacheEditGuard>
                                  <DropdownMenuItem onClick={() => openEditModal(typeTache)}>
                                    <Edit className="h-4 w-4 mr-2" />
                                    Modifier
                                  </DropdownMenuItem>
                                </TypeTacheEditGuard>
                                
                                <TypeTacheDeleteGuard>
                                  <DropdownMenuItem 
                                    onClick={() => openDeleteDialog(typeTache)}
                                    className="text-red-600 focus:text-red-600"
                                  >
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Supprimer
                                  </DropdownMenuItem>
                                </TypeTacheDeleteGuard>
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <CheckSquare className="h-12 w-12 text-gray-300 mx-auto mb-4" />
                    <p className="text-gray-500 mb-2">Aucun type de tâche trouvé</p>
                    <p className="text-sm text-gray-400">
                      {debouncedSearchTerm 
                        ? 'Aucun résultat pour votre recherche'
                        : 'Les types de tâches apparaîtront ici une fois qu\'ils seront créés'
                      }
                    </p>
                    {permissions.canCreate && !debouncedSearchTerm && (
                      <TypeTacheCreateGuard>
                        <Button onClick={openCreateModal} className="mt-4" variant="outline">
                          <Plus className="h-4 w-4 mr-2" />
                          Créer le premier type
                        </Button>
                      </TypeTacheCreateGuard>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex items-center justify-between mt-6">
                <div className="text-sm text-gray-500">
                  Page {currentPage} sur {totalPages} ({totalItems} éléments)
                </div>
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(currentPage - 1)}
                    disabled={currentPage === 1}
                  >
                    Précédent
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(currentPage + 1)}
                    disabled={currentPage === totalPages}
                  >
                    Suivant
                  </Button>
                </div>
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Modales */}
      {showCreateDialog && (
        <CreateTypeTacheDialog
          open={showCreateDialog}
          onOpenChange={setShowCreateDialog}
          onSubmit={handleCreateTypeTache}
        />
      )}

      {showEditDialog && selectedTypeTache && (
        <EditTypeTacheDialog
          open={showEditDialog}
          onOpenChange={setShowEditDialog}
          typeTache={selectedTypeTache}
          onSubmit={handleUpdateTypeTache}
        />
      )}

      {showStatsDialog && selectedTypeTache && (
        <TypeTacheStatsDialog
          open={showStatsDialog}
          onOpenChange={setShowStatsDialog}
          typeTacheId={selectedTypeTache.id}
        />
      )}

      {/* Dialog de suppression */}
      <AlertDialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer le type de tâche "{typeTacheToDelete?.nom}" ?
              {typeTacheToDelete && (typeTacheToDelete.taches_count || 0) > 0 && (
                <span className="block mt-2 text-red-600">
                  ⚠️ Ce type est actuellement utilisé par {typeTacheToDelete.taches_count} tâche(s).
                </span>
              )}
              Cette action est irréversible.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleDeleteTypeTache}
              className="bg-red-600 hover:bg-red-700"
            >
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
} 