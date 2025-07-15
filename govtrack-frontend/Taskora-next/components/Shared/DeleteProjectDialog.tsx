'use client';

import React, { useState } from 'react';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Loader2, AlertTriangle } from 'lucide-react';
import { apiClient, Project } from '@/lib/api';
import { toast } from 'sonner';

interface DeleteProjectDialogProps {
  isOpen: boolean;
  onClose: () => void;
  project: Project | null;
  onSuccess: () => void;
}

export default function DeleteProjectDialog({ 
  isOpen, 
  onClose, 
  project, 
  onSuccess 
}: DeleteProjectDialogProps) {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    if (!project) return;

    try {
      setLoading(true);
      await apiClient.deleteProject(project.id);
      toast.success('Instruction supprimée avec succès');
      onSuccess();
      onClose();
    } catch (error: any) {
      console.error('Erreur suppression instruction:', error);
      
      // Gestion des erreurs spécifiques du backend
      if (error.response?.status === 422) {
        // Erreur de validation
        toast.error(error.response.data.message || 'Erreur de validation lors de la suppression');
      } else if (error.response?.status === 403) {
        // Erreur de permission
        toast.error('Vous n\'avez pas les permissions pour supprimer cette instruction');
      } else if (error.response?.status === 404) {
        // Projet non trouvé
        toast.error('L\'instruction n\'existe plus ou a déjà été supprimée');
      } else if (error.message.includes('en cours')) {
        toast.error('Impossible de supprimer une instruction en cours');
      } else if (error.response?.data?.message) {
        toast.error(error.response.data.message);
      } else {
        toast.error(error.message || 'Erreur lors de la suppression de l\'instruction');
      }
    } finally {
      setLoading(false);
    }
  };

  if (!project) return null;

  return (
    <AlertDialog open={isOpen} onOpenChange={onClose}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-red-500" />
            Confirmer la suppression
          </AlertDialogTitle>
          <AlertDialogDescription>
            Êtes-vous sûr de vouloir supprimer l'instruction <strong>"{project.titre}"</strong> ?
            <br />
            <br />
            <span className="text-red-600 font-medium">
              ⚠️ Cette action est irréversible et supprimera définitivement :
            </span>
            <ul className="list-disc list-inside mt-2 text-sm space-y-1">
              <li>L'instruction et toutes ses données</li>
              <li>Toutes les tâches associées</li>
              <li>L'historique des statuts</li>
              <li>Les discussions et commentaires</li>
              <li>Les pièces jointes</li>
            </ul>
            <br />
            {project.statut === 'en_cours' && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-3 mt-2">
                <span className="text-red-700 font-medium">
                  ❌ Impossible de supprimer une instruction en cours
                </span>
              </div>
            )}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={loading}>
            Annuler
          </AlertDialogCancel>
          <AlertDialogAction
            onClick={handleDelete}
            disabled={loading || project.statut === 'en_cours'}
            className="bg-red-600 hover:bg-red-700"
          >
            {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            Supprimer définitivement
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
} 