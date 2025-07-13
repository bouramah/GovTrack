'use client';

import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { useToast } from '@/components/ui/use-toast';
import { apiClient } from '@/lib/api';
import { TypeTache, TypeTacheStatistiques } from '@/lib/api';
import { BarChart3, Clock, CheckCircle, XCircle, TrendingUp, AlertTriangle } from 'lucide-react';

interface TypeTacheStatsDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  typeTacheId: number;
}

export function TypeTacheStatsDialog({ open, onOpenChange, typeTacheId }: TypeTacheStatsDialogProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [typeTache, setTypeTache] = useState<TypeTache | null>(null);
  const [statistiques, setStatistiques] = useState<TypeTacheStatistiques | null>(null);

  // Charger les statistiques
  const loadStats = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getTypeTacheStatistiques(typeTacheId);
      setTypeTache(response.type_tache);
      setStatistiques(response.statistiques);
    } catch (error: any) {
      toast({
        title: 'Erreur',
        description: error.message || 'Erreur lors du chargement des statistiques',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  // Effet pour charger les données
  useEffect(() => {
    if (open && typeTacheId) {
      loadStats();
    }
  }, [open, typeTacheId]);

  // Réinitialiser les données quand le dialogue se ferme
  useEffect(() => {
    if (!open) {
      setTypeTache(null);
      setStatistiques(null);
    }
  }, [open]);

  if (!typeTache || !statistiques) {
    return (
      <Dialog open={open} onOpenChange={onOpenChange}>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Statistiques du type de tâche</DialogTitle>
            <DialogDescription>
              Chargement des statistiques...
            </DialogDescription>
          </DialogHeader>
          <div className="flex items-center justify-center h-32">
            <div className="text-gray-500">
              {loading ? 'Chargement...' : 'Aucune donnée disponible'}
            </div>
          </div>
        </DialogContent>
      </Dialog>
    );
  }

  // Calculer les pourcentages pour les statuts
  const totalTaches = statistiques.total_taches;
  const statutsWithPercentages = Object.entries(statistiques.taches_par_statut).map(([statut, count]) => ({
    statut,
    count,
    percentage: totalTaches > 0 ? (count / totalTaches) * 100 : 0,
  }));

  // Couleurs pour les statuts
  const getStatutColor = (statut: string) => {
    switch (statut) {
      case 'a_faire': return 'bg-gray-500';
      case 'en_cours': return 'bg-blue-500';
      case 'bloque': return 'bg-red-500';
      case 'termine': return 'bg-green-500';
      default: return 'bg-gray-400';
    }
  };

  const getStatutLabel = (statut: string) => {
    switch (statut) {
      case 'a_faire': return 'À faire';
      case 'en_cours': return 'En cours';
      case 'bloque': return 'Bloqué';
      case 'termine': return 'Terminé';
      default: return statut;
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[700px] max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <BarChart3 className="h-5 w-5" />
            Statistiques du type de tâche
          </DialogTitle>
          <DialogDescription>
            Aperçu détaillé de l'utilisation et des performances du type "{typeTache.nom}"
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-6">
          {/* En-tête avec informations du type */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-3">
                <div
                  className="w-4 h-4 rounded-full"
                  style={{ backgroundColor: typeTache.couleur }}
                />
                {typeTache.nom}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="text-center">
                  <div className="text-2xl font-bold text-blue-600">{statistiques.total_taches}</div>
                  <div className="text-sm text-gray-500">Total tâches</div>
                </div>
                <div className="text-center">
                  <div className="text-2xl font-bold text-green-600">
                    {statistiques.niveau_execution_moyen.toFixed(1)}%
                  </div>
                  <div className="text-sm text-gray-500">Niveau moyen</div>
                </div>
                <div className="text-center">
                  <div className="text-2xl font-bold text-red-600">{statistiques.taches_en_retard}</div>
                  <div className="text-sm text-gray-500">En retard</div>
                </div>
                <div className="text-center">
                  <div className="text-2xl font-bold text-gray-600">
                    {typeTache.actif ? 'Actif' : 'Inactif'}
                  </div>
                  <div className="text-sm text-gray-500">Statut</div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Répartition par statut */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4" />
                Répartition par statut
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {statutsWithPercentages.map(({ statut, count, percentage }) => (
                  <div key={statut} className="space-y-2">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <div className={`w-3 h-3 rounded-full ${getStatutColor(statut)}`} />
                        <span className="font-medium">{getStatutLabel(statut)}</span>
                        <Badge variant="outline">{count}</Badge>
                      </div>
                      <span className="text-sm text-gray-500">{percentage.toFixed(1)}%</span>
                    </div>
                    <Progress value={percentage} className="h-2" />
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Niveau d'exécution moyen */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="h-4 w-4" />
                Niveau d'exécution moyen
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="font-medium">Progression globale</span>
                  <span className="text-lg font-bold text-blue-600">
                    {statistiques.niveau_execution_moyen.toFixed(1)}%
                  </span>
                </div>
                <Progress 
                  value={statistiques.niveau_execution_moyen} 
                  className="h-3"
                />
                <div className="grid grid-cols-3 gap-4 text-center text-sm">
                  <div>
                    <div className="font-semibold text-gray-900">0%</div>
                    <div className="text-gray-500">Début</div>
                  </div>
                  <div>
                    <div className="font-semibold text-gray-900">50%</div>
                    <div className="text-gray-500">Milieu</div>
                  </div>
                  <div>
                    <div className="font-semibold text-gray-900">100%</div>
                    <div className="text-gray-500">Terminé</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Tâches en retard */}
          {statistiques.taches_en_retard > 0 && (
            <Card className="border-red-200 bg-red-50">
              <CardHeader>
                <CardTitle className="flex items-center gap-2 text-red-700">
                  <AlertTriangle className="h-4 w-4" />
                  Tâches en retard
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-3">
                  <div className="text-3xl font-bold text-red-600">
                    {statistiques.taches_en_retard}
                  </div>
                  <div className="text-red-700">
                    <div className="font-medium">Tâches en retard</div>
                    <div className="text-sm">
                      {((statistiques.taches_en_retard / statistiques.total_taches) * 100).toFixed(1)}% du total
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}

          {/* Informations générales */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                Informations générales
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="font-medium text-gray-700">Ordre d'affichage :</span>
                  <span className="ml-2 text-gray-600">{typeTache.ordre}</span>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Créé le :</span>
                  <span className="ml-2 text-gray-600">
                    {new Date(typeTache.date_creation).toLocaleDateString('fr-FR')}
                  </span>
                </div>
                <div>
                  <span className="font-medium text-gray-700">Créé par :</span>
                  <span className="ml-2 text-gray-600">{typeTache.creer_par}</span>
                </div>
                {typeTache.date_modification && (
                  <div>
                    <span className="font-medium text-gray-700">Modifié le :</span>
                    <span className="ml-2 text-gray-600">
                      {new Date(typeTache.date_modification).toLocaleDateString('fr-FR')}
                    </span>
                  </div>
                )}
                {typeTache.description && (
                  <div className="md:col-span-2">
                    <span className="font-medium text-gray-700">Description :</span>
                    <p className="mt-1 text-gray-600">{typeTache.description}</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      </DialogContent>
    </Dialog>
  );
} 