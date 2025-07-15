'use client';

import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Slider } from '@/components/ui/slider';
import { AlertCircle, Loader2, Info, Lock, Unlock } from 'lucide-react';
import { apiClient, Project, ProjectExecutionLevelRequest } from '@/lib/api';
import { useToast } from '@/components/ui/use-toast';

interface ProjectExecutionLevelModalProps {
  isOpen: boolean;
  onClose: () => void;
  project: Project | null;
  onSuccess: () => void;
}

export default function ProjectExecutionLevelModal({ 
  isOpen, 
  onClose, 
  project, 
  onSuccess 
}: ProjectExecutionLevelModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [niveauExecution, setNiveauExecution] = useState<number>(0);
  const [commentaire, setCommentaire] = useState('');
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});
  const [executionInfo, setExecutionInfo] = useState<{
    projet_id: number;
    niveau_actuel: number;
    mode: 'automatique' | 'manuel';
    nombre_taches: number;
    niveau_moyen_taches: number | null;
    peut_modifier: boolean;
  } | null>(null);

  // Charger les informations du niveau d'exécution quand le projet change
  useEffect(() => {
    if (project && isOpen) {
      loadExecutionInfo();
    }
  }, [project, isOpen]);

  const loadExecutionInfo = async () => {
    if (!project) return;
    
    try {
      setLoading(true);
      const info = await apiClient.getProjectExecutionLevelInfo(project.id);
      setExecutionInfo(info);
      setNiveauExecution(info.niveau_actuel);
      setCommentaire('');
      setServerErrors({});
    } catch (error: any) {
      console.error('Erreur chargement info niveau d\'exécution:', error);
      toast({
        variant: "destructive",
        title: "Erreur",
        description: 'Erreur lors du chargement des informations du niveau d\'exécution',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!project) return;

    // Effacer les erreurs précédentes
    setServerErrors({});
    
    // Validation côté client
    if (niveauExecution < 0 || niveauExecution > 99) {
      toast({
        variant: "destructive",
        title: "Erreur de validation",
        description: "Le niveau d'exécution doit être entre 0 et 99%",
      });
      return;
    }

    try {
      setLoading(true);
      
      const data: ProjectExecutionLevelRequest = {
        niveau_execution: niveauExecution,
        commentaire: commentaire.trim() || undefined
      };

      const updatedProject = await apiClient.updateProjectExecutionLevel(project.id, data);
      
      // Message de succès adaptatif selon la progression
      const progression = niveauExecution - project.niveau_execution;
      let successMessage = '';
      
      if (progression > 0) {
        successMessage = `Niveau d'exécution augmenté avec succès (${project.niveau_execution}% → ${niveauExecution}%)`;
      } else if (progression < 0) {
        successMessage = `Niveau d'exécution diminué avec succès (${project.niveau_execution}% → ${niveauExecution}%)`;
      } else {
        successMessage = niveauExecution === 100 
          ? 'Niveau d\'exécution confirmé à 100%'
          : 'Niveau d\'exécution confirmé avec succès';
      }
      
      toast({
        title: "Succès",
        description: successMessage,
      });
      onSuccess();
      onClose();
    } catch (error: any) {
      console.error('Erreur mise à jour niveau d\'exécution:', error);
      
      // Gestion des erreurs de validation du serveur
      if (error.message === 'Erreur de validation' && error.response?.data?.errors) {
        setServerErrors(error.response.data.errors);
        toast({
          variant: "destructive",
          title: "Erreur de validation",
          description: "Veuillez corriger les erreurs de validation ci-dessous",
        });
      } else if (error.response?.data?.message) {
        toast({
          variant: "destructive",
          title: "Erreur",
          description: error.response.data.message,
        });
      } else {
        toast({
          variant: "destructive",
          title: "Erreur",
          description: error.message || 'Erreur lors de la mise à jour du niveau d\'exécution',
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleNiveauChange = (value: number[]) => {
    setNiveauExecution(value[0]);
    // Effacer l'erreur du champ modifié
    if (serverErrors.niveau_execution) {
      setServerErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors.niveau_execution;
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

  if (!project || !executionInfo) return null;

  const isProjectEnCours = project.statut === 'en_cours';
  const progression = niveauExecution - executionInfo.niveau_actuel;

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertCircle className="h-5 w-5 text-blue-500" />
            Mettre à jour le niveau d'exécution
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Informations de l'instruction */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-semibold text-gray-900 mb-2">{project.titre}</h3>
            <div className="text-sm text-gray-600 space-y-1">
              <p><span className="font-medium">Statut actuel :</span> {project.statut_libelle}</p>
              <p><span className="font-medium">Niveau actuel :</span> {executionInfo.niveau_actuel}%</p>
            </div>
          </div>

          {/* Informations sur le mode d'exécution */}
          <div className={`rounded-lg p-4 ${
            executionInfo.mode === 'automatique' 
              ? 'bg-blue-50 border border-blue-200' 
              : 'bg-green-50 border border-green-200'
          }`}>
            <div className="flex items-start gap-3">
              {executionInfo.mode === 'automatique' ? (
                <Lock className="h-5 w-5 text-blue-500 mt-0.5" />
              ) : (
                <Unlock className="h-5 w-5 text-green-500 mt-0.5" />
              )}
              <div className="flex-1">
                <h3 className="text-sm font-medium text-gray-900 mb-1">
                  Mode {executionInfo.mode === 'automatique' ? 'Automatique' : 'Manuel'}
                </h3>
                {executionInfo.mode === 'automatique' ? (
                  <div className="text-sm text-blue-800">
                    <p>Le niveau d'exécution est calculé automatiquement basé sur les tâches de l'instruction.</p>
                    <p className="mt-1">
                      <span className="font-medium">Nombre de tâches :</span> {executionInfo.nombre_taches}
                      {executionInfo.niveau_moyen_taches !== null && (
                        <span className="ml-2">
                          <span className="font-medium">Moyenne :</span> {executionInfo.niveau_moyen_taches}%
                        </span>
                      )}
                    </p>
                    <p className="text-xs text-blue-700 mt-2">
                      Pour modifier manuellement le niveau, supprimez toutes les tâches de l'instruction.
                    </p>
                  </div>
                ) : (
                  <div className="text-sm text-green-800">
                    <p>Le niveau d'exécution peut être modifié manuellement.</p>
                    <p className="text-xs text-green-700 mt-1">
                      Aucune tâche associée à cette instruction.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Vérification des conditions de modification */}
          {(!isProjectEnCours || !executionInfo.peut_modifier) && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <div className="flex">
                <AlertCircle className="h-5 w-5 text-red-400 mt-0.5" />
                <div className="ml-3">
                  <h3 className="text-sm font-medium text-red-800">
                    Modification impossible
                  </h3>
                  <div className="text-sm text-red-700 mt-1 space-y-1">
                    {!isProjectEnCours && (
                      <p>Le niveau d'exécution ne peut être modifié que lorsque l'instruction est en cours.</p>
                    )}
                    {!executionInfo.peut_modifier && executionInfo.mode === 'automatique' && (
                      <p>Le niveau d'exécution est calculé automatiquement basé sur les tâches de l'instruction.</p>
                    )}
                  </div>
                </div>
              </div>
            </div>
          )}

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

          {/* Niveau d'exécution et progression - seulement si modifiable */}
          {executionInfo.peut_modifier && isProjectEnCours && (
            <div className="space-y-4">
              {/* Slider */}
              <div className="space-y-2">
                <Label htmlFor="niveau-execution">
                  Nouveau niveau d'exécution ({niveauExecution}%)
                </Label>
                <Slider
                  id="niveau-execution"
                  value={[niveauExecution]}
                  onValueChange={handleNiveauChange}
                  max={99}
                  min={0}
                  step={1}
                  className={serverErrors.niveau_execution ? "border-red-500" : ""}
                />
                <div className="flex justify-between text-xs text-gray-500">
                  <span>0%</span>
                  <span>99%</span>
                </div>
              </div>
              {/* Progression */}
              {progression > 0 && (
                <div className="bg-green-50 border border-green-200 rounded-lg p-3">
                  <p className="text-sm text-green-800">
                    <span className="font-medium">Progression :</span> +{progression}%
                  </p>
                </div>
              )}
              {progression < 0 && (
                <div className="bg-orange-50 border border-orange-200 rounded-lg p-3">
                  <p className="text-sm text-orange-800">
                    <span className="font-medium">Diminution :</span> {progression}%
                  </p>
                  <p className="text-xs text-orange-700 mt-1">
                    Le niveau d'exécution sera diminué. Assurez-vous que c'est justifié.
                  </p>
                </div>
              )}
              {progression === 0 && niveauExecution === executionInfo.niveau_actuel && (
                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                  <p className="text-sm text-yellow-800">
                    <span className="font-medium">Note :</span> Le niveau reste identique. Un commentaire est recommandé.
                  </p>
                </div>
              )}
            </div>
          )}

          {/* Commentaire - seulement si modifiable */}
          {executionInfo.peut_modifier && isProjectEnCours && (
            <div className="space-y-2">
              <Label htmlFor="commentaire">Commentaire (optionnel)</Label>
              <Textarea
                id="commentaire"
                value={commentaire}
                onChange={handleCommentaireChange}
                placeholder="Expliquez la progression ou ajoutez des détails..."
                rows={3}
                className={serverErrors.commentaire ? "border-red-500 focus:border-red-500" : ""}
              />
              {serverErrors.commentaire && (
                <p className="text-sm text-red-600">{serverErrors.commentaire[0]}</p>
              )}
              <p className="text-xs text-gray-500">
                Recommandé pour justifier les changements ou confirmer le niveau actuel.
              </p>
            </div>
          )}

          {/* Actions */}
          <div className="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Fermer
            </Button>
            {executionInfo.peut_modifier && isProjectEnCours && (
              <Button 
                type="submit" 
                disabled={loading}
              >
                {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                Mettre à jour
              </Button>
            )}
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
} 