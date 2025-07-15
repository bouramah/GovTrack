"use client";

import React, { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, AlertCircle, FileText } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache, TacheStatut } from "@/types/tache";

const TACHE_STATUSES = [
  { value: 'a_faire', label: 'À faire', description: 'Tâche en attente de démarrage' },
  { value: 'en_cours', label: 'En cours', description: 'Tâche en cours d\'exécution' },
  { value: 'bloque', label: 'Bloqué', description: 'Tâche temporairement bloquée' },
  { value: 'demande_de_cloture', label: 'Demande de clôture', description: 'Demande de clôture en attente de validation' },
  { value: 'termine', label: 'Terminé', description: 'Tâche terminée avec succès' }
];

interface TaskStatusChangeModalProps {
  isOpen: boolean;
  onClose: () => void;
  task: Tache | null;
  onSuccess: (updatedTask: Tache) => void;
}

export default function TaskStatusChangeModal({ 
  isOpen, 
  onClose, 
  task, 
  onSuccess 
}: TaskStatusChangeModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [nouveauStatut, setNouveauStatut] = useState<string>('');
  const [commentaire, setCommentaire] = useState('');
  const [niveauExecution, setNiveauExecution] = useState<number>(0);
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});

  // Initialiser le statut quand la tâche change
  React.useEffect(() => {
    if (task) {
      setNouveauStatut(task.statut);
      setNiveauExecution(task.niveau_execution);
      setCommentaire('');
      setServerErrors({});
    }
  }, [task]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!task) return;

    // Effacer les erreurs précédentes
    setServerErrors({});
    
    // Validation côté client
    if (!nouveauStatut) {
      toast({
        title: "Erreur",
        description: "Veuillez sélectionner un statut",
        variant: "destructive",
      });
      return;
    }

    // Si même statut, vérifier qu'il y a un commentaire ou un changement de niveau
    if (nouveauStatut === task.statut && !commentaire.trim() && niveauExecution === task.niveau_execution) {
      toast({
        title: "Erreur",
        description: "Pour mettre à jour le même statut, veuillez fournir un commentaire ou modifier le niveau d'exécution",
        variant: "destructive",
      });
      return;
    }

    try {
      setLoading(true);
      
      const data = {
        nouveau_statut: nouveauStatut as TacheStatut,
        niveau_execution: niveauExecution,
        commentaire: commentaire.trim() || undefined,
      };

      const response = await apiClient.changeTacheStatut(task.id, data);
      
      if (response.success && response.data) {
        toast({
          title: "Succès",
          description: response.message || 'Statut de la tâche modifié avec succès',
        });
        
        // Passer la tâche mise à jour au callback
        onSuccess(response.data);
        onClose();
      } else {
        toast({
          title: "Erreur",
          description: response.message || 'Erreur lors du changement de statut',
          variant: "destructive",
        });
      }
    } catch (error: any) {
      console.error('Erreur changement de statut:', error);
      
      // Gestion des erreurs de validation du serveur
      if (error.response?.data?.errors) {
        setServerErrors(error.response.data.errors);
        toast({
          title: "Erreur de validation",
          description: "Veuillez corriger les erreurs de validation ci-dessous",
          variant: "destructive",
        });
      } else if (error.response?.data?.message) {
        // Si c'est une erreur de validation mais sans format errors, on l'affiche quand même
        if (error.response.status === 422) {
          setServerErrors({
            nouveau_statut: [error.response.data.message]
          });
          toast({
            title: "Erreur de validation",
            description: "Veuillez corriger les erreurs de validation ci-dessous",
            variant: "destructive",
          });
        } else {
          toast({
            title: "Erreur",
            description: error.response.data.message,
            variant: "destructive",
          });
        }
      } else {
        toast({
          title: "Erreur",
          description: error.message || 'Erreur lors du changement de statut',
          variant: "destructive",
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleStatutChange = (value: string) => {
    setNouveauStatut(value);
    // Effacer l'erreur du champ modifié
    if (serverErrors.nouveau_statut) {
      setServerErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors.nouveau_statut;
        return newErrors;
      });
    }
  };

  const handleCommentaireChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    setCommentaire(e.target.value);
    // Effacer l'erreur du champ modifié
    if (serverErrors.commentaire) {
      setServerErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors.commentaire;
        return newErrors;
      });
    }
  };

  const handleNiveauExecutionChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value >= 0 && value <= 100) {
      setNiveauExecution(value);
    }
  };

  if (!task) return null;

  const currentStatus = TACHE_STATUSES.find(s => s.value === task.statut);
  const selectedStatus = TACHE_STATUSES.find(s => s.value === nouveauStatut);
  const isSameStatus = nouveauStatut === task.statut;
  const requiresJustificatif = nouveauStatut === 'demande_de_cloture' || nouveauStatut === 'termine';
  const hasJustificatifs = task.pieces_jointes?.some(piece => piece.est_justificatif) || false;

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertCircle className="h-5 w-5 text-blue-500" />
            Changer le statut de la tâche
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Informations de la tâche */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-semibold text-gray-900 mb-2">{task.titre}</h3>
            <div className="text-sm text-gray-600 space-y-1">
              <p><span className="font-medium">Statut actuel :</span> {task.statut_libelle}</p>
              <p><span className="font-medium">Niveau d'exécution :</span> {task.niveau_execution}%</p>
              <p><span className="font-medium">Instruction :</span> {task.projet?.titre || 'Instruction inconnue'}</p>
            </div>
          </div>

          {/* Résumé des erreurs */}
          {Object.keys(serverErrors).length > 0 && (
            <div className="bg-red-50 border border-red-200 rounded-md p-4">
              <div className="flex">
                <AlertCircle className="h-5 w-5 text-red-400" />
                <div className="ml-3">
                  <h3 className="text-sm font-medium text-red-800">
                    Erreurs de validation
                  </h3>
                  <div className="mt-2 text-sm text-red-700">
                    <ul className="list-disc pl-5 space-y-1">
                      {Object.entries(serverErrors).map(([field, errors]) => (
                        <li key={field}>{errors[0]}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Sélection du statut */}
          <div className="space-y-2">
            <Label htmlFor="nouveau-statut">Nouveau statut</Label>
            <SearchableSelect
              options={TACHE_STATUSES.map((status) => ({
                value: status.value,
                label: status.label,
                description: status.description
              }))}
              value={nouveauStatut}
              onValueChange={handleStatutChange}
              placeholder="Sélectionner un statut"
              searchPlaceholder="Rechercher un statut..."
              className={serverErrors.nouveau_statut ? "border-red-500" : ""}
            />
            {serverErrors.nouveau_statut && (
              <div className="text-sm text-red-600">
                {serverErrors.nouveau_statut.map((error, index) => (
                  <p key={index} className="flex items-center gap-1">
                    <AlertCircle className="h-3 w-3" />
                    {error}
                  </p>
                ))}
              </div>
            )}
          </div>

          {/* Description du statut sélectionné */}
          {selectedStatus && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <p className="text-sm text-blue-800">
                <span className="font-medium">{selectedStatus.label} :</span> {selectedStatus.description}
              </p>
            </div>
          )}

          {/* Avertissement pour le même statut */}
          {isSameStatus && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
              <p className="text-sm text-yellow-800">
                <span className="font-medium">Note :</span> La tâche a déjà ce statut. Un commentaire ou un changement de niveau d'exécution est obligatoire pour mettre à jour.
              </p>
            </div>
          )}

          {/* Avertissement pour demande de clôture */}
          {requiresJustificatif && (
            <div className="bg-orange-50 border border-orange-200 rounded-lg p-3">
              <div className="flex">
                <FileText className="h-4 w-4 text-orange-400 mt-0.5" />
                <div className="ml-2">
                  <p className="text-sm text-orange-800">
                    <span className="font-medium">Important :</span> Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour {nouveauStatut === 'demande_de_cloture' ? 'demander la clôture' : 'terminer la tâche'}.
                  </p>
                  {!hasJustificatifs && (
                    <p className="text-sm text-red-600 mt-1">
                      ⚠️ Aucun justificatif trouvé. Veuillez d'abord ajouter une pièce jointe marquée comme justificatif.
                    </p>
                  )}
                </div>
              </div>
            </div>
          )}

          {/* Niveau d'exécution */}
          <div className="space-y-2">
            <Label htmlFor="niveau-execution">Niveau d'exécution (%)</Label>
            <input
              type="number"
              id="niveau-execution"
              min="0"
              max="100"
              value={niveauExecution}
              onChange={handleNiveauExecutionChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <div className="flex justify-between text-xs text-gray-500">
              <span>0%</span>
              <span>25%</span>
              <span>50%</span>
              <span>75%</span>
              <span>100%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div 
                className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                style={{ width: `${niveauExecution}%` }}
              />
            </div>
          </div>

          {/* Commentaire */}
          <div className="space-y-2">
            <Label htmlFor="commentaire">
              Commentaire {isSameStatus && <span className="text-red-500">*</span>}
            </Label>
            <Textarea
              id="commentaire"
              value={commentaire}
              onChange={handleCommentaireChange}
              placeholder={isSameStatus 
                ? "Commentaire obligatoire pour mettre à jour le même statut..." 
                : "Expliquez le changement de statut ou ajoutez des détails..."
              }
              rows={3}
              className={serverErrors.commentaire ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.commentaire && (
              <p className="text-sm text-red-600">{serverErrors.commentaire[0]}</p>
            )}
            <p className="text-xs text-gray-500">
              {isSameStatus 
                ? "Commentaire obligatoire pour confirmer la mise à jour du statut."
                : "Recommandé pour justifier le changement de statut."
              }
            </p>
          </div>

          {/* Actions */}
          <div className="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Annuler
            </Button>
            <Button 
              type="submit" 
              disabled={loading || !nouveauStatut || (isSameStatus && !commentaire.trim() && niveauExecution === task.niveau_execution) || (requiresJustificatif && !hasJustificatifs)}
            >
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {isSameStatus ? 'Mettre à jour' : 'Changer le statut'}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
} 