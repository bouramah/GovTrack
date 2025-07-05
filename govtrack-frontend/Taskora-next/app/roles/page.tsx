"use client";

import { useState, useEffect } from "react";
import { useDebounce } from "@/hooks/use-debounce";
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { useToast } from "@/components/ui/use-toast";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Checkbox } from "@/components/ui/checkbox";
import { 
  Shield,
  Key,
  Users,
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  Plus,
  Search,
  Calendar,
  UserPlus,
  UserMinus,
  Settings,
  Crown,
  CheckCircle,
  XCircle,
  Filter,
  ChevronLeft,
  ChevronRight,
  ChevronFirst,
  ChevronLast,
  BarChart3
} from "lucide-react";
import { apiClient, Role, Permission, User } from "@/lib/api";
import { useRolePermissions } from "@/hooks/useRolePermissions";
import { usePermissionPermissions } from "@/hooks/usePermissionPermissions";
import { ProtectedPage } from "@/components/ProtectedPage";
import {
  RoleListGuard,
  RoleCreateGuard,
  RoleEditGuard,
  RoleDeleteGuard,
  RoleDetailsGuard,
  RoleAssignPermissionsGuard,
  RoleRemovePermissionsGuard,
  RoleUsersGuard,
  RoleAssignToUserGuard,
  RoleRemoveFromUserGuard,
  RoleStatsGuard,
} from "@/components/Shared/RoleGuards";
import {
  PermissionListGuard,
  PermissionCreateGuard,
  PermissionEditGuard,
  PermissionDeleteGuard,
  PermissionDetailsGuard,
  PermissionUsersGuard,
  PermissionRolesGuard,
  PermissionStatsGuard,
} from "@/components/Shared/PermissionGuards";
import { ConditionalSeparator } from "@/components/Shared/ConditionalSeparator";
import { formatBackendErrors } from "@/lib/utils";
import { ScrollArea } from "@/components/ui/scroll-area";
import PermissionManager from '@/components/Shared/PermissionManager';
import RoleManager from '@/components/Shared/RoleManager';

interface RoleWithDetails extends Role {
  permissions_count?: number;
  users_count?: number;
  nombre_permissions?: number;
  nombre_utilisateurs?: number;
  utilisateurs?: any[];
  permissions?: Permission[];
}

interface PermissionWithDetails extends Permission {
  roles_count?: number;
  users_count?: number;
  nombre_roles?: number;
  roles?: Role[];
  utilisateurs?: any[];
}

