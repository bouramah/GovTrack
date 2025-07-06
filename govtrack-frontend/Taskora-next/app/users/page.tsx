"use client";

import { useState, useEffect, useMemo } from 'react';
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useToast } from '@/components/ui/use-toast';
import { usePermissions } from '@/hooks/use-permissions';
import { useAuth } from '@/contexts/AuthContext';
import { 
  UsersListGuard, 
  CreateUserGuard, 
  EditUserGuard, 
  DeleteUserGuard, 
  UserDetailsGuard,
  UserAssignmentsGuard,
  UserRolesGuard,
  UserStatsGuard 
} from '@/components/PermissionGuard';
import { ProtectedPage } from '@/components/ProtectedPage';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
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
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Users,
  UserPlus,
  Search,
  Filter,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  Building,
  Shield,
  Loader2,
  AlertTriangle,
  ChevronDown,
  Activity,
  ChevronLeft,
  ChevronRight,
  User as UserIcon,
  Phone as ContactIcon,
  Crown,
  Calendar,
  MapPin,
  X,
  Plus,
  UserCheck,
  Minus
} from 'lucide-react';
import { Combobox, ComboboxOption } from "@/components/ui/combobox";
import { SearchableSelect } from "@/components/ui/searchable-select";
import { apiClient, User, UserDetailed, Role, Entite, Poste } from '@/lib/api';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";

// Hook personnalisé pour le debouncing
function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}

