'use client';

import React, { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';
import { AlertCircle, Loader2, FileText } from 'lucide-react';
import { apiClient, Project } from '@/lib/api';
import { toast } from 'sonner';

interface ProjectStatusChangeModalProps {
  isOpen: boolean;
  onClose: () => void;
  project: Project | null;
  onSuccess: () => void;
}

const PROJECT_STATUSES = [
  { value: 'a_faire', label: 'À faire', description: 'Instruction en attente de démarrage' },
  { value: 'en_cours', label: 'En cours', description: 'Instruction en cours d\'exécution' },
  { value: 'bloque', label: 'Bloqué', description: 'Instruction temporairement bloquée' },
  { value: 'demande_de_cloture', label: 'Demande de clôture', description: 'Demande de clôture en attente de validation' },
  { value: 'termine', label: 'Terminé', description: 'Instruction terminée avec succès' }
];

export default function ProjectStatusChangeModal({ 
  isOpen, 
  onClose, 
  project, 
  onSuccess 
}: ProjectStatusChangeModalProps) {
  const [loading, setLoading] = useState(false);
  const [nouveauStatut, setNouveauStatut] = useState<string>('');
  const [commentaire, setCommentaire] = useState('');
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});

  // Initialiser le statut quand l'instruction change
  React.useEffect(() => {
    if (project) {
      setNouveauStatut(project.statut);
      setCommentaire('');
      setServerErrors({});
    }
  }, [project]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!project) return;

    // Effacer les erreurs précédentes
    setServerErrors({});
    
    // Validation côté client
    if (!nouveauStatut) {
      toast.error('Veuillez sélectionner un statut');
      return;
    }

    // Si même statut, vérifier qu'il y a un commentaire
    if (nouveauStatut === project.statut && !commentaire.trim()) {
      toast.error('Pour mettre à jour le même statut, veuillez fournir un commentaire');
      return;
    }

    try {
      setLoading(true);
      
      const data = {
        nouveau_statut: nouveauStatut as 'a_faire' | 'en_cours' | 'bloque' | 'demande_de_cloture' | 'termine',
        commentaire: commentaire.trim() || undefined,
        justificatif_path: undefined // Pour l'instant, pas de gestion des fichiers
      };

      const response = await apiClient.changeProjectStatut(project.id, data);
      
      if (response.success) {
        toast.success(response.message || 'Statut de l\'instruction modifié avec succès');
        onSuccess();
        onClose();
      } else {
        toast.error(response.message || 'Erreur lors du changement de statut');
      }
    } catch (error: any) {
      console.error('Erreur changement de statut:', error);
      
      // Gestion des erreurs de validation du serveur
      if (error.response?.data?.errors) {
        setServerErrors(error.response.data.errors);
        toast.error('Veuillez corriger les erreurs de validation ci-dessous');
      } else if (error.response?.data?.message) {
        // Si c'est une erreur de validation mais sans format errors, on l'affiche quand même
        if (error.response.status === 422) {
          setServerErrors({
            nouveau_statut: [error.response.data.message]
          });
          toast.error('Veuillez corriger les erreurs de validation ci-dessous');
        } else {
          toast.error(error.response.data.message);
        }
      } else {
        toast.error(error.message || 'Erreur lors du changement de statut');
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

  if (!project) return null;

  const currentStatus = PROJECT_STATUSES.find(s => s.value === project.statut);
  const selectedStatus = PROJECT_STATUSES.find(s => s.value === nouveauStatut);
  const isSameStatus = nouveauStatut === project.statut;
  const requiresJustificatif = nouveauStatut === 'demande_de_cloture' || nouveauStatut === 'termine';
  const hasJustificatifs = project.pieces_jointes?.some(piece => piece.est_justificatif) || false;

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertCircle className="h-5 w-5 text-blue-500" />
            Changer le statut de l'instruction
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Informations de l'instruction */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="font-semibold text-gray-900 mb-2">{project.titre}</h3>
            <div className="text-sm text-gray-600 space-y-1">
              <p><span className="font-medium">Statut actuel :</span> {project.statut_libelle}</p>
              <p><span className="font-medium">Niveau d'exécution :</span> {project.niveau_execution}%</p>
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
              options={PROJECT_STATUSES.map((status) => ({
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
                <span className="font-medium">Note :</span> L'instruction a déjà ce statut. Un commentaire est obligatoire pour mettre à jour.
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
                    <span className="font-medium">Important :</span> Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour {nouveauStatut === 'demande_de_cloture' ? 'demander la clôture' : 'terminer l\'instruction'}.
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
              disabled={loading || !nouveauStatut || (isSameStatus && !commentaire.trim()) || (requiresJustificatif && !hasJustificatifs)}
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