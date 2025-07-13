"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Search, Filter, X, RefreshCw } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN } from "@/types/tache";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";

export interface TaskFilters {
  search?: string;
  statut?: TacheStatut;
  projet_id?: number;
  responsable_id?: number;
  entite_id?: number;
  en_retard?: boolean;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

interface TaskFiltersProps {
  filters: TaskFilters;
  onFiltersChange: (filters: TaskFilters) => void;
  onReset: () => void;
}

export default function TaskFilters({ filters, onFiltersChange, onReset }: TaskFiltersProps) {
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [projects, setProjects] = useState<any[]>([]);
  const [users, setUsers] = useState<any[]>([]);
  const [entites, setEntites] = useState<any[]>([]);
  const [loadingProjects, setLoadingProjects] = useState(false);
  const [loadingUsers, setLoadingUsers] = useState(false);
  const [loadingEntites, setLoadingEntites] = useState(false);

  // Charger les projets
  useEffect(() => {
    const loadProjects = async () => {
      try {
        setLoadingProjects(true);
        const response = await apiClient.getProjects({ per_page: 1000 });
        if (response.success && response.data) {
          setProjects(response.data);
        }
      } catch (error) {
        console.error('Erreur chargement projets:', error);
      } finally {
        setLoadingProjects(false);
      }
    };

    loadProjects();
  }, []);

  // Charger les utilisateurs
  useEffect(() => {
    const loadUsers = async () => {
      try {
        setLoadingUsers(true);
        const response = await apiClient.getUsersDetailed();
        if (response.success && response.data) {
          setUsers(response.data);
        }
      } catch (error) {
        console.error('Erreur chargement utilisateurs:', error);
      } finally {
        setLoadingUsers(false);
      }
    };

    loadUsers();
  }, []);

  // Charger les entités
  useEffect(() => {
    const loadEntites = async () => {
      try {
        setLoadingEntites(true);
        const response = await apiClient.getEntitesDetailed();
        if (response.success && response.data) {
          setEntites(response.data);
        }
      } catch (error) {
        console.error('Erreur chargement entités:', error);
      } finally {
        setLoadingEntites(false);
      }
    };

    loadEntites();
  }, []);

  const updateFilter = (key: keyof TaskFilters, value: any) => {
    onFiltersChange({
      ...filters,
      [key]: value,
    });
  };

  const clearFilter = (key: keyof TaskFilters) => {
    const newFilters = { ...filters };
    delete newFilters[key];
    onFiltersChange(newFilters);
  };

  const getActiveFiltersCount = () => {
    return Object.keys(filters).filter(key => 
      filters[key as keyof TaskFilters] !== undefined && 
      filters[key as keyof TaskFilters] !== null &&
      filters[key as keyof TaskFilters] !== ''
    ).length;
  };

  // Préparer les options pour les selects searchables
  const projectOptions: SearchableSelectOption[] = [
    { value: 'all', label: 'Tous les projets' },
    ...projects.map(project => ({
      value: project.id.toString(),
      label: project.titre
    }))
  ];

  const userOptions: SearchableSelectOption[] = [
    { value: 'all', label: 'Tous les responsables' },
    ...users.map(user => ({
      value: user.id.toString(),
      label: `${user.prenom} ${user.nom}`,
      description: user.email,
      badge: user.matricule
    }))
  ];

  const statutOptions: SearchableSelectOption[] = [
    { value: 'all', label: 'Tous les statuts' },
    ...Object.entries(TACHE_STATUTS_KANBAN).map(([key, value]) => ({
      value: key,
      label: value
    }))
  ];

  const entiteOptions: SearchableSelectOption[] = [
    { value: 'all', label: 'Toutes les entités' },
    ...entites.map(entite => ({
      value: entite.id.toString(),
      label: entite.nom,
      description: entite.type_entite?.nom
    }))
  ];

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle className="flex items-center gap-2">
            <Filter className="h-5 w-5" />
            Filtres
          </CardTitle>
          <div className="flex items-center gap-2">
            {getActiveFiltersCount() > 0 && (
              <Button
                variant="outline"
                size="sm"
                onClick={onReset}
                className="h-8"
              >
                <RefreshCw className="h-4 w-4 mr-2" />
                Réinitialiser
              </Button>
            )}
            <Button
              variant="outline"
              size="sm"
              onClick={() => setShowAdvanced(!showAdvanced)}
              className="h-8"
            >
              {showAdvanced ? 'Filtres simples' : 'Filtres avancés'}
            </Button>
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-4">
        {/* Filtres de base */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Recherche */}
          <div className="space-y-2">
            <Label htmlFor="search">Recherche</Label>
            <div className="relative">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
              <Input
                id="search"
                type="search"
                placeholder="Titre, description..."
                value={filters.search || ''}
                onChange={(e) => updateFilter('search', e.target.value || undefined)}
                className="pl-8"
              />
            </div>
          </div>

          {/* Statut */}
          <div className="space-y-2">
            <Label htmlFor="statut">Statut</Label>
            <SearchableSelect
              options={statutOptions}
              value={filters.statut || 'all'}
              onValueChange={(value) => updateFilter('statut', value === 'all' ? undefined : value)}
              placeholder="Tous les statuts"
              searchPlaceholder="Rechercher un statut..."
              disabled={loadingProjects}
            />
          </div>

          {/* Projet */}
          <div className="space-y-2">
            <Label htmlFor="projet">Projet</Label>
            <SearchableSelect
              options={projectOptions}
              value={filters.projet_id?.toString() || 'all'}
              onValueChange={(value) => updateFilter('projet_id', value === 'all' ? undefined : parseInt(value))}
              placeholder="Tous les projets"
              searchPlaceholder="Rechercher un projet..."
              disabled={loadingProjects}
            />
          </div>

          {/* Responsable */}
          <div className="space-y-2">
            <Label htmlFor="responsable">Responsable</Label>
            <SearchableSelect
              options={userOptions}
              value={filters.responsable_id?.toString() || 'all'}
              onValueChange={(value) => updateFilter('responsable_id', value === 'all' ? undefined : parseInt(value))}
              placeholder="Tous les responsables"
              searchPlaceholder="Rechercher un responsable..."
              disabled={loadingUsers}
            />
          </div>

          {/* Entité */}
          <div className="space-y-2">
            <Label htmlFor="entite">Entité</Label>
            <SearchableSelect
              options={entiteOptions}
              value={filters.entite_id?.toString() || 'all'}
              onValueChange={(value) => updateFilter('entite_id', value === 'all' ? undefined : parseInt(value))}
              placeholder="Toutes les entités"
              searchPlaceholder="Rechercher une entité..."
              disabled={loadingEntites}
            />
          </div>
        </div>

        {/* Filtres avancés */}
        {showAdvanced && (
          <div className="space-y-4 pt-4 border-t">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {/* En retard */}
              <div className="space-y-2">
                <Label>État d'échéance</Label>
                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="en_retard"
                    checked={filters.en_retard === true}
                    onCheckedChange={(checked) => updateFilter('en_retard', checked ? true : undefined)}
                  />
                  <Label htmlFor="en_retard" className="text-sm font-normal">
                    En retard uniquement
                  </Label>
                </div>
              </div>

              {/* Tri */}
              <div className="space-y-2">
                <Label htmlFor="sort_by">Trier par</Label>
                <Select
                  value={filters.sort_by || 'date_creation'}
                  onValueChange={(value) => updateFilter('sort_by', value)}
                >
                  <SelectTrigger id="sort_by">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="date_creation">Date de création</SelectItem>
                    <SelectItem value="date_fin_previsionnelle">Date d'échéance</SelectItem>
                    <SelectItem value="titre">Titre</SelectItem>
                    <SelectItem value="statut">Statut</SelectItem>
                    <SelectItem value="niveau_execution">Progression</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Ordre */}
              <div className="space-y-2">
                <Label htmlFor="sort_order">Ordre</Label>
                <Select
                  value={filters.sort_order || 'desc'}
                  onValueChange={(value) => updateFilter('sort_order', value as 'asc' | 'desc')}
                >
                  <SelectTrigger id="sort_order">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="desc">Décroissant</SelectItem>
                    <SelectItem value="asc">Croissant</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>
        )}

