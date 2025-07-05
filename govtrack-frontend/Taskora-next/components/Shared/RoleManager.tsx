"use client";

import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Settings, 
  UserPlus, 
  UserMinus, 
  CheckCircle, 
  XCircle, 
  Shield,
  Users,
  AlertCircle
} from 'lucide-react';
import { Permission, Role } from '@/lib/api';
import { useToast } from '@/hooks/use-toast';

interface RoleManagerProps {
  permission: Permission | null;
  availableRoles: Role[];
  assignedRoles: Role[];
  onAssignRole: (roleId: number) => Promise<void>;
  onRemoveRole: (roleId: number) => Promise<void>;
  loading?: boolean;
}

export default function RoleManager({
  permission,
  availableRoles,
  assignedRoles,
  onAssignRole,
  onRemoveRole,
  loading = false
}: RoleManagerProps) {
  const { toast } = useToast();
  const [selectedRoleToAssign, setSelectedRoleToAssign] = useState<string>('');
  const [selectedRoleToRemove, setSelectedRoleToRemove] = useState<string>('');
  const [isAssigning, setIsAssigning] = useState(false);
  const [isRemoving, setIsRemoving] = useState(false);

  // Calculer les rôles disponibles (non assignés)
  const availableRolesFiltered = availableRoles.filter(
    role => !assignedRoles.find(r => r.id === role.id)
  );

  // Options pour le select d'assignation
  const assignOptions: SearchableSelectOption[] = availableRolesFiltered.map(role => ({
    value: role.id.toString(),
    label: role.nom,
    description: role.description,
    badge: 'Disponible'
  }));

  // Options pour le select de suppression
  const removeOptions: SearchableSelectOption[] = assignedRoles.map(role => ({
    value: role.id.toString(),
    label: role.nom,
    description: role.description,
    badge: 'Assigné'
  }));

  // Réinitialiser les sélections quand les données changent
  useEffect(() => {
    setSelectedRoleToAssign('');
    setSelectedRoleToRemove('');
  }, [assignedRoles, availableRoles]);

  const handleAssignRole = async () => {
    if (!selectedRoleToAssign) return;
    
    try {
      setIsAssigning(true);
      await onAssignRole(parseInt(selectedRoleToAssign));
      setSelectedRoleToAssign('');
      toast({
        title: "✅ Rôle assigné",
        description: "Le rôle a été assigné avec succès à cette permission.",
      });
    } catch (error) {
      console.error('Erreur assignation rôle:', error);
      toast({
        title: "❌ Erreur",
        description: "Erreur lors de l'assignation du rôle.",
        variant: "destructive"
      });
    } finally {
      setIsAssigning(false);
    }
  };

  const handleRemoveRole = async () => {
    if (!selectedRoleToRemove) return;
    
    try {
      setIsRemoving(true);
      await onRemoveRole(parseInt(selectedRoleToRemove));
      setSelectedRoleToRemove('');
      toast({
        title: "✅ Rôle retiré",
        description: "Le rôle a été retiré avec succès de cette permission.",
      });
    } catch (error) {
      console.error('Erreur suppression rôle:', error);
      toast({
        title: "❌ Erreur",
        description: "Erreur lors du retrait du rôle.",
        variant: "destructive"
      });
    } finally {
      setIsRemoving(false);
    }
  };

  // Calculer les statistiques
  const totalRoles = availableRoles.length;
  const assignedCount = assignedRoles.length;
  const availableCount = availableRolesFiltered.length;
  const assignmentRate = totalRoles > 0 ? Math.round((assignedCount / totalRoles) * 100) : 0;

  return (
    <div className="space-y-6">
      {/* Section d'assignation rapide */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg">
            <UserPlus className="h-5 w-5 text-blue-600" />
            Assigner un rôle
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-3 items-end">
            <div className="flex-1">
              <Label htmlFor="assign-role">Rôle à assigner</Label>
              <SearchableSelect
                options={assignOptions}
                value={selectedRoleToAssign}
                onValueChange={setSelectedRoleToAssign}
                placeholder="Sélectionner un rôle..."
                searchPlaceholder="Rechercher un rôle..."
                emptyMessage="Tous les rôles sont déjà assignés"
                disabled={loading || isAssigning}
                maxHeight="300px"
              />
            </div>
            <Button
              onClick={handleAssignRole}
              disabled={!selectedRoleToAssign || loading || isAssigning}
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
        </CardContent>
      </Card>

      {/* Section de suppression rapide */}
      {assignedRoles.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg">
              <UserMinus className="h-5 w-5 text-red-600" />
              Retirer un rôle
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex gap-3 items-end">
              <div className="flex-1">
                <Label htmlFor="remove-role">Rôle à retirer</Label>
                <SearchableSelect
                  options={removeOptions}
                  value={selectedRoleToRemove}
                  onValueChange={setSelectedRoleToRemove}
                  placeholder="Sélectionner un rôle..."
                  searchPlaceholder="Rechercher un rôle..."
                  emptyMessage="Aucun rôle assigné"
                  disabled={loading || isRemoving}
                  maxHeight="300px"
                />
              </div>
              <Button
                onClick={handleRemoveRole}
                disabled={!selectedRoleToRemove || loading || isRemoving}
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
          </CardContent>
        </Card>
      )}

      {/* Vue d'ensemble des rôles assignés */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <CheckCircle className="h-5 w-5 text-green-600" />
            Rôles assignés ({assignedCount})
          </CardTitle>
        </CardHeader>
        <CardContent>
          {assignedRoles.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {assignedRoles.map((role) => (
                <div
                  key={role.id}
                  className="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md"
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-green-800 truncate">
                      {role.nom}
                    </p>
                    {role.description && (
                      <p className="text-xs text-green-600 truncate">
                        {role.description}
                      </p>
                    )}
                  </div>
                  <Badge variant="outline" className="text-xs bg-green-100 text-green-700 border-green-300">
                    Assigné
                  </Badge>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-gray-500">
              <XCircle className="h-12 w-12 mx-auto mb-2 text-gray-300" />
              <p>Aucun rôle assigné à cette permission</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Vue d'ensemble des rôles disponibles */}
      {availableRolesFiltered.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <XCircle className="h-5 w-5 text-blue-600" />
              Rôles disponibles ({availableCount})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {availableRolesFiltered.map((role) => (
                <div
                  key={role.id}
                  className="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-md"
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-blue-800 truncate">
                      {role.nom}
                    </p>
                    {role.description && (
                      <p className="text-xs text-blue-600 truncate">
                        {role.description}
                      </p>
                    )}
                  </div>
                  <Badge variant="outline" className="text-xs bg-blue-100 text-blue-700 border-blue-300">
                    Disponible
                  </Badge>
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
              <div className="text-sm text-gray-600">Assignés</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-blue-600">{availableCount}</div>
              <div className="text-sm text-gray-600">Disponibles</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-purple-600">{totalRoles}</div>
              <div className="text-sm text-gray-600">Total</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-orange-600">{assignmentRate}%</div>
              <div className="text-sm text-gray-600">Taux d'assignation</div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Note d'information */}
      <Card className="border-orange-200 bg-orange-50">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <AlertCircle className="h-5 w-5 text-orange-600 mt-0.5" />
            <div>
              <h4 className="font-medium text-orange-800 mb-1">Note importante</h4>
              <p className="text-sm text-orange-700">
                Pour assigner cette permission à un rôle, utilisez la gestion des permissions depuis l'onglet Rôles. 
                Cette vue vous permet de voir quels rôles ont actuellement cette permission.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
} 