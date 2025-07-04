"use client";

import { useState, useEffect, useMemo } from "react";
import { useDebounce } from "@/hooks/use-debounce";
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import { ProtectedPage } from '@/components/ProtectedPage';
import { 
  ViewEntitiesListGuard,
  CreateEntityGuard,
  EditEntityGuard,
  DeleteEntityGuard,
  ViewEntityDetailsGuard,
  ViewEntityHierarchyGuard,
  ViewEntityUsersGuard,
  ManageEntityAssignmentsGuard,
  ViewEntityChiefHistoryGuard,
  useEntityPermissions
} from '@/components/Shared/EntityGuards';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogDescription, DialogFooter } from "@/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { useToast } from "@/components/ui/use-toast";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { 
  Building,
  Users,
  Crown,
  TreePine,
  Building2,
  GitBranch,
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  Plus,
  UserCheck,
  UserX,
  UserPlus,
  History,
  Network,
  Search,
  Calendar,
  ChevronRight,
  ChevronDown,
  MapPin,
  Clock,
  Filter,
  Package,
  Briefcase,
  ChevronLeft,
  ChevronFirst,
  ChevronLast
} from "lucide-react";
import { Combobox, ComboboxOption } from "@/components/ui/combobox";
import { apiClient, TypeEntite, Entite, User, Poste } from "@/lib/api";

// Composant pour afficher conditionnellement un séparateur
const ConditionalSeparator: React.FC<{ 
  showIfAnyVisible: boolean;
}> = ({ showIfAnyVisible }) => {
  if (!showIfAnyVisible) return null;
  return <Separator />;
};

interface EntiteWithDetails extends Entite {
  chef_actuel?: any;
  employes_actuels?: any[];
  nombre_enfants?: number;
}

interface PosteWithDetails extends Poste {
  affectations_actuelles_count?: number;
  affectations_count?: number;
}

interface OrganigrammeNodeData {
  id: number;
  nom: string;
  description?: string;
  type_entite: {
    id: number;
    nom: string;
    description: string;
  };
  chef_actuel?: {
    id: number;
    nom: string;
    prenom: string;
    matricule: string;
    email: string;
    date_debut_mandat: string;
    duree_mandat_jours: number;
  };
  effectifs: {
    nombre_employes: number;
    employes: any[];
  };
  statistiques: {
    nombre_enfants_directs: number;
    nombre_total_descendants: number;
    a_chef: boolean;
    niveau_hierarchique: number;
  };
  enfants: OrganigrammeNodeData[];
  metadata: {
    date_creation: string;
    creer_par: string;
  };
}

const formatBackendErrors = (error: any): string => {
  // Gestion des erreurs de permission (422)
  if (error.name === 'PermissionError') {
    return error.message;
  }
  
  // Gestion des erreurs de validation Laravel
  if (error.response?.data?.errors) {
    const errors = error.response.data.errors;
    return Object.values(errors).flat().join(', ');
  }
  
  // Gestion des messages d'erreur du serveur
  if (error.response?.data?.message) {
    return error.response.data.message;
  }
  
  // Gestion des erreurs réseau
  if (error.message && error.message.includes('Network Error')) {
    return "Erreur de connexion au serveur. Vérifiez votre connexion internet.";
  }
  
  // Gestion des erreurs de timeout
  if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
    return "La requête a pris trop de temps. Veuillez réessayer.";
  }
  
  // Erreur générique
  return "Une erreur inattendue s'est produite";
};

