"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";
import { SearchableMultiSelect, SearchableMultiSelectOption } from "@/components/ui/searchable-multi-select";
import { Checkbox } from "@/components/ui/checkbox";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { 
  Filter, 
  X, 
  Calendar, 
  Users, 
  Building, 
  Search,
  ChevronDown,
  ChevronUp,
  RefreshCw
} from "lucide-react";
import { cn } from "@/lib/utils";
import { apiClient, ProjectFilters, ProjectPermissions, FilterEntity, FilterUser, TypeProjet } from "@/lib/api";
import { toast } from "sonner";
import { PrioritySelect } from "./Shared/PrioritySelect";

interface ProjectsAdvancedFiltersProps {
  filters: ProjectFilters;
  onFiltersChange: (filters: ProjectFilters) => void;
  permissions?: ProjectPermissions;
  className?: string;
}

export default function ProjectsAdvancedFilters({
  filters,
  onFiltersChange,
  permissions,
  className
}: ProjectsAdvancedFiltersProps) {
  const [isExpanded, setIsExpanded] = useState(false);
  const [loading, setLoading] = useState(false);
  const [typeProjets, setTypeProjets] = useState<TypeProjet[]>([]);
  const [entities, setEntities] = useState<FilterEntity[]>([]);
  const [users, setUsers] = useState<FilterUser[]>([]);

  // Charger les données pour les filtres
  useEffect(() => {
    loadFilterData();
  }, [permissions]);

  const loadFilterData = async () => {
    setLoading(true);
    try {
      console.log('🔍 [ProjectsAdvancedFilters] Chargement des données de filtres...');
      console.log('📋 [ProjectsAdvancedFilters] Permissions reçues:', permissions);
      
      // Charger les types de projets (toujours disponible)
      const typesResponse = await apiClient.getTypeProjets({ per_page: 100 });
      setTypeProjets(typesResponse.data || []);

      // Charger les entités si l'utilisateur a les permissions
      if (permissions?.can_filter_by_entity) {
        console.log('🏢 [ProjectsAdvancedFilters] Chargement des entités pour filtres...');
        const entitiesResponse = await apiClient.getProjectFilterEntities();
        console.log('🏢 [ProjectsAdvancedFilters] Entités chargées:', entitiesResponse);
        setEntities(entitiesResponse);
      } else {
        console.log('❌ [ProjectsAdvancedFilters] Pas de permission pour filtrer par entité');
      }

      // Charger les utilisateurs si l'utilisateur a les permissions
      if (permissions?.can_filter_by_user) {
        console.log('👥 [ProjectsAdvancedFilters] Chargement des utilisateurs pour filtres...');
        const usersResponse = await apiClient.getProjectFilterUsers();
        console.log('👥 [ProjectsAdvancedFilters] Utilisateurs chargés:', usersResponse);
        setUsers(usersResponse);
      } else {
        console.log('❌ [ProjectsAdvancedFilters] Pas de permission pour filtrer par utilisateur');
      }
    } catch (error: any) {
      console.error('❌ [ProjectsAdvancedFilters] Erreur lors du chargement des données de filtres:', error);
      toast.error("Erreur lors du chargement des données de filtres");
    } finally {
      setLoading(false);
    }
  };

  const updateFilter = (key: keyof ProjectFilters, value: any) => {
    const newFilters = { ...filters };
    
    if (value === null || value === undefined || value === "") {
      delete newFilters[key];
    } else {
      newFilters[key] = value;
    }
    
    onFiltersChange(newFilters);
  };

  const clearAllFilters = () => {
    onFiltersChange({});
  };

  const clearFilter = (key: keyof ProjectFilters) => {
    const newFilters = { ...filters };
    delete newFilters[key];
    onFiltersChange(newFilters);
  };

  const getActiveFiltersCount = () => {
    return Object.keys(filters).filter(key => 
      key !== 'page' && key !== 'per_page' && key !== 'sort_by' && key !== 'sort_order'
    ).length;
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('fr-FR');
  };

  return (
    <div className={cn("space-y-4", className)}>
      {/* En-tête des filtres */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <Filter className="h-5 w-5 text-gray-600" />
          <h3 className="text-lg font-semibold">Filtres</h3>
          {getActiveFiltersCount() > 0 && (
            <Badge variant="secondary" className="ml-2">
              {getActiveFiltersCount()} actif{getActiveFiltersCount() > 1 ? 's' : ''}
            </Badge>
          )}
        </div>
        <div className="flex items-center gap-2">
          {getActiveFiltersCount() > 0 && (
            <Button
              variant="ghost"
              size="sm"
              onClick={clearAllFilters}
              className="text-gray-500 hover:text-gray-700"
            >
              <X className="h-4 w-4 mr-1" />
              Effacer tout
            </Button>
          )}
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setIsExpanded(!isExpanded)}
            className="text-gray-600 hover:text-gray-800"
          >
            {isExpanded ? (
              <>
                <ChevronUp className="h-4 w-4 mr-1" />
                Réduire
              </>
            ) : (
              <>
                <ChevronDown className="h-4 w-4 mr-1" />
                Étendre
              </>
            )}
          </Button>
        </div>
      </div>

      {/* Filtres actifs */}
      {getActiveFiltersCount() > 0 && (
        <div className="flex flex-wrap gap-2">
          {filters.search && (
            <Badge variant="outline" className="flex items-center gap-1">
              Recherche: {filters.search}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('search')}
              />
            </Badge>
          )}
          {filters.statut && (
            <Badge variant="outline" className="flex items-center gap-1">
              Statut: {filters.statut}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('statut')}
              />
            </Badge>
          )}
          {filters.type_projet_id && (
            <Badge variant="outline" className="flex items-center gap-1">
              Type: {typeProjets.find(t => t.id === filters.type_projet_id)?.nom}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('type_projet_id')}
              />
            </Badge>
          )}
          {filters.en_retard && (
            <Badge variant="outline" className="flex items-center gap-1">
              En retard
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('en_retard')}
              />
            </Badge>
          )}
          {filters.priorite && (
            <Badge variant="outline" className="flex items-center gap-1">
              Priorité: {filters.priorite}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('priorite')}
              />
            </Badge>
          )}
          {filters.favoris && (
            <Badge variant="outline" className="flex items-center gap-1">
              Favoris uniquement
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('favoris')}
              />
            </Badge>
          )}
          {filters.porteur_id && (
            <Badge variant="outline" className="flex items-center gap-1">
              Porteur: {users.find(u => u.id === filters.porteur_id)?.display_name}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('porteur_id')}
              />
            </Badge>
          )}
          {filters.entite_id && (
            <Badge variant="outline" className="flex items-center gap-1">
              Entité: {entities.find(e => e.id === filters.entite_id)?.nom}
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('entite_id')}
              />
            </Badge>
          )}
        </div>
      )}

      {/* Filtres étendus */}
      {isExpanded && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filtres avancés
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Recherche */}
            <div className="space-y-2">
              <Label htmlFor="search">Recherche</Label>
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="search"
                  placeholder="Rechercher dans le titre, description, porteur..."
                  value={filters.search || ""}
                  onChange={(e) => updateFilter('search', e.target.value)}
                  className="pl-10"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {/* Statut */}
              <div className="space-y-2">
                <Label htmlFor="statut">Statut</Label>
                <SearchableSelect
                  options={[
                    { value: "all", label: "Tous les statuts" },
                    { value: "a_faire", label: "À faire" },
                    { value: "en_cours", label: "En cours" },
                    { value: "demande_de_cloture", label: "Demande de clôture" },
                    { value: "termine", label: "Terminé" },
                    { value: "bloque", label: "Bloqué" }
                  ]}
                  value={filters.statut || "all"}
                  onValueChange={(value) => updateFilter('statut', value === "all" ? null : value)}
                  placeholder="Tous les statuts"
                  searchPlaceholder="Rechercher un statut..."
                />
              </div>

              {/* Type d'instruction */}
              <div className="space-y-2">
                <Label htmlFor="type_projet">Type d'instruction</Label>
                <SearchableSelect
                  options={[
                    { value: "all", label: "Tous les types" },
                    ...typeProjets.map((type) => ({
                      value: type.id.toString(),
                      label: type.nom,
                      description: type.description,
                      badge: `${type.duree_previsionnelle_jours} jours`
                    }))
                  ]}
                  value={filters.type_projet_id?.toString() || "all"}
                  onValueChange={(value) => updateFilter('type_projet_id', value === "all" ? null : parseInt(value))}
                  placeholder="Tous les types"
                  searchPlaceholder="Rechercher un Type d'instruction..."
                />
              </div>

              {/* En retard */}
              <div className="space-y-2">
                <Label htmlFor="en_retard">En retard</Label>
                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="en_retard"
                    checked={filters.en_retard || false}
                    onCheckedChange={(checked) => updateFilter('en_retard', checked)}
                  />
                  <Label htmlFor="en_retard" className="text-sm">Instructions en retard uniquement</Label>
                </div>
              </div>

              {/* Priorité */}
              <div className="space-y-2">
                <Label htmlFor="priorite">Priorité</Label>
                <PrioritySelect
                  value={filters.priorite}
                  onValueChange={(value) => updateFilter('priorite', value)}
                  placeholder="Toutes les priorités"
                />
              </div>

              {/* Favoris */}
              <div className="space-y-2">
                <Label htmlFor="favoris">Favoris</Label>
                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="favoris"
                    checked={filters.favoris || false}
                    onCheckedChange={(checked) => updateFilter('favoris', checked)}
                  />
                  <Label htmlFor="favoris" className="text-sm">Mes favoris uniquement</Label>
                </div>
              </div>
            </div>

            {/* Filtres par utilisateur (selon permissions) */}
            {permissions?.can_filter_by_user && (
              <>
                <Separator />
                <div className="space-y-4">
                  <h4 className="font-medium flex items-center gap-2">
                    <Users className="h-4 w-4" />
                    Filtres par utilisateur
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="porteur">Porteurs</Label>
                      <SearchableMultiSelect
                        options={users.map((user) => ({
                          value: user.id.toString(),
                          label: user.display_name,
                          description: user.email,
                          badge: user.matricule
                        }))}
                        value={filters.porteur_ids?.map(id => id.toString()) || []}
                        onValueChange={(values) => updateFilter('porteur_ids', values.map(v => parseInt(v)))}
                        placeholder="Sélectionner les porteurs..."
                        searchPlaceholder="Rechercher des porteurs..."
                        maxSelectedItems={5}
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="donneur_ordre">Ordonnateurs de l'instruction</Label>
                      <SearchableMultiSelect
                        options={users.map((user) => ({
                          value: user.id.toString(),
                          label: user.display_name,
                          description: user.email,
                          badge: user.matricule
                        }))}
                        value={filters.donneur_ordre_ids?.map(id => id.toString()) || []}
                        onValueChange={(values) => updateFilter('donneur_ordre_ids', values.map(v => parseInt(v)))}
                        placeholder="Sélectionner les ordonnateurs..."
                        searchPlaceholder="Rechercher des ordonnateurs..."
                        maxSelectedItems={5}
                      />
                    </div>
                  </div>
                </div>
              </>
            )}

            {/* Filtre par entité (selon permissions) */}
            {permissions?.can_filter_by_entity && (
              <>
                <Separator />
                <div className="space-y-4">
                  <h4 className="font-medium flex items-center gap-2">
                    <Building className="h-4 w-4" />
                    Filtre par entité
                  </h4>
                  <div className="space-y-2">
                    <Label htmlFor="entite">Entité</Label>
                    <SearchableSelect
                      options={[
                        { value: "all", label: "Toutes les entités" },
                        ...entities.map((entity) => ({
                          value: entity.id.toString(),
                          label: entity.nom,
                          description: entity.type,
                          badge: entity.type
                        }))
                      ]}
                      value={filters.entite_id?.toString() || "all"}
                      onValueChange={(value) => updateFilter('entite_id', value === "all" ? null : parseInt(value))}
                      placeholder="Toutes les entités"
                      searchPlaceholder="Rechercher une entité..."
                    />
                  </div>
                </div>
              </>
            )}

            {/* Filtres de date */}
            {permissions?.can_filter_by_date && (
              <>
                <Separator />
                <div className="space-y-4">
                  <h4 className="font-medium flex items-center gap-2">
                    <Calendar className="h-4 w-4" />
                    Filtres de date
                  </h4>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="date_debut_debut">Date début prévisionnelle (début)</Label>
                      <Input
                        type="date"
                        value={filters.date_debut_previsionnelle_debut || ""}
                        onChange={(e) => updateFilter('date_debut_previsionnelle_debut', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_debut_fin">Date début prévisionnelle (fin)</Label>
                      <Input
                        type="date"
                        value={filters.date_debut_previsionnelle_fin || ""}
                        onChange={(e) => updateFilter('date_debut_previsionnelle_fin', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_fin_debut">Date fin prévisionnelle (début)</Label>
                      <Input
                        type="date"
                        value={filters.date_fin_previsionnelle_debut || ""}
                        onChange={(e) => updateFilter('date_fin_previsionnelle_debut', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_fin_fin">Date fin prévisionnelle (fin)</Label>
                      <Input
                        type="date"
                        value={filters.date_fin_previsionnelle_fin || ""}
                        onChange={(e) => updateFilter('date_fin_previsionnelle_fin', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_creation_debut">Date création (début)</Label>
                      <Input
                        type="date"
                        value={filters.date_creation_debut || ""}
                        onChange={(e) => updateFilter('date_creation_debut', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_creation_fin">Date création (fin)</Label>
                      <Input
                        type="date"
                        value={filters.date_creation_fin || ""}
                        onChange={(e) => updateFilter('date_creation_fin', e.target.value)}
                      />
                    </div>
                  </div>
                </div>
              </>
            )}

            {/* Niveau d'exécution */}
            <Separator />
            <div className="space-y-4">
              <h4 className="font-medium">Niveau d'exécution</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="niveau_min">Minimum (%)</Label>
                  <Input
                    type="number"
                    min="0"
                    max="100"
                    placeholder="0"
                    value={filters.niveau_execution_min || ""}
                    onChange={(e) => updateFilter('niveau_execution_min', e.target.value ? parseInt(e.target.value) : null)}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="niveau_max">Maximum (%)</Label>
                  <Input
                    type="number"
                    min="0"
                    max="100"
                    placeholder="100"
                    value={filters.niveau_execution_max || ""}
                    onChange={(e) => updateFilter('niveau_execution_max', e.target.value ? parseInt(e.target.value) : null)}
                  />
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="flex justify-end gap-2 pt-4">
              <Button
                variant="outline"
                onClick={clearAllFilters}
                disabled={getActiveFiltersCount() === 0}
              >
                <X className="h-4 w-4 mr-2" />
                Effacer tout
              </Button>
              <Button
                variant="outline"
                onClick={loadFilterData}
                disabled={loading}
              >
                <RefreshCw className={cn("h-4 w-4 mr-2", loading && "animate-spin")} />
                Actualiser
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
} 