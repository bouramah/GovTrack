"use client";

import { useState } from "react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, AlertTriangle } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";

interface DeleteTaskDialogProps {
  isOpen: boolean;
  onClose: () => void;
  task: Tache | null;
  onSuccess: (taskId: number) => void;
}

export default function DeleteTaskDialog({
  isOpen,
  onClose,
  task,
  onSuccess,
}: DeleteTaskDialogProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    if (!task) return;

    try {
      setLoading(true);
      
      const response = await apiClient.deleteTache(task.id);
      
      if (response.success) {
        onSuccess(task.id);
        onClose();
        toast({
          title: "Succès",
          description: "Tâche supprimée avec succès",
        });
      }
    } catch (error: any) {
      console.error('Erreur suppression tâche:', error);
      toast({
        title: "Erreur",
        description: error.message || "Erreur lors de la suppression de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  if (!task) return null;

  return (
    <AlertDialog open={isOpen} onOpenChange={onClose}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-red-500" />
            Supprimer la tâche
          </AlertDialogTitle>
          <AlertDialogDescription>
            Êtes-vous sûr de vouloir supprimer la tâche "{task.titre}" ?
            <br />
            <br />
            <strong>Attention :</strong> Cette action est irréversible. Toutes les données associées à cette tâche (pièces jointes, discussions, historique) seront également supprimées.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={loading}>
            Annuler
          </AlertDialogCancel>
          <AlertDialogAction
            onClick={handleDelete}
            disabled={loading}
            className="bg-red-600 hover:bg-red-700"
          >
            {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
            Supprimer définitivement
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
} 