export default function EntitiesPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [typeEntites, setTypeEntites] = useState<TypeEntite[]>([]);
  const [entites, setEntites] = useState<EntiteWithDetails[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [postes, setPostes] = useState<PosteWithDetails[]>([]);
  const [selectedEntite, setSelectedEntite] = useState<EntiteWithDetails | null>(null);
  const [selectedPoste, setSelectedPoste] = useState<PosteWithDetails | null>(null);
  const [organigramme, setOrganigramme] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("entites");
  const [searchTerm, setSearchTerm] = useState("");
  const [selectedTypeFilter, setSelectedTypeFilter] = useState<string>("tous");
  const [searchTermPostes, setSearchTermPostes] = useState("");
  
  const entityPermissions = useEntityPermissions();
  
  // Valeurs debounced pour éviter les appels API trop fréquents
  const debouncedSearchTerm = useDebounce(searchTerm, 1000);
  const debouncedSearchTermPostes = useDebounce(searchTermPostes, 1000);
  
  // États pour la pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [itemsPerPage, setItemsPerPage] = useState(15);
  
  // États pour la pagination des postes
  const [currentPagePostes, setCurrentPagePostes] = useState(1);
  const [totalPagesPostes, setTotalPagesPostes] = useState(1);
  const [totalItemsPostes, setTotalItemsPostes] = useState(0);
  const [itemsPerPagePostes, setItemsPerPagePostes] = useState(15);

  // États des modales
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [showHierarchyModal, setShowHierarchyModal] = useState(false);
  const [showChefModal, setShowChefModal] = useState(false);
  const [showChefHistoryModal, setShowChefHistoryModal] = useState(false);
  const [showUtilisateursModal, setShowUtilisateursModal] = useState(false);
  const [showTerminerMandatModal, setShowTerminerMandatModal] = useState(false);

  // États des modales pour types d'entités
  const [showCreateTypeModal, setShowCreateTypeModal] = useState(false);
  const [showEditTypeModal, setShowEditTypeModal] = useState(false);
  const [showDetailTypeModal, setShowDetailTypeModal] = useState(false);
  const [showDeleteTypeDialog, setShowDeleteTypeDialog] = useState(false);

  // États des modales pour postes
  const [showCreatePosteModal, setShowCreatePosteModal] = useState(false);
  const [showEditPosteModal, setShowEditPosteModal] = useState(false);
  const [showDetailPosteModal, setShowDetailPosteModal] = useState(false);
  const [showDeletePosteDialog, setShowDeletePosteDialog] = useState(false);

  // Données pour les modales
  const [entiteToDelete, setEntiteToDelete] = useState<EntiteWithDetails | null>(null);
  const [entiteHierarchy, setEntiteHierarchy] = useState<any>(null);
  const [chefHistory, setChefHistory] = useState<any[]>([]);
  const [chefsActuels, setChefsActuels] = useState<any[]>([]);
  const [utilisateursEntite, setUtilisateursEntite] = useState<any>(null);
  const [enfants, setEnfants] = useState<EntiteWithDetails[]>([]);
  const [selectedEntiteForAction, setSelectedEntiteForAction] = useState<EntiteWithDetails | null>(null);

  // Données pour les modales types d'entités
  const [selectedTypeEntite, setSelectedTypeEntite] = useState<TypeEntite | null>(null);
  const [typeEntiteToDelete, setTypeEntiteToDelete] = useState<TypeEntite | null>(null);

  // Données pour les modales postes
  const [posteToDelete, setPosteToDelete] = useState<PosteWithDetails | null>(null);

  // États des formulaires
  const [formData, setFormData] = useState({
    nom: "",
    type_entite_id: "",
    parent_id: "null",
    description: ""
  });

  const [typeFormData, setTypeFormData] = useState({
    nom: "",
    description: ""
  });

  const [posteFormData, setPosteFormData] = useState({
    nom: "",
    description: ""
  });

  const [chefFormData, setChefFormData] = useState({
    user_id: "",
    date_debut: "",
    terminer_mandat_precedent: false
  });

  const [terminerMandatData, setTerminerMandatData] = useState({
    date_fin: "",
    raison: ""
  });

  const [filtresUtilisateurs, setFiltresUtilisateurs] = useState({
    statut: "tous",
    role: "tous",
    include_historique: false
  });

  const { toast } = useToast();

  useEffect(() => {
    loadData();
  }, [currentPage, itemsPerPage, debouncedSearchTerm, selectedTypeFilter, currentPagePostes, itemsPerPagePostes, debouncedSearchTermPostes]);

  // Gestionnaires de pagination
  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  const handleItemsPerPageChange = (newItemsPerPage: number) => {
    setItemsPerPage(newItemsPerPage);
    setCurrentPage(1); // Reset à la première page
  };

  const handleSearch = (value: string) => {
    setSearchTerm(value);
    setCurrentPage(1); // Reset à la première page lors de la recherche
  };

  const handleTypeFilter = (typeId: string) => {
    setSelectedTypeFilter(typeId);
    setCurrentPage(1); // Reset à la première page lors du filtrage
  };

  // Gestionnaires de pagination pour les postes
  const handlePageChangePostes = (page: number) => {
    setCurrentPagePostes(page);
  };

  const handleItemsPerPageChangePostes = (newItemsPerPage: number) => {
    setItemsPerPagePostes(newItemsPerPage);
    setCurrentPagePostes(1); // Reset à la première page
  };

  const handleSearchPostes = (value: string) => {
    setSearchTermPostes(value);
    setCurrentPagePostes(1); // Reset à la première page lors de la recherche
  };

  const loadData = async () => {
    try {
      setLoading(true);
      
      // Paramètres pour l'API entités
      const entiteParams = {
        nom: debouncedSearchTerm || undefined,
        type_entite_id: selectedTypeFilter !== "tous" ? parseInt(selectedTypeFilter) : undefined,
        page: currentPage,
        per_page: itemsPerPage,
        sort_by: 'nom',
        sort_order: 'asc' as const
      };

      // Paramètres pour l'API postes
      const posteParams = {
        nom: debouncedSearchTermPostes || undefined,
        page: currentPagePostes,
        per_page: itemsPerPagePostes,
        sort_by: 'nom',
        sort_order: 'asc' as const
      };

      const [typeEntitesData, entitesResponse, usersData, postesResponse] = await Promise.all([
        apiClient.getTypeEntites(),
        apiClient.getEntitesDetailed(entiteParams),
        apiClient.getUsersDetailed(),
        apiClient.getPostes(posteParams)
      ]);

      setTypeEntites(typeEntitesData);
      setEntites(entitesResponse.data || []);
      setTotalPages(entitesResponse.pagination?.last_page || 1);
      setTotalItems(entitesResponse.pagination?.total || 0);
      setUsers(usersData.data || []);
      setPostes(postesResponse.data || []);
      setTotalPagesPostes(postesResponse.pagination?.last_page || 1);
      setTotalItemsPostes(postesResponse.pagination?.total || 0);
      

    } catch (error: any) {
      console.error("Erreur de chargement:", error);
      console.error("Type d'erreur:", typeof error);
      console.error("Nom de l'erreur:", error.name);
      console.error("Message de l'erreur:", error.message);
      console.error("Response:", error.response);
      console.error("Status:", error.response?.status);
      console.error("Data:", error.response?.data);
      
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const loadOrganigramme = async () => {
    try {
      const data = await apiClient.getOrganigramme();
      setOrganigramme(data);
    } catch (error) {
      console.error("Erreur organigramme:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleCreateEntite = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.nom.trim()) {
      toast({
        title: "❌ Erreur",
        description: "Le nom de l'entité est obligatoire",
        variant: "destructive"
      });
      return;
    }

    if (!formData.type_entite_id) {
      toast({
        title: "❌ Erreur", 
        description: "Le type d'entité est obligatoire",
        variant: "destructive"
      });
      return;
    }

    try {
      await apiClient.createEntite({
        nom: formData.nom.trim(),
        type_entite_id: parseInt(formData.type_entite_id),
        parent_id: formData.parent_id && formData.parent_id !== "null" ? parseInt(formData.parent_id) : undefined,
        description: formData.description.trim() || undefined
      });

      toast({
        title: "✅ Succès",
        description: "Entité créée avec succès"
      });

      setShowCreateModal(false);
      setFormData({ nom: "", type_entite_id: "", parent_id: "null", description: "" });
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdateEntite = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedEntite) return;

    try {
      await apiClient.updateEntite(selectedEntite.id, {
        nom: formData.nom.trim(),
        type_entite_id: parseInt(formData.type_entite_id),
        parent_id: formData.parent_id && formData.parent_id !== "null" ? parseInt(formData.parent_id) : undefined,
        description: formData.description.trim() || undefined
      });

      toast({
        title: "✅ Succès",
        description: "Entité mise à jour avec succès"
      });

      setShowEditModal(false);
      setSelectedEntite(null);
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeleteEntite = async () => {
    if (!entiteToDelete) return;

    try {
      await apiClient.deleteEntite(entiteToDelete.id);
      
      toast({
        title: "✅ Succès",
        description: "Entité supprimée avec succès"
      });

      setShowDeleteDialog(false);
      setEntiteToDelete(null);
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewDetails = async (entite: EntiteWithDetails) => {
    try {
      const detailData = await apiClient.getEntite(entite.id);
      setSelectedEntite(detailData);
      setShowDetailModal(true);
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewHierarchy = async (entite: EntiteWithDetails) => {
    try {
      const [hierarchyData, enfantsData] = await Promise.all([
        apiClient.getEntiteHierarchy(entite.id),
        apiClient.getEntiteEnfants(entite.id)
      ]);
      
      setEntiteHierarchy(hierarchyData);
      setEnfants(enfantsData.data);
      setSelectedEntite(entite);
      setShowHierarchyModal(true);
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openEditModal = (entite: EntiteWithDetails) => {
    setSelectedEntite(entite);
    setFormData({
      nom: entite.nom,
      type_entite_id: entite.type_entite.id.toString(),
      parent_id: entite.parent?.id?.toString() || "null",
      description: entite.description || ""
    });
    setShowEditModal(true);
  };

  const openDeleteDialog = (entite: EntiteWithDetails) => {
    setEntiteToDelete(entite);
    setShowDeleteDialog(true);
  };

  // ==========================================
  // GESTION DES CHEFS
  // ==========================================

  const loadChefsActuels = async () => {
    try {
      const data = await apiClient.getChefsActuels();
      setChefsActuels(data);
    } catch (error) {
      console.error("Erreur chefs actuels:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleAffecterChef = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedEntiteForAction || !chefFormData.user_id || !chefFormData.date_debut) {
      toast({
        title: "❌ Erreur",
        description: "Tous les champs sont obligatoires",
        variant: "destructive"
      });
      return;
    }

    try {
      await apiClient.affecterChef(selectedEntiteForAction.id, {
        user_id: parseInt(chefFormData.user_id),
        date_debut: chefFormData.date_debut,
        terminer_mandat_precedent: chefFormData.terminer_mandat_precedent
      });

      toast({
        title: "✅ Succès",
        description: "Chef affecté avec succès",
      });

      setShowChefModal(false);
      setChefFormData({ user_id: "", date_debut: "", terminer_mandat_precedent: false });
      loadData();
      loadChefsActuels();
    } catch (error) {
      console.error("Erreur affectation chef:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleTerminerMandat = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedEntiteForAction || !terminerMandatData.date_fin) {
      toast({
        title: "❌ Erreur",
        description: "La date de fin est obligatoire",
        variant: "destructive"
      });
      return;
    }

    try {
      await apiClient.terminerMandatChef(selectedEntiteForAction.id, {
        date_fin: terminerMandatData.date_fin,
        raison: terminerMandatData.raison
      });

      toast({
        title: "✅ Succès",
        description: "Mandat de chef terminé avec succès",
      });

      setShowTerminerMandatModal(false);
      setTerminerMandatData({ date_fin: "", raison: "" });
      loadData();
      loadChefsActuels();
    } catch (error) {
      console.error("Erreur fin mandat:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const loadHistoriqueChefs = async (entite: EntiteWithDetails) => {
    try {
      const data = await apiClient.getHistoriqueChefs(entite.id);
      setChefHistory(data);
      setSelectedEntiteForAction(entite);
      setShowChefHistoryModal(true);
    } catch (error) {
      console.error("Erreur historique chefs:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  // ==========================================
  // GESTION DES UTILISATEURS
  // ==========================================

  const loadUtilisateursEntite = async (entite: EntiteWithDetails) => {
    try {
      const params = {
        statut: filtresUtilisateurs.statut,
        role: filtresUtilisateurs.role,
        include_historique: filtresUtilisateurs.include_historique
      };
      const data = await apiClient.getUtilisateursEntite(entite.id, params);
      setUtilisateursEntite(data);
      setSelectedEntiteForAction(entite);
      setShowUtilisateursModal(true);
    } catch (error) {
      console.error("Erreur utilisateurs entité:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openChefModal = (entite: EntiteWithDetails) => {
    setSelectedEntiteForAction(entite);
    setShowChefModal(true);
  };

  const openTerminerMandatModal = (entite: EntiteWithDetails) => {
    setSelectedEntiteForAction(entite);
    setShowTerminerMandatModal(true);
  };

  // ==========================================
  // GESTION DES TYPES D'ENTITÉS
  // ==========================================

  const handleCreateTypeEntite = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!typeFormData.nom.trim()) {
      toast({
        title: "❌ Erreur",
        description: "Le nom du type d'entité est obligatoire",
        variant: "destructive"
      });
      return;
    }

    try {
      await apiClient.createTypeEntite({
        nom: typeFormData.nom.trim(),
        description: typeFormData.description.trim() || undefined
      });

      toast({
        title: "✅ Succès",
        description: "Type d'entité créé avec succès"
      });

      setShowCreateTypeModal(false);
      setTypeFormData({ nom: "", description: "" });
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdateTypeEntite = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTypeEntite) return;

    try {
      await apiClient.updateTypeEntite(selectedTypeEntite.id, {
        nom: typeFormData.nom.trim(),
        description: typeFormData.description.trim() || undefined
      });

      toast({
        title: "✅ Succès",
        description: "Type d'entité mis à jour avec succès"
      });

      setShowEditTypeModal(false);
      setSelectedTypeEntite(null);
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeleteTypeEntite = async () => {
    if (!typeEntiteToDelete) return;

    try {
      await apiClient.deleteTypeEntite(typeEntiteToDelete.id);
      
      toast({
        title: "✅ Succès",
        description: "Type d'entité supprimé avec succès"
      });

      setShowDeleteTypeDialog(false);
      setTypeEntiteToDelete(null);
      loadData();
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewTypeDetails = async (type: TypeEntite) => {
    try {
      const detailData = await apiClient.getTypeEntite(type.id);
      setSelectedTypeEntite(detailData);
      setShowDetailTypeModal(true);
    } catch (error) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openEditTypeModal = (type: TypeEntite) => {
    setSelectedTypeEntite(type);
    setTypeFormData({
      nom: type.nom,
      description: type.description || ""
    });
    setShowEditTypeModal(true);
  };

  const openDeleteTypeDialog = (type: TypeEntite) => {
    setTypeEntiteToDelete(type);
    setShowDeleteTypeDialog(true);
  };

  // ============================================
  // FONCTIONS GESTION DES POSTES
  // ============================================

  const handleCreatePoste = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await apiClient.createPoste(posteFormData);
      await loadData();
      setShowCreatePosteModal(false);
      setPosteFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Poste créé avec succès"
      });
    } catch (error: any) {
      console.error("Erreur création poste:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdatePoste = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPoste) return;
    
    try {
      await apiClient.updatePoste(selectedPoste.id, posteFormData);
      await loadData();
      setShowEditPosteModal(false);
      setSelectedPoste(null);
      setPosteFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Poste modifié avec succès"
      });
    } catch (error: any) {
      console.error("Erreur modification poste:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeletePoste = async () => {
    if (!posteToDelete) return;
    
    try {
      await apiClient.deletePoste(posteToDelete.id);
      await loadData();
      setShowDeletePosteDialog(false);
      setPosteToDelete(null);
      toast({
        title: "✅ Succès",
        description: "Poste supprimé avec succès"
      });
    } catch (error: any) {
      console.error("Erreur suppression poste:", error);
      toast({
        title: "❌ Erreur", 
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewPosteDetails = async (poste: PosteWithDetails) => {
    try {
      const response = await apiClient.getPoste(poste.id);
      setSelectedPoste(response);
      setShowDetailPosteModal(true);
    } catch (error: any) {
      console.error("Erreur détails poste:", error);
      toast({
        title: "❌ Erreur",
        description: "Impossible de récupérer les détails du poste",
        variant: "destructive"
      });
    }
  };

  const openEditPosteModal = (poste: PosteWithDetails) => {
    setSelectedPoste(poste);
    setPosteFormData({
      nom: poste.nom,
      description: poste.description || ""
    });
    setShowEditPosteModal(true);
  };

  const openDeletePosteDialog = (poste: PosteWithDetails) => {
    setPosteToDelete(poste);
    setShowDeletePosteDialog(true);
  };

  const filteredPostes = postes.filter(poste =>
    poste.nom.toLowerCase().includes(searchTermPostes.toLowerCase()) ||
    (poste.description || "").toLowerCase().includes(searchTermPostes.toLowerCase())
  );

  const filteredEntites = entites.filter(entite => {
    const matchesSearch = entite.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         entite.type_entite.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         entite.description?.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesType = selectedTypeFilter === "tous" || entite.type_entite.id.toString() === selectedTypeFilter;
    
    return matchesSearch && matchesType;
  });

  if (loading) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar
            name="Gestion des Entités"
            sidebarOpen={sidebarOpen}
            setSidebarOpen={setSidebarOpen}
          />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
      <div className="flex items-center justify-center h-64">
        <span className="ml-2">Chargement des entités...</span>
            </div>
          </main>
        </div>
      </div>
    );
  }

  return (
    <ProtectedPage permission="view_entities_list">
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar
            name="Gestion des Entités"
            sidebarOpen={sidebarOpen}
            setSidebarOpen={setSidebarOpen}
          />

          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Gestion des Entités</h1>
          <p className="text-muted-foreground">
            Gérer la structure organisationnelle et la hiérarchie
          </p>
        </div>
        <CreateEntityGuard>
          <Button onClick={() => setShowCreateModal(true)}>
            <Plus className="h-4 w-4 mr-2" />
            Nouvelle Entité
          </Button>
        </CreateEntityGuard>
      </div>

      {/* Statistiques */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Types d'Entité</CardTitle>
            <Building2 className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{typeEntites.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Entités</CardTitle>
            <Building className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{entites.length}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avec Chef</CardTitle>
            <Crown className="h-4 w-4 text-amber-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {entites.filter(e => e.chef_actuel).length}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Entités Racines</CardTitle>
            <TreePine className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {entites.filter(e => !e.parent).length}
            </div>
          </CardContent>
        </Card>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList>
          <TabsTrigger value="entites">Entités</TabsTrigger>
          <TabsTrigger value="types">Types d'Entité</TabsTrigger>
          <TabsTrigger value="postes">
            <Briefcase className="h-4 w-4 mr-2" />
            Postes
          </TabsTrigger>
          <ViewEntityHierarchyGuard>
            <TabsTrigger value="organigramme" onClick={loadOrganigramme}>Organigramme</TabsTrigger>
          </ViewEntityHierarchyGuard>
          <ManageEntityAssignmentsGuard>
            <TabsTrigger value="chefs" onClick={loadChefsActuels}>Chefs Actuels</TabsTrigger>
          </ManageEntityAssignmentsGuard>
        </TabsList>

        <TabsContent value="entites" className="space-y-4">
          {/* Filtres et recherche */}
          <Card>
            <CardContent className="pt-6">
              <div className="flex gap-4">
                <div className="flex-1">
                  <div className="relative">
                    <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      placeholder="Rechercher une entité..."
                      value={searchTerm}
                      onChange={(e) => handleSearch(e.target.value)}
                      className="pl-9"
                    />
                  </div>
                </div>
                <div className="w-48">
                  <Select value={selectedTypeFilter} onValueChange={handleTypeFilter}>
                    <SelectTrigger>
                      <SelectValue placeholder="Filtrer par type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="tous">Tous les types</SelectItem>
                      {typeEntites.map(type => (
                        <SelectItem key={type.id} value={type.id.toString()}>
                          {type.nom}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Liste des entités */}
          <Card>
            <CardHeader>
              <CardTitle>Liste des Entités ({filteredEntites.length})</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {filteredEntites.map((entite) => (
                  <div key={entite.id} className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                    <div className="flex justify-between items-start">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-2">
                          <h3 className="font-semibold text-lg">{entite.nom}</h3>
                          <Badge variant="outline">
                            {entite.type_entite.nom}
                          </Badge>
                          {entite.chef_actuel && (
                            <Badge className="bg-amber-100 text-amber-800">
                              <Crown className="h-3 w-3 mr-1" />
                              Chef assigné
                            </Badge>
                          )}
                        </div>

                        {entite.parent && (
                          <p className="text-sm text-muted-foreground mb-1">
                            <MapPin className="h-3 w-3 inline mr-1" />
                            Parent: {entite.parent.nom}
                          </p>
                        )}

                        {entite.description && (
                          <p className="text-sm text-muted-foreground mb-2">
                            {entite.description}
                          </p>
                        )}

                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <Users className="h-4 w-4" />
                            <span>{entite.employes_actuels?.length || 0} employés</span>
                      </div>
                          {(entite.nombre_enfants || 0) > 0 && (
                            <div className="flex items-center gap-1">
                              <GitBranch className="h-4 w-4" />
                              <span>{entite.nombre_enfants} sous-entités</span>
                            </div>
                          )}
                          <div className="flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Créée le {new Date(entite.date_creation).toLocaleDateString('fr-FR')}</span>
                          </div>
                        </div>
                      </div>

                      <div className="flex flex-col items-end gap-2">
                        {entite.chef_actuel && (
                          <div className="text-right">
                            <div className="text-sm font-medium">Chef actuel</div>
                            <div className="text-sm text-muted-foreground">
                              {entite.chef_actuel?.user?.prenom || entite.chef_actuel?.prenom} {entite.chef_actuel?.user?.nom || entite.chef_actuel?.nom}
                            </div>
                          </div>
                        )}

                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <ViewEntityDetailsGuard>
                              <DropdownMenuItem onClick={() => handleViewDetails(entite)}>
                                <Eye className="h-4 w-4 mr-2" />
                                Voir détails
                              </DropdownMenuItem>
                            </ViewEntityDetailsGuard>
                            <EditEntityGuard>
                              <DropdownMenuItem onClick={() => openEditModal(entite)}>
                                <Edit className="h-4 w-4 mr-2" />
                                Modifier
                              </DropdownMenuItem>
                            </EditEntityGuard>
                            <ViewEntityHierarchyGuard>
                              <DropdownMenuItem onClick={() => handleViewHierarchy(entite)}>
                                <Network className="h-4 w-4 mr-2" />
                                Voir hiérarchie
                              </DropdownMenuItem>
                            </ViewEntityHierarchyGuard>
                            <ViewEntityUsersGuard>
                              <DropdownMenuItem onClick={() => loadUtilisateursEntite(entite)}>
                                <Users className="h-4 w-4 mr-2" />
                                Voir utilisateurs
                              </DropdownMenuItem>
                            </ViewEntityUsersGuard>
                            <ManageEntityAssignmentsGuard>
                              <DropdownMenuItem onClick={() => openChefModal(entite)}>
                                <UserPlus className="h-4 w-4 mr-2" />
                                Affecter chef
                              </DropdownMenuItem>
                              {entite.chef_actuel && (
                                <DropdownMenuItem onClick={() => openTerminerMandatModal(entite)}>
                                  <Clock className="h-4 w-4 mr-2" />
                                  Terminer mandat
                                </DropdownMenuItem>
                              )}
                            </ManageEntityAssignmentsGuard>
                            <ViewEntityChiefHistoryGuard>
                              <DropdownMenuItem onClick={() => loadHistoriqueChefs(entite)}>
                                <History className="h-4 w-4 mr-2" />
                                Historique chefs
                              </DropdownMenuItem>
                            </ViewEntityChiefHistoryGuard>
                            <ConditionalSeparator showIfAnyVisible={entityPermissions.canDelete} />
                            <DeleteEntityGuard>
                              <DropdownMenuItem onClick={() => openDeleteDialog(entite)} className="text-destructive">
                                <Trash2 className="h-4 w-4 mr-2" />
                                Supprimer
                              </DropdownMenuItem>
                            </DeleteEntityGuard>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
              
              {filteredEntites.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                  Aucune entité trouvée
                </div>
              )}

                                  {/* Pagination */}
                    
                    {/* Forcer l'affichage pour test */}
                    {true && (
                <div className="flex items-center justify-between mt-6">
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>
                      Affichage de {((currentPage - 1) * itemsPerPage) + 1} à {Math.min(currentPage * itemsPerPage, totalItems)} sur {totalItems} entités
                    </span>
                  </div>
                  
                  <div className="flex items-center gap-2">
                    <Select value={itemsPerPage.toString()} onValueChange={(value) => handleItemsPerPageChange(parseInt(value))}>
                      <SelectTrigger className="w-20">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="10">10</SelectItem>
                        <SelectItem value="15">15</SelectItem>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                      </SelectContent>
                    </Select>
                    
                    <div className="flex items-center gap-1">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(1)}
                        disabled={currentPage === 1}
                      >
                        <ChevronFirst className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(currentPage - 1)}
                        disabled={currentPage === 1}
                      >
                        <ChevronLeft className="h-4 w-4" />
                      </Button>
                      
                      <div className="flex items-center gap-1">
                        {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                          let pageNum;
                          if (totalPages <= 5) {
                            pageNum = i + 1;
                          } else if (currentPage <= 3) {
                            pageNum = i + 1;
                          } else if (currentPage >= totalPages - 2) {
                            pageNum = totalPages - 4 + i;
                          } else {
                            pageNum = currentPage - 2 + i;
                          }
                          
                          return (
                            <Button
                              key={pageNum}
                              variant={currentPage === pageNum ? "default" : "outline"}
                              size="sm"
                              onClick={() => handlePageChange(pageNum)}
                              className="w-8 h-8"
                            >
                              {pageNum}
                            </Button>
                          );
                        })}
                      </div>
                      
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(currentPage + 1)}
                        disabled={currentPage === totalPages}
                      >
                        <ChevronRight className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChange(totalPages)}
                        disabled={currentPage === totalPages}
                      >
                        <ChevronLast className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="types" className="space-y-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle>Types d'Entité ({typeEntites.length})</CardTitle>
              <CreateEntityGuard>
                <Button onClick={() => setShowCreateTypeModal(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Nouveau type
                </Button>
              </CreateEntityGuard>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {typeEntites.map((type) => (
                  <Card key={type.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="pt-4">
                      <div className="flex justify-between items-start mb-2">
                    <h3 className="font-semibold">{type.nom}</h3>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <ViewEntityDetailsGuard>
                              <DropdownMenuItem onClick={() => handleViewTypeDetails(type)}>
                                <Eye className="h-4 w-4 mr-2" />
                                Voir détails
                              </DropdownMenuItem>
                            </ViewEntityDetailsGuard>
                            <EditEntityGuard>
                              <DropdownMenuItem onClick={() => openEditTypeModal(type)}>
                                <Edit className="h-4 w-4 mr-2" />
                                Modifier
                              </DropdownMenuItem>
                            </EditEntityGuard>
                            <ConditionalSeparator showIfAnyVisible={entityPermissions.canDelete} />
                            <DeleteEntityGuard>
                              <DropdownMenuItem 
                                onClick={() => openDeleteTypeDialog(type)} 
                                className="text-destructive"
                                disabled={entites.filter(e => e.type_entite.id === type.id).length > 0}
                              >
                                <Trash2 className="h-4 w-4 mr-2" />
                                Supprimer
                              </DropdownMenuItem>
                            </DeleteEntityGuard>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                      
                    {type.description && (
                        <p className="text-sm text-muted-foreground mb-3">
                        {type.description}
                      </p>
                    )}
                      
                      <div className="flex justify-between items-center">
                        <Badge variant="secondary">
                          {entites.filter(e => e.type_entite.id === type.id).length} entités
                        </Badge>
                        
                        <div className="text-xs text-muted-foreground">
                          Créé le {new Date(type.date_creation).toLocaleDateString('fr-FR')}
                    </div>
                  </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
              
              {typeEntites.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                  <Package className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                  <p>Aucun type d'entité trouvé</p>
                  <Button 
                    variant="outline" 
                    className="mt-4"
                    onClick={() => setShowCreateTypeModal(true)}
                  >
                    <Plus className="h-4 w-4 mr-2" />
                    Créer le premier type
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="postes" className="space-y-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Briefcase className="h-5 w-5" />
                Postes ({postes.length})
              </CardTitle>
              <CreateEntityGuard>
                <Button onClick={() => setShowCreatePosteModal(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Nouveau Poste
                </Button>
              </CreateEntityGuard>
            </CardHeader>
            <CardContent>
              {/* Recherche */}
              <div className="mb-4">
                <div className="relative">
                  <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Rechercher un poste..."
                    value={searchTermPostes}
                    onChange={(e) => handleSearchPostes(e.target.value)}
                    className="pl-9"
                  />
                </div>
              </div>

              {/* Statistiques des postes */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <Card>
                  <CardContent className="pt-4">
                    <div className="text-center">
                      <div className="text-2xl font-bold">{postes.length}</div>
                      <div className="text-sm text-muted-foreground">Total Postes</div>
                    </div>
                  </CardContent>
                </Card>
                <Card>
                  <CardContent className="pt-4">
                    <div className="text-center">
                      <div className="text-2xl font-bold">
                        {postes.filter(p => (p.nombre_affectations_actives || 0) > 0).length}
                      </div>
                      <div className="text-sm text-muted-foreground">Postes Occupés</div>
                    </div>
                  </CardContent>
                </Card>
                <Card>
                  <CardContent className="pt-4">
                    <div className="text-center">
                      <div className="text-2xl font-bold">
                        {postes.reduce((sum, p) => sum + (p.statistiques?.total_affectations || 0), 0)}
                      </div>
                      <div className="text-sm text-muted-foreground">Total Affectations</div>
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Liste des postes */}
              <div className="space-y-4">
                {filteredPostes.map((poste) => (
                  <Card key={poste.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="pt-4">
                      <div className="flex justify-between items-start">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <h3 className="font-semibold text-lg">{poste.nom}</h3>
                            <Badge variant={(poste.nombre_affectations_actives || 0) > 0 ? "default" : "secondary"}>
                              {(poste.nombre_affectations_actives || 0) > 0 ? "Occupé" : "Disponible"}
                            </Badge>
                          </div>
                          
                          {poste.description && (
                            <p className="text-sm text-muted-foreground mb-2">
                              {poste.description}
                            </p>
                          )}
                          
                          <div className="flex items-center gap-4 text-sm text-muted-foreground">
                            <div className="flex items-center gap-1">
                              <Users className="h-4 w-4" />
                              <span>{poste.statistiques?.total_affectations || 0} affectations totales</span>
                            </div>
                            <div className="flex items-center gap-1">
                              <Calendar className="h-4 w-4" />
                              <span>Créé le {new Date(poste.date_creation).toLocaleDateString('fr-FR')}</span>
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
                            <ViewEntityDetailsGuard>
                              <DropdownMenuItem onClick={() => handleViewPosteDetails(poste)}>
                                <Eye className="h-4 w-4 mr-2" />
                                Voir détails
                              </DropdownMenuItem>
                            </ViewEntityDetailsGuard>
                            <EditEntityGuard>
                              <DropdownMenuItem onClick={() => openEditPosteModal(poste)}>
                                <Edit className="h-4 w-4 mr-2" />
                                Modifier
                              </DropdownMenuItem>
                            </EditEntityGuard>
                            <Separator />
                            <DeleteEntityGuard>
                              <DropdownMenuItem 
                                onClick={() => openDeletePosteDialog(poste)} 
                                className="text-destructive"
                              >
                                <Trash2 className="h-4 w-4 mr-2" />
                                Supprimer
                              </DropdownMenuItem>
                            </DeleteEntityGuard>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
              
              {filteredPostes.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                  <Briefcase className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                  <p>
                    {searchTermPostes 
                      ? 'Aucun poste trouvé pour cette recherche' 
                      : 'Aucun poste trouvé'
                    }
                  </p>
                  {!searchTermPostes && (
                    <Button 
                      variant="outline" 
                      className="mt-4"
                      onClick={() => setShowCreatePosteModal(true)}
                    >
                      <Plus className="h-4 w-4 mr-2" />
                      Créer le premier poste
                    </Button>
                  )}
                </div>
              )}

                    
                    {/* Forcer l'affichage pour test */}
                    {true && (
                <div className="flex items-center justify-between mt-6">
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>
                      Affichage de {((currentPagePostes - 1) * itemsPerPagePostes) + 1} à {Math.min(currentPagePostes * itemsPerPagePostes, totalItemsPostes)} sur {totalItemsPostes} postes
                    </span>
                  </div>
                  
                  <div className="flex items-center gap-2">
                    <Select value={itemsPerPagePostes.toString()} onValueChange={(value) => handleItemsPerPageChangePostes(parseInt(value))}>
                      <SelectTrigger className="w-20">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="10">10</SelectItem>
                        <SelectItem value="15">15</SelectItem>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                      </SelectContent>
                    </Select>
                    
                    <div className="flex items-center gap-1">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChangePostes(1)}
                        disabled={currentPagePostes === 1}
                      >
                        <ChevronFirst className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChangePostes(currentPagePostes - 1)}
                        disabled={currentPagePostes === 1}
                      >
                        <ChevronLeft className="h-4 w-4" />
                      </Button>
                      
                      <div className="flex items-center gap-1">
                        {Array.from({ length: Math.min(5, totalPagesPostes) }, (_, i) => {
                          let pageNum;
                          if (totalPagesPostes <= 5) {
                            pageNum = i + 1;
                          } else if (currentPagePostes <= 3) {
                            pageNum = i + 1;
                          } else if (currentPagePostes >= totalPagesPostes - 2) {
                            pageNum = totalPagesPostes - 4 + i;
                          } else {
                            pageNum = currentPagePostes - 2 + i;
                          }
                          
                          return (
                            <Button
                              key={pageNum}
                              variant={currentPagePostes === pageNum ? "default" : "outline"}
                              size="sm"
                              onClick={() => handlePageChangePostes(pageNum)}
                              className="w-8 h-8"
                            >
                              {pageNum}
                            </Button>
                          );
                        })}
                      </div>
                      
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChangePostes(currentPagePostes + 1)}
                        disabled={currentPagePostes === totalPagesPostes}
                      >
                        <ChevronRight className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handlePageChangePostes(totalPagesPostes)}
                        disabled={currentPagePostes === totalPagesPostes}
                      >
                        <ChevronLast className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <ViewEntityHierarchyGuard>
          <TabsContent value="organigramme" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <TreePine className="h-5 w-5" />
                  Organigramme Organisationnel
                </CardTitle>
              </CardHeader>
              <CardContent>
                {organigramme ? (
                  <div className="space-y-4">
                    {/* Statistiques globales */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-muted/50 rounded-lg">
                      <div className="text-center">
                        <div className="text-2xl font-bold">{organigramme.statistiques?.total_entites || 0}</div>
                        <div className="text-sm text-muted-foreground">Total entités</div>
                      </div>
                      <div className="text-center">
                        <div className="text-2xl font-bold">{organigramme.statistiques?.total_employes_actifs || 0}</div>
                        <div className="text-sm text-muted-foreground">Employés actifs</div>
                      </div>
                      <div className="text-center">
                        <div className="text-2xl font-bold">{organigramme.statistiques?.total_chefs_actuels || 0}</div>
                        <div className="text-sm text-muted-foreground">Chefs actuels</div>
                      </div>
                      <div className="text-center">
                        <div className="text-2xl font-bold">{organigramme.statistiques?.profondeur_max || 0}</div>
                        <div className="text-sm text-muted-foreground">Niveaux max</div>
                      </div>
                    </div>

                    {/* Arbre hiérarchique */}
                    <div className="p-4 border rounded-lg">
                      <h4 className="font-medium mb-4">Structure Hiérarchique</h4>
                      {organigramme.organigramme && organigramme.organigramme.map((node: OrganigrammeNodeData) => (
                        <OrganigrammeNode key={node.id} node={node} level={0} />
                      ))}
                    </div>
                  </div>
                ) : (
                <div className="text-center py-8 text-muted-foreground">
                  <TreePine className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                    <p>Chargement de l'organigramme...</p>
                </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </ViewEntityHierarchyGuard>

        <ManageEntityAssignmentsGuard>
          <TabsContent value="chefs" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Chefs Actuels ({chefsActuels.length})</CardTitle>
              </CardHeader>
              <CardContent>
                {chefsActuels.length > 0 ? (
                  <div className="space-y-4">
                    {chefsActuels.map((chef) => (
                      <div key={chef.id} className="p-4 border rounded-lg">
                        <div className="flex justify-between items-start">
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                              <Crown className="h-5 w-5 text-amber-600" />
                              <h3 className="font-semibold text-lg">
                                {chef.chef?.prenom} {chef.chef?.nom}
                              </h3>
                              <Badge variant="outline">{chef.chef?.matricule}</Badge>
                            </div>
                            
                            <div className="space-y-1 text-sm text-muted-foreground">
                              <p><strong>Entité:</strong> {chef.entite.nom}</p>
                              <p><strong>Type:</strong> {chef.entite.type}</p>
                              <p><strong>Email:</strong> {chef.chef?.email}</p>
                              <p><strong>Depuis:</strong> {new Date(chef.date_debut).toLocaleDateString('fr-FR')}</p>
                              <p><strong>Durée:</strong> {chef.duree_mandat}</p>
                            </div>
                          </div>
                          
                          <div className="flex flex-col gap-2">
                            <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => openTerminerMandatModal({
                                id: chef.entite.id,
                                nom: chef.entite.nom,
                                chef_actuel: chef.chef
                              } as EntiteWithDetails)}
                            >
                              <Clock className="h-4 w-4 mr-2" />
                              Terminer mandat
                            </Button>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    Aucun chef actuellement en poste
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </ManageEntityAssignmentsGuard>
      </Tabs>

      {/* Modal de création */}
      <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Créer une nouvelle entité</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleCreateEntite} className="space-y-4">
            <div>
              <Label htmlFor="nom">Nom de l'entité *</Label>
              <Input
                id="nom"
                value={formData.nom}
                onChange={(e) => setFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Nom de l'entité"
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="type_entite_id">Type d'entité *</Label>
              <Select value={formData.type_entite_id} onValueChange={(value) => setFormData(prev => ({ ...prev, type_entite_id: value }))}>
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un type" />
                </SelectTrigger>
                <SelectContent>
                  {typeEntites.map(type => (
                    <SelectItem key={type.id} value={type.id.toString()}>
                      {type.nom}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label htmlFor="parent_id">Entité parente</Label>
              <Select value={formData.parent_id} onValueChange={(value) => setFormData(prev => ({ ...prev, parent_id: value }))}>
                <SelectTrigger>
                  <SelectValue placeholder="Aucune (entité racine)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="null">Aucune (entité racine)</SelectItem>
                  {entites.map(entite => (
                    <SelectItem key={entite.id} value={entite.id.toString()}>
                      {entite.nom} ({entite.type_entite.nom})
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description de l'entité (optionnel)"
                rows={3}
              />
            </div>

            <div className="flex justify-end space-x-2">
              <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                Créer
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de modification */}
      <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Modifier l'entité</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleUpdateEntite} className="space-y-4">
            <div>
              <Label htmlFor="edit-nom">Nom de l'entité *</Label>
              <Input
                id="edit-nom"
                value={formData.nom}
                onChange={(e) => setFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Nom de l'entité"
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="edit-type">Type d'entité *</Label>
              <Select value={formData.type_entite_id} onValueChange={(value) => setFormData(prev => ({ ...prev, type_entite_id: value }))}>
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un type" />
                </SelectTrigger>
                <SelectContent>
                  {typeEntites.map(type => (
                    <SelectItem key={type.id} value={type.id.toString()}>
                      {type.nom}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label htmlFor="edit-parent">Entité parente</Label>
              <Select value={formData.parent_id} onValueChange={(value) => setFormData(prev => ({ ...prev, parent_id: value }))}>
                <SelectTrigger>
                  <SelectValue placeholder="Aucune (entité racine)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="null">Aucune (entité racine)</SelectItem>
                  {entites.filter(e => e.id !== selectedEntite?.id).map(entite => (
                    <SelectItem key={entite.id} value={entite.id.toString()}>
                      {entite.nom} ({entite.type_entite.nom})
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label htmlFor="edit-description">Description</Label>
              <Textarea
                id="edit-description"
                value={formData.description}
                onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description de l'entité (optionnel)"
                rows={3}
              />
            </div>

            <div className="flex justify-end space-x-2">
              <Button type="button" variant="outline" onClick={() => setShowEditModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                Sauvegarder
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de détails */}
      <Dialog open={showDetailModal} onOpenChange={setShowDetailModal}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Détails de l'entité</DialogTitle>
          </DialogHeader>
          {selectedEntite && (
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Nom</Label>
                  <p className="text-sm text-muted-foreground">{selectedEntite.nom}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Type</Label>
                  <p className="text-sm text-muted-foreground">{selectedEntite.type_entite.nom}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Parent</Label>
                  <p className="text-sm text-muted-foreground">
                    {selectedEntite.parent ? selectedEntite.parent.nom : "Aucun (entité racine)"}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Date de création</Label>
                  <p className="text-sm text-muted-foreground">
                    {new Date(selectedEntite.date_creation).toLocaleDateString('fr-FR')}
                  </p>
                </div>
              </div>

              {selectedEntite.description && (
                <div>
                  <Label className="text-sm font-medium">Description</Label>
                  <p className="text-sm text-muted-foreground">{selectedEntite.description}</p>
                </div>
              )}

              <Separator />

              <div className="space-y-4">
                <h4 className="font-medium">Chef actuel</h4>
                {selectedEntite.chef_actuel ? (
                  <div className="p-3 border rounded-lg">
                    <p className="font-medium">
                      {selectedEntite.chef_actuel?.user?.prenom || selectedEntite.chef_actuel?.prenom} {selectedEntite.chef_actuel?.user?.nom || selectedEntite.chef_actuel?.nom}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Matricule: {selectedEntite.chef_actuel?.user?.matricule || selectedEntite.chef_actuel?.matricule}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Depuis le: {new Date(selectedEntite.chef_actuel.date_debut).toLocaleDateString('fr-FR')}
                    </p>
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">Aucun chef assigné</p>
                )}
              </div>

              <div className="space-y-4">
                <h4 className="font-medium">Employés actuels ({selectedEntite.employes_actuels?.length || 0})</h4>
                {selectedEntite.employes_actuels && selectedEntite.employes_actuels.length > 0 ? (
                  <div className="space-y-2">
                    {selectedEntite.employes_actuels.map((employe, index) => (
                      <div key={index} className="p-3 border rounded-lg">
                        <p className="font-medium">
                          {employe.user?.prenom} {employe.user?.nom}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Poste: {employe.poste} | Matricule: {employe.user?.matricule}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Depuis le: {new Date(employe.date_debut).toLocaleDateString('fr-FR')}
                        </p>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">Aucun employé affecté</p>
                )}
              </div>

              {selectedEntite.enfants && selectedEntite.enfants.length > 0 && (
                <div className="space-y-4">
                  <h4 className="font-medium">Sous-entités ({selectedEntite.enfants.length})</h4>
                  <div className="space-y-2">
                    {selectedEntite.enfants.map((enfant) => (
                      <div key={enfant.id} className="p-3 border rounded-lg">
                        <p className="font-medium">{enfant.nom}</p>
                        <p className="text-sm text-muted-foreground">
                          {enfant.type_entite.nom} | {enfant.nombre_enfants || 0} sous-entités
                        </p>
                        {enfant.description && (
                          <p className="text-xs text-muted-foreground mt-1">
                            {enfant.description}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Modal de hiérarchie */}
      <Dialog open={showHierarchyModal} onOpenChange={setShowHierarchyModal}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Hiérarchie de l'entité</DialogTitle>
          </DialogHeader>
          {entiteHierarchy && selectedEntite && (
            <div className="space-y-6">
              {/* Entité actuelle */}
              <div className="text-center p-4 bg-primary/10 rounded-lg">
                <h3 className="font-bold text-lg">{entiteHierarchy.entite_actuelle.nom}</h3>
                <p className="text-sm text-muted-foreground">
                  {entiteHierarchy.entite_actuelle.type_entite}
                </p>
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                {/* Parents */}
                <div>
                  <h4 className="font-medium mb-3 flex items-center gap-2">
                    <ChevronRight className="h-4 w-4" />
                    Entités parentes
                  </h4>
                  {entiteHierarchy.parents && entiteHierarchy.parents.length > 0 ? (
                    <div className="space-y-2">
                      {entiteHierarchy.parents.map((parent: any, index: number) => (
                        <div key={index} className="p-3 border rounded-lg">
                          <p className="font-medium">{parent.nom}</p>
                          <p className="text-sm text-muted-foreground">
                            Niveau {parent.niveau} - {parent.type_entite}
                          </p>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">Entité racine</p>
                  )}
                </div>

                {/* Enfants */}
                <div>
                  <h4 className="font-medium mb-3 flex items-center gap-2">
                    <ChevronDown className="h-4 w-4" />
                    Sous-entités ({enfants.length})
                  </h4>
                  {enfants.length > 0 ? (
                    <div className="space-y-2">
                      {enfants.map((enfant) => (
                        <div key={enfant.id} className="p-3 border rounded-lg">
                          <p className="font-medium">{enfant.nom}</p>
                          <p className="text-sm text-muted-foreground">
                            {enfant.type_entite.nom} | {enfant.nombre_enfants || 0} sous-entités
                          </p>
                          {enfant.description && (
                            <p className="text-xs text-muted-foreground mt-1">
                              {enfant.description}
                            </p>
                          )}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">Aucune sous-entité</p>
                  )}
                </div>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Dialog de suppression */}
      <AlertDialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer l'entité "{entiteToDelete?.nom}" ?
              Cette action est irréversible.
              {entiteToDelete?.nombre_enfants && entiteToDelete.nombre_enfants > 0 && (
                <div className="mt-2 p-3 bg-destructive/10 rounded border">
                  <p className="text-sm font-medium text-destructive">
                    ⚠️ Cette entité a {entiteToDelete.nombre_enfants} sous-entités. 
                    Vous devez d'abord les supprimer ou les réaffecter.
                  </p>
                </div>
              )}
              {entiteToDelete?.employes_actuels && entiteToDelete.employes_actuels.length > 0 && (
                <div className="mt-2 p-3 bg-destructive/10 rounded border">
                  <p className="text-sm font-medium text-destructive">
                    ⚠️ Cette entité a {entiteToDelete.employes_actuels.length} employés actifs. 
                    Vous devez d'abord terminer leurs affectations.
                  </p>
                </div>
              )}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction 
              onClick={handleDeleteEntite}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            >
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* Modal d'affectation de chef */}
      <Dialog open={showChefModal} onOpenChange={setShowChefModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Affecter un chef à l'entité</DialogTitle>
            <DialogDescription>
              Entité: {selectedEntiteForAction?.nom}
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={handleAffecterChef} className="space-y-4">
            <div>
              <Label htmlFor="user_id">Utilisateur *</Label>
              <Combobox
                options={users.map(user => ({
                  value: user.id.toString(),
                  label: `${user.prenom} ${user.nom}`,
                  description: `Matricule: ${user.matricule} - Email: ${user.email}`,
                  searchTerms: `${user.prenom} ${user.nom} ${user.matricule} ${user.email}`
                }))}
                value={chefFormData.user_id}
                onValueChange={(value) => setChefFormData({...chefFormData, user_id: value})}
                placeholder="Sélectionner un utilisateur"
                searchPlaceholder="Rechercher par nom, prénom, matricule..."
                emptyMessage="Aucun utilisateur trouvé"
              />
            </div>

            <div>
              <Label htmlFor="date_debut">Date de début *</Label>
              <Input
                id="date_debut"
                type="date"
                value={chefFormData.date_debut}
                onChange={(e) => setChefFormData({...chefFormData, date_debut: e.target.value})}
                required
              />
            </div>

            {selectedEntiteForAction?.chef_actuel && (
              <div className="flex items-center space-x-2">
                <input
                  type="checkbox"
                  id="terminer_mandat"
                  checked={chefFormData.terminer_mandat_precedent}
                  onChange={(e) => setChefFormData({...chefFormData, terminer_mandat_precedent: e.target.checked})}
                />
                <Label htmlFor="terminer_mandat">
                  Terminer automatiquement le mandat précédent
                </Label>
              </div>
            )}

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowChefModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                <UserCheck className="h-4 w-4 mr-2" />
                Affecter chef
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de fin de mandat */}
      <Dialog open={showTerminerMandatModal} onOpenChange={setShowTerminerMandatModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Terminer le mandat de chef</DialogTitle>
            <DialogDescription>
              Entité: {selectedEntiteForAction?.nom}
              {selectedEntiteForAction?.chef_actuel && (
                <div className="mt-2 p-3 bg-amber-50 rounded border">
                  <p className="text-sm">
                    <strong>Chef actuel:</strong> {selectedEntiteForAction.chef_actuel?.user?.prenom || selectedEntiteForAction.chef_actuel?.prenom} {selectedEntiteForAction.chef_actuel?.user?.nom || selectedEntiteForAction.chef_actuel?.nom}
                  </p>
                </div>
              )}
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={handleTerminerMandat} className="space-y-4">
            <div>
              <Label htmlFor="date_fin">Date de fin *</Label>
              <Input
                id="date_fin"
                type="date"
                value={terminerMandatData.date_fin}
                onChange={(e) => setTerminerMandatData({...terminerMandatData, date_fin: e.target.value})}
                required
              />
            </div>

            <div>
              <Label htmlFor="raison">Raison (optionnel)</Label>
              <Textarea
                id="raison"
                value={terminerMandatData.raison}
                onChange={(e) => setTerminerMandatData({...terminerMandatData, raison: e.target.value})}
                placeholder="Motif de fin de mandat..."
                rows={3}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowTerminerMandatModal(false)}>
                Annuler
              </Button>
              <Button type="submit" variant="destructive">
                <Clock className="h-4 w-4 mr-2" />
                Terminer mandat
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal historique des chefs */}
      <Dialog open={showChefHistoryModal} onOpenChange={setShowChefHistoryModal}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Historique des chefs</DialogTitle>
            <DialogDescription>
              Entité: {selectedEntiteForAction?.nom}
            </DialogDescription>
          </DialogHeader>
          <div className="max-h-96 overflow-y-auto">
            {chefHistory.length > 0 ? (
              <div className="space-y-4">
                {chefHistory.map((chef) => (
                  <div key={chef.id} className="p-4 border rounded-lg">
                    <div className="flex items-start justify-between">
                      <div className="space-y-2">
                        <div className="flex items-center gap-2">
                          <Crown className="h-4 w-4 text-amber-600" />
                          <span className="font-medium">
                            {chef.user?.prenom} {chef.user?.nom}
                          </span>
                          <Badge variant="outline">{chef.user?.matricule}</Badge>
                        </div>
                        <div className="text-sm text-muted-foreground">
                          <p><strong>Email:</strong> {chef.user?.email}</p>
                          <p><strong>Début:</strong> {new Date(chef.date_debut).toLocaleDateString('fr-FR')}</p>
                          {chef.date_fin && (
                            <p><strong>Fin:</strong> {new Date(chef.date_fin).toLocaleDateString('fr-FR')}</p>
                          )}
                        </div>
                      </div>
                      {chef.date_fin ? (
                        <Badge variant="secondary">Mandat terminé</Badge>
                      ) : (
                        <Badge className="bg-green-100 text-green-800">Actuel</Badge>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                Aucun historique de chef pour cette entité
              </div>
            )}
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal des utilisateurs de l'entité */}
      <Dialog open={showUtilisateursModal} onOpenChange={setShowUtilisateursModal}>
        <DialogContent className="max-w-6xl">
          <DialogHeader>
            <DialogTitle>Utilisateurs de l'entité</DialogTitle>
            <DialogDescription>
              Entité: {selectedEntiteForAction?.nom}
            </DialogDescription>
          </DialogHeader>
          
          {/* Filtres */}
          <div className="flex gap-4 mb-4">
            <div className="flex-1">
              <Label>Statut</Label>
              <Select value={filtresUtilisateurs.statut} onValueChange={(value) => 
                setFiltresUtilisateurs({...filtresUtilisateurs, statut: value})
              }>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="tous">Tous</SelectItem>
                  <SelectItem value="actuel">Actuels</SelectItem>
                  <SelectItem value="historique">Historique</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex-1">
              <Label>Rôle</Label>
              <Select value={filtresUtilisateurs.role} onValueChange={(value) => 
                setFiltresUtilisateurs({...filtresUtilisateurs, role: value})
              }>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="tous">Tous</SelectItem>
                  <SelectItem value="chef">Chefs</SelectItem>
                  <SelectItem value="employe">Employés</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex items-end">
              <Button 
                onClick={() => selectedEntiteForAction && loadUtilisateursEntite(selectedEntiteForAction)}
                className="mb-[2px]"
              >
                <Filter className="h-4 w-4 mr-2" />
                Filtrer
              </Button>
            </div>
          </div>

          <div className="max-h-96 overflow-y-auto">
            {utilisateursEntite ? (
              <div className="space-y-4">
                {utilisateursEntite.utilisateurs && utilisateursEntite.utilisateurs.length > 0 ? (
                  utilisateursEntite.utilisateurs.map((utilisateur: any) => (
                    <div key={`${utilisateur.user.id}-${utilisateur.type}`} className="p-4 border rounded-lg">
                      <div className="flex items-start justify-between">
                        <div className="space-y-2">
                          <div className="flex items-center gap-2">
                            {utilisateur.type === 'chef' ? (
                              <Crown className="h-4 w-4 text-amber-600" />
                            ) : (
                              <Users className="h-4 w-4 text-blue-600" />
                            )}
                            <span className="font-medium">
                              {utilisateur.user?.prenom} {utilisateur.user?.nom}
                            </span>
                            <Badge variant="outline">{utilisateur.user?.matricule}</Badge>
                            <Badge className={utilisateur.type === 'chef' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'}>
                              {utilisateur.type}
                            </Badge>
                          </div>
                          <div className="text-sm text-muted-foreground">
                            <p><strong>Email:</strong> {utilisateur.user?.email}</p>
                            {utilisateur.poste && (
                              <p><strong>Poste:</strong> {utilisateur.poste.nom}</p>
                            )}
                            <p><strong>Début:</strong> {new Date(utilisateur.date_debut).toLocaleDateString('fr-FR')}</p>
                            {utilisateur.date_fin && (
                              <p><strong>Fin:</strong> {new Date(utilisateur.date_fin).toLocaleDateString('fr-FR')}</p>
                            )}
                          </div>
                        </div>
                        <div className="flex flex-col gap-1">
                          {utilisateur.est_actuel ? (
                            <Badge className="bg-green-100 text-green-800">Actuel</Badge>
                          ) : (
                            <Badge variant="secondary">Historique</Badge>
                          )}
                        </div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    Aucun utilisateur trouvé pour ces critères
                  </div>
                )}
                
                {/* Statistiques */}
                {utilisateursEntite.statistiques && (
                  <div className="mt-4 p-4 bg-muted/50 rounded-lg">
                    <h4 className="font-medium mb-2">Statistiques</h4>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span className="text-muted-foreground">Total:</span>
                        <span className="font-medium ml-1">{utilisateursEntite.statistiques?.total_utilisateurs || 0}</span>
                      </div>
                      <div>
                        <span className="text-muted-foreground">Employés actuels:</span>
                        <span className="font-medium ml-1">{utilisateursEntite.statistiques?.employes_actuels || 0}</span>
                      </div>
                      <div>
                        <span className="text-muted-foreground">Chefs:</span>
                        <span className="font-medium ml-1">{utilisateursEntite.repartition?.chefs || 0}</span>
                      </div>
                      <div>
                        <span className="text-muted-foreground">Employés:</span>
                        <span className="font-medium ml-1">{utilisateursEntite.repartition?.employes || 0}</span>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                Chargement des utilisateurs...
              </div>
            )}
          </div>
        </DialogContent>
      </Dialog>

      {/* ========================================= */}
      {/* MODALES POUR LES TYPES D'ENTITÉS */}
      {/* ========================================= */}

      {/* Modal de création de type d'entité */}
      <Dialog open={showCreateTypeModal} onOpenChange={setShowCreateTypeModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Créer un nouveau type d'entité</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleCreateTypeEntite} className="space-y-4">
            <div>
              <Label htmlFor="type-nom">Nom du type *</Label>
              <Input
                id="type-nom"
                value={typeFormData.nom}
                onChange={(e) => setTypeFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Ex: Direction, Service, Division..."
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="type-description">Description</Label>
              <Textarea
                id="type-description"
                value={typeFormData.description}
                onChange={(e) => setTypeFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description du type d'entité (optionnel)"
                rows={3}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowCreateTypeModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                <Plus className="h-4 w-4 mr-2" />
                Créer
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de modification de type d'entité */}
      <Dialog open={showEditTypeModal} onOpenChange={setShowEditTypeModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Modifier le type d'entité</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleUpdateTypeEntite} className="space-y-4">
            <div>
              <Label htmlFor="edit-type-nom">Nom du type *</Label>
              <Input
                id="edit-type-nom"
                value={typeFormData.nom}
                onChange={(e) => setTypeFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Ex: Direction, Service, Division..."
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="edit-type-description">Description</Label>
              <Textarea
                id="edit-type-description"
                value={typeFormData.description}
                onChange={(e) => setTypeFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description du type d'entité (optionnel)"
                rows={3}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowEditTypeModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                <Edit className="h-4 w-4 mr-2" />
                Sauvegarder
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de détails de type d'entité */}
      <Dialog open={showDetailTypeModal} onOpenChange={setShowDetailTypeModal}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Détails du type d'entité</DialogTitle>
          </DialogHeader>
          {selectedTypeEntite && (
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Nom</Label>
                  <p className="text-sm text-muted-foreground">{selectedTypeEntite.nom}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Nombre d'entités</Label>
                  <p className="text-sm text-muted-foreground">
                    {entites.filter(e => e.type_entite.id === selectedTypeEntite.id).length} entités utilisent ce type
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Date de création</Label>
                  <p className="text-sm text-muted-foreground">
                    {new Date(selectedTypeEntite.date_creation).toLocaleDateString('fr-FR')}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Créé par</Label>
                  <p className="text-sm text-muted-foreground">{selectedTypeEntite.creer_par}</p>
                </div>
              </div>

              {selectedTypeEntite.description && (
                <div>
                  <Label className="text-sm font-medium">Description</Label>
                  <p className="text-sm text-muted-foreground">{selectedTypeEntite.description}</p>
                </div>
              )}

              <Separator />

              <div className="space-y-4">
                <h4 className="font-medium">Entités utilisant ce type ({entites.filter(e => e.type_entite.id === selectedTypeEntite.id).length})</h4>
                {entites.filter(e => e.type_entite.id === selectedTypeEntite.id).length > 0 ? (
                  <div className="space-y-2">
                    {entites.filter(e => e.type_entite.id === selectedTypeEntite.id).map((entite) => (
                      <div key={entite.id} className="p-3 border rounded-lg">
                        <p className="font-medium">{entite.nom}</p>
                        <p className="text-sm text-muted-foreground">
                          {entite.parent ? `Sous-entité de ${entite.parent.nom}` : 'Entité racine'}
                          {entite.chef_actuel?.user?.prenom && ` | Chef: ${entite.chef_actuel.user.prenom} ${entite.chef_actuel.user.nom}`}
                        </p>
                        {entite.description && (
                          <p className="text-xs text-muted-foreground mt-1">
                            {entite.description}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">Aucune entité n'utilise encore ce type</p>
                )}
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Dialog de suppression de type d'entité */}
      <AlertDialog open={showDeleteTypeDialog} onOpenChange={setShowDeleteTypeDialog}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer le type d'entité "{typeEntiteToDelete?.nom}" ?
              Cette action est irréversible.
              {typeEntiteToDelete && entites.filter(e => e.type_entite.id === typeEntiteToDelete.id).length > 0 && (
                <div className="mt-2 p-3 bg-destructive/10 rounded border">
                  <p className="text-sm font-medium text-destructive">
                    ⚠️ Ce type est utilisé par {entites.filter(e => e.type_entite.id === typeEntiteToDelete.id).length} entité(s). 
                    Vous devez d'abord modifier le type de ces entités.
                  </p>
                </div>
              )}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction 
              onClick={handleDeleteTypeEntite}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              disabled={typeEntiteToDelete ? entites.filter(e => e.type_entite.id === typeEntiteToDelete.id).length > 0 : false}
            >
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* ============================================ */}
      {/* MODALES POUR LA GESTION DES POSTES */}
      {/* ============================================ */}

      {/* Modal de création de poste */}
      <Dialog open={showCreatePosteModal} onOpenChange={setShowCreatePosteModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Créer un nouveau poste</DialogTitle>
            <DialogDescription>
              Remplissez les informations du nouveau poste.
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={handleCreatePoste} className="space-y-4">
            <div>
              <Label htmlFor="poste-nom">Nom du poste *</Label>
              <Input
                id="poste-nom"
                value={posteFormData.nom}
                onChange={(e) => setPosteFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Ex: Directeur, Chef de service, Secrétaire..."
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="poste-description">Description</Label>
              <Textarea
                id="poste-description"
                value={posteFormData.description}
                onChange={(e) => setPosteFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description du poste (optionnel)"
                rows={3}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowCreatePosteModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                <Plus className="h-4 w-4 mr-2" />
                Créer
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de modification de poste */}
      <Dialog open={showEditPosteModal} onOpenChange={setShowEditPosteModal}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Modifier le poste</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleUpdatePoste} className="space-y-4">
            <div>
              <Label htmlFor="edit-poste-nom">Nom du poste *</Label>
              <Input
                id="edit-poste-nom"
                value={posteFormData.nom}
                onChange={(e) => setPosteFormData(prev => ({ ...prev, nom: e.target.value }))}
                placeholder="Ex: Directeur, Chef de service, Secrétaire..."
                maxLength={255}
                required
              />
            </div>

            <div>
              <Label htmlFor="edit-poste-description">Description</Label>
              <Textarea
                id="edit-poste-description"
                value={posteFormData.description}
                onChange={(e) => setPosteFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description du poste (optionnel)"
                rows={3}
              />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setShowEditPosteModal(false)}>
                Annuler
              </Button>
              <Button type="submit">
                <Edit className="h-4 w-4 mr-2" />
                Sauvegarder
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Modal de détails de poste */}
      <Dialog open={showDetailPosteModal} onOpenChange={setShowDetailPosteModal}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Détails du poste</DialogTitle>
          </DialogHeader>
          {selectedPoste && (
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Nom</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.nom}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Statut</Label>
                  <p className="text-sm text-muted-foreground">
                    <Badge variant={(selectedPoste.affectations_actuelles_count || 0) > 0 ? "default" : "secondary"}>
                      {(selectedPoste.affectations_actuelles_count || 0) > 0 ? "Occupé" : "Disponible"}
                    </Badge>
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Date de création</Label>
                  <p className="text-sm text-muted-foreground">
                    {new Date(selectedPoste.date_creation).toLocaleDateString('fr-FR')}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Créé par</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.creer_par}</p>
                </div>
              </div>

              {selectedPoste.description && (
                <div>
                  <Label className="text-sm font-medium">Description</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.description}</p>
                </div>
              )}

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <Label className="text-sm font-medium">Affectations totales</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.statistiques?.total_affectations || 0}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Affectations actives</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.statistiques?.affectations_actives || 0}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Affectations terminées</Label>
                  <p className="text-sm text-muted-foreground">{selectedPoste.statistiques?.affectations_terminees || 0}</p>
                </div>
              </div>

              <Separator />

              {/* Affectations actuelles */}
              <div className="space-y-4">
                <h4 className="font-medium">Affectations actuelles ({selectedPoste.affectations_actuelles?.length || 0})</h4>
                {selectedPoste.affectations_actuelles && selectedPoste.affectations_actuelles.length > 0 ? (
                  <div className="space-y-2">
                    {selectedPoste.affectations_actuelles.map((affectation: any) => (
                      <div key={affectation.id} className="p-3 border rounded-lg">
                        <p className="font-medium">
                          {affectation.user.prenom} {affectation.user.nom} 
                          <Badge variant="outline" className="ml-2">{affectation.user.matricule}</Badge>
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Entité: {affectation.entite.nom} | Depuis le {new Date(affectation.date_debut).toLocaleDateString('fr-FR')}
                        </p>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">Aucune affectation active</p>
                )}
              </div>

              {/* Historique des affectations */}
              {selectedPoste.historique_affectations && selectedPoste.historique_affectations.length > 0 && (
                <div className="space-y-4">
                  <h4 className="font-medium">Historique des affectations ({selectedPoste.historique_affectations.length})</h4>
                  <div className="space-y-2 max-h-40 overflow-y-auto">
                    {selectedPoste.historique_affectations.map((affectation: any, index: number) => (
                      <div key={index} className="p-3 border rounded-lg bg-muted/30">
                        <p className="font-medium">
                          {affectation.user.prenom} {affectation.user.nom}
                          <Badge variant="outline" className="ml-2">{affectation.user.matricule}</Badge>
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Entité: {affectation.entite} | 
                          Du {new Date(affectation.date_debut).toLocaleDateString('fr-FR')} 
                          au {new Date(affectation.date_fin).toLocaleDateString('fr-FR')}
                        </p>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Dialog de suppression de poste */}
      <AlertDialog open={showDeletePosteDialog} onOpenChange={setShowDeletePosteDialog}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer le poste "{posteToDelete?.nom}" ?
              Cette action est irréversible et supprimera toutes les données associées.
              {posteToDelete && (posteToDelete.affectations_actuelles_count || 0) > 0 && (
                <div className="mt-2 p-3 bg-destructive/10 rounded border">
                  <p className="text-sm font-medium text-destructive">
                    ⚠️ Ce poste a {posteToDelete.affectations_actuelles_count} affectation(s) active(s). 
                    Vous devez d'abord terminer ces affectations.
                  </p>
                </div>
              )}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction 
              onClick={handleDeletePoste}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                            disabled={posteToDelete ? (posteToDelete.affectations_actuelles?.length || 0) > 0 : false}
            >
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
          </div>
        </main>
      </div>
    </div>
    </ProtectedPage>
  );
}

// Composant pour afficher les noeuds de l'organigramme
function OrganigrammeNode({ node, level }: { node: OrganigrammeNodeData; level: number }) {
  const [isExpanded, setIsExpanded] = useState(level < 2);

  // Indentation fixe avec style inline pour éviter les problèmes Tailwind
  // Niveau 0 = aucune indentation
  const indentationStyle = level > 0 ? { marginLeft: `${level * 1.5}rem` } : {};

  return (
    <div style={indentationStyle}>
      <div className="flex items-center gap-2 p-2 border-l-2 border-muted">
        {node.enfants && node.enfants.length > 0 && (
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setIsExpanded(!isExpanded)}
            className="h-6 w-6 p-0"
          >
            {isExpanded ? (
              <ChevronDown className="h-3 w-3" />
            ) : (
              <ChevronRight className="h-3 w-3" />
            )}
          </Button>
        )}
        
        <div className="flex-1">
          <div className="flex items-center gap-2">
            <span className="font-medium">{node.nom}</span>
            <Badge variant="outline" className="text-xs">
              {node.type_entite.nom}
            </Badge>
            {node.chef_actuel && (
              <Badge className="text-xs bg-amber-100 text-amber-800">
                <Crown className="h-3 w-3 mr-1" />
                {node.chef_actuel?.prenom} {node.chef_actuel?.nom}
              </Badge>
            )}
          </div>
          <div className="text-xs text-muted-foreground">
            {node.effectifs?.nombre_employes || 0} employés | Niveau {node.statistiques?.niveau_hierarchique || 0}
          </div>
        </div>
      </div>

      {isExpanded && node.enfants && (
        <div>
          {node.enfants.map((enfant) => (
            <OrganigrammeNode key={enfant.id} node={enfant} level={level + 1} />
          ))}
        </div>
      )}
    </div>
  );
}