export default function UsersPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [entites, setEntites] = useState<Entite[]>([]);
  const [postes, setPostes] = useState<Poste[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<'all' | 'active' | 'inactive'>('all');
  
  // Recherche avec debounce pour éviter les appels API à chaque lettre
  const debouncedSearchTerm = useDebounce(searchTerm, 1000); // 1000ms de délai
  
  // États pour la pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalUsers, setTotalUsers] = useState(0);
  const [itemsPerPage, setItemsPerPage] = useState(15);
  
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
  const [isAffectationsModalOpen, setIsAffectationsModalOpen] = useState(false);
  const [isNewAffectationModalOpen, setIsNewAffectationModalOpen] = useState(false);
  const [isTerminerAffectationModalOpen, setIsTerminerAffectationModalOpen] = useState(false);
  const [isRolesModalOpen, setIsRolesModalOpen] = useState(false);
  const [isAssignRoleModalOpen, setIsAssignRoleModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [userDetails, setUserDetails] = useState<UserDetailed | null>(null);
  const [userAffectations, setUserAffectations] = useState<any[]>([]);
  const [userRoles, setUserRoles] = useState<any[]>([]);
  const [availableRoles, setAvailableRoles] = useState<Role[]>([]);
  const [loadingDetails, setLoadingDetails] = useState(false);
  const [loadingAffectations, setLoadingAffectations] = useState(false);
  const [loadingRoles, setLoadingRoles] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const [formData, setFormData] = useState({
    matricule: '',
    nom: '',
    prenom: '',
    email: '',
    password: '',
    telephone: '',
    adresse: '',
    statut: true,
  });

  const [affectationFormData, setAffectationFormData] = useState({
    entite_id: '',
    poste_id: '',
    date_debut: new Date().toISOString().split('T')[0],
    terminer_affectation_precedente: false,
  });

  const [terminationFormData, setTerminationFormData] = useState({
    date_fin: new Date().toISOString().split('T')[0],
    raison: '',
  });

  const [roleFormData, setRoleFormData] = useState({
    role_id: '',
  });

  const { toast } = useToast();
  const permissions = usePermissions();
  const { user } = useAuth();



  useEffect(() => {
    // Attendre que l'utilisateur soit chargé avant de vérifier les permissions
    if (!user) return;
    
    // Vérifier si l'utilisateur a la permission de voir la liste des utilisateurs
    if (!permissions.canViewUsersList()) {
      toast({
        title: "❌ Accès refusé",
        description: "Vous n'avez pas la permission de voir la liste des utilisateurs",
        variant: "destructive"
      });
      return;
    }
    
    loadData();
  }, [currentPage, itemsPerPage, debouncedSearchTerm, statusFilter, user]);

  const loadData = async () => {
    try {
      setLoading(true);
      
      // Paramètres pour l'API
      const userParams = {
        page: currentPage,
        per_page: itemsPerPage,
        search: debouncedSearchTerm || undefined,
        statut: statusFilter === 'all' ? undefined : statusFilter === 'active',
        sort_by: 'nom',
        sort_order: 'asc' as const
      };

      const [usersResponse, rolesData, entitesData, postesData] = await Promise.all([
        apiClient.getUsersDetailed(userParams),
        apiClient.getRoles(),
        apiClient.getEntitesDetailed(),
        apiClient.getPostes()
      ]);

      setUsers(usersResponse.data || []);
      setTotalPages(usersResponse.pagination?.last_page || 1);
      setTotalUsers(usersResponse.pagination?.total || 0);
      setRoles(rolesData.data || []);
      setEntites(entitesData.data || []);
      setPostes(postesData.data || []);
    } catch (error: any) {
      console.error('Erreur de chargement:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (value: string) => {
    setSearchTerm(value);
    setCurrentPage(1); // Reset à la première page lors de la recherche
  };

  const handleStatusFilter = (status: 'all' | 'active' | 'inactive') => {
    setStatusFilter(status);
    setCurrentPage(1); // Reset à la première page lors du filtrage
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  const handleItemsPerPageChange = (newItemsPerPage: number) => {
    setItemsPerPage(newItemsPerPage);
    setCurrentPage(1); // Reset à la première page
  };

  const loadUserDetails = async (userId: number) => {
    try {
      setLoadingDetails(true);
      const details = await apiClient.getUser(userId);
      setUserDetails(details);
      setIsDetailModalOpen(true);
    } catch (error: any) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoadingDetails(false);
    }
  };

  const loadUserAffectations = async (userId: number) => {
    try {
      setLoadingAffectations(true);
      const affectations = await apiClient.getUserAffectations(userId);
      setUserAffectations(affectations);
    } catch (error: any) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoadingAffectations(false);
    }
  };

  const handleOpenAffectationsModal = (user: User) => {
    setSelectedUser(user);
    setIsAffectationsModalOpen(true);
    loadUserAffectations(user.id);
  };

  const handleNewAffectation = async () => {
    if (!selectedUser) return;

    const validationErrors: string[] = [];
    if (!affectationFormData.entite_id) validationErrors.push("L'entité est requise");
    if (!affectationFormData.poste_id) validationErrors.push("Le poste est requis");
    if (!affectationFormData.date_debut) validationErrors.push("La date de début est requise");

    if (validationErrors.length > 0) {
      toast({
        title: "❌ Erreurs de validation",
        description: validationErrors.join(", "),
        variant: "destructive"
      });
      return;
    }

    try {
      setIsSubmitting(true);
      await apiClient.affecterUser(selectedUser.id, {
        entite_id: parseInt(affectationFormData.entite_id),
        poste_id: parseInt(affectationFormData.poste_id),
        date_debut: affectationFormData.date_debut,
        terminer_affectation_precedente: affectationFormData.terminer_affectation_precedente,
      });

      toast({
        title: "✅ Succès",
        description: "Affectation créée avec succès"
      });

      setIsNewAffectationModalOpen(false);
      resetAffectationForm();
      loadUserAffectations(selectedUser.id);
      loadData(); // Recharger la liste principale
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur d'affectation",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleTerminerAffectation = async () => {
    if (!selectedUser) return;

    const validationErrors: string[] = [];
    if (!terminationFormData.date_fin) validationErrors.push("La date de fin est requise");

    if (validationErrors.length > 0) {
      toast({
        title: "❌ Erreurs de validation",
        description: validationErrors.join(", "),
        variant: "destructive"
      });
      return;
    }

    try {
      setIsSubmitting(true);
      await apiClient.terminerAffectationUser(selectedUser.id, {
        date_fin: terminationFormData.date_fin,
        raison: terminationFormData.raison || undefined,
      });

      toast({
        title: "✅ Succès",
        description: "Affectation terminée avec succès"
      });

      setIsTerminerAffectationModalOpen(false);
      resetTerminationForm();
      loadUserAffectations(selectedUser.id);
      loadData(); // Recharger la liste principale
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur de fin d'affectation",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const resetAffectationForm = () => {
    setAffectationFormData({
      entite_id: '',
      poste_id: '',
      date_debut: new Date().toISOString().split('T')[0],
      terminer_affectation_precedente: false,
    });
  };

  const resetTerminationForm = () => {
    setTerminationFormData({
      date_fin: new Date().toISOString().split('T')[0],
      raison: '',
    });
  };

  const resetRoleForm = () => {
    setRoleFormData({
      role_id: '',
    });
  };

  const handleOpenRolesModal = async (user: User) => {
    setSelectedUser(user);
    setIsRolesModalOpen(true);
    await loadUserRoles(user.id);
  };

  const loadUserRoles = async (userId: number) => {
    try {
      setLoadingRoles(true);
      const [userDetailsResponse, allRolesResponse] = await Promise.all([
        apiClient.getUser(userId),
        apiClient.getRoles()
      ]);
      
      setUserRoles(userDetailsResponse.roles || []);
      setAvailableRoles(allRolesResponse.data || []);
    } catch (error: any) {
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoadingRoles(false);
    }
  };

  const handleAssignRole = async () => {
    if (!selectedUser) return;

    const validationErrors: string[] = [];
    if (!roleFormData.role_id) validationErrors.push("Le rôle est requis");

    if (validationErrors.length > 0) {
      toast({
        title: "❌ Erreurs de validation",
        description: validationErrors.join(", "),
        variant: "destructive"
      });
      return;
    }

    try {
      setIsSubmitting(true);
      await apiClient.assignRoleToUser(selectedUser.id, {
        role_id: parseInt(roleFormData.role_id),
      });

      toast({
        title: "✅ Succès",
        description: "Rôle assigné avec succès"
      });

      setIsAssignRoleModalOpen(false);
      resetRoleForm();
      loadUserRoles(selectedUser.id);
      loadData(); // Recharger la liste principale
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur d'assignation",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleRemoveRole = async (roleId: number, roleName: string) => {
    if (!selectedUser) return;

    try {
      setIsSubmitting(true);
      await apiClient.removeRoleFromUser(selectedUser.id, roleId);

      toast({
        title: "✅ Succès",
        description: `Rôle "${roleName}" retiré avec succès`
      });

      loadUserRoles(selectedUser.id);
      loadData(); // Recharger la liste principale
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur de suppression",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleViewDetails = (user: User) => {
    setSelectedUser(user);
    loadUserDetails(user.id);
  };

  // Fonction de validation des données utilisateur
  const validateUserData = (data: typeof formData, isUpdate = false) => {
    const errors: string[] = [];

    // Validation des champs requis
    if (!data.matricule?.trim()) {
      errors.push("Le matricule est requis");
    } else if (data.matricule.length > 20) {
      errors.push("Le matricule ne peut pas dépasser 20 caractères");
    }

    if (!data.nom?.trim()) {
      errors.push("Le nom est requis");
    } else if (data.nom.length > 255) {
      errors.push("Le nom ne peut pas dépasser 255 caractères");
    }

    if (!data.prenom?.trim()) {
      errors.push("Le prénom est requis");
    } else if (data.prenom.length > 255) {
      errors.push("Le prénom ne peut pas dépasser 255 caractères");
    }

    if (!data.email?.trim()) {
      errors.push("L'email est requis");
    } else {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(data.email)) {
        errors.push("L'email n'est pas valide");
      }
    }

    // Le mot de passe est requis seulement lors de la création
    if (!isUpdate) {
      if (!data.password?.trim()) {
        errors.push("Le mot de passe est requis");
      } else if (data.password.length < 8) {
        errors.push("Le mot de passe doit contenir au moins 8 caractères");
      }
    }

    // Validation des champs optionnels s'ils sont remplis
    if (data.telephone && data.telephone.length > 20) {
      errors.push("Le téléphone ne peut pas dépasser 20 caractères");
    }

    if (data.adresse && data.adresse.length > 500) {
      errors.push("L'adresse ne peut pas dépasser 500 caractères");
    }

    return errors;
  };

  // Fonction pour formater les erreurs du backend
  const formatBackendErrors = (error: any): string => {
    // Erreurs de permissions (422)
    if (error.name === 'PermissionError') {
      return error.message;
    }
    
    // Erreurs 422 (permissions insuffisantes)
    if (error.response?.status === 422) {
      const errorData = error.response.data;
      
      // 1️⃣ Priorité aux messages détaillés de validation
      if (errorData.errors) {
        const backendErrors = errorData.errors;
        const errorMessages: string[] = [];
        Object.keys(backendErrors).forEach(field => {
          const fieldErrors = backendErrors[field];
          if (Array.isArray(fieldErrors)) {
            errorMessages.push(...fieldErrors);
          } else if (typeof fieldErrors === 'string') {
            errorMessages.push(fieldErrors);
          }
        });
        if (errorMessages.length) {
          return errorMessages.join(', ');
        }
      }

      // 2️⃣ Ensuite, le message ou l'erreur générique renvoyés par le backend
      if (errorData.message) {
        return errorData.message;
      }
      if (errorData.error) {
        return errorData.error;
      }

      return "Accès refusé - Permissions insuffisantes";
    }
    
    // Erreurs de validation Laravel (autres codes)
    if (error.response?.data?.errors) {
      const backendErrors = error.response.data.errors;
      const errorMessages: string[] = [];
      
      Object.keys(backendErrors).forEach(field => {
        const fieldErrors = backendErrors[field];
        if (Array.isArray(fieldErrors)) {
          errorMessages.push(...fieldErrors);
        } else {
          errorMessages.push(fieldErrors);
        }
      });
      
      return errorMessages.join(", ");
    }
    
    // Erreur simple du backend
    if (error.response?.data?.message) {
      return error.response.data.message;
    }
    
    // Erreur générique
    return error.message || "Une erreur inattendue s'est produite";
  };

  const handleCreateUser = async () => {
    // Validation côté frontend
    const validationErrors = validateUserData(formData);
    if (validationErrors.length > 0) {
      toast({
        title: "❌ Erreurs de validation",
        description: validationErrors.join(", "),
        variant: "destructive"
      });
      return;
    }

    try {
      setIsSubmitting(true);
      const newUser = await apiClient.createUser(formData);
      
      // Mettre à jour la liste des utilisateurs
      await loadData(); // Recharger les données pour avoir les informations complètes
      
      setIsCreateModalOpen(false);
      resetForm();
      toast({
        title: "✅ Succès",
        description: "Utilisateur créé avec succès"
      });
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur de création",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleUpdateUser = async () => {
    if (!selectedUser) return;

    // Validation côté frontend (en mode mise à jour)
    const validationErrors = validateUserData(formData, true);
    if (validationErrors.length > 0) {
      toast({
        title: "❌ Erreurs de validation",
        description: validationErrors.join(", "),
        variant: "destructive"
      });
      return;
    }

    try {
      setIsSubmitting(true);
      const updatedUser = await apiClient.updateUser(selectedUser.id, formData);
      
      // Mettre à jour la liste des utilisateurs
      await loadData(); // Recharger les données pour avoir les informations complètes
      
      setIsEditModalOpen(false);
      setSelectedUser(null);
      resetForm();
      toast({
        title: "✅ Succès",
        description: "Utilisateur mis à jour avec succès"
      });
    } catch (error: any) {
      const errorMessage = formatBackendErrors(error);
      toast({
        title: "❌ Erreur de mise à jour",
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDeleteUser = async (userId: number) => {
    try {
      await apiClient.deleteUser(userId);
      setUsers(users.filter(user => user.id !== userId));
      toast({
        title: "✅ Succès",
        description: "Utilisateur supprimé avec succès"
      });
    } catch (error: any) {
      console.error('Erreur suppression utilisateur:', error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openEditModal = (user: User) => {
    setSelectedUser(user);
    setFormData({
      matricule: user.matricule,
      nom: user.nom,
      prenom: user.prenom,
      email: user.email,
      password: '',
      telephone: user.telephone || '',
      adresse: user.adresse || '',
      statut: user.statut,
    });
    setIsEditModalOpen(true);
  };

  const resetForm = () => {
    setFormData({
      matricule: '',
      nom: '',
      prenom: '',
      email: '',
      password: '',
      telephone: '',
      adresse: '',
      statut: true,
    });
  };

  // Calculs de statistiques basés sur les données réelles
  const activeUsers = users.filter(user => user.statut).length;
  const inactiveUsers = users.filter(user => !user.statut).length;
  const usersWithRoles = users.filter(user => user.roles && user.roles.length > 0).length;
  const usersWithAffectations = users.filter(user => user.affectation_actuelle).length;

  if (loading) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar
            name="Gestion Utilisateurs"
            sidebarOpen={sidebarOpen}
            setSidebarOpen={setSidebarOpen}
          />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <div className="flex items-center justify-center h-64">
              <Loader2 className="h-8 w-8 animate-spin mr-2" />
              <span>Chargement des utilisateurs...</span>
            </div>
          </main>
        </div>
      </div>
    );
  }

  return (
    <ProtectedPage permission="view_users_list">
      <div className="bg-gray-50">
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar
            name="Gestion Utilisateurs"
            sidebarOpen={sidebarOpen}
            setSidebarOpen={setSidebarOpen}
          />

          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="space-y-6">
            {/* En-tête avec action */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <div>
                <h1 className="text-2xl lg:text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
                <p className="text-gray-600 mt-1">
                  Gérer les comptes utilisateurs, rôles et affectations ({totalUsers} utilisateurs)
                </p>
              </div>
              <CreateUserGuard>
                <Dialog open={isCreateModalOpen} onOpenChange={setIsCreateModalOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <UserPlus className="h-4 w-4" />
                      Nouvel Utilisateur
                    </Button>
                  </DialogTrigger>
                <DialogContent className="max-w-md">
                  <DialogHeader>
                    <DialogTitle>Créer un Utilisateur</DialogTitle>
                  </DialogHeader>
                  <div className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <Label htmlFor="prenom">Prénom <span className="text-red-500">*</span></Label>
                        <Input
                          id="prenom"
                          value={formData.prenom}
                          onChange={(e) => setFormData({...formData, prenom: e.target.value})}
                          placeholder="Prénom"
                          required
                          className={!formData.prenom.trim() ? "border-red-300 focus:border-red-500" : ""}
                        />
                      </div>
                      <div>
                        <Label htmlFor="nom">Nom <span className="text-red-500">*</span></Label>
                        <Input
                          id="nom"
                          value={formData.nom}
                          onChange={(e) => setFormData({...formData, nom: e.target.value})}
                          placeholder="Nom"
                          required
                          className={!formData.nom.trim() ? "border-red-300 focus:border-red-500" : ""}
                        />
                      </div>
                    </div>
                    <div>
                      <Label htmlFor="matricule">Matricule <span className="text-red-500">*</span></Label>
                      <Input
                        id="matricule"
                        value={formData.matricule}
                        onChange={(e) => setFormData({...formData, matricule: e.target.value})}
                        placeholder="Matricule unique (max 20 caractères)"
                        required
                        maxLength={20}
                        className={!formData.matricule.trim() ? "border-red-300 focus:border-red-500" : ""}
                      />
                    </div>
                    <div>
                      <Label htmlFor="email">Email <span className="text-red-500">*</span></Label>
                      <Input
                        id="email"
                        type="email"
                        value={formData.email}
                        onChange={(e) => setFormData({...formData, email: e.target.value})}
                        placeholder="adresse@email.com"
                        required
                        className={!formData.email.trim() ? "border-red-300 focus:border-red-500" : ""}
                      />
                    </div>
                    <div>
                      <Label htmlFor="password">Mot de passe <span className="text-red-500">*</span></Label>
                      <Input
                        id="password"
                        type="password"
                        value={formData.password}
                        onChange={(e) => setFormData({...formData, password: e.target.value})}
                        placeholder="Minimum 8 caractères"
                        required
                        minLength={8}
                        className={!formData.password.trim() ? "border-red-300 focus:border-red-500" : ""}
                      />
                    </div>
                    <div>
                      <Label htmlFor="telephone">Téléphone</Label>
                      <Input
                        id="telephone"
                        value={formData.telephone}
                        onChange={(e) => setFormData({...formData, telephone: e.target.value})}
                        placeholder="Numéro de téléphone (optionnel)"
                        maxLength={20}
                      />
                    </div>
                    <div>
                      <Label htmlFor="adresse">Adresse</Label>
                      <Input
                        id="adresse"
                        value={formData.adresse}
                        onChange={(e) => setFormData({...formData, adresse: e.target.value})}
                        placeholder="Adresse complète (optionnel, max 500 caractères)"
                        maxLength={500}
                      />
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch
                        id="statut"
                        checked={formData.statut}
                        onCheckedChange={(checked) => setFormData({...formData, statut: checked})}
                      />
                      <Label htmlFor="statut">Compte actif</Label>
                    </div>
                    <div className="flex gap-2 pt-4">
                      <Button 
                        variant="outline" 
                        onClick={() => setIsCreateModalOpen(false)}
                        className="flex-1"
                      >
                        Annuler
                      </Button>
                      <Button 
                        onClick={handleCreateUser}
                        disabled={isSubmitting}
                        className="flex-1"
                      >
                        {isSubmitting ? (
                          <><Loader2 className="h-4 w-4 animate-spin mr-2" />Création...</>
                        ) : (
                          <>Créer</>
                        )}
                      </Button>
                    </div>
                  </div>
                </DialogContent>
              </Dialog>
              </CreateUserGuard>
            </div>

            {/* Dashboard statistiques */}
            <UserStatsGuard>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <Card key="total" className="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                  <CardContent className="p-4">
                    <div className="flex items-center gap-3">
                      <div className="p-2 bg-blue-600 rounded-lg">
                        <Users className="h-6 w-6 text-white" />
                      </div>
                      <div>
                        <p className="text-sm font-medium text-blue-700">Total Utilisateurs</p>
                        <p className="text-2xl font-bold text-blue-900">{totalUsers}</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card key="active" className="bg-gradient-to-r from-green-500 to-green-600 text-white">
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium text-green-50">Utilisateurs Actifs</CardTitle>
                    <UserIcon className="h-5 w-5 text-green-100" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-3xl font-bold">{activeUsers}</div>
                    <p className="text-xs text-green-100 mt-1">
                      {totalUsers > 0 ? Math.round((activeUsers / totalUsers) * 100) : 0}% du total
                    </p>
                  </CardContent>
                </Card>

                <Card key="inactive" className="bg-gradient-to-r from-red-500 to-red-600 text-white">
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium text-red-50">Utilisateurs Inactifs</CardTitle>
                    <UserIcon className="h-5 w-5 text-red-100" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-3xl font-bold">{inactiveUsers}</div>
                    <p className="text-xs text-red-100 mt-1">
                      {totalUsers > 0 ? Math.round((inactiveUsers / totalUsers) * 100) : 0}% du total
                    </p>
                  </CardContent>
                </Card>

                <Card key="with-roles" className="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium text-purple-50">Avec Rôles</CardTitle>
                    <Shield className="h-5 w-5 text-purple-100" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-3xl font-bold">{usersWithRoles}</div>
                    <p className="text-xs text-purple-100 mt-1">
                      {totalUsers > 0 ? Math.round((usersWithRoles / totalUsers) * 100) : 0}% assignés
                    </p>
                  </CardContent>
                </Card>

                <Card key="with-affectations" className="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium text-orange-50">Avec Affectations</CardTitle>
                    <Building className="h-5 w-5 text-orange-100" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-3xl font-bold">{usersWithAffectations}</div>
                    <p className="text-xs text-orange-100 mt-1">
                      {totalUsers > 0 ? Math.round((usersWithAffectations / totalUsers) * 100) : 0}% affectés
                    </p>
                  </CardContent>
                </Card>
              </div>
            </UserStatsGuard>

            {/* Filtres et recherche */}
            <Card>
              <CardContent className="p-6">
                <div className="flex flex-col sm:flex-row gap-4 items-center justify-between">
                  <div className="flex flex-col sm:flex-row gap-4 items-center flex-1">
                    <div className="relative min-w-64">
                      <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                      <Input
                        placeholder="Rechercher par nom, prénom, matricule ou email..."
                        value={searchTerm}
                        onChange={(e) => handleSearch(e.target.value)}
                        className="pl-9"
                      />
                    </div>
                    
                    <Select value={statusFilter} onValueChange={handleStatusFilter}>
                      <SelectTrigger className="w-48">
                        <SelectValue placeholder="Filtrer par statut" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">Tous les statuts</SelectItem>
                        <SelectItem value="active">Actifs uniquement</SelectItem>
                        <SelectItem value="inactive">Inactifs uniquement</SelectItem>
                      </SelectContent>
                    </Select>

                    <Select 
                      value={itemsPerPage.toString()} 
                      onValueChange={(value) => handleItemsPerPageChange(Number(value))}
                    >
                      <SelectTrigger className="w-32">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="10">10 par page</SelectItem>
                        <SelectItem value="15">15 par page</SelectItem>
                        <SelectItem value="25">25 par page</SelectItem>
                        <SelectItem value="50">50 par page</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="text-sm text-gray-500">
                    Page {currentPage} sur {totalPages} ({totalUsers} au total)
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Table des utilisateurs */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Users className="h-5 w-5" />
                  Liste des Utilisateurs
                </CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-gray-50">
                        <TableHead className="font-semibold">Utilisateur</TableHead>
                        <TableHead className="font-semibold">Contact</TableHead>
                        <TableHead className="font-semibold">Affectation</TableHead>
                        <TableHead className="font-semibold">Rôles</TableHead>
                        <TableHead className="font-semibold">Statut</TableHead>
                        <TableHead className="text-right font-semibold">Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {users.map((user) => (
                        <TableRow key={user.id} className="hover:bg-gray-50">
                          <TableCell>
                            <div className="flex items-center gap-3">
                              <Avatar className="h-10 w-10">
                                <AvatarImage src={user.photo} alt={`${user.prenom} ${user.nom}`} />
                                <AvatarFallback className="bg-blue-100 text-blue-600 font-semibold">
                                  {user.prenom.charAt(0)}{user.nom.charAt(0)}
                                </AvatarFallback>
                              </Avatar>
                              <div>
                                <div className="font-semibold text-gray-900">
                                  {user.prenom} {user.nom}
                                </div>
                                <div className="flex items-center gap-1 text-sm text-gray-500">
                                  <ContactIcon className="h-3 w-3" />
                                  {user.matricule}
                                </div>
                              </div>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="space-y-1">
                              <div className="flex items-center gap-2 text-sm">
                                <ContactIcon className="h-3 w-3 text-gray-400" />
                                <span>{user.email}</span>
                              </div>
                              {user.telephone && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <ContactIcon className="h-3 w-3 text-gray-400" />
                                  <span>{user.telephone}</span>
                                </div>
                              )}
                              {user.adresse && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <ContactIcon className="h-3 w-3 text-gray-400" />
                                  <span className="truncate max-w-[150px]">{user.adresse}</span>
                                </div>
                              )}
                            </div>
                          </TableCell>
                          <TableCell>
                            {user.affectation_actuelle ? (
                              <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                  <Building className="h-4 w-4 text-blue-600" />
                                  <span className="font-medium text-sm">
                                    {user.affectation_actuelle.entite}
                                  </span>
                                </div>
                                <div className="text-xs text-gray-500">
                                  {user.affectation_actuelle.poste}
                                </div>
                                {user.affectation_actuelle.date_debut && (
                                  <div className="text-xs text-gray-400">
                                    Depuis {new Date(user.affectation_actuelle.date_debut).toLocaleDateString('fr-FR')}
                                  </div>
                                )}
                              </div>
                            ) : (
                              <Badge variant="secondary" className="text-xs">
                                Non affecté
                              </Badge>
                            )}
                          </TableCell>
                          <TableCell>
                            <div className="flex flex-wrap gap-1">
                              {user.roles && user.roles.length > 0 ? (
                                user.roles.map((roleName, index) => (
                                  <Badge key={index} variant="outline" className="text-xs">
                                    <Crown className="h-3 w-3 mr-1" />
                                    {roleName}
                                  </Badge>
                                ))
                              ) : (
                                <Badge variant="secondary" className="text-xs">
                                  Aucun rôle
                                </Badge>
                              )}
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="flex items-center gap-2">
                              {user.statut ? (
                                <Badge className="bg-green-100 text-green-800 border-green-200">
                                  <Activity className="h-3 w-3 mr-1" />
                                  Actif
                                </Badge>
                              ) : (
                                <Badge variant="secondary" className="bg-red-100 text-red-800 border-red-200">
                                  <UserIcon className="h-3 w-3 mr-1" />
                                  Inactif
                                </Badge>
                              )}
                            </div>
                          </TableCell>
                          <TableCell className="text-right">
                            <DropdownMenu>
                              <DropdownMenuTrigger asChild>
                                <Button variant="ghost" className="h-8 w-8 p-0">
                                  <MoreHorizontal className="h-4 w-4" />
                                </Button>
                              </DropdownMenuTrigger>
                              <DropdownMenuContent align="end">
                                <UserDetailsGuard>
                                  <DropdownMenuItem key="view" onClick={() => handleViewDetails(user)}>
                                    <Eye className="mr-2 h-4 w-4" />
                                    Voir détails
                                  </DropdownMenuItem>
                                </UserDetailsGuard>
                                <EditUserGuard>
                                  <DropdownMenuItem key="edit" onClick={() => openEditModal(user)}>
                                    <Edit className="mr-2 h-4 w-4" />
                                    Modifier
                                  </DropdownMenuItem>
                                </EditUserGuard>
                                <UserAssignmentsGuard>
                                  <DropdownMenuItem key="affectations" onClick={() => handleOpenAffectationsModal(user)}>
                                    <Building className="mr-2 h-4 w-4" />
                                    Gérer affectations
                                  </DropdownMenuItem>
                                </UserAssignmentsGuard>
                                <UserRolesGuard>
                                  <DropdownMenuItem key="roles" onClick={() => handleOpenRolesModal(user)}>
                                    <Shield className="mr-2 h-4 w-4" />
                                    Gérer rôles
                                  </DropdownMenuItem>
                                </UserRolesGuard>
                                <DeleteUserGuard>
                                  <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                      <DropdownMenuItem 
                                        key="delete"
                                        onSelect={(e) => e.preventDefault()}
                                        className="text-red-600"
                                      >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        Supprimer
                                      </DropdownMenuItem>
                                    </AlertDialogTrigger>
                                  <AlertDialogContent>
                                    <AlertDialogHeader>
                                      <AlertDialogTitle>Supprimer l'utilisateur</AlertDialogTitle>
                                      <AlertDialogDescription>
                                        Êtes-vous sûr de vouloir supprimer l'utilisateur{' '}
                                        <strong>{user.prenom} {user.nom}</strong> ?
                                        Cette action est irréversible.
                                      </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                      <AlertDialogCancel>Annuler</AlertDialogCancel>
                                      <AlertDialogAction
                                        onClick={() => handleDeleteUser(user.id)}
                                        className="bg-red-600 hover:bg-red-700"
                                      >
                                        Supprimer
                                      </AlertDialogAction>
                                    </AlertDialogFooter>
                                  </AlertDialogContent>
                                </AlertDialog>
                                </DeleteUserGuard>
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
                
                {/* Pagination avec composant UI */}
                {totalPages > 1 && (
                  <div className="flex items-center justify-between px-6 py-4 border-t bg-gray-50">
                    <div className="text-sm text-gray-700">
                      Affichage de {((currentPage - 1) * itemsPerPage) + 1} à{' '}
                      {Math.min(currentPage * itemsPerPage, totalUsers)} sur {totalUsers} utilisateurs
                    </div>
                    
                    <Pagination>
                      <PaginationContent>
                        <PaginationItem>
                          <PaginationPrevious 
                            href="#"
                            onClick={(e) => {
                              e.preventDefault();
                              if (currentPage > 1) handlePageChange(currentPage - 1);
                            }}
                            className={currentPage === 1 ? 'pointer-events-none opacity-50' : ''}
                            size="default"
                          />
                        </PaginationItem>
                        
                        {/* Pages à afficher */}
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
                            <PaginationItem key={pageNum}>
                              <PaginationLink
                                href="#"
                                onClick={(e) => {
                                  e.preventDefault();
                                  handlePageChange(pageNum);
                                }}
                                isActive={currentPage === pageNum}
                                size="default"
                              >
                                {pageNum}
                              </PaginationLink>
                            </PaginationItem>
                          );
                        })}
                        
                        {/* Ellipsis si nécessaire */}
                        {totalPages > 5 && currentPage < totalPages - 2 && (
                          <PaginationItem>
                            <PaginationEllipsis />
                          </PaginationItem>
                        )}
                        
                        <PaginationItem>
                          <PaginationNext
                            href="#"
                            onClick={(e) => {
                              e.preventDefault();
                              if (currentPage < totalPages) handlePageChange(currentPage + 1);
                            }}
                            className={currentPage === totalPages ? 'pointer-events-none opacity-50' : ''}
                            size="default"
                          />
                        </PaginationItem>
                      </PaginationContent>
                    </Pagination>
                  </div>
                )}
                
                {users.length === 0 && (
                  <div className="text-center py-12">
                    <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun utilisateur trouvé</h3>
                    <p className="text-gray-500">
                      {searchTerm || statusFilter !== 'all' 
                        ? "Essayez de modifier vos critères de recherche"
                        : "Commencez par créer votre premier utilisateur"
                      }
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Modal de détails utilisateur */}
          <Dialog open={isDetailModalOpen} onOpenChange={setIsDetailModalOpen}>
            <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-3">
                  <UserIcon className="h-5 w-5" />
                  Détails de l'Utilisateur
                  {loadingDetails && <Loader2 className="h-4 w-4 animate-spin" />}
                </DialogTitle>
              </DialogHeader>
              
              {userDetails ? (
                <div className="space-y-6">
                  {/* En-tête avec photo et infos principales */}
                  <div className="flex items-start gap-6 p-6 bg-gray-50 rounded-lg">
                    <div className="flex-shrink-0">
                      {userDetails.photo ? (
                        <img 
                          src={userDetails.photo} 
                          alt={`Photo de ${userDetails.prenom} ${userDetails.nom}`}
                          className="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
                        />
                      ) : (
                        <div className="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center border-2 border-gray-200">
                          <UserIcon className="h-8 w-8 text-gray-600" />
                        </div>
                      )}
                    </div>
                    <div className="flex-1">
                      <h3 className="text-xl font-semibold text-gray-900">
                        {userDetails.prenom} {userDetails.nom}
                      </h3>
                      <p className="text-sm text-gray-600 mt-1">Matricule: {userDetails.matricule}</p>
                      <div className="flex items-center gap-2 mt-2">
                        <Badge variant={userDetails.statut ? "default" : "secondary"}>
                          {userDetails.statut ? "Actif" : "Inactif"}
                        </Badge>
                        {userDetails.roles.length > 0 && (
                          <Badge variant="outline">
                            {userDetails.roles.length} rôle{userDetails.roles.length > 1 ? 's' : ''}
                          </Badge>
                        )}
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Informations personnelles */}
                    <Card>
                      <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                          <ContactIcon className="h-5 w-5" />
                          Informations Personnelles
                        </CardTitle>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        <div>
                          <Label className="text-sm font-medium text-gray-600">Email</Label>
                          <p className="text-sm">{userDetails.email}</p>
                        </div>
                        {userDetails.telephone && (
                          <div>
                            <Label className="text-sm font-medium text-gray-600">Téléphone</Label>
                            <p className="text-sm">{userDetails.telephone}</p>
                          </div>
                        )}
                        {userDetails.adresse && (
                          <div>
                            <Label className="text-sm font-medium text-gray-600">Adresse</Label>
                            <p className="text-sm">{userDetails.adresse}</p>
                          </div>
                        )}
                      </CardContent>
                    </Card>

                    {/* Affectation actuelle */}
                    <Card>
                      <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                          <Building className="h-5 w-5" />
                          Affectation Actuelle
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        {userDetails.affectation_actuelle ? (
                          <div className="space-y-3">
                            <div>
                              <Label className="text-sm font-medium text-gray-600">Poste</Label>
                              <p className="text-sm font-medium">{userDetails.affectation_actuelle.poste}</p>
                            </div>
                            <div>
                              <Label className="text-sm font-medium text-gray-600">Entité</Label>
                              <p className="text-sm">{userDetails.affectation_actuelle.entite}</p>
                            </div>
                            <div>
                              <Label className="text-sm font-medium text-gray-600">Depuis le</Label>
                              <p className="text-sm">{new Date(userDetails.affectation_actuelle.date_debut).toLocaleDateString('fr-FR')}</p>
                            </div>
                          </div>
                        ) : (
                          <p className="text-sm text-gray-500 italic">Aucune affectation actuelle</p>
                        )}
                      </CardContent>
                    </Card>
                  </div>

                  {/* Rôles et Permissions */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2 text-lg">
                        <Shield className="h-5 w-5" />
                        Rôles et Permissions
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div>
                        <Label className="text-sm font-medium text-gray-600 mb-2 block">Rôles attribués</Label>
                        {userDetails.roles.length > 0 ? (
                          <div className="space-y-3">
                            {userDetails.roles.map((role, index) => (
                              <div key={role.id || index} className="p-3 bg-gray-50 rounded-lg">
                                <div className="flex items-start justify-between">
                                  <div className="flex-1">
                                    <Badge variant="default" className="mb-2">
                                      {role.nom}
                                    </Badge>
                                    {role.description && (
                                      <p className="text-sm text-gray-600">{role.description}</p>
                                    )}
                                    {role.permissions && role.permissions.length > 0 && (
                                      <div className="mt-2">
                                        <p className="text-xs text-gray-500 mb-1">Permissions de ce rôle:</p>
                                        <div className="flex flex-wrap gap-1">
                                          {role.permissions.slice(0, 3).map((permission, permIndex) => (
                                            <Badge key={permIndex} variant="outline" className="text-xs">
                                              {permission}
                                            </Badge>
                                          ))}
                                          {role.permissions.length > 3 && (
                                            <Badge variant="outline" className="text-xs">
                                              +{role.permissions.length - 3} autres
                                            </Badge>
                                          )}
                                        </div>
                                      </div>
                                    )}
                                  </div>
                                </div>
                              </div>
                            ))}
                          </div>
                        ) : (
                          <p className="text-sm text-gray-500 italic">Aucun rôle attribué</p>
                        )}
                      </div>
                    </CardContent>
                  </Card>

                  {/* Entités dirigées */}
                  {userDetails.entites_dirigees && userDetails.entites_dirigees.length > 0 && (
                    <Card>
                      <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                          <Crown className="h-5 w-5" />
                          Entités Dirigées
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="space-y-3">
                          {userDetails.entites_dirigees.map((entite, index) => (
                            <div key={index} className="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                              <div>
                                <p className="font-medium">{entite.entite_nom}</p>
                                <p className="text-sm text-gray-600">Depuis le {new Date(entite.date_debut).toLocaleDateString('fr-FR')}</p>
                              </div>
                              <Badge variant="secondary">Chef</Badge>
                            </div>
                          ))}
                        </div>
                      </CardContent>
                    </Card>
                  )}
                </div>
              ) : (
                <div className="flex items-center justify-center py-12">
                  <Loader2 className="h-8 w-8 animate-spin mr-2" />
                  <span>Chargement des détails...</span>
                </div>
              )}
            </DialogContent>
          </Dialog>

          {/* Modal de modification */}
          <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Modifier l'Utilisateur</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label htmlFor="edit-prenom">Prénom <span className="text-red-500">*</span></Label>
                    <Input
                      id="edit-prenom"
                      value={formData.prenom}
                      onChange={(e) => setFormData({...formData, prenom: e.target.value})}
                      placeholder="Prénom"
                      required
                      className={!formData.prenom.trim() ? "border-red-300 focus:border-red-500" : ""}
                    />
                  </div>
                  <div>
                    <Label htmlFor="edit-nom">Nom <span className="text-red-500">*</span></Label>
                    <Input
                      id="edit-nom"
                      value={formData.nom}
                      onChange={(e) => setFormData({...formData, nom: e.target.value})}
                      placeholder="Nom"
                      required
                      className={!formData.nom.trim() ? "border-red-300 focus:border-red-500" : ""}
                    />
                  </div>
                </div>
                <div>
                  <Label htmlFor="edit-matricule">Matricule <span className="text-red-500">*</span></Label>
                  <Input
                    id="edit-matricule"
                    value={formData.matricule}
                    onChange={(e) => setFormData({...formData, matricule: e.target.value})}
                    placeholder="Matricule unique (max 20 caractères)"
                    required
                    maxLength={20}
                    className={!formData.matricule.trim() ? "border-red-300 focus:border-red-500" : ""}
                  />
                </div>
                <div>
                  <Label htmlFor="edit-email">Email <span className="text-red-500">*</span></Label>
                  <Input
                    id="edit-email"
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                    placeholder="adresse@email.com"
                    required
                    className={!formData.email.trim() ? "border-red-300 focus:border-red-500" : ""}
                  />
                </div>
                <div>
                  <Label htmlFor="edit-password">
                    Nouveau mot de passe 
                    <span className="text-gray-500 text-sm ml-1">(optionnel)</span>
                  </Label>
                  <Input
                    id="edit-password"
                    type="password"
                    value={formData.password}
                    onChange={(e) => setFormData({...formData, password: e.target.value})}
                    placeholder="Laisser vide pour ne pas changer"
                    minLength={8}
                  />
                  {formData.password && formData.password.length > 0 && formData.password.length < 8 && (
                    <p className="text-sm text-red-500 mt-1">Le mot de passe doit contenir au moins 8 caractères</p>
                  )}
                </div>
                <div>
                  <Label htmlFor="edit-telephone">Téléphone</Label>
                  <Input
                    id="edit-telephone"
                    value={formData.telephone}
                    onChange={(e) => setFormData({...formData, telephone: e.target.value})}
                    placeholder="Numéro de téléphone (optionnel)"
                    maxLength={20}
                  />
                </div>
                <div>
                  <Label htmlFor="edit-adresse">Adresse</Label>
                  <Input
                    id="edit-adresse"
                    value={formData.adresse}
                    onChange={(e) => setFormData({...formData, adresse: e.target.value})}
                    placeholder="Adresse complète (optionnel, max 500 caractères)"
                    maxLength={500}
                  />
                </div>
                <div className="flex items-center space-x-2">
                  <Switch
                    id="edit-statut"
                    checked={formData.statut}
                    onCheckedChange={(checked) => setFormData({...formData, statut: checked})}
                  />
                  <Label htmlFor="edit-statut">Compte actif</Label>
                </div>
                <div className="flex gap-2 pt-4">
                  <Button 
                    variant="outline" 
                    onClick={() => setIsEditModalOpen(false)}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                  <Button 
                    onClick={handleUpdateUser}
                    disabled={isSubmitting}
                    className="flex-1"
                  >
                    {isSubmitting ? (
                      <><Loader2 className="h-4 w-4 animate-spin mr-2" />Mise à jour...</>
                    ) : (
                      <>Mettre à jour</>
                    )}
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>

          {/* Modal de gestion des affectations */}
          <Dialog open={isAffectationsModalOpen} onOpenChange={setIsAffectationsModalOpen}>
            <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <Building className="h-5 w-5" />
                  Gestion des Affectations - {selectedUser?.prenom} {selectedUser?.nom}
                </DialogTitle>
              </DialogHeader>
              
              {loadingAffectations ? (
                <div className="flex items-center justify-center py-8">
                  <Loader2 className="h-6 w-6 animate-spin mr-2" />
                  <span>Chargement de l'historique...</span>
                </div>
              ) : (
                <div className="space-y-6">
                  {/* Actions rapides */}
                  <div className="flex gap-3">
                    <Button onClick={() => setIsNewAffectationModalOpen(true)} className="flex-1">
                      <Plus className="h-4 w-4 mr-2" />
                      Nouvelle Affectation
                    </Button>
                    {userAffectations.some(aff => aff.statut) && (
                      <Button 
                        variant="outline" 
                        onClick={() => setIsTerminerAffectationModalOpen(true)}
                        className="flex-1"
                      >
                        <X className="h-4 w-4 mr-2" />
                        Terminer Affectation Actuelle
                      </Button>
                    )}
                  </div>

                  {/* Historique des affectations */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Historique des Affectations</CardTitle>
                    </CardHeader>
                    <CardContent>
                      {userAffectations.length > 0 ? (
                        <div className="space-y-4">
                          {userAffectations.map((affectation, index) => (
                            <div 
                              key={affectation.id} 
                              className={`p-4 rounded-lg border ${
                                affectation.statut 
                                  ? 'bg-green-50 border-green-200' 
                                  : 'bg-gray-50 border-gray-200'
                              }`}
                            >
                              <div className="flex justify-between items-start">
                                <div className="flex-1">
                                  <div className="flex items-center gap-2 mb-2">
                                    <Badge variant={affectation.statut ? "default" : "secondary"}>
                                      {affectation.statut ? "Actuelle" : "Terminée"}
                                    </Badge>
                                    <span className="font-medium">{affectation.poste.nom}</span>
                                  </div>
                                  <div className="text-sm text-gray-600 space-y-1">
                                    <div className="flex items-center gap-2">
                                      <Building className="h-4 w-4" />
                                      <span>{affectation.entite.nom} ({affectation.entite.type})</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                      <Calendar className="h-4 w-4" />
                                      <span>
                                        Du {new Date(affectation.date_debut).toLocaleDateString('fr-FR')}
                                        {affectation.date_fin && (
                                          <> au {new Date(affectation.date_fin).toLocaleDateString('fr-FR')}</>
                                        )}
                                      </span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <div className="text-center py-8">
                          <Building className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                          <h3 className="text-lg font-medium text-gray-900 mb-2">Aucune affectation</h3>
                          <p className="text-gray-500">Cet utilisateur n'a pas encore d'affectation</p>
                        </div>
                      )}
                    </CardContent>
                  </Card>
                </div>
              )}
            </DialogContent>
          </Dialog>

          {/* Modal de nouvelle affectation */}
          <Dialog open={isNewAffectationModalOpen} onOpenChange={setIsNewAffectationModalOpen}>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Nouvelle Affectation</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="affectation-entite">Entité *</Label>
                  <SearchableSelect
                    options={entites.map((entite) => ({
                      value: entite.id.toString(),
                      label: entite.nom,
                      description: `Type: ${entite.type_entite.nom}`,
                      badge: entite.type_entite.nom
                    }))}
                    value={affectationFormData.entite_id}
                    onValueChange={(value) => setAffectationFormData({...affectationFormData, entite_id: value})}
                    placeholder="Sélectionner une entité"
                    searchPlaceholder="Rechercher par nom d'entité..."
                    emptyMessage="Aucune entité trouvée"
                  />
                </div>

                <div>
                  <Label htmlFor="affectation-poste">Poste *</Label>
                  <SearchableSelect
                    options={postes.map((poste) => ({
                      value: poste.id.toString(),
                      label: poste.nom,
                      description: poste.description || "Aucune description"
                    }))}
                    value={affectationFormData.poste_id}
                    onValueChange={(value) => setAffectationFormData({...affectationFormData, poste_id: value})}
                    placeholder="Sélectionner un poste"
                    searchPlaceholder="Rechercher par nom de poste..."
                    emptyMessage="Aucun poste trouvé"
                  />
                </div>

                <div>
                  <Label htmlFor="affectation-date-debut">Date de début *</Label>
                  <Input
                    id="affectation-date-debut"
                    type="date"
                    value={affectationFormData.date_debut}
                    onChange={(e) => setAffectationFormData({...affectationFormData, date_debut: e.target.value})}
                  />
                </div>

                <div className="flex items-center space-x-2">
                  <Switch
                    id="terminer-precedente"
                    checked={affectationFormData.terminer_affectation_precedente}
                    onCheckedChange={(checked) => setAffectationFormData({
                      ...affectationFormData, 
                      terminer_affectation_precedente: checked
                    })}
                  />
                  <Label htmlFor="terminer-precedente" className="text-sm">
                    Terminer automatiquement l'affectation précédente
                  </Label>
                </div>

                <div className="flex gap-2 pt-4">
                  <Button 
                    variant="outline" 
                    onClick={() => {
                      setIsNewAffectationModalOpen(false);
                      resetAffectationForm();
                    }}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                  <Button 
                    onClick={handleNewAffectation}
                    disabled={isSubmitting}
                    className="flex-1"
                  >
                    {isSubmitting ? (
                      <><Loader2 className="h-4 w-4 animate-spin mr-2" />Affectation...</>
                    ) : (
                      <>Affecter</>
                    )}
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>

          {/* Modal de fin d'affectation */}
          <Dialog open={isTerminerAffectationModalOpen} onOpenChange={setIsTerminerAffectationModalOpen}>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Terminer l'Affectation Actuelle</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="termination-date-fin">Date de fin *</Label>
                  <Input
                    id="termination-date-fin"
                    type="date"
                    value={terminationFormData.date_fin}
                    onChange={(e) => setTerminationFormData({...terminationFormData, date_fin: e.target.value})}
                  />
                </div>

                <div>
                  <Label htmlFor="termination-raison">Raison (optionnelle)</Label>
                  <Textarea
                    id="termination-raison"
                    placeholder="Raison de la fin d'affectation..."
                    value={terminationFormData.raison}
                    onChange={(e) => setTerminationFormData({...terminationFormData, raison: e.target.value})}
                    rows={3}
                  />
                </div>

                <div className="flex gap-2 pt-4">
                  <Button 
                    variant="outline" 
                    onClick={() => {
                      setIsTerminerAffectationModalOpen(false);
                      resetTerminationForm();
                    }}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                  <Button 
                    onClick={handleTerminerAffectation}
                    disabled={isSubmitting}
                    variant="destructive"
                    className="flex-1"
                  >
                    {isSubmitting ? (
                      <><Loader2 className="h-4 w-4 animate-spin mr-2" />Finalisation...</>
                    ) : (
                      <>Terminer</>
                    )}
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>

          {/* Modal de gestion des rôles */}
          <Dialog open={isRolesModalOpen} onOpenChange={setIsRolesModalOpen}>
            <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle className="flex items-center gap-2">
                  <Shield className="h-5 w-5" />
                  Gestion des Rôles - {selectedUser?.prenom} {selectedUser?.nom}
                </DialogTitle>
              </DialogHeader>
              
              {loadingRoles ? (
                <div className="flex items-center justify-center py-8">
                  <Loader2 className="h-6 w-6 animate-spin mr-2" />
                  <span>Chargement des rôles...</span>
                </div>
              ) : (
                <div className="space-y-6">
                  {/* Actions rapides */}
                  <div className="flex gap-3">
                    <Button onClick={() => setIsAssignRoleModalOpen(true)} className="flex-1">
                      <Plus className="h-4 w-4 mr-2" />
                      Assigner un Rôle
                    </Button>
                  </div>

                  {/* Rôles actuels */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg">Rôles Actuels</CardTitle>
                    </CardHeader>
                    <CardContent>
                      {userRoles.length > 0 ? (
                        <div className="space-y-4">
                          {userRoles.map((role) => (
                            <div 
                              key={role.id} 
                              className="p-4 rounded-lg border bg-blue-50 border-blue-200"
                            >
                              <div className="flex justify-between items-start">
                                <div className="flex-1">
                                  <div className="flex items-center gap-2 mb-2">
                                    <Badge variant="default">
                                      <Crown className="h-3 w-3 mr-1" />
                                      {role.nom}
                                    </Badge>
                                  </div>
                                  {role.description && (
                                    <p className="text-sm text-gray-600 mb-2">{role.description}</p>
                                  )}
                                  {role.permissions && role.permissions.length > 0 && (
                                    <div className="text-sm text-gray-600">
                                      <p className="font-medium mb-1">Permissions incluses:</p>
                                      <div className="flex flex-wrap gap-1">
                                                                                 {role.permissions.slice(0, 3).map((permission: string, index: number) => (
                                           <Badge key={index} variant="outline" className="text-xs">
                                             {permission}
                                           </Badge>
                                         ))}
                                        {role.permissions.length > 3 && (
                                          <Badge variant="outline" className="text-xs">
                                            +{role.permissions.length - 3} autres
                                          </Badge>
                                        )}
                                      </div>
                                    </div>
                                  )}
                                </div>
                                <AlertDialog>
                                  <AlertDialogTrigger asChild>
                                    <Button variant="outline" size="sm" className="text-red-600 hover:text-red-700">
                                      <Minus className="h-4 w-4 mr-1" />
                                      Retirer
                                    </Button>
                                  </AlertDialogTrigger>
                                  <AlertDialogContent>
                                    <AlertDialogHeader>
                                      <AlertDialogTitle>Retirer le rôle</AlertDialogTitle>
                                      <AlertDialogDescription>
                                        Êtes-vous sûr de vouloir retirer le rôle "{role.nom}" à{' '}
                                        <strong>{selectedUser?.prenom} {selectedUser?.nom}</strong> ?
                                        Cette action supprimera toutes les permissions associées à ce rôle.
                                      </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                      <AlertDialogCancel>Annuler</AlertDialogCancel>
                                      <AlertDialogAction
                                        onClick={() => handleRemoveRole(role.id, role.nom)}
                                        className="bg-red-600 hover:bg-red-700"
                                      >
                                        Retirer le rôle
                                      </AlertDialogAction>
                                    </AlertDialogFooter>
                                  </AlertDialogContent>
                                </AlertDialog>
                              </div>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <div className="text-center py-8">
                          <Shield className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                          <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun rôle assigné</h3>
                          <p className="text-gray-500">Cet utilisateur n'a pas encore de rôle</p>
                        </div>
                      )}
                    </CardContent>
                  </Card>
                </div>
              )}
            </DialogContent>
          </Dialog>

          {/* Modal d'assignation de rôle */}
          <Dialog open={isAssignRoleModalOpen} onOpenChange={setIsAssignRoleModalOpen}>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Assigner un Rôle</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="role-select">Rôle *</Label>
                  <SearchableSelect
                    options={availableRoles
                      .filter(role => !userRoles.some(userRole => userRole.id === role.id))
                      .map((role) => ({
                        value: role.id.toString(),
                        label: role.nom,
                        description: role.description || "Aucune description",
                        badge: "Rôle"
                      }))}
                    value={roleFormData.role_id}
                    onValueChange={(value) => setRoleFormData({...roleFormData, role_id: value})}
                    placeholder="Sélectionner un rôle"
                    searchPlaceholder="Rechercher un rôle..."
                    emptyMessage="Aucun rôle disponible"
                  />
                  {availableRoles.filter(role => !userRoles.some(userRole => userRole.id === role.id)).length === 0 && (
                    <p className="text-sm text-gray-500 mt-2">
                      Tous les rôles disponibles sont déjà assignés à cet utilisateur.
                    </p>
                  )}
                </div>

                <div className="flex gap-2 pt-4">
                  <Button 
                    variant="outline" 
                    onClick={() => {
                      setIsAssignRoleModalOpen(false);
                      resetRoleForm();
                    }}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                  <Button 
                    onClick={handleAssignRole}
                    disabled={isSubmitting || !roleFormData.role_id}
                    className="flex-1"
                  >
                    {isSubmitting ? (
                      <><Loader2 className="h-4 w-4 animate-spin mr-2" />Assignation...</>
                    ) : (
                      <>Assigner</>
                    )}
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </main>
      </div>
    </div>
    </ProtectedPage>
  );
} 