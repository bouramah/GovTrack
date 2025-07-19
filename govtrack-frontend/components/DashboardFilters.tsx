"use client";

import { useState, useEffect } from "react";
import { useDebounce } from "@/hooks/use-debounce";
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

interface DashboardFiltersProps {
  filters: ProjectFilters;
  onFiltersChange: (filters: ProjectFilters) => void;
  permissions?: ProjectPermissions;
  className?: string;
}

export default function DashboardFilters({
  filters,
  onFiltersChange,
  permissions,
  className
}: DashboardFiltersProps) {
  const [isExpanded, setIsExpanded] = useState(false);
  const [loading, setLoading] = useState(false);
  const [typeProjets, setTypeProjets] = useState<TypeProjet[]>([]);
  const [entities, setEntities] = useState<FilterEntity[]>([]);
  const [users, setUsers] = useState<FilterUser[]>([]);
  
  // État local pour la recherche avec debounce
  const [searchValue, setSearchValue] = useState(filters.search || "");
  const debouncedSearchValue = useDebounce(searchValue, 500);
  
  // État local pour les dates avec debounce
  const [dateFilters, setDateFilters] = useState({
    date_debut_previsionnelle_debut: filters.date_debut_previsionnelle_debut || "",
    date_debut_previsionnelle_fin: filters.date_debut_previsionnelle_fin || "",
    date_fin_previsionnelle_debut: filters.date_fin_previsionnelle_debut || "",
    date_fin_previsionnelle_fin: filters.date_fin_previsionnelle_fin || "",
    date_creation_debut: filters.date_creation_debut || "",
    date_creation_fin: filters.date_creation_fin || ""
  });
  const debouncedDateFilters = useDebounce(dateFilters, 500);
  
  // État local pour les niveaux d'exécution avec debounce
  const [executionFilters, setExecutionFilters] = useState({
    niveau_execution_min: filters.niveau_execution_min || "",
    niveau_execution_max: filters.niveau_execution_max || ""
  });
  const debouncedExecutionFilters = useDebounce(executionFilters, 500);

  // Charger les données pour les filtres
  useEffect(() => {
    loadFilterData();
  }, [permissions]);

  // Synchroniser la valeur debounced avec les filtres
  useEffect(() => {
    updateFilter('search', debouncedSearchValue);
  }, [debouncedSearchValue]);

  // Synchroniser les dates debounced avec les filtres
  useEffect(() => {
    Object.entries(debouncedDateFilters).forEach(([key, value]) => {
      updateFilter(key as keyof ProjectFilters, value || null);
    });
  }, [debouncedDateFilters]);

  // Synchroniser les niveaux d'exécution debounced avec les filtres
  useEffect(() => {
    Object.entries(debouncedExecutionFilters).forEach(([key, value]) => {
      updateFilter(key as keyof ProjectFilters, value || null);
    });
  }, [debouncedExecutionFilters]);



  const loadFilterData = async () => {
    setLoading(true);
    try {
      console.log('🔍 Chargement des données de filtres...');
      console.log('📋 Permissions reçues:', permissions);
      
      // Charger les types de projets (toujours disponible)
      const typesResponse = await apiClient.getTypeProjets({ per_page: 100 });
      setTypeProjets(typesResponse.data || []);

      // Charger les entités si l'utilisateur a les permissions
      if (permissions?.can_filter_by_entity) {
        console.log('🏢 Chargement des entités pour filtres...');
        const entitiesResponse = await apiClient.getProjectFilterEntities();
        console.log('🏢 Entités chargées:', entitiesResponse);
        setEntities(entitiesResponse);
      } else {
        console.log('❌ Pas de permission pour filtrer par entité');
      }

      // Charger les utilisateurs si l'utilisateur a les permissions
      if (permissions?.can_filter_by_user) {
        console.log('👥 Chargement des utilisateurs pour filtres...');
        const usersResponse = await apiClient.getProjectFilterUsers();
        console.log('👥 Utilisateurs chargés:', usersResponse);
        setUsers(usersResponse);
      } else {
        console.log('❌ Pas de permission pour filtrer par utilisateur');
      }
    } catch (error: any) {
      console.error('❌ Erreur lors du chargement des données de filtres:', error);
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
    // Effacer directement tous les filtres sans passer par les états locaux
    onFiltersChange({});
    
    // Réinitialiser les états locaux après un court délai pour éviter les conflits
    setTimeout(() => {
      setSearchValue("");
      setDateFilters({
        date_debut_previsionnelle_debut: "",
        date_debut_previsionnelle_fin: "",
        date_fin_previsionnelle_debut: "",
        date_fin_previsionnelle_fin: "",
        date_creation_debut: "",
        date_creation_fin: ""
      });
      setExecutionFilters({
        niveau_execution_min: "",
        niveau_execution_max: ""
      });
    }, 100);
  };

  const clearFilter = (key: keyof ProjectFilters) => {
    const newFilters = { ...filters };
    delete newFilters[key];
    onFiltersChange(newFilters);
    
    // Réinitialiser les états locaux correspondants après un court délai
    setTimeout(() => {
      if (key === 'search') {
        setSearchValue("");
      } else if (key === 'date_debut_previsionnelle_debut') {
        setDateFilters(prev => ({ ...prev, date_debut_previsionnelle_debut: "" }));
      } else if (key === 'date_debut_previsionnelle_fin') {
        setDateFilters(prev => ({ ...prev, date_debut_previsionnelle_fin: "" }));
      } else if (key === 'date_fin_previsionnelle_debut') {
        setDateFilters(prev => ({ ...prev, date_fin_previsionnelle_debut: "" }));
      } else if (key === 'date_fin_previsionnelle_fin') {
        setDateFilters(prev => ({ ...prev, date_fin_previsionnelle_fin: "" }));
      } else if (key === 'date_creation_debut') {
        setDateFilters(prev => ({ ...prev, date_creation_debut: "" }));
      } else if (key === 'date_creation_fin') {
        setDateFilters(prev => ({ ...prev, date_creation_fin: "" }));
      } else if (key === 'niveau_execution_min') {
        setExecutionFilters(prev => ({ ...prev, niveau_execution_min: "" }));
      } else if (key === 'niveau_execution_max') {
        setExecutionFilters(prev => ({ ...prev, niveau_execution_max: "" }));
      }
    }, 100);
  };

  const getActiveFiltersCount = () => {
    return Object.keys(filters).filter(key => 
      key !== 'page' && key !== 'per_page' && key !== 'sort_by' && key !== 'sort_order'
    ).length;
  };

  return (
    <div className={cn("space-y-4", className)}>
      {/* En-tête des filtres */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <Filter className="h-5 w-5 text-gray-600" />
          <h3 className="text-lg font-semibold">Filtres du tableau de bord</h3>
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
          {filters.porteur_ids && filters.porteur_ids.length > 0 && (
            <Badge variant="outline" className="flex items-center gap-1">
              Porteurs: {filters.porteur_ids.length} sélectionné(s)
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('porteur_ids')}
              />
            </Badge>
          )}
          {filters.donneur_ordre_ids && filters.donneur_ordre_ids.length > 0 && (
            <Badge variant="outline" className="flex items-center gap-1">
              Ordonnateurs: {filters.donneur_ordre_ids.length} sélectionné(s)
              <X 
                className="h-3 w-3 cursor-pointer" 
                onClick={() => clearFilter('donneur_ordre_ids')}
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
                  value={searchValue}
                  onChange={(e) => setSearchValue(e.target.value)}
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
                  searchPlaceholder="Rechercher un type d'instruction..."
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
                        value={dateFilters.date_debut_previsionnelle_debut}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_debut_previsionnelle_debut: e.target.value }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_debut_fin">Date début prévisionnelle (fin)</Label>
                      <Input
                        type="date"
                        value={dateFilters.date_debut_previsionnelle_fin}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_debut_previsionnelle_fin: e.target.value }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_fin_debut">Date fin prévisionnelle (début)</Label>
                      <Input
                        type="date"
                        value={dateFilters.date_fin_previsionnelle_debut}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_fin_previsionnelle_debut: e.target.value }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_fin_fin">Date fin prévisionnelle (fin)</Label>
                      <Input
                        type="date"
                        value={dateFilters.date_fin_previsionnelle_fin}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_fin_previsionnelle_fin: e.target.value }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_creation_debut">Date création (début)</Label>
                      <Input
                        type="date"
                        value={dateFilters.date_creation_debut}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_creation_debut: e.target.value }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="date_creation_fin">Date création (fin)</Label>
                      <Input
                        type="date"
                        value={dateFilters.date_creation_fin}
                        onChange={(e) => setDateFilters(prev => ({ ...prev, date_creation_fin: e.target.value }))}
                      />
                    </div>
                  </div>
                </div>
              </>
            )}

            {/* Filtres de niveau d'exécution */}
            <Separator />
            <div className="space-y-4">
              <h4 className="font-medium">Niveau d'exécution</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="niveau_execution_min">Niveau minimum (%)</Label>
                  <Input
                    type="number"
                    min="0"
                    max="100"
                    value={executionFilters.niveau_execution_min}
                    onChange={(e) => setExecutionFilters(prev => ({ ...prev, niveau_execution_min: e.target.value }))}
                    placeholder="0"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="niveau_execution_max">Niveau maximum (%)</Label>
                  <Input
                    type="number"
                    min="0"
                    max="100"
                    value={executionFilters.niveau_execution_max}
                    onChange={(e) => setExecutionFilters(prev => ({ ...prev, niveau_execution_max: e.target.value }))}
                    placeholder="100"
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
} 