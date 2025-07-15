"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, Clock, User, MessageSquare } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache, TacheHistoriqueStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN, TACHE_STATUT_COLORS } from "@/types/tache";

interface TaskHistoryModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task: Tache | null;
}

export default function TaskHistoryModal({
  open,
  onOpenChange,
  task
}: TaskHistoryModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState<TacheHistoriqueStatut[]>([]);

  useEffect(() => {
    if (open && task) {
      loadHistory();
    }
  }, [open, task]);

  const loadHistory = async () => {
    if (!task) return;

    try {
      setLoading(true);
      const response = await apiClient.getTacheHistoriqueStatuts(task.id);
      
      if (response.success && response.data) {
        setHistory(response.data);
      }
    } catch (error: any) {
      console.error('Erreur lors du chargement de l\'historique:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger l'historique de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getStatusColor = (statut: string) => {
    return TACHE_STATUT_COLORS[statut as keyof typeof TACHE_STATUT_COLORS] || 'bg-gray-100 text-gray-800';
  };

  if (!task) return null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[700px] max-h-[80vh]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Historique des statuts - {task.titre}
          </DialogTitle>
        </DialogHeader>

        <div className="space-y-4">
          {/* Statut actuel */}
          <div className="bg-gray-50 p-4 rounded-lg">
            <h3 className="font-semibold text-gray-900 mb-2">Statut actuel</h3>
            <div className="flex items-center gap-3">
              <Badge className={getStatusColor(task.statut)}>
                {TACHE_STATUTS_KANBAN[task.statut as keyof typeof TACHE_STATUTS_KANBAN]}
              </Badge>
              <span className="text-sm text-gray-600">
                Niveau d'exécution: {task.niveau_execution}%
              </span>
            </div>
          </div>

          {/* Historique */}
          <div>
            <h3 className="font-semibold text-gray-900 mb-3">Historique des changements</h3>
            
            {loading ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="h-6 w-6 animate-spin text-blue-600" />
                <span className="ml-2 text-gray-600">Chargement de l'historique...</span>
              </div>
            ) : history.length === 0 ? (
              <div className="text-center py-8 text-gray-500">
                <Clock className="h-12 w-12 mx-auto mb-3 text-gray-300" />
                <p>Aucun historique disponible</p>
              </div>
            ) : (
              <ScrollArea className="h-[400px]">
                <div className="space-y-4">
                  {history.map((item, index) => (
                    <div key={item.id} className="border-l-2 border-blue-200 pl-4 pb-4">
                      <div className="flex items-start justify-between">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <Badge className={getStatusColor(item.nouveau_statut)}>
                              {TACHE_STATUTS_KANBAN[item.nouveau_statut as keyof typeof TACHE_STATUTS_KANBAN]}
                            </Badge>
                            {item.ancien_statut !== item.nouveau_statut && (
                              <>
                                <span className="text-gray-400">→</span>
                                <Badge variant="outline" className="text-gray-600">
                                  {TACHE_STATUTS_KANBAN[item.ancien_statut as keyof typeof TACHE_STATUTS_KANBAN]}
                                </Badge>
                              </>
                            )}
                          </div>
                          
                          {item.commentaire && (
                            <div className="flex items-start gap-2 mb-2">
                              <MessageSquare className="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
                              <p className="text-sm text-gray-700 bg-gray-50 p-2 rounded">
                                {item.commentaire}
                              </p>
                            </div>
                          )}
                          
                          <div className="flex items-center gap-4 text-xs text-gray-500">
                            <div className="flex items-center gap-1">
                              <User className="h-3 w-3" />
                              <span>{item.user?.prenom} {item.user?.nom}</span>
                            </div>
                            <div className="flex items-center gap-1">
                              <Clock className="h-3 w-3" />
                              <span>{formatDate(item.date_changement)}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </ScrollArea>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
} 