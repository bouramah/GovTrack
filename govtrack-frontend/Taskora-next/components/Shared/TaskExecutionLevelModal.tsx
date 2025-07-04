"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Slider } from "@/components/ui/slider";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, Save, X, TrendingUp } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";

interface TaskExecutionLevelModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task: Tache | null;
  onSuccess?: (task: Tache) => void;
}

export default function TaskExecutionLevelModal({
  open,
  onOpenChange,
  task,
  onSuccess
}: TaskExecutionLevelModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [niveauExecution, setNiveauExecution] = useState(0);
  const [commentaire, setCommentaire] = useState("");

  // Initialiser les valeurs quand le modal s'ouvre
  useEffect(() => {
    if (task && open) {
      setNiveauExecution(task.niveau_execution);
      setCommentaire("");
    }
  }, [task, open]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!task) return;

    // Validation
    if (niveauExecution < 0 || niveauExecution > 100) {
      toast({
        title: "Erreur",
        description: "Le niveau d'exécution doit être entre 0 et 100%",
        variant: "destructive",
      });
      return;
    }

    // Si le niveau n'a pas changé et qu'il n'y a pas de commentaire
    if (niveauExecution === task.niveau_execution && !commentaire.trim()) {
      toast({
        title: "Erreur",
        description: "Le niveau d'exécution n'a pas changé. Veuillez ajouter un commentaire pour confirmer.",
        variant: "destructive",
      });
      return;
    }

    try {
      setLoading(true);
      
      const response = await apiClient.changeTacheStatut(task.id, {
        nouveau_statut: task.statut, // Garder le même statut
        niveau_execution: niveauExecution,
        commentaire: commentaire.trim() || undefined,
      });

      if (response.success && response.data) {
        toast({
          title: "Succès",
          description: `Niveau d'exécution mis à jour : ${task.niveau_execution}% → ${niveauExecution}%`,
        });
        
        onSuccess?.(response.data);
        onOpenChange(false);
      }
    } catch (error: any) {
      console.error('Erreur lors de la mise à jour du niveau d\'exécution:', error);
      
      // Gestion des erreurs de validation du backend
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        const errorMessages = Object.values(errors).flat().join(', ');
        toast({
          title: "Erreur de validation",
          description: errorMessages,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Erreur",
          description: error.response?.data?.message || "Erreur lors de la mise à jour du niveau d'exécution",
          variant: "destructive",
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleSliderChange = (value: number[]) => {
    setNiveauExecution(value[0]);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value >= 0 && value <= 100) {
      setNiveauExecution(value);
    }
  };

  if (!task) return null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <TrendingUp className="h-5 w-5" />
            Modifier le niveau d'exécution
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Informations de la tâche */}
          <div className="bg-gray-50 p-3 rounded-lg">
            <h4 className="font-medium text-gray-900 mb-1">{task.titre}</h4>
            <p className="text-sm text-gray-600">
              Projet : {task.projet?.titre || 'Projet inconnu'}
            </p>
            <p className="text-sm text-gray-600">
              Niveau actuel : <span className="font-medium">{task.niveau_execution}%</span>
            </p>
          </div>

          {/* Slider pour le niveau d'exécution */}
          <div className="space-y-2">
            <Label htmlFor="niveau-execution">
              Nouveau niveau d'exécution : {niveauExecution}%
            </Label>
            <div className="space-y-4">
              <Slider
                id="niveau-execution"
                min={0}
                max={100}
                step={1}
                value={[niveauExecution]}
                onValueChange={handleSliderChange}
                className="w-full"
              />
              <div className="flex items-center gap-2">
                <Input
                  type="number"
                  min={0}
                  max={100}
                  value={niveauExecution}
                  onChange={handleInputChange}
                  className="w-20"
                />
                <span className="text-sm text-gray-500">%</span>
              </div>
            </div>
          </div>

          {/* Indicateurs visuels */}
          <div className="flex justify-between text-xs text-gray-500">
            <span>0%</span>
            <span>25%</span>
            <span>50%</span>
            <span>75%</span>
            <span>100%</span>
          </div>

          {/* Barre de progression visuelle */}
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div 
              className="bg-blue-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${niveauExecution}%` }}
            />
          </div>

          {/* Commentaire */}
          <div className="space-y-2">
            <Label htmlFor="commentaire">Commentaire (optionnel)</Label>
            <Textarea
              id="commentaire"
              value={commentaire}
              onChange={(e) => setCommentaire(e.target.value)}
              placeholder="Expliquez pourquoi vous modifiez le niveau d'exécution..."
              rows={3}
            />
            <p className="text-xs text-gray-500">
              {niveauExecution === task.niveau_execution 
                ? "Un commentaire est recommandé pour confirmer le niveau actuel."
                : "Ajoutez un commentaire pour expliquer le changement."
              }
            </p>
          </div>

          {/* Résumé du changement */}
          {niveauExecution !== task.niveau_execution && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <p className="text-sm text-blue-800">
                <strong>Changement :</strong> {task.niveau_execution}% → {niveauExecution}%
                {niveauExecution > task.niveau_execution && (
                  <span className="text-green-600 ml-2">(+{niveauExecution - task.niveau_execution}%)</span>
                )}
                {niveauExecution < task.niveau_execution && (
                  <span className="text-red-600 ml-2">({niveauExecution - task.niveau_execution}%)</span>
                )}
              </p>
            </div>
          )}

          <DialogFooter className="gap-2">
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
              disabled={loading}
            >
              <X className="h-4 w-4 mr-2" />
              Annuler
            </Button>
            <Button type="submit" disabled={loading}>
              {loading ? (
                <Loader2 className="h-4 w-4 mr-2 animate-spin" />
              ) : (
                <Save className="h-4 w-4 mr-2" />
              )}
              Mettre à jour
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
} 