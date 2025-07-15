"use client";

import { useState, useEffect } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Progress } from "@/components/ui/progress";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Calendar, User, FileText, MessageSquare, Clock, AlertTriangle, Download, Loader2 } from "lucide-react";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import { cn } from "@/lib/utils";
import { apiClient } from "@/lib/api";
import type { Tache, TacheHistoriqueStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";
import TaskDiscussionsList from "./Shared/TaskDiscussionsList";
import { useToast } from "@/components/ui/use-toast";

interface TacheDetailModalProps {
  tache: Tache;
}

export default function TacheDetailModal({ tache }: TacheDetailModalProps) {
  const [activeTab, setActiveTab] = useState("overview");
  const [historiqueStatuts, setHistoriqueStatuts] = useState<TacheHistoriqueStatut[]>([]);
  const [loadingHistorique, setLoadingHistorique] = useState(false);
  const { toast } = useToast();

  // Fonction pour obtenir les initiales
  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  // Vérifier si la tâche est en retard
  const isEnRetard = tache.est_en_retard || 
    (tache.date_fin_previsionnelle && 
     new Date(tache.date_fin_previsionnelle) < new Date() && 
     tache.statut !== 'termine');

  // Charger l'historique des statuts
  const loadHistoriqueStatuts = async () => {
    try {
      setLoadingHistorique(true);
      const response = await apiClient.getTacheHistoriqueStatuts(tache.id);
      if (response.success && response.data) {
        setHistoriqueStatuts(response.data);
      }
    } catch (error: any) {
      console.error('Erreur lors du chargement de l\'historique:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger l'historique de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoadingHistorique(false);
    }
  };

  // Charger l'historique quand l'onglet historique est activé
  useEffect(() => {
    if (activeTab === "history" && historiqueStatuts.length === 0) {
      loadHistoriqueStatuts();
    }
  }, [activeTab, tache.id]);

  return (
    <div className="space-y-6 max-h-[70vh] overflow-y-auto">
      {/* Informations générales */}
      <div className="space-y-4">
        {/* En-tête avec statut et progression */}
        <div className="flex items-center justify-between">
          <Badge className={cn("font-medium", TACHE_STATUT_COLORS[tache.statut])}>
            {TACHE_STATUTS_KANBAN[tache.statut]}
          </Badge>
          <div className="text-sm text-gray-500">
            Progression: {tache.niveau_execution}%
          </div>
        </div>

        {/* Barre de progression */}
        <div className="space-y-2">
          <Progress value={tache.niveau_execution} className="h-2" />
          <div className="flex justify-between text-xs text-gray-500">
            <span>0%</span>
            <span>100%</span>
          </div>
        </div>

        {/* Description */}
        {tache.description && (
          <div className="space-y-2">
            <h4 className="text-sm font-medium text-gray-900">Description</h4>
            <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-md">
              {tache.description}
            </p>
          </div>
        )}
      </div>

      {/* Onglets */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="overview">Vue d'ensemble</TabsTrigger>
          <TabsTrigger value="attachments">Pièces jointes</TabsTrigger>
          <TabsTrigger value="discussions">Discussions</TabsTrigger>
          <TabsTrigger value="history">Historique</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          {/* Informations de l'instruction */}
          <div className="bg-blue-50 rounded-lg p-4">
            <h4 className="text-sm font-medium text-blue-900 mb-2">Instruction</h4>
            <div className="text-sm text-blue-800">
              <p className="font-medium">{tache.projet?.titre || 'Instruction inconnue'}</p>
              {tache.projet?.typeProjet && (
                <p className="text-xs text-blue-600">{tache.projet.typeProjet.nom.replace('projet', 'instruction')}</p>
              )}
            </div>
          </div>

          {/* Responsable */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h4 className="text-sm font-medium text-gray-900 mb-2 flex items-center">
              <User className="h-4 w-4 mr-2" />
              Responsable
            </h4>
            {tache.responsable ? (
              <div className="flex items-center">
                <Avatar className="h-8 w-8 mr-3">
                  <AvatarFallback className="bg-blue-100 text-blue-700 text-sm">
                    {getInitials(`${tache.responsable.prenom} ${tache.responsable.nom}`)}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <p className="text-sm font-medium text-gray-900">
                    {tache.responsable.prenom} {tache.responsable.nom}
                  </p>
                  <p className="text-xs text-gray-500">{tache.responsable.email}</p>
                </div>
              </div>
            ) : (
              <p className="text-sm text-gray-500">Non assignée</p>
            )}
          </div>

          {/* Dates */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h4 className="text-sm font-medium text-gray-900 mb-2 flex items-center">
              <Calendar className="h-4 w-4 mr-2" />
              Dates
            </h4>
            <div className="space-y-2 text-sm">
              {tache.date_debut_previsionnelle && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Début prévisionnel:</span>
                  <span className="font-medium">
                    {format(new Date(tache.date_debut_previsionnelle), "dd/MM/yyyy", { locale: fr })}
                  </span>
                </div>
              )}
              {tache.date_fin_previsionnelle && (
                <div className={cn(
                  "flex justify-between",
                  isEnRetard ? "text-red-600" : ""
                )}>
                  <span className={isEnRetard ? "text-red-600" : "text-gray-600"}>
                    Fin prévisionnelle:
                  </span>
                  <span className="font-medium flex items-center">
                    {format(new Date(tache.date_fin_previsionnelle), "dd/MM/yyyy", { locale: fr })}
                    {isEnRetard && <AlertTriangle className="h-3 w-3 ml-1" />}
                  </span>
                </div>
              )}
              {tache.date_debut_reelle && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Début réel:</span>
                  <span className="font-medium">
                    {format(new Date(tache.date_debut_reelle), "dd/MM/yyyy", { locale: fr })}
                  </span>
                </div>
              )}
              {tache.date_fin_reelle && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Fin réelle:</span>
                  <span className="font-medium">
                    {format(new Date(tache.date_fin_reelle), "dd/MM/yyyy", { locale: fr })}
                  </span>
                </div>
              )}
            </div>
          </div>

          {/* Informations d'audit */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h4 className="text-sm font-medium text-gray-900 mb-2 flex items-center">
              <Clock className="h-4 w-4 mr-2" />
              Informations
            </h4>
            <div className="space-y-1 text-xs text-gray-600">
              <p>Créée le: {format(new Date(tache.date_creation), "dd/MM/yyyy à HH:mm", { locale: fr })}</p>
              <p>Par: {tache.creer_par}</p>
              {tache.modifier_par && (
                <>
                  <p>Modifiée le: {format(new Date(tache.date_modification), "dd/MM/yyyy à HH:mm", { locale: fr })}</p>
                  <p>Par: {tache.modifier_par}</p>
                </>
              )}
            </div>
          </div>
        </TabsContent>

        <TabsContent value="attachments" className="space-y-4">
          {tache.pieces_jointes && tache.pieces_jointes.length > 0 ? (
            <div className="space-y-3">
              {tache.pieces_jointes?.map((piece) => (
                <div key={piece.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                  <div className="flex items-center flex-1 min-w-0">
                    <div className="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-3 flex-shrink-0">
                      {piece.mime_type?.startsWith('image/') ? (
                        <FileText className="h-5 w-5 text-blue-600" />
                      ) : piece.mime_type?.includes('pdf') ? (
                        <FileText className="h-5 w-5 text-red-600" />
                      ) : piece.mime_type?.includes('word') || piece.mime_type?.includes('document') ? (
                        <FileText className="h-5 w-5 text-blue-600" />
                      ) : piece.mime_type?.includes('excel') || piece.mime_type?.includes('spreadsheet') ? (
                        <FileText className="h-5 w-5 text-green-600" />
                      ) : (
                        <FileText className="h-5 w-5 text-gray-600" />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900 truncate">{piece.nom_original}</p>
                      <div className="flex items-center space-x-2 text-xs text-gray-500 mt-1">
                        <span>{piece.user && `${piece.user.prenom} ${piece.user.nom}`}</span>
                        <span>•</span>
                        <span>{format(new Date(piece.date_creation), "dd/MM/yyyy", { locale: fr })}</span>
                        {piece.taille && (
                          <>
                            <span>•</span>
                            <span>{(piece.taille / 1024 / 1024).toFixed(2)} MB</span>
                          </>
                        )}
                      </div>
                      {piece.description && (
                        <p className="text-xs text-gray-600 mt-1 truncate">{piece.description}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center space-x-2 ml-3">
                    <Badge variant={piece.est_justificatif ? "default" : "secondary"} className="text-xs">
                      {piece.est_justificatif ? "Justificatif" : piece.type_document || "Pièce jointe"}
                    </Badge>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => window.open(piece.fichier_path, '_blank')}
                      className="h-8 w-8 p-0"
                      title="Télécharger le fichier"
                    >
                      <Download className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-gray-500">
              <FileText className="h-12 w-12 mx-auto mb-2 text-gray-300" />
              <p>Aucune pièce jointe</p>
              <p className="text-xs text-gray-400 mt-1">Utilisez le menu d'actions pour ajouter des fichiers</p>
            </div>
          )}
        </TabsContent>

        <TabsContent value="discussions" className="max-h-[60vh] overflow-y-auto">
          <TaskDiscussionsList 
            taskId={tache.id} 
            onRefresh={() => {
              // Optionnel : recharger les détails de la tâche si nécessaire
            }}
          />
        </TabsContent>

        <TabsContent value="history" className="space-y-4">
          {loadingHistorique ? (
            <div className="flex items-center justify-center py-8">
              <Loader2 className="h-6 w-6 animate-spin text-blue-600 mr-2" />
              <span className="text-gray-600">Chargement de l'historique...</span>
            </div>
          ) : historiqueStatuts.length > 0 ? (
            <div className="space-y-3">
              {historiqueStatuts.map((historique) => (
                <div key={historique.id} className="p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-start space-x-3">
                    <Avatar className="h-8 w-8">
                      <AvatarFallback className="bg-green-100 text-green-700 text-sm">
                        {historique.user && getInitials(`${historique.user.prenom} ${historique.user.nom}`)}
                      </AvatarFallback>
                    </Avatar>
                    <div className="flex-1">
                      <div className="flex items-center space-x-2 mb-1">
                        <span className="text-sm font-medium text-gray-900">
                          {historique.user ? `${historique.user.prenom} ${historique.user.nom}` : 'Utilisateur'}
                        </span>
                        <span className="text-xs text-gray-500">
                          {format(new Date(historique.date_changement), "dd/MM/yyyy à HH:mm", { locale: fr })}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2 mb-1">
                        {historique.ancien_statut ? (
                          <>
                            <Badge variant="outline" className="text-xs">
                              {TACHE_STATUTS_KANBAN[historique.ancien_statut]}
                            </Badge>
                            <span className="text-gray-400">→</span>
                          </>
                        ) : (
                          <span className="text-xs text-green-600 font-medium">Création</span>
                        )}
                        <Badge className={cn("text-xs", TACHE_STATUT_COLORS[historique.nouveau_statut])}>
                          {TACHE_STATUTS_KANBAN[historique.nouveau_statut]}
                        </Badge>
                      </div>
                      {historique.commentaire && (
                        <p className="text-sm text-gray-700 mt-1">{historique.commentaire}</p>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-gray-500">
              <Clock className="h-12 w-12 mx-auto mb-2 text-gray-300" />
              <p>Aucun historique</p>
              <p className="text-xs text-gray-400 mt-1">L'historique des changements de statut apparaîtra ici</p>
            </div>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}

 