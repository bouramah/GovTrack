"use client";

import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
  Settings, 
  UserPlus, 
  UserMinus, 
  CheckCircle, 
  XCircle, 
  Key,
  Users,
  AlertCircle
} from 'lucide-react';
import { Permission, Role } from '@/lib/api';
import { useToast } from '@/hooks/use-toast';
import { Checkbox } from '@/components/ui/checkbox';

interface PermissionManagerProps {
  role: Role | null;
  availablePermissions: Permission[];
  assignedPermissions: Permission[];
  onAssignPermission: (permissionId: number) => Promise<void>;
  onRemovePermission: (permissionId: number) => Promise<void>;
  onAssignPermissionsBulk?: (permissionIds: number[]) => Promise<void>;
  onRemovePermissionsBulk?: (permissionIds: number[]) => Promise<void>;
  loading?: boolean;
}

export default function PermissionManager({
  role,
  availablePermissions,
  assignedPermissions,
  onAssignPermission,
  onRemovePermission,
  onAssignPermissionsBulk,
  onRemovePermissionsBulk,
  loading = false
}: PermissionManagerProps) {
  const { toast } = useToast();
  const [selectedPermissionToAssign, setSelectedPermissionToAssign] = useState<string>('');
  const [selectedPermissionToRemove, setSelectedPermissionToRemove] = useState<string>('');
  const [isAssigning, setIsAssigning] = useState(false);
  const [isRemoving, setIsRemoving] = useState(false);
  const [selectedAssignIds, setSelectedAssignIds] = useState<Set<number>>(new Set());
  const [selectedRemoveIds, setSelectedRemoveIds] = useState<Set<number>>(new Set());

  // Calculer les permissions disponibles (non assignées)
  const availablePermissionsFiltered = availablePermissions.filter(
    permission => !assignedPermissions.find(p => p.id === permission.id)
  );

  // Options pour le select d'assignation
  const assignOptions: SearchableSelectOption[] = availablePermissionsFiltered.map(permission => ({
    value: permission.id.toString(),
    label: permission.nom,
    description: permission.description,
    badge: 'Disponible'
  }));

  // Options pour le select de suppression
  const removeOptions: SearchableSelectOption[] = assignedPermissions.map(permission => ({
    value: permission.id.toString(),
    label: permission.nom,
    description: permission.description,
    badge: 'Assignée'
  }));

  // Réinitialiser les sélections quand les données changent
  useEffect(() => {
    setSelectedPermissionToAssign('');
    setSelectedPermissionToRemove('');
    setSelectedAssignIds(new Set());
    setSelectedRemoveIds(new Set());
  }, [assignedPermissions, availablePermissions]);

  const handleAssignPermission = async () => {
    if (!selectedPermissionToAssign) return;
    
    try {
      setIsAssigning(true);
      await onAssignPermission(parseInt(selectedPermissionToAssign));
      setSelectedPermissionToAssign('');
      toast({
        title: "✅ Permission assignée",
        description: "La permission a été assignée avec succès au rôle.",
      });
    } catch (error) {
      console.error('Erreur assignation permission:', error);
      toast({
        title: "❌ Erreur",
        description: "Erreur lors de l'assignation de la permission.",
        variant: "destructive"
      });
    } finally {
      setIsAssigning(false);
    }
  };

  const handleRemovePermission = async () => {
    if (!selectedPermissionToRemove) return;
    
    try {
      setIsRemoving(true);
      await onRemovePermission(parseInt(selectedPermissionToRemove));
      setSelectedPermissionToRemove('');
      toast({
        title: "✅ Permission retirée",
        description: "La permission a été retirée avec succès du rôle.",
      });
    } catch (error) {
      console.error('Erreur suppression permission:', error);
      toast({
        title: "❌ Erreur",
        description: "Erreur lors du retrait de la permission.",
        variant: "destructive"
      });
    } finally {
      setIsRemoving(false);
    }
  };

  // Grouper les permissions par catégorie pour une meilleure organisation
  const groupPermissionsByCategory = (permissions: Permission[]) => {
    const categories: { [key: string]: Permission[] } = {};
    
    permissions.forEach(permission => {
      const category = getPermissionCategory(permission.nom);
      if (!categories[category]) {
        categories[category] = [];
      }
      categories[category].push(permission);
    });
    
    return categories;
  };

  const getPermissionCategory = (permissionName: string): string => {
    if (permissionName.includes('user')) return 'Utilisateurs';
    if (permissionName.includes('role')) return 'Rôles';
    if (permissionName.includes('permission')) return 'Permissions';
    if (permissionName.includes('entity') || permissionName.includes('entite')) return 'Entités';
    if (permissionName.includes('poste')) return 'Postes';
    if (permissionName.includes('project') || permissionName.includes('projet')) return 'Projets';
    if (permissionName.includes('task') || permissionName.includes('tache')) return 'Tâches';
    if (permissionName.includes('type')) return 'Types';
    return 'Autres';
  };

  const assignedCategories = groupPermissionsByCategory(assignedPermissions);
  const availableCategories = groupPermissionsByCategory(availablePermissionsFiltered);

  // Calculer les statistiques
  const totalPermissions = availablePermissions.length;
  const assignedCount = assignedPermissions.length;
  const availableCount = availablePermissionsFiltered.length;
  const assignmentRate = totalPermissions > 0 ? Math.round((assignedCount / totalPermissions) * 100) : 0;

  // Handlers BULK
  const handleAssignBulk = async () => {
    if (selectedAssignIds.size === 0) return;

    try {
      setIsAssigning(true);
      if (onAssignPermissionsBulk) {
        await onAssignPermissionsBulk(Array.from(selectedAssignIds));
      } else {
        // Fallback : on boucle sur la callback unitaire
        await Promise.all(Array.from(selectedAssignIds).map(id => onAssignPermission(id)));
      }
      toast({
        title: '✅ Permissions assignées',
        description: `${selectedAssignIds.size} permission(s) assignée(s).`
      });
      setSelectedAssignIds(new Set());
    } catch (error) {
      console.error('Erreur assignation bulk:', error);
      toast({
        title: '❌ Erreur',
        description: "Erreur lors de l'assignation des permissions.",
        variant: 'destructive'
      });
    } finally {
      setIsAssigning(false);
    }
  };

  const handleRemoveBulk = async () => {
    if (selectedRemoveIds.size === 0) return;

    try {
      setIsRemoving(true);
      if (onRemovePermissionsBulk) {
        await onRemovePermissionsBulk(Array.from(selectedRemoveIds));
      } else {
        await Promise.all(Array.from(selectedRemoveIds).map(id => onRemovePermission(id)));
      }
      toast({
        title: '✅ Permissions retirées',
        description: `${selectedRemoveIds.size} permission(s) retirée(s).`
      });
      setSelectedRemoveIds(new Set());
    } catch (error) {
      console.error('Erreur suppression bulk:', error);
      toast({
        title: '❌ Erreur',
        description: "Erreur lors du retrait des permissions.",
        variant: 'destructive'
      });
    } finally {
      setIsRemoving(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Section d'assignation rapide */}
      <Card>
        <CardHeader className="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
          <CardTitle className="flex items-center gap-2 text-lg">
            <UserPlus className="h-5 w-5 text-blue-600" />
            Assigner une permission
          </CardTitle>
          {selectedAssignIds.size > 0 && (
            <Button size="sm" onClick={handleAssignBulk} disabled={loading || isAssigning}>
              {isAssigning ? 'Assignation...' : `Assigner ${selectedAssignIds.size}`}
            </Button>
          )}
        </CardHeader>
        <CardContent>
          <div className="flex gap-3 items-end">
            <div className="flex-1">
              <Label htmlFor="assign-permission">Permission à assigner</Label>
              <SearchableSelect
                options={assignOptions}
                value={selectedPermissionToAssign}
                onValueChange={setSelectedPermissionToAssign}
                placeholder="Sélectionner une permission..."
                searchPlaceholder="Rechercher une permission..."
                emptyMessage="Toutes les permissions sont déjà assignées"
                disabled={loading || isAssigning}
                maxHeight="300px"
              />
            </div>
            <div>
              <Button
                onClick={handleAssignPermission}
                disabled={!selectedPermissionToAssign || loading || isAssigning}
                className="shrink-0"
              >
                {isAssigning ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Assignation...
                  </>
                ) : (
                  <>
                    <UserPlus className="h-4 w-4 mr-2" />
                    Assigner
                  </>
                )}
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Section de suppression rapide */}
      {assignedPermissions.length > 0 && (
        <Card>
          <CardHeader className="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <CardTitle className="flex items-center gap-2 text-lg">
              <UserMinus className="h-5 w-5 text-red-600" />
              Retirer une permission
            </CardTitle>
            {selectedRemoveIds.size > 0 && (
              <Button size="sm" variant="outline" className="border-red-200 text-red-700 hover:bg-red-50" onClick={handleRemoveBulk} disabled={loading || isRemoving}>
                {isRemoving ? 'Suppression...' : `Retirer ${selectedRemoveIds.size}`}
              </Button>
            )}
          </CardHeader>
          <CardContent>
            <div className="flex gap-3 items-end">
              <div className="flex-1">
                <Label htmlFor="remove-permission">Permission à retirer</Label>
                <SearchableSelect
                  options={removeOptions}
                  value={selectedPermissionToRemove}
                  onValueChange={setSelectedPermissionToRemove}
                  placeholder="Sélectionner une permission..."
                  searchPlaceholder="Rechercher une permission..."
                  emptyMessage="Aucune permission assignée"
                  disabled={loading || isRemoving}
                  maxHeight="300px"
                />
              </div>
              <div>
                <Button
                  onClick={handleRemovePermission}
                  disabled={!selectedPermissionToRemove || loading || isRemoving}
                  variant="outline"
                  className="shrink-0 border-red-200 text-red-700 hover:bg-red-50"
                >
                  {isRemoving ? (
                    <>
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-red-700 mr-2"></div>
                      Suppression...
                    </>
                  ) : (
                    <>
                      <UserMinus className="h-4 w-4 mr-2" />
                      Retirer
                    </>
                  )}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Vue d'ensemble des permissions assignées */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <CheckCircle className="h-5 w-5 text-green-600" />
            Permissions assignées ({assignedCount})
          </CardTitle>
        </CardHeader>
        <CardContent>
          {assignedPermissions.length > 0 ? (
            <div className="space-y-4">
              {Object.entries(assignedCategories).map(([category, permissions]) => (
                <div key={category}>
                  <h4 className="font-medium text-sm text-gray-700 mb-2 flex items-center gap-2">
                    <Key className="h-4 w-4" />
                    {category} ({permissions.length})
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    {permissions.map((permission) => (
                      <div
                        key={permission.id}
                        className="flex items-center justify-between p-2 bg-green-50 border border-green-200 rounded-md"
                      >
                        <div className="flex items-center gap-2 flex-1 min-w-0">
                          <Checkbox
                            checked={selectedRemoveIds.has(permission.id)}
                            onCheckedChange={(checked) => {
                              const newSet = new Set(selectedRemoveIds);
                              if (checked) newSet.add(permission.id);
                              else newSet.delete(permission.id);
                              setSelectedRemoveIds(newSet);
                            }}
                          />
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-green-800 truncate">
                              {permission.nom}
                            </p>
                            {permission.description && (
                              <p className="text-xs text-green-600 truncate">
                                {permission.description}
                              </p>
                            )}
                          </div>
                        </div>
                        <Badge variant="outline" className="text-xs bg-green-100 text-green-700 border-green-300">
                          Assignée
                        </Badge>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-gray-500">
              <XCircle className="h-12 w-12 mx-auto mb-2 text-gray-300" />
              <p>Aucune permission assignée à ce rôle</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Vue d'ensemble des permissions disponibles */}
      {availablePermissionsFiltered.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <XCircle className="h-5 w-5 text-blue-600" />
              Permissions disponibles ({availableCount})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {Object.entries(availableCategories).map(([category, permissions]) => (
                <div key={category}>
                  <h4 className="font-medium text-sm text-gray-700 mb-2 flex items-center gap-2">
                    <Key className="h-4 w-4" />
                    {category} ({permissions.length})
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    {permissions.map((permission) => (
                      <div
                        key={permission.id}
                        className="flex items-center justify-between p-2 bg-blue-50 border border-blue-200 rounded-md"
                      >
                        <div className="flex items-center gap-2 flex-1 min-w-0">
                          <Checkbox
                            checked={selectedAssignIds.has(permission.id)}
                            onCheckedChange={(checked) => {
                              const newSet = new Set(selectedAssignIds);
                              if (checked) newSet.add(permission.id);
                              else newSet.delete(permission.id);
                              setSelectedAssignIds(newSet);
                            }}
                          />
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-blue-800 truncate">
                              {permission.nom}
                            </p>
                            {permission.description && (
                              <p className="text-xs text-blue-600 truncate">
                                {permission.description}
                              </p>
                            )}
                          </div>
                        </div>
                        <Badge variant="outline" className="text-xs bg-blue-100 text-blue-700 border-blue-300">
                          Disponible
                        </Badge>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Statistiques */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Settings className="h-5 w-5" />
            Statistiques
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="text-center">
              <div className="text-2xl font-bold text-green-600">{assignedCount}</div>
              <div className="text-sm text-gray-600">Assignées</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-blue-600">{availableCount}</div>
              <div className="text-sm text-gray-600">Disponibles</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-purple-600">{totalPermissions}</div>
              <div className="text-sm text-gray-600">Total</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-orange-600">{assignmentRate}%</div>
              <div className="text-sm text-gray-600">Taux d'assignation</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
} 