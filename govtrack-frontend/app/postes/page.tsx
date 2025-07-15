"use client";

import { useState, useEffect } from 'react';
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Trash2, Edit, Plus, Eye, Search, Calendar, User, Building, TrendingUp, MoreHorizontal } from "lucide-react";
import { useToast } from "@/components/ui/use-toast";
import { apiClient, Poste } from '@/lib/api';
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from "@/components/ui/alert-dialog";

interface PosteFormData {
  nom: string;
  description: string;
}

interface PosteStatsData {
  total_postes: number;
  postes_avec_affectations: number;
  postes_sans_affectations: number;
  total_affectations: number;
}

export default function PostesPage() {
  const [postes, setPostes] = useState<Poste[]>([]);
  const [stats, setStats] = useState<PosteStatsData>({
    total_postes: 0,
    postes_avec_affectations: 0,
    postes_sans_affectations: 0,
    total_affectations: 0
  });
  const [loading, setLoading] = useState(true);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedPoste, setSelectedPoste] = useState<Poste | null>(null);
  const [formData, setFormData] = useState<PosteFormData>({
    nom: '',
    description: ''
  });
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [isDetailsModalOpen, setIsDetailsModalOpen] = useState(false);
  const [posteToDelete, setPosteToDelete] = useState<Poste | null>(null);
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const { toast } = useToast();

  useEffect(() => {
    fetchPostes();
  }, []);

  const fetchPostes = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getPostes();
      console.log('API Response:', response);
      console.log('Response data:', response.data);
      
      const postesData = response.data || [];
      console.log('Postes data:', postesData);
      
      setPostes(postesData);
      
      // Calculer les statistiques
      const totalPostes = postesData.length;
      console.log('Total postes:', totalPostes);
      
      const postesAvecAffectations = postesData.filter(poste => (poste.affectations_actuelles_count || 0) > 0).length;
      console.log('Postes avec affectations:', postesAvecAffectations);
      
      const totalAffectations = postesData.reduce((sum, poste) => {
        console.log(`Poste ${poste.nom}: affectations_count = ${poste.affectations_count}, affectations_actuelles_count = ${poste.affectations_actuelles_count}`);
        return sum + (poste.affectations_count || 0);
      }, 0);
      console.log('Total affectations calculé:', totalAffectations);
      
      setStats({
        total_postes: totalPostes,
        postes_avec_affectations: postesAvecAffectations,
        postes_sans_affectations: totalPostes - postesAvecAffectations,
        total_affectations: totalAffectations
      });
      
      console.log('Stats finales:', {
        total_postes: totalPostes,
        postes_avec_affectations: postesAvecAffectations,
        postes_sans_affectations: totalPostes - postesAvecAffectations,
        total_affectations: totalAffectations
      });
    } catch (error) {
      console.error('Erreur lors de la récupération des postes:', error);
      toast({
        title: "Erreur",
        description: "Impossible de récupérer les postes",
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const handleCreatePoste = async () => {
    try {
      setErrors({});
      
      // Validation frontend
      const newErrors: { [key: string]: string } = {};
      
      if (!formData.nom.trim()) {
        newErrors.nom = "Le nom est requis";
      } else if (formData.nom.length < 3) {
        newErrors.nom = "Le nom doit contenir au moins 3 caractères";
      }
      
      if (!formData.description.trim()) {
        newErrors.description = "La description est requise";
      } else if (formData.description.length < 10) {
        newErrors.description = "La description doit contenir au moins 10 caractères";
      }
      
      if (Object.keys(newErrors).length > 0) {
        setErrors(newErrors);
        return;
      }
      
      const response = await apiClient.createPoste(formData);
      
      await fetchPostes();
      setIsModalOpen(false);
      resetForm();
      
      toast({
        title: "Succès",
        description: "Poste créé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur lors de la création du poste:', error);
      
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        toast({
          title: "Erreur",
          description: error.response?.data?.message || "Erreur lors de la création du poste",
          variant: "destructive"
        });
      }
    }
  };

  const handleUpdatePoste = async () => {
    if (!selectedPoste) return;
    
    try {
      setErrors({});
      
      // Validation frontend
      const newErrors: { [key: string]: string } = {};
      
      if (!formData.nom.trim()) {
        newErrors.nom = "Le nom est requis";
      } else if (formData.nom.length < 3) {
        newErrors.nom = "Le nom doit contenir au moins 3 caractères";
      }
      
      if (!formData.description.trim()) {
        newErrors.description = "La description est requise";
      } else if (formData.description.length < 10) {
        newErrors.description = "La description doit contenir au moins 10 caractères";
      }
      
      if (Object.keys(newErrors).length > 0) {
        setErrors(newErrors);
        return;
      }
      
      const response = await apiClient.updatePoste(selectedPoste.id, formData);
      
      await fetchPostes();
      setIsModalOpen(false);
      setIsEditMode(false);
      resetForm();
      
      toast({
        title: "Succès",
        description: "Poste modifié avec succès"
      });
    } catch (error: any) {
      console.error('Erreur lors de la modification du poste:', error);
      
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        toast({
          title: "Erreur",
          description: error.response?.data?.message || "Erreur lors de la modification du poste",
          variant: "destructive"
        });
      }
    }
  };

  const handleDeletePoste = async () => {
    if (!posteToDelete) return;
    
    try {
      await apiClient.deletePoste(posteToDelete.id);
      await fetchPostes();
      setPosteToDelete(null);
      
      toast({
        title: "Succès",
        description: "Poste supprimé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur lors de la suppression du poste:', error);
      toast({
        title: "Erreur",
        description: error.response?.data?.message || "Erreur lors de la suppression du poste",
        variant: "destructive"
      });
    }
  };

  const resetForm = () => {
    setFormData({
      nom: '',
      description: ''
    });
    setErrors({});
    setSelectedPoste(null);
  };

  const openCreateModal = () => {
    resetForm();
    setIsEditMode(false);
    setIsModalOpen(true);
  };

  const openEditModal = (poste: Poste) => {
    setSelectedPoste(poste);
    setFormData({
      nom: poste.nom,
      description: poste.description || ''
    });
    setIsEditMode(true);
    setIsModalOpen(true);
  };

  const openDetailsModal = async (poste: Poste) => {
    try {
      const response = await apiClient.getPoste(poste.id);
      setSelectedPoste(response);
      setIsDetailsModalOpen(true);
    } catch (error) {
      console.error('Erreur lors de la récupération des détails:', error);
      toast({
        title: "Erreur",
        description: "Impossible de récupérer les détails du poste",
        variant: "destructive"
      });
    }
  };

  const filteredPostes = postes.filter(poste =>
    poste.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
    poste.description?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    poste.description === null
  );

  const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  };

  if (loading) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar name="Gestion des Postes" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <div className="flex items-center justify-center min-h-[400px]">
              <div className="text-center">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-4 text-gray-600">Chargement des postes...</p>
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
        <Topbar name="Gestion des Postes" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
        <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="max-w-7xl mx-auto">
            {/* En-tête avec bouton d'action */}
            <div className="flex justify-between items-center mb-6">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Gestion des Postes</h1>
                <p className="text-gray-600">Gérez les postes et leurs affectations</p>
              </div>
              <Button onClick={openCreateModal} className="bg-blue-600 hover:bg-blue-700">
                <Plus className="h-4 w-4 mr-2" />
                Nouveau Poste
              </Button>
            </div>

            {/* Statistiques */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Postes</CardTitle>
                  <Building className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.total_postes}</div>
                  <p className="text-xs text-muted-foreground">postes créés</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Avec Affectations</CardTitle>
                  <User className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.postes_avec_affectations}</div>
                  <p className="text-xs text-muted-foreground">postes occupés</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Sans Affectations</CardTitle>
                  <Building className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.postes_sans_affectations}</div>
                  <p className="text-xs text-muted-foreground">postes disponibles</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Affectations</CardTitle>
                  <TrendingUp className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.total_affectations}</div>
                  <p className="text-xs text-muted-foreground">affectations historiques</p>
                </CardContent>
              </Card>
            </div>

            {/* Recherche */}
            <div className="mb-6">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  placeholder="Rechercher par nom ou description..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>
            </div>

            {/* Liste des postes */}
            {filteredPostes.length === 0 ? (
              <Card>
                <CardContent className="text-center py-8">
                  <Building className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">
                    {searchTerm ? 'Aucun poste trouvé' : 'Aucun poste'}
                  </h3>
                  <p className="text-gray-500 mb-4">
                    {searchTerm 
                      ? 'Essayez de modifier vos critères de recherche'
                      : 'Créez votre premier poste pour commencer'
                    }
                  </p>
                  {!searchTerm && (
                    <Button onClick={openCreateModal} className="bg-blue-600 hover:bg-blue-700">
                      <Plus className="h-4 w-4 mr-2" />
                      Créer un poste
                    </Button>
                  )}
                </CardContent>
              </Card>
            ) : (
              <div className="grid gap-4">
                {filteredPostes.map((poste) => (
                  <Card key={poste.id} className="hover:shadow-md transition-shadow">
                    <CardHeader>
                      <div className="flex justify-between items-start">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <CardTitle className="text-lg">{poste.nom}</CardTitle>
                            <Badge variant={(poste.affectations_actuelles_count || 0) > 0 ? "default" : "secondary"}>
                              {(poste.affectations_actuelles_count || 0) > 0 ? "Occupé" : "Disponible"}
                            </Badge>
                          </div>
                          <CardDescription className="text-sm text-gray-600">
                            {poste.description || 'N/A'}
                          </CardDescription>
                        </div>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => openDetailsModal(poste)}>
                              <Eye className="h-4 w-4 mr-2" />
                              Détails
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => openEditModal(poste)}>
                              <Edit className="h-4 w-4 mr-2" />
                              Modifier
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                              onClick={() => setPosteToDelete(poste)}
                              className="text-red-600 hover:text-red-800"
                            >
                              <Trash2 className="h-4 w-4 mr-2" />
                              Supprimer
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </CardHeader>
                    <CardContent>
                      <div className="flex justify-between items-center text-sm text-gray-500">
                        <div className="flex items-center gap-4">
                          <span className="flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            Créé le {formatDate(poste.date_creation)}
                          </span>
                          <span className="flex items-center gap-1">
                            <User className="h-4 w-4" />
                            {poste.affectations_count || 0} affectations
                          </span>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Modal Créer/Modifier */}
      <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>
              {isEditMode ? "Modifier le poste" : "Créer un nouveau poste"}
            </DialogTitle>
            <DialogDescription>
              {isEditMode 
                ? "Modifiez les informations du poste ci-dessous."
                : "Remplissez les informations du nouveau poste."
              }
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="nom">Nom du poste</Label>
              <Input
                id="nom"
                value={formData.nom}
                onChange={(e) => setFormData({...formData, nom: e.target.value})}
                placeholder="Nom du poste"
                className={errors.nom ? "border-red-500" : ""}
              />
              {errors.nom && <p className="text-red-500 text-sm">{errors.nom}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => setFormData({...formData, description: e.target.value})}
                placeholder="Description du poste"
                className={errors.description ? "border-red-500" : ""}
              />
              {errors.description && <p className="text-red-500 text-sm">{errors.description}</p>}
            </div>
          </div>
          <div className="flex justify-end space-x-2 mt-6">
            <Button variant="outline" onClick={() => setIsModalOpen(false)}>
              Annuler
            </Button>
            <Button 
              onClick={isEditMode ? handleUpdatePoste : handleCreatePoste}
              className="bg-blue-600 hover:bg-blue-700"
            >
              {isEditMode ? "Modifier" : "Créer"}
            </Button>
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal Détails */}
      <Dialog open={isDetailsModalOpen} onOpenChange={setIsDetailsModalOpen}>
        <DialogContent className="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>Détails du poste</DialogTitle>
          </DialogHeader>
          {selectedPoste && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium text-gray-500">Nom</Label>
                  <p className="text-sm font-medium">{selectedPoste.nom}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-gray-500">Statut</Label>
                  <Badge variant={(selectedPoste.affectations_actuelles_count || 0) > 0 ? "default" : "secondary"}>
                    {(selectedPoste.affectations_actuelles_count || 0) > 0 ? "Occupé" : "Disponible"}
                  </Badge>
                </div>
              </div>
              <div>
                <Label className="text-sm font-medium text-gray-500">Description</Label>
                <p className="text-sm">{selectedPoste.description || 'N/A'}</p>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium text-gray-500">Date de création</Label>
                  <p className="text-sm">{formatDate(selectedPoste.date_creation)}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-gray-500">Dernière modification</Label>
                  <p className="text-sm">{formatDate(selectedPoste.date_modification)}</p>
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium text-gray-500">Affectations totales</Label>
                  <p className="text-sm">{selectedPoste.affectations_count || 0}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-gray-500">Affectations actuelles</Label>
                  <p className="text-sm">{selectedPoste.affectations_actuelles_count || 0}</p>
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium text-gray-500">Créé par</Label>
                  <p className="text-sm">{selectedPoste.creer_par || 'N/A'}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-gray-500">Modifié par</Label>
                  <p className="text-sm">{selectedPoste.modifier_par || 'N/A'}</p>
                </div>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Dialog de confirmation de suppression */}
      <AlertDialog open={!!posteToDelete} onOpenChange={() => setPosteToDelete(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer le poste "{posteToDelete?.nom}" ? 
              Cette action est irréversible et supprimera toutes les données associées.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction 
              onClick={handleDeletePoste}
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