export default function RolesPermissionsPage() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [roles, setRoles] = useState<RoleWithDetails[]>([]);
  const [permissions, setPermissions] = useState<PermissionWithDetails[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("roles");
  const [searchTerm, setSearchTerm] = useState("");
  const [searchTermPermissions, setSearchTermPermissions] = useState("");
  
  // Valeurs debounced pour éviter les appels API trop fréquents
  const debouncedSearchTerm = useDebounce(searchTerm, 1000);
  const debouncedSearchTermPermissions = useDebounce(searchTermPermissions, 1000);
  
  // États pour la pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [itemsPerPage, setItemsPerPage] = useState(15);
  
  // États pour la pagination des permissions
  const [currentPagePermissions, setCurrentPagePermissions] = useState(1);
  const [totalPagesPermissions, setTotalPagesPermissions] = useState(1);
  const [totalItemsPermissions, setTotalItemsPermissions] = useState(0);
  const [itemsPerPagePermissions, setItemsPerPagePermissions] = useState(15);

  // États des modales pour les rôles
  const [showCreateRoleModal, setShowCreateRoleModal] = useState(false);
  const [showEditRoleModal, setShowEditRoleModal] = useState(false);
  const [showDetailRoleModal, setShowDetailRoleModal] = useState(false);
  const [showDeleteRoleDialog, setShowDeleteRoleDialog] = useState(false);
  const [showManagePermissionsModal, setShowManagePermissionsModal] = useState(false);

  // États des modales pour les permissions
  const [showCreatePermissionModal, setShowCreatePermissionModal] = useState(false);
  const [showEditPermissionModal, setShowEditPermissionModal] = useState(false);
  const [showDetailPermissionModal, setShowDetailPermissionModal] = useState(false);
  const [showDeletePermissionDialog, setShowDeletePermissionDialog] = useState(false);
  const [showManageRolesModal, setShowManageRolesModal] = useState(false);
  const [showPermissionUsersModal, setShowPermissionUsersModal] = useState(false);

  // Données sélectionnées
  const [selectedRole, setSelectedRole] = useState<RoleWithDetails | null>(null);
  const [selectedPermission, setSelectedPermission] = useState<PermissionWithDetails | null>(null);
  const [roleToDelete, setRoleToDelete] = useState<RoleWithDetails | null>(null);
  const [permissionToDelete, setPermissionToDelete] = useState<PermissionWithDetails | null>(null);
  const [availablePermissions, setAvailablePermissions] = useState<Permission[]>([]);
  const [availableRoles, setAvailableRoles] = useState<Role[]>([]);
  const [permissionUsers, setPermissionUsers] = useState<User[]>([]);

  // Formulaires
  const [roleFormData, setRoleFormData] = useState({
    nom: "",
    description: ""
  });

  const [permissionFormData, setPermissionFormData] = useState({
    nom: "",
    description: ""
  });

  const [selectedPermissionsForRole, setSelectedPermissionsForRole] = useState<number[]>([]);
  const [selectedRolesForPermission, setSelectedRolesForPermission] = useState<number[]>([]);

  const { toast } = useToast();

  const rolePermissions = useRolePermissions();
  const permissionPermissions = usePermissionPermissions();

  useEffect(() => {
    loadData();
  }, [currentPage, itemsPerPage, debouncedSearchTerm, currentPagePermissions, itemsPerPagePermissions, debouncedSearchTermPermissions]);

  // Gestionnaires de pagination pour les rôles
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

  // Gestionnaires de pagination pour les permissions
  const handlePageChangePermissions = (page: number) => {
    setCurrentPagePermissions(page);
  };

  const handleItemsPerPageChangePermissions = (newItemsPerPage: number) => {
    setItemsPerPagePermissions(newItemsPerPage);
    setCurrentPagePermissions(1); // Reset à la première page
  };

  const handleSearchPermissions = (value: string) => {
    setSearchTermPermissions(value);
    setCurrentPagePermissions(1); // Reset à la première page lors de la recherche
  };

  const loadData = async () => {
    try {
      setLoading(true);
      
      // Paramètres pour l'API rôles
      const roleParams = {
        nom: debouncedSearchTerm || undefined,
        page: currentPage,
        per_page: itemsPerPage,
        sort_by: 'nom',
        sort_order: 'asc' as const
      };

      // Paramètres pour l'API permissions
      const permissionParams = {
        nom: debouncedSearchTermPermissions || undefined,
        page: currentPagePermissions,
        per_page: itemsPerPagePermissions,
        sort_by: 'nom',
        sort_order: 'asc' as const
      };

      const [rolesResponse, permissionsResponse, usersData] = await Promise.all([
        apiClient.getRoles(roleParams),
        apiClient.getPermissions(permissionParams),
        apiClient.getUsersDetailed()
      ]);

      setRoles(rolesResponse.data || []);
      setTotalPages(rolesResponse.pagination?.last_page || 1);
      setTotalItems(rolesResponse.pagination?.total || 0);
      
      setPermissions(permissionsResponse.data || []);
      setTotalPagesPermissions(permissionsResponse.pagination?.last_page || 1);
      setTotalItemsPermissions(permissionsResponse.pagination?.total || 0);
      

      
      setUsers(usersData.data || []);
    } catch (error) {
      console.error("Erreur de chargement:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  // ============================================
  // FONCTIONS GESTION DES RÔLES
  // ============================================

  const handleCreateRole = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await apiClient.createRole(roleFormData);
      await loadData();
      setShowCreateRoleModal(false);
      setRoleFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Rôle créé avec succès"
      });
    } catch (error: any) {
      console.error("Erreur création rôle:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdateRole = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedRole) return;
    
    try {
      await apiClient.updateRole(selectedRole.id, roleFormData);
      await loadData();
      setShowEditRoleModal(false);
      setSelectedRole(null);
      setRoleFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Rôle modifié avec succès"
      });
    } catch (error: any) {
      console.error("Erreur modification rôle:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeleteRole = async () => {
    if (!roleToDelete) return;
    
    try {
      await apiClient.deleteRole(roleToDelete.id);
      await loadData();
      setShowDeleteRoleDialog(false);
      setRoleToDelete(null);
      toast({
        title: "✅ Succès",
        description: "Rôle supprimé avec succès"
      });
    } catch (error: any) {
      console.error("Erreur suppression rôle:", error);
      toast({
        title: "❌ Erreur", 
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewRoleDetails = async (role: RoleWithDetails) => {
    try {
      const response = await apiClient.getRole(role.id);
      setSelectedRole(response);
      setShowDetailRoleModal(true);
    } catch (error: any) {
      console.error("Erreur détails rôle:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openEditRoleModal = (role: RoleWithDetails) => {
    setSelectedRole(role);
    setRoleFormData({
      nom: role.nom,
      description: role.description || ""
    });
    setShowEditRoleModal(true);
  };

  const openDeleteRoleDialog = (role: RoleWithDetails) => {
    setRoleToDelete(role);
    setShowDeleteRoleDialog(true);
  };

  const openManagePermissionsModal = async (role: RoleWithDetails) => {
    try {
      const [roleDetails, availablePermsResponse] = await Promise.all([
        apiClient.getRole(role.id),
        apiClient.getAvailablePermissionsForRole(role.id)
      ]);
      
      setSelectedRole(roleDetails);
      // L'API retourne { role, permissions_disponibles, permissions_deja_assignees }
      setAvailablePermissions(availablePermsResponse.permissions_disponibles || []);
      setSelectedPermissionsForRole(roleDetails.permissions?.map((p: Permission) => p.id) || []);
      setShowManagePermissionsModal(true);
    } catch (error: any) {
      console.error("Erreur chargement permissions:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleAssignPermissionToRole = async (permissionId: number) => {
    if (!selectedRole) return;
    
    try {
      await apiClient.assignPermissionToRole(selectedRole.id, { permission_id: permissionId });
      
      // Mettre à jour le rôle sélectionné
      const updatedRole = await apiClient.getRole(selectedRole.id);
      setSelectedRole(updatedRole);
      setSelectedPermissionsForRole(updatedRole.permissions?.map((p: Permission) => p.id) || []);
      
      // Mettre à jour les permissions disponibles
      const availablePermsResponse = await apiClient.getAvailablePermissionsForRole(selectedRole.id);
      setAvailablePermissions(availablePermsResponse.permissions_disponibles || []);
      
      // Recharger les données générales
      await loadData();
      
      toast({
        title: "✅ Succès",
        description: "Permission assignée au rôle"
      });
    } catch (error: any) {
      console.error("Erreur assignation permission:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleRemovePermissionFromRole = async (permissionId: number) => {
    if (!selectedRole) return;
    
    try {
      await apiClient.removePermissionFromRole(selectedRole.id, permissionId);
      
      // Mettre à jour le rôle sélectionné
      const updatedRole = await apiClient.getRole(selectedRole.id);
      setSelectedRole(updatedRole);
      setSelectedPermissionsForRole(updatedRole.permissions?.map((p: Permission) => p.id) || []);
      
      // Mettre à jour les permissions disponibles
      const availablePermsResponse = await apiClient.getAvailablePermissionsForRole(selectedRole.id);
      setAvailablePermissions(availablePermsResponse.permissions_disponibles || []);
      
      // Recharger les données générales
      await loadData();
      
      toast({
        title: "✅ Succès",
        description: "Permission retirée du rôle"
      });
    } catch (error: any) {
      console.error("Erreur retrait permission:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  // ============================================
  // FONCTIONS GESTION DES PERMISSIONS
  // ============================================

  const handleCreatePermission = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await apiClient.createPermission(permissionFormData);
      await loadData();
      setShowCreatePermissionModal(false);
      setPermissionFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Permission créée avec succès"
      });
    } catch (error: any) {
      console.error("Erreur création permission:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleUpdatePermission = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPermission) return;
    
    try {
      await apiClient.updatePermission(selectedPermission.id, permissionFormData);
      await loadData();
      setShowEditPermissionModal(false);
      setSelectedPermission(null);
      setPermissionFormData({ nom: "", description: "" });
      toast({
        title: "✅ Succès",
        description: "Permission modifiée avec succès"
      });
    } catch (error: any) {
      console.error("Erreur modification permission:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleDeletePermission = async () => {
    if (!permissionToDelete) return;
    
    try {
      await apiClient.deletePermission(permissionToDelete.id);
      await loadData();
      setShowDeletePermissionDialog(false);
      setPermissionToDelete(null);
      toast({
        title: "✅ Succès",
        description: "Permission supprimée avec succès"
      });
    } catch (error: any) {
      console.error("Erreur suppression permission:", error);
      toast({
        title: "❌ Erreur", 
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const handleViewPermissionDetails = async (permission: PermissionWithDetails) => {
    try {
      const response = await apiClient.getPermission(permission.id);
      setSelectedPermission(response);
      setShowDetailPermissionModal(true);
    } catch (error: any) {
      console.error("Erreur détails permission:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openEditPermissionModal = (permission: PermissionWithDetails) => {
    setSelectedPermission(permission);
    setPermissionFormData({
      nom: permission.nom,
      description: permission.description || ""
    });
    setShowEditPermissionModal(true);
  };

  const openDeletePermissionDialog = (permission: PermissionWithDetails) => {
    setPermissionToDelete(permission);
    setShowDeletePermissionDialog(true);
  };

  const openManageRolesModal = async (permission: PermissionWithDetails) => {
    try {
      const [permissionDetails, availableRolesResponse] = await Promise.all([
        apiClient.getPermission(permission.id),
        apiClient.getAvailableRolesForPermission(permission.id)
      ]);
      
      setSelectedPermission(permissionDetails);
      // L'API retourne { permission, roles_disponibles, roles_deja_assignes }
      setAvailableRoles(availableRolesResponse.roles_disponibles || []);
      setSelectedRolesForPermission(permissionDetails.roles?.map((r: Role) => r.id) || []);
      setShowManageRolesModal(true);
    } catch (error: any) {
      console.error("Erreur chargement rôles:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const openPermissionUsersModal = async (permission: PermissionWithDetails) => {
    try {
      const response = await apiClient.getPermissionUsers(permission.id);
      // L'API retourne { permission, utilisateurs, statistiques }
      setPermissionUsers(response.utilisateurs || []);
      setSelectedPermission(permission);
      setShowPermissionUsersModal(true);
    } catch (error: any) {
      console.error("Erreur chargement utilisateurs:", error);
      toast({
        title: "❌ Erreur",
        description: formatBackendErrors(error),
        variant: "destructive"
      });
    }
  };

  const filteredRoles = roles.filter(role =>
    role.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (role.description || "").toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredPermissions = permissions.filter(permission =>
    permission.nom.toLowerCase().includes(searchTermPermissions.toLowerCase()) ||
    (permission.description || "").toLowerCase().includes(searchTermPermissions.toLowerCase())
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

  // Vérifier si l'utilisateur a au moins une permission pour voir cette page
  const hasAnyPermission = rolePermissions.canViewList || permissionPermissions.canViewList;

  if (!hasAnyPermission) {
    return (
      <ProtectedPage 
        permissions={['view_roles_list', 'view_permissions_list']}
        redirectTo="/"
      >
        <div>Contenu protégé</div>
      </ProtectedPage>
    );
  }

  if (loading) {
    return (
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar name="Rôles & Permissions" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <div className="flex items-center justify-center min-h-[400px]">
              <div className="text-center">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-4 text-gray-600">Chargement des rôles et permissions...</p>
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
        <Topbar name="Rôles & Permissions" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
        <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="max-w-7xl mx-auto">
            {/* En-tête */}
            <div className="flex justify-between items-center mb-6">
        <div>
                <h1 className="text-3xl font-bold text-gray-900">Rôles & Permissions</h1>
                <p className="text-gray-600">Gérer le système de contrôle d'accès basé sur les rôles (RBAC)</p>
        </div>
      </div>

            {/* Statistiques */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Rôles</CardTitle>
            <Shield className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{roles.length}</div>
                  <p className="text-xs text-muted-foreground">rôles configurés</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Permissions</CardTitle>
            <Key className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{permissions.length}</div>
                  <p className="text-xs text-muted-foreground">permissions disponibles</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Utilisateurs avec Rôles</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {users.filter(u => u.roles && u.roles.length > 0).length}
            </div>
                  <p className="text-xs text-muted-foreground">ont des rôles assignés</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Rôles Actifs</CardTitle>
            <Settings className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
                    {roles.filter(r => (r.nombre_utilisateurs || 0) > 0).length}
            </div>
                  <p className="text-xs text-muted-foreground">avec utilisateurs</p>
          </CardContent>
        </Card>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList>
          <RoleListGuard>
            <TabsTrigger value="roles">
              <Shield className="h-4 w-4 mr-2" />
              Rôles
            </TabsTrigger>
          </RoleListGuard>
          <PermissionListGuard>
            <TabsTrigger value="permissions">
              <Key className="h-4 w-4 mr-2" />
              Permissions
            </TabsTrigger>
          </PermissionListGuard>
          <TabsTrigger value="overview">
            <Users className="h-4 w-4 mr-2" />
            Vue d'ensemble
          </TabsTrigger>
        </TabsList>

              {/* Onglet Rôles */}
        <TabsContent value="roles" className="space-y-4">
          <Card>
                  <CardHeader className="flex flex-row items-center justify-between">
                    <CardTitle className="flex items-center gap-2">
                      <Shield className="h-5 w-5" />
                      Rôles ({roles.length})
                    </CardTitle>
                    <RoleCreateGuard>
                      <Button onClick={() => setShowCreateRoleModal(true)}>
                        <Plus className="h-4 w-4 mr-2" />
                        Nouveau Rôle
                      </Button>
                    </RoleCreateGuard>
                  </CardHeader>
            <CardContent>
                    {/* Recherche */}
                    <div className="mb-4">
                      <div className="relative">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input
                          placeholder="Rechercher un rôle..."
                          value={searchTerm}
                          onChange={(e) => handleSearch(e.target.value)}
                          className="pl-9"
                        />
                        </div>
                        </div>

                    {/* Liste des rôles */}
                    <div className="space-y-4">
                      {filteredRoles.map((role) => (
                        <Card key={role.id} className="hover:shadow-md transition-shadow">
                          <CardContent className="pt-4">
                            <div className="flex justify-between items-start">
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-2">
                                  <h3 className="font-semibold text-lg">{role.nom}</h3>
                                  <Badge variant={(role.nombre_utilisateurs || 0) > 0 ? "default" : "secondary"}>
                                    {role.nombre_utilisateurs || 0} utilisateurs
                          </Badge>
                                  <Badge variant="outline">
                                    {role.nombre_permissions || 0} permissions
                          </Badge>
                                </div>
                                
                                {role.description && (
                                  <p className="text-sm text-muted-foreground mb-2">
                                    {role.description}
                                  </p>
                                )}
                                
                                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                  <div className="flex items-center gap-1">
                                    <Calendar className="h-4 w-4" />
                                    <span>Créé le {formatDate(role.date_creation)}</span>
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
                                  <RoleDetailsGuard>
                                    <DropdownMenuItem onClick={() => handleViewRoleDetails(role)}>
                                      <Eye className="h-4 w-4 mr-2" />
                                      Voir détails
                                    </DropdownMenuItem>
                                  </RoleDetailsGuard>
                                  <RoleEditGuard>
                                    <DropdownMenuItem onClick={() => openEditRoleModal(role)}>
                                      <Edit className="h-4 w-4 mr-2" />
                                      Modifier
                                    </DropdownMenuItem>
                                  </RoleEditGuard>
                                  <RoleAssignPermissionsGuard>
                                    <DropdownMenuItem onClick={() => openManagePermissionsModal(role)}>
                                      <Settings className="h-4 w-4 mr-2" />
                                      Gérer permissions
                                    </DropdownMenuItem>
                                  </RoleAssignPermissionsGuard>
                                  <ConditionalSeparator showIfAnyVisible={rolePermissions.canDelete} />
                                  <RoleDeleteGuard>
                                    <DropdownMenuItem 
                                      onClick={() => openDeleteRoleDialog(role)} 
                                      className="text-destructive"
                                    >
                                      <Trash2 className="h-4 w-4 mr-2" />
                                      Supprimer
                                    </DropdownMenuItem>
                                  </RoleDeleteGuard>
                                </DropdownMenuContent>
                              </DropdownMenu>
                            </div>
                          </CardContent>
                        </Card>
                      ))}
                    </div>
                    
                    {filteredRoles.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                        <Shield className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                        <p>
                          {searchTerm 
                            ? 'Aucun rôle trouvé pour cette recherche' 
                            : 'Aucun rôle trouvé'
                          }
                        </p>
                        {!searchTerm && (
                          <RoleCreateGuard>
                            <Button 
                              variant="outline" 
                              className="mt-4"
                              onClick={() => setShowCreateRoleModal(true)}
                            >
                              <Plus className="h-4 w-4 mr-2" />
                              Créer le premier rôle
                            </Button>
                          </RoleCreateGuard>
                        )}
                      </div>
                    )}

                    {/* Pagination des rôles */}
                    {totalPages > 1 && (
                      <div className="flex items-center justify-between mt-6">
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                          <span>
                            Affichage de {((currentPage - 1) * itemsPerPage) + 1} à {Math.min(currentPage * itemsPerPage, totalItems)} sur {totalItems} rôles
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

              {/* Onglet Permissions */}
        <TabsContent value="permissions" className="space-y-4">
          <Card>
                  <CardHeader className="flex flex-row items-center justify-between">
                    <CardTitle className="flex items-center gap-2">
                      <Key className="h-5 w-5" />
                      Permissions ({permissions.length})
                    </CardTitle>
                    <PermissionCreateGuard>
                      <Button onClick={() => setShowCreatePermissionModal(true)}>
                        <Plus className="h-4 w-4 mr-2" />
                        Nouvelle Permission
                      </Button>
                    </PermissionCreateGuard>
            </CardHeader>
            <CardContent>
                    {/* Recherche */}
                    <div className="mb-4">
                      <div className="relative">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input
                          placeholder="Rechercher une permission..."
                          value={searchTermPermissions}
                          onChange={(e) => handleSearchPermissions(e.target.value)}
                          className="pl-9"
                        />
                        </div>
                    </div>

                    {/* Liste des permissions */}
                    <div className="space-y-4">
                      {filteredPermissions.map((permission) => (
                        <Card key={permission.id} className="hover:shadow-md transition-shadow">
                          <CardContent className="pt-4">
                            <div className="flex justify-between items-start">
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-2">
                                  <h3 className="font-semibold text-lg">{permission.nom}</h3>
                        <Badge variant="outline">
                                    {permission.roles?.length || 0} rôles
                        </Badge>
                        </div>
                                
                                {permission.description && (
                                  <p className="text-sm text-muted-foreground mb-2">
                                    {permission.description}
                                  </p>
                                )}
                                
                                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                  <div className="flex items-center gap-1">
                                    <Calendar className="h-4 w-4" />
                                    <span>Créée le {formatDate(permission.date_creation)}</span>
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
                                  <PermissionDetailsGuard>
                                    <DropdownMenuItem onClick={() => handleViewPermissionDetails(permission)}>
                                      <Eye className="h-4 w-4 mr-2" />
                                      Voir détails
                                    </DropdownMenuItem>
                                  </PermissionDetailsGuard>
                                  <PermissionEditGuard>
                                    <DropdownMenuItem onClick={() => openEditPermissionModal(permission)}>
                                      <Edit className="h-4 w-4 mr-2" />
                                      Modifier
                                    </DropdownMenuItem>
                                  </PermissionEditGuard>
                                  <PermissionRolesGuard>
                                    <DropdownMenuItem onClick={() => openManageRolesModal(permission)}>
                                      <Settings className="h-4 w-4 mr-2" />
                                      Gérer rôles
                                    </DropdownMenuItem>
                                  </PermissionRolesGuard>
                                  <PermissionUsersGuard>
                                    <DropdownMenuItem onClick={() => openPermissionUsersModal(permission)}>
                                      <Users className="h-4 w-4 mr-2" />
                                      Voir utilisateurs
                                    </DropdownMenuItem>
                                  </PermissionUsersGuard>
                                  <ConditionalSeparator showIfAnyVisible={permissionPermissions.canDelete} />
                                  <PermissionDeleteGuard>
                                    <DropdownMenuItem 
                                      onClick={() => openDeletePermissionDialog(permission)} 
                                      className="text-destructive"
                                    >
                                      <Trash2 className="h-4 w-4 mr-2" />
                                      Supprimer
                                    </DropdownMenuItem>
                                  </PermissionDeleteGuard>
                                </DropdownMenuContent>
                              </DropdownMenu>
                            </div>
                          </CardContent>
                        </Card>
                      ))}
                    </div>
                    
                    {filteredPermissions.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                        <Key className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                        <p>
                          {searchTermPermissions 
                            ? 'Aucune permission trouvée pour cette recherche' 
                            : 'Aucune permission trouvée'
                          }
                        </p>
                        {!searchTermPermissions && (
                          <PermissionCreateGuard>
                            <Button 
                              variant="outline" 
                              className="mt-4"
                              onClick={() => setShowCreatePermissionModal(true)}
                            >
                              <Plus className="h-4 w-4 mr-2" />
                              Créer la première permission
                            </Button>
                          </PermissionCreateGuard>
                        )}
                      </div>
                    )}

                    {/* Pagination des permissions */}
                    {totalPagesPermissions > 1 && (
                      <div className="flex items-center justify-between mt-6">
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                          <span>
                            Affichage de {((currentPagePermissions - 1) * itemsPerPagePermissions) + 1} à {Math.min(currentPagePermissions * itemsPerPagePermissions, totalItemsPermissions)} sur {totalItemsPermissions} permissions
                          </span>
                        </div>
                        
                        <div className="flex items-center gap-2">
                          <Select value={itemsPerPagePermissions.toString()} onValueChange={(value) => handleItemsPerPageChangePermissions(parseInt(value))}>
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
                              onClick={() => handlePageChangePermissions(1)}
                              disabled={currentPagePermissions === 1}
                            >
                              <ChevronFirst className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handlePageChangePermissions(currentPagePermissions - 1)}
                              disabled={currentPagePermissions === 1}
                            >
                              <ChevronLeft className="h-4 w-4" />
                            </Button>
                            
                            <div className="flex items-center gap-1">
                              {Array.from({ length: Math.min(5, totalPagesPermissions) }, (_, i) => {
                                let pageNum;
                                if (totalPagesPermissions <= 5) {
                                  pageNum = i + 1;
                                } else if (currentPagePermissions <= 3) {
                                  pageNum = i + 1;
                                } else if (currentPagePermissions >= totalPagesPermissions - 2) {
                                  pageNum = totalPagesPermissions - 4 + i;
                                } else {
                                  pageNum = currentPagePermissions - 2 + i;
                                }
                                
                                return (
                                  <Button
                                    key={pageNum}
                                    variant={currentPagePermissions === pageNum ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => handlePageChangePermissions(pageNum)}
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
                              onClick={() => handlePageChangePermissions(currentPagePermissions + 1)}
                              disabled={currentPagePermissions === totalPagesPermissions}
                            >
                              <ChevronRight className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handlePageChangePermissions(totalPagesPermissions)}
                              disabled={currentPagePermissions === totalPagesPermissions}
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

              {/* Onglet Vue d'ensemble */}
              <TabsContent value="overview" className="space-y-4">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Rôles les plus utilisés */}
          <Card>
            <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Crown className="h-5 w-5" />
                        Rôles les plus utilisés
                      </CardTitle>
            </CardHeader>
            <CardContent>
                      <div className="space-y-4">
                        {roles
                          .sort((a, b) => (b.nombre_utilisateurs || 0) - (a.nombre_utilisateurs || 0))
                          .slice(0, 5)
                          .map((role) => (
                            <div key={role.id} className="flex items-center justify-between">
                              <div className="flex items-center gap-2">
                                <Shield className="h-4 w-4 text-muted-foreground" />
                                <span className="font-medium">{role.nom}</span>
                              </div>
                              <Badge variant="secondary">
                                {role.nombre_utilisateurs || 0} utilisateurs
                              </Badge>
                            </div>
                          ))}
                      </div>
                    </CardContent>
                  </Card>

                  {/* Permissions les plus assignées */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Key className="h-5 w-5" />
                        Permissions les plus assignées
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-4">
                        {permissions
                          .sort((a, b) => (b.roles?.length || 0) - (a.roles?.length || 0))
                          .slice(0, 5)
                          .map((permission) => (
                            <div key={permission.id} className="flex items-center justify-between">
                              <div className="flex items-center gap-2">
                                <Key className="h-4 w-4 text-muted-foreground" />
                                <span className="font-medium">{permission.nom}</span>
                              </div>
                              <Badge variant="outline">
                                {permission.roles?.length || 0} rôles
                              </Badge>
                            </div>
                          ))}
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </TabsContent>
            </Tabs>

            {/* MODALES - ROLES */}
            
            {/* Modal Création Rôle */}
            <Dialog open={showCreateRoleModal} onOpenChange={setShowCreateRoleModal}>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Créer un nouveau rôle</DialogTitle>
                  <DialogDescription>
                    Ajoutez un nouveau rôle au système. Vous pourrez gérer ses permissions par la suite.
                  </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleCreateRole}>
                  <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                      <Label htmlFor="role-nom">Nom du rôle *</Label>
                      <Input
                        id="role-nom"
                        value={roleFormData.nom}
                        onChange={(e) => setRoleFormData({...roleFormData, nom: e.target.value})}
                        placeholder="Ex: Administrateur, Gestionnaire..."
                        required
                      />
                    </div>
                    <div className="grid gap-2">
                      <Label htmlFor="role-description">Description</Label>
                      <Textarea
                        id="role-description"
                        value={roleFormData.description}
                        onChange={(e) => setRoleFormData({...roleFormData, description: e.target.value})}
                        placeholder="Description du rôle et de ses responsabilités..."
                        rows={3}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => setShowCreateRoleModal(false)}>
                      Annuler
                    </Button>
                    <Button type="submit">Créer le rôle</Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>

            {/* Modal Modification Rôle */}
            <Dialog open={showEditRoleModal} onOpenChange={setShowEditRoleModal}>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Modifier le rôle</DialogTitle>
                  <DialogDescription>
                    Modifiez les informations de ce rôle.
                  </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleUpdateRole}>
                  <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                      <Label htmlFor="edit-role-nom">Nom du rôle *</Label>
                      <Input
                        id="edit-role-nom"
                        value={roleFormData.nom}
                        onChange={(e) => setRoleFormData({...roleFormData, nom: e.target.value})}
                        required
                      />
                    </div>
                    <div className="grid gap-2">
                      <Label htmlFor="edit-role-description">Description</Label>
                      <Textarea
                        id="edit-role-description"
                        value={roleFormData.description}
                        onChange={(e) => setRoleFormData({...roleFormData, description: e.target.value})}
                        rows={3}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => setShowEditRoleModal(false)}>
                      Annuler
                    </Button>
                    <Button type="submit">Enregistrer</Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>

            {/* Modal Détails Rôle */}
            <Dialog open={showDetailRoleModal} onOpenChange={setShowDetailRoleModal}>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle className="flex items-center gap-2">
                    <Shield className="h-5 w-5" />
                    Détails du rôle : {selectedRole?.nom}
                  </DialogTitle>
                </DialogHeader>
                {selectedRole && (
                  <div className="space-y-6">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                        <Label className="text-sm font-medium text-muted-foreground">Nom</Label>
                        <p className="text-lg font-semibold">{selectedRole.nom}</p>
                          </div>
                      <div>
                        <Label className="text-sm font-medium text-muted-foreground">Utilisateurs</Label>
                        <p className="text-lg font-semibold">{selectedRole.statistiques?.total_utilisateurs || 0}</p>
                          </div>
                        </div>
                    
                    {selectedRole.description && (
                      <div>
                        <Label className="text-sm font-medium text-muted-foreground">Description</Label>
                        <p className="mt-1">{selectedRole.description}</p>
                      </div>
                    )}

                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">
                        Permissions ({selectedRole.permissions?.length || 0})
                      </Label>
                      <div className="mt-2 space-y-2 max-h-40 overflow-y-auto">
                        {selectedRole.permissions?.map((permission) => (
                          <div key={permission.id} className="flex items-center gap-2 p-2 bg-gray-50 rounded">
                            <Key className="h-4 w-4 text-muted-foreground" />
                            <span className="font-medium">{permission.nom}</span>
                        </div>
                        )) || <p className="text-muted-foreground">Aucune permission assignée</p>}
                      </div>
                    </div>

                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">
                        Utilisateurs avec ce rôle ({selectedRole.utilisateurs?.length || 0})
                      </Label>
                      <div className="mt-2 space-y-2 max-h-40 overflow-y-auto">
                        {selectedRole.utilisateurs && selectedRole.utilisateurs.length > 0 ? (
                          selectedRole.utilisateurs.map((user: any) => (
                            <div key={user.id} className="flex items-center gap-2 p-2 bg-gray-50 rounded">
                              <Users className="h-4 w-4 text-muted-foreground" />
                              <span className="font-medium">{user.prenom} {user.nom}</span>
                              <span className="text-sm text-muted-foreground">({user.matricule})</span>
                            </div>
                            ))
                          ) : (
                          <p className="text-muted-foreground">Aucun utilisateur assigné à ce rôle</p>
                          )}
                        </div>
                    </div>

                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                      <div>Créé le {formatDate(selectedRole.date_creation)}</div>
                      {selectedRole.date_modification && (
                        <div>Modifié le {formatDate(selectedRole.date_modification)}</div>
                      )}
                    </div>
                  </div>
                )}
                <DialogFooter>
                  <Button onClick={() => setShowDetailRoleModal(false)}>Fermer</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>

            {/* Dialog Suppression Rôle */}
            <AlertDialog open={showDeleteRoleDialog} onOpenChange={setShowDeleteRoleDialog}>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Supprimer le rôle</AlertDialogTitle>
                  <AlertDialogDescription>
                    Êtes-vous sûr de vouloir supprimer le rôle "{roleToDelete?.nom}" ?
                    {(roleToDelete?.nombre_utilisateurs || 0) > 0 && (
                      <div className="mt-2 p-3 bg-orange-50 border border-orange-200 rounded">
                        <p className="text-orange-800 text-sm">
                          ⚠️ Attention : Ce rôle est assigné à {roleToDelete?.nombre_utilisateurs || 0} utilisateur(s). 
                          La suppression retirera automatiquement ce rôle de tous les utilisateurs.
                        </p>
                      </div>
                    )}
                    Cette action est irréversible.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Annuler</AlertDialogCancel>
                  <AlertDialogAction onClick={handleDeleteRole} className="bg-destructive hover:bg-destructive/90">
                    Supprimer
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>

            {/* Modal Gestion Permissions pour Rôle */}
            <Dialog open={showManagePermissionsModal} onOpenChange={setShowManagePermissionsModal}>
              <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle className="flex items-center gap-2">
                    <Settings className="h-5 w-5" />
                    Gérer les permissions : {selectedRole?.nom}
                  </DialogTitle>
                  <DialogDescription>
                    Assignez ou retirez des permissions pour ce rôle en utilisant les selects searchable ci-dessous.
                  </DialogDescription>
                </DialogHeader>
                
                <PermissionManager
                  role={selectedRole}
                  availablePermissions={availablePermissions}
                  assignedPermissions={selectedRole?.permissions || []}
                  onAssignPermission={handleAssignPermissionToRole}
                  onRemovePermission={handleRemovePermissionFromRole}
                  loading={loading}
                />
                
                <DialogFooter>
                  <Button onClick={() => setShowManagePermissionsModal(false)}>Fermer</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>

            {/* MODALES - PERMISSIONS */}
            
            {/* Modal Création Permission */}
            <Dialog open={showCreatePermissionModal} onOpenChange={setShowCreatePermissionModal}>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Créer une nouvelle permission</DialogTitle>
                  <DialogDescription>
                    Ajoutez une nouvelle permission au système.
                  </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleCreatePermission}>
                  <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                      <Label htmlFor="permission-nom">Nom de la permission *</Label>
                      <Input
                        id="permission-nom"
                        value={permissionFormData.nom}
                        onChange={(e) => setPermissionFormData({...permissionFormData, nom: e.target.value})}
                        placeholder="Ex: gerer_projets, consulter_rapports..."
                        required
                      />
                    </div>
                    <div className="grid gap-2">
                      <Label htmlFor="permission-description">Description</Label>
                      <Textarea
                        id="permission-description"
                        value={permissionFormData.description}
                        onChange={(e) => setPermissionFormData({...permissionFormData, description: e.target.value})}
                        placeholder="Description de cette permission..."
                        rows={3}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => setShowCreatePermissionModal(false)}>
                      Annuler
                    </Button>
                    <Button type="submit">Créer la permission</Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>

            {/* Modal Modification Permission */}
            <Dialog open={showEditPermissionModal} onOpenChange={setShowEditPermissionModal}>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Modifier la permission</DialogTitle>
                  <DialogDescription>
                    Modifiez les informations de cette permission.
                  </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleUpdatePermission}>
                  <div className="grid gap-4 py-4">
                    <div className="grid gap-2">
                      <Label htmlFor="edit-permission-nom">Nom de la permission *</Label>
                      <Input
                        id="edit-permission-nom"
                        value={permissionFormData.nom}
                        onChange={(e) => setPermissionFormData({...permissionFormData, nom: e.target.value})}
                        required
                      />
                    </div>
                    <div className="grid gap-2">
                      <Label htmlFor="edit-permission-description">Description</Label>
                      <Textarea
                        id="edit-permission-description"
                        value={permissionFormData.description}
                        onChange={(e) => setPermissionFormData({...permissionFormData, description: e.target.value})}
                        rows={3}
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => setShowEditPermissionModal(false)}>
                      Annuler
                    </Button>
                    <Button type="submit">Enregistrer</Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>

            {/* Modal Détails Permission */}
            <Dialog open={showDetailPermissionModal} onOpenChange={setShowDetailPermissionModal}>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle className="flex items-center gap-2">
                    <Key className="h-5 w-5" />
                    Détails de la permission : {selectedPermission?.nom}
                  </DialogTitle>
                </DialogHeader>
                {selectedPermission && (
                  <div className="space-y-6">
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <Label className="text-sm font-medium text-muted-foreground">Nom</Label>
                        <p className="text-lg font-semibold">{selectedPermission.nom}</p>
                      </div>
                      <div>
                        <Label className="text-sm font-medium text-muted-foreground">Rôles associés</Label>
                        <p className="text-lg font-semibold">{selectedPermission.roles?.length || 0}</p>
                      </div>
                    </div>
                    
                    {selectedPermission.description && (
                      <div>
                        <Label className="text-sm font-medium text-muted-foreground">Description</Label>
                        <p className="mt-1">{selectedPermission.description}</p>
                      </div>
                    )}

                    <div>
                      <Label className="text-sm font-medium text-muted-foreground">
                        Rôles ayant cette permission ({selectedPermission.roles?.length || 0})
                      </Label>
                      <div className="mt-2 space-y-2 max-h-40 overflow-y-auto">
                        {selectedPermission.roles?.map((role: Role) => (
                          <div key={role.id} className="flex items-center gap-2 p-2 bg-gray-50 rounded">
                            <Shield className="h-4 w-4 text-muted-foreground" />
                            <span className="font-medium">{role.nom}</span>
                          </div>
                        )) || <p className="text-muted-foreground">Aucun rôle n'a cette permission</p>}
                      </div>
                    </div>

                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                      <div>Créée le {formatDate(selectedPermission.date_creation)}</div>
                      {selectedPermission.date_modification && (
                        <div>Modifiée le {formatDate(selectedPermission.date_modification)}</div>
                      )}
                    </div>
                  </div>
                )}
                <DialogFooter>
                  <Button onClick={() => setShowDetailPermissionModal(false)}>Fermer</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>

            {/* Dialog Suppression Permission */}
            <AlertDialog open={showDeletePermissionDialog} onOpenChange={setShowDeletePermissionDialog}>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Supprimer la permission</AlertDialogTitle>
                  <AlertDialogDescription>
                    Êtes-vous sûr de vouloir supprimer la permission "{permissionToDelete?.nom}" ?
                    {permissionToDelete?.roles && permissionToDelete.roles.length > 0 && (
                      <div className="mt-2 p-3 bg-orange-50 border border-orange-200 rounded">
                        <p className="text-orange-800 text-sm">
                          ⚠️ Attention : Cette permission est assignée à {permissionToDelete.roles.length} rôle(s). 
                          La suppression retirera automatiquement cette permission de tous les rôles.
                        </p>
                      </div>
                    )}
                    Cette action est irréversible.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Annuler</AlertDialogCancel>
                  <AlertDialogAction onClick={handleDeletePermission} className="bg-destructive hover:bg-destructive/90">
                    Supprimer
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>

            {/* Modal Gestion Rôles pour Permission */}
            <Dialog open={showManageRolesModal} onOpenChange={setShowManageRolesModal}>
              <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle className="flex items-center gap-2">
                    <Settings className="h-5 w-5" />
                    Gérer les rôles : {selectedPermission?.nom}
                  </DialogTitle>
                  <DialogDescription>
                    Voyez quels rôles ont cette permission et gérez les assignations.
                  </DialogDescription>
                </DialogHeader>
                
                <RoleManager
                  permission={selectedPermission}
                  availableRoles={availableRoles}
                  assignedRoles={selectedPermission?.roles || []}
                  onAssignRole={async (roleId: number) => {
                    // Cette fonction n'est pas implémentée car l'assignation se fait depuis l'onglet Rôles
                    toast({
                      title: "ℹ️ Information",
                      description: "Pour assigner cette permission à un rôle, utilisez la gestion des permissions depuis l'onglet Rôles.",
                    });
                  }}
                  onRemoveRole={async (roleId: number) => {
                    // Cette fonction n'est pas implémentée car la suppression se fait depuis l'onglet Rôles
                    toast({
                      title: "ℹ️ Information",
                      description: "Pour retirer cette permission d'un rôle, utilisez la gestion des permissions depuis l'onglet Rôles.",
                    });
                  }}
                  loading={loading}
                />
                
                <DialogFooter>
                  <Button onClick={() => setShowManageRolesModal(false)}>Fermer</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>

            {/* Modal Utilisateurs ayant la Permission */}
            <Dialog open={showPermissionUsersModal} onOpenChange={setShowPermissionUsersModal}>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle className="flex items-center gap-2">
                    <Users className="h-5 w-5" />
                    Utilisateurs ayant la permission : {selectedPermission?.nom}
                  </DialogTitle>
                  <DialogDescription>
                    Liste des utilisateurs qui ont cette permission via leurs rôles.
                  </DialogDescription>
                </DialogHeader>
                <div className="space-y-4">
                  {permissionUsers.length > 0 ? (
                    <div className="space-y-2 max-h-60 overflow-y-auto">
                      {permissionUsers.map((user) => (
                        <div key={user.id} className="flex items-center gap-3 p-3 border rounded">
                          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span className="text-sm font-medium text-blue-700">
                              {user.prenom.charAt(0)}{user.nom.charAt(0)}
                            </span>
                          </div>
                          <div className="flex-1">
                            <p className="font-medium">{user.prenom} {user.nom}</p>
                            <p className="text-sm text-muted-foreground">{user.email}</p>
                            <div className="flex gap-1 mt-1">
                              {user.roles_avec_cette_permission?.map((role: any) => (
                                <Badge key={role.id} variant="outline" className="text-xs">
                                  {role.nom}
                                </Badge>
                              ))}
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                <div className="text-center py-8 text-muted-foreground">
                      <Users className="h-16 w-16 mx-auto mb-4 text-muted-foreground/50" />
                      <p>Aucun utilisateur n'a cette permission</p>
                </div>
              )}
                </div>
                <DialogFooter>
                  <Button onClick={() => setShowPermissionUsersModal(false)}>Fermer</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </main>
      </div>
    </div>
  );
}
