'use client';

import React, { useState, useEffect, useRef } from 'react';
import { useDebounce } from '@/hooks/use-debounce';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { 
  BarChart3, 
  Clock, 
  CheckCircle, 
  AlertTriangle, 
  TrendingUp, 
  Users, 
  Calendar,
  Loader2,
  RefreshCw,
  Plus,
  List
} from 'lucide-react';
import { apiClient, ProjectDashboard as DashboardData, Project, ProjectFilters, ProjectPermissions } from '@/lib/api';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import Link from "next/link";
import DashboardFilters from './DashboardFilters';
// import { AuditStatsCard } from './AuditStatsCard';

interface ProjectDashboardProps {
  className?: string;
}

export default function ProjectDashboard({ className }: ProjectDashboardProps) {
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<ProjectFilters>({});
  const [permissions, setPermissions] = useState<ProjectPermissions | null>(null);
  
  // Debounce les filtres pour éviter les appels API trop fréquents
  const debouncedFilters = useDebounce(filters, 1000);
  
  // Référence pour stocker les filtres précédents et éviter les appels API inutiles
  const previousFiltersRef = useRef<ProjectFilters>({});

  const loadDashboard = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Nettoyer les filtres vides avant l'appel API
      const cleanFilters = Object.fromEntries(
        Object.entries(debouncedFilters).filter(([_, value]) => 
          value !== null && value !== undefined && value !== ""
        )
      );
      
      const data = await apiClient.getProjectDashboard(cleanFilters);
      setDashboardData(data);
      
      // Récupérer les permissions depuis la réponse des projets
      if (data.permissions_info) {
        const permissionsData = {
          level: data.permissions_info.level,
          can_filter_by_user: data.permissions_info.level === 'all_projects' || data.permissions_info.level === 'entity_projects',
          can_filter_by_entity: data.permissions_info.level === 'all_projects',
          can_filter_by_date: true,
          available_filters: {
            basic: ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
            date: ['date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin', 'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin', 'date_creation_debut', 'date_creation_fin'],
            user: data.permissions_info.level === 'all_projects' || data.permissions_info.level === 'entity_projects' ? ['porteur_id', 'donneur_ordre_id'] : [],
            entity: data.permissions_info.level === 'all_projects' ? ['entite_id'] : []
          },
          description: data.permissions_info.description
        };
        
        console.log('🔍 [ProjectDashboard] Permissions définies:', permissionsData);
        console.log('🔍 [ProjectDashboard] Niveau:', data.permissions_info.level);
        console.log('🔍 [ProjectDashboard] can_filter_by_user:', permissionsData.can_filter_by_user);
        
        setPermissions(permissionsData);
      }
    } catch (err: any) {
      setError(err.message || 'Erreur lors du chargement du tableau de bord');
      toast.error(err.message || 'Impossible de charger le tableau de bord');
    } finally {
      setLoading(false);
    }
  };

  // Chargement initial
  useEffect(() => {
    if (!dashboardData) {
      loadDashboard();
    }
  }, []);

  // Chargement lors des changements de filtres
  useEffect(() => {
    // Éviter les appels API inutiles si les filtres sont identiques
    const filtersChanged = JSON.stringify(debouncedFilters) !== JSON.stringify(previousFiltersRef.current);
    
    // Charger le dashboard si les filtres ont changé (même s'ils sont vides)
    if (filtersChanged) {
      previousFiltersRef.current = debouncedFilters;
      loadDashboard();
    }
  }, [debouncedFilters]);

  const handleFiltersChange = (newFilters: ProjectFilters) => {
    setFilters(newFilters);
  };

  const getStatusColor = (statut: string) => {
    switch (statut) {
      case "a_faire":
        return "bg-gray-100 text-gray-800";
      case "en_cours":
        return "bg-blue-100 text-blue-800";
      case "demande_de_cloture":
        return "bg-yellow-100 text-yellow-800";
      case "termine":
        return "bg-green-100 text-green-800";
      case "bloque":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  if (loading) {
    return (
      <div className={cn("flex items-center justify-center py-12 min-h-[60vh] bg-gradient-to-br from-blue-50 to-indigo-50", className)}>
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
        <span className="ml-2 text-gray-600">Chargement du tableau de bord...</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className={cn("text-center py-12 min-h-[60vh] bg-gradient-to-br from-blue-50 to-indigo-50", className)}>
        <AlertTriangle className="h-12 w-12 mx-auto text-red-500 mb-4" />
        <h3 className="text-lg font-semibold mb-2">Erreur de chargement</h3>
        <p className="text-gray-600 mb-4">{error}</p>
        <Button onClick={loadDashboard}>
          <RefreshCw className="h-4 w-4 mr-2" />
          Réessayer
        </Button>
      </div>
    );
  }

  if (!dashboardData) {
    return null;
  }

  const { 
    total_projets, 
    projets_par_statut, 
    projets_en_retard, 
    niveau_execution_moyen,
    projets_recents,
    permissions_info 
  } = dashboardData;

  return (
    <div className={cn("relative min-h-[90vh] bg-gradient-to-br from-blue-50 to-indigo-50 p-0 md:p-6", className)}>
      {/* Header principal */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div className="text-center md:text-left flex-1">
          <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">Tableau de bord</h1>
          <p className="text-gray-600 text-base md:text-lg max-w-2xl mx-auto md:mx-0">
            Suivi global des instructions, progression, retards et accès rapide à vos actions clés.
          </p>
        </div>
        <div className="flex justify-center md:justify-end">
          <Link href="/projects">
            <Button size="lg" className="bg-blue-600 hover:bg-blue-700 text-white shadow-lg">
              <List className="h-5 w-5 mr-2" />
              Voir toutes les instructions 
            </Button>
          </Link>
        </div>
      </div>

      {/* Filtres du tableau de bord */}
      {permissions && (
        <div className="mb-8">
          <DashboardFilters
            filters={filters}
            onFiltersChange={handleFiltersChange}
            permissions={permissions}
          />
        </div>
      )}

      {/* Statistiques principales */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <Card className="hover:scale-105 transition-transform shadow-md">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Instructions</CardTitle>
            <BarChart3 className="h-6 w-6 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-extrabold text-gray-900">{total_projets}</div>
            <p className="text-xs text-muted-foreground">Instructions au total</p>
          </CardContent>
        </Card>
        <Card className="hover:scale-105 transition-transform shadow-md">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">En Retard</CardTitle>
            <AlertTriangle className="h-6 w-6 text-red-500" />
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-extrabold text-red-600">{projets_en_retard}</div>
            <p className="text-xs text-muted-foreground">Instructions en retard</p>
          </CardContent>
        </Card>
        <Card className="hover:scale-105 transition-transform shadow-md">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Progression Moyenne</CardTitle>
            <TrendingUp className="h-6 w-6 text-green-500" />
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-extrabold text-green-600">{niveau_execution_moyen}%</div>
            <p className="text-xs text-muted-foreground">Niveau d'exécution moyen</p>
          </CardContent>
        </Card>
        <Card className="hover:scale-105 transition-transform shadow-md">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">En Cours</CardTitle>
            <Clock className="h-6 w-6 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-extrabold text-blue-600">{projets_par_statut.en_cours?.count || 0}</div>
            <p className="text-xs text-muted-foreground">Instructions actives</p>
          </CardContent>
        </Card>
      </div>

      {/* Statistiques d'audit */}
      {/* <div className="mb-10">
        <AuditStatsCard />
      </div> */}

      {/* Section Instructions récentes */}
      <div className="bg-white rounded-xl shadow-lg p-6 mb-10">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-bold text-gray-900">Instructions récentes</h2>
          <Link href="/projects">
            <Button variant="ghost" size="sm" className="text-blue-600">Voir toutes</Button>
          </Link>
        </div>
        <div className="divide-y divide-gray-100">
          {projets_recents && projets_recents.length > 0 ? (
            projets_recents.map((proj: Project) => (
              <div key={proj.id} className="flex items-center py-4 gap-4 hover:bg-blue-50 rounded-lg transition">
                <Avatar className="h-10 w-10">
                  <AvatarFallback className="bg-blue-100 text-blue-700">
                    {getInitials(`${proj.porteur?.prenom || ''} ${proj.porteur?.nom || ''}`)}
                  </AvatarFallback>
                </Avatar>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <Link href={`/projects/${proj.id}`} className="font-semibold text-gray-900 hover:text-blue-700 truncate">
                      {proj.titre}
                    </Link>
                    <Badge className={getStatusColor(proj.statut)}>{proj.statut.replace(/_/g, ' ')}</Badge>
                  </div>
                  <div className="text-xs text-gray-500 truncate">{proj.description}</div>
                </div>
                <div className="flex flex-col items-end">
                  <span className="text-sm font-bold text-blue-700">{proj.niveau_execution}%</span>
                  <span className="text-xs text-gray-400">{new Date(proj.date_debut_previsionnelle).toLocaleDateString('fr-FR')}</span>
                </div>
              </div>
            ))
          ) : (
            <div className="text-gray-500 text-center py-8">Aucune instruction récente</div>
          )}
        </div>
      </div>

      {/* Répartition par statut */}
      <div className="bg-white rounded-xl shadow-lg p-6 mb-20">
        <h2 className="text-xl font-bold text-gray-900 mb-4">Répartition par statut</h2>
        <div className="space-y-3">
          {Object.entries(projets_par_statut).map(([statut, info]: any) => (
            <div key={statut} className="flex items-center gap-4">
              <span className="w-32 capitalize text-gray-700">{statut.replace(/_/g, ' ')}</span>
              <div className="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                <div
                  className={cn("h-4 rounded-full transition-all", getStatusColor(statut))}
                  style={{ width: `${(info.count / total_projets) * 100 || 0}%` }}
                ></div>
              </div>
              <span className="ml-2 font-semibold text-gray-900">{info.count}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Bouton Actualiser flottant */}
      <Button
        onClick={loadDashboard}
        className="fixed bottom-8 right-8 z-50 bg-blue-600 hover:bg-blue-700 text-white shadow-lg rounded-full p-4"
        size="icon"
        title="Actualiser le tableau de bord"
      >
        <RefreshCw className="h-6 w-6" />
      </Button>
    </div>
  );
} 