"use client";

import { ProtectedRoute } from "@/components/ProtectedRoute";
import ProjectsAdvancedFilters from "@/components/projects-advanced-filters";
import { ProjectFilters, ProjectPermissions } from "@/lib/api";
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

export default function TestFiltersPage() {
  const [filters, setFilters] = useState<ProjectFilters>({});
  const [permissions, setPermissions] = useState<ProjectPermissions>({
    level: 'all_projects',
    can_filter_by_user: true,
    can_filter_by_entity: true,
    can_filter_by_date: true,
    available_filters: {
      basic: ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
      date: ['date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin', 'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin', 'date_creation_debut', 'date_creation_fin'],
      user: ['porteur_id', 'donneur_ordre_id'],
      entity: ['entite_id']
    },
    description: 'Accès complet à toutes les instructions'
  });

  return (
    <ProtectedRoute>
      <div className="container mx-auto p-6 space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-bold">Test des Filtres Avancés</h1>
          <Badge variant="outline">Mode Test</Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Filtres */}
          <div className="lg:col-span-2">
            <ProjectsAdvancedFilters
              filters={filters}
              onFiltersChange={setFilters}
              permissions={permissions}
            />
          </div>

          {/* Informations de debug */}
          <div className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Filtres Actifs</CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="text-xs bg-gray-100 p-2 rounded overflow-auto">
                  {JSON.stringify(filters, null, 2)}
                </pre>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Permissions</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="text-sm">Niveau:</span>
                    <Badge variant="outline">{permissions.level}</Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm">Filtres utilisateur:</span>
                    <Badge variant={permissions.can_filter_by_user ? "default" : "secondary"}>
                      {permissions.can_filter_by_user ? "Oui" : "Non"}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm">Filtres entité:</span>
                    <Badge variant={permissions.can_filter_by_entity ? "default" : "secondary"}>
                      {permissions.can_filter_by_entity ? "Oui" : "Non"}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm">Filtres date:</span>
                    <Badge variant={permissions.can_filter_by_date ? "default" : "secondary"}>
                      {permissions.can_filter_by_date ? "Oui" : "Non"}
                    </Badge>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Test des différents niveaux de permissions */}
            <Card>
              <CardHeader>
                <CardTitle>Test Permissions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <button
                  onClick={() => setPermissions({
                    level: 'all_projects',
                    can_filter_by_user: true,
                    can_filter_by_entity: true,
                    can_filter_by_date: true,
                    available_filters: {
                      basic: ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
                      date: ['date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin', 'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin', 'date_creation_debut', 'date_creation_fin'],
                      user: ['porteur_id', 'donneur_ordre_id'],
                      entity: ['entite_id']
                    },
                    description: 'Accès complet à toutes les instructions'
                  })}
                  className="w-full text-left p-2 text-sm bg-blue-50 hover:bg-blue-100 rounded"
                >
                  🔓 Administrateur (Tous les filtres)
                </button>
                <button
                  onClick={() => setPermissions({
                    level: 'entity_projects',
                    can_filter_by_user: true,
                    can_filter_by_entity: false,
                    can_filter_by_date: true,
                    available_filters: {
                      basic: ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
                      date: ['date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin', 'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin', 'date_creation_debut', 'date_creation_fin'],
                      user: ['porteur_id', 'donneur_ordre_id'],
                      entity: []
                    },
                    description: 'Projets de votre entité'
                  })}
                  className="w-full text-left p-2 text-sm bg-green-50 hover:bg-green-100 rounded"
                >
                  🏢 Chef d'entité (Filtres utilisateur limités)
                </button>
                <button
                  onClick={() => setPermissions({
                    level: 'my_projects',
                    can_filter_by_user: false,
                    can_filter_by_entity: false,
                    can_filter_by_date: true,
                    available_filters: {
                      basic: ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
                      date: ['date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin', 'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin', 'date_creation_debut', 'date_creation_fin'],
                      user: [],
                      entity: []
                    },
                    description: 'Vos projets personnels'
                  })}
                  className="w-full text-left p-2 text-sm bg-gray-50 hover:bg-gray-100 rounded"
                >
                  👤 Utilisateur standard (Filtres de base uniquement)
                </button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </ProtectedRoute>
  );
} 