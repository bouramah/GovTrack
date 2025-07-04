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

export interface TaskFilters {
  search?: string;
  statut?: TacheStatut;
  projet_id?: number;
  responsable_id?: number;
  en_retard?: boolean;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

interface TaskFiltersProps {
  filters: TaskFilters;
  onFiltersChange: (filters: TaskFilters) => void;
  onReset: () => void;
}

interface Project {
  id: number;
  titre: string;
}

interface User {
  id: number;
  nom: string;
  prenom: string;
}

export default function TaskFilters({ filters, onFiltersChange, onReset }: TaskFiltersProps) {
  const [projects, setProjects] = useState<Project[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [loadingProjects, setLoadingProjects] = useState(false);
  const [loadingUsers, setLoadingUsers] = useState(false);
  const [showAdvanced, setShowAdvanced] = useState(false);

  // Charger les projets
  useEffect(() => {
    const loadProjects = async () => {
      try {
        setLoadingProjects(true);
        const response = await apiClient.getProjects();
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

  const updateFilter = (key: keyof TaskFilters, value: any) => {
    const newFilters = { ...filters };
    if (value === undefined || value === null || value === '') {
      delete newFilters[key];
    } else {
      newFilters[key] = value;
    }
    onFiltersChange(newFilters);
  };

  const getActiveFiltersCount = () => {
    return Object.keys(filters).filter(key => 
      key !== 'sort_by' && key !== 'sort_order' && filters[key as keyof TaskFilters] !== undefined
    ).length;
  };

  const clearAllFilters = () => {
    onReset();
  };

  return (
    <Card className="mb-6">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg flex items-center gap-2">
            <Filter className="h-5 w-5" />
            Filtres
            {getActiveFiltersCount() > 0 && (
              <Badge variant="secondary" className="ml-2">
                {getActiveFiltersCount()}
              </Badge>
            )}
          </CardTitle>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setShowAdvanced(!showAdvanced)}
            >
              {showAdvanced ? 'Filtres simples' : 'Filtres avancés'}
            </Button>
            {getActiveFiltersCount() > 0 && (
              <Button
                variant="outline"
                size="sm"
                onClick={clearAllFilters}
              >
                <X className="h-4 w-4 mr-1" />
                Effacer
              </Button>
            )}
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
            <Select
              value={filters.statut || 'all'}
              onValueChange={(value) => updateFilter('statut', value === 'all' ? undefined : value)}
            >
              <SelectTrigger id="statut">
                <SelectValue placeholder="Tous les statuts" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les statuts</SelectItem>
                {Object.entries(TACHE_STATUTS_KANBAN).map(([key, value]) => (
                  <SelectItem key={key} value={key}>
                    {value}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* Projet */}
          <div className="space-y-2">
            <Label htmlFor="projet">Projet</Label>
            <Select
              value={filters.projet_id?.toString() || 'all'}
              onValueChange={(value) => updateFilter('projet_id', value === 'all' ? undefined : parseInt(value))}
            >
              <SelectTrigger id="projet">
                <SelectValue placeholder="Tous les projets" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les projets</SelectItem>
                {loadingProjects ? (
                  <SelectItem value="loading" disabled>Chargement...</SelectItem>
                ) : (
                  projects.map((project) => (
                    <SelectItem key={project.id} value={project.id.toString()}>
                      {project.titre}
                    </SelectItem>
                  ))
                )}
              </SelectContent>
            </Select>
          </div>

          {/* Responsable */}
          <div className="space-y-2">
            <Label htmlFor="responsable">Responsable</Label>
            <Select
              value={filters.responsable_id?.toString() || 'all'}
              onValueChange={(value) => updateFilter('responsable_id', value === 'all' ? undefined : parseInt(value))}
            >
              <SelectTrigger id="responsable">
                <SelectValue placeholder="Tous les responsables" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les responsables</SelectItem>
                {loadingUsers ? (
                  <SelectItem value="loading" disabled>Chargement...</SelectItem>
                ) : (
                  users.map((user) => (
                    <SelectItem key={user.id} value={user.id.toString()}>
                      {user.prenom} {user.nom}
                    </SelectItem>
                  ))
                )}
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* Filtres avancés */}
        {showAdvanced && (
          <div className="border-t pt-4 space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {/* En retard */}
              <div className="space-y-2">
                <Label>État d'échéance</Label>
                <div className="space-y-2">
                  <div className="flex items-center space-x-2">
                    <Checkbox
                      id="en_retard"
                      checked={filters.en_retard === true}
                      onCheckedChange={(checked) => updateFilter('en_retard', checked ? true : undefined)}
                    />
                    <Label htmlFor="en_retard" className="text-sm">En retard</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Checkbox
                      id="a_jour"
                      checked={filters.en_retard === false}
                      onCheckedChange={(checked) => updateFilter('en_retard', checked ? false : undefined)}
                    />
                    <Label htmlFor="a_jour" className="text-sm">À jour</Label>
                  </div>
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
                    <SelectItem value="date_modification">Date de modification</SelectItem>
                    <SelectItem value="titre">Titre</SelectItem>
                    <SelectItem value="niveau_execution">Niveau d'exécution</SelectItem>
                    <SelectItem value="date_fin_previsionnelle">Date d'échéance</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Ordre de tri */}
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

        {/* Résumé des filtres actifs */}
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