        {/* Filtres actifs */}
        {getActiveFiltersCount() > 0 && (
          <div className="border-t pt-4">
            <div className="flex items-center gap-2 mb-2">
              <span className="text-sm font-medium text-gray-700">Filtres actifs :</span>
            </div>
            <div className="flex flex-wrap gap-2">
              {filters.search && (
                <Badge variant="outline" className="text-xs">
                  Recherche: "{filters.search}"
                </Badge>
              )}
              {filters.statut && (
                <Badge variant="outline" className="text-xs">
                  Statut: {TACHE_STATUTS_KANBAN[filters.statut]}
                </Badge>
              )}
              {filters.projet_id && (
                <Badge variant="outline" className="text-xs">
                  Projet: {projects.find(p => p.id === filters.projet_id)?.titre || 'ID: ' + filters.projet_id}
                </Badge>
              )}
              {filters.responsable_id && (
                <Badge variant="outline" className="text-xs">
                  Responsable: {users.find(u => u.id === filters.responsable_id)?.prenom + ' ' + users.find(u => u.id === filters.responsable_id)?.nom || 'ID: ' + filters.responsable_id}
                </Badge>
              )}
              {filters.entite_id && (
                <Badge variant="outline" className="text-xs">
                  Entité: {entites.find(e => e.id === filters.entite_id)?.nom || 'ID: ' + filters.entite_id}
                </Badge>
              )}
              {filters.en_retard !== undefined && (
                <Badge variant="outline" className="text-xs">
                  {filters.en_retard ? 'En retard' : 'À jour'}
                </Badge>
              )}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
} 