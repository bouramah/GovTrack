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
import { Loader2, Trash2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";

interface DeleteTaskDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task: Tache | null;
  onSuccess?: () => void;
}

export default function DeleteTaskDialog({
  open,
  onOpenChange,
  task,
  onSuccess
}: DeleteTaskDialogProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    if (!task) return;

    try {
      setLoading(true);
      
      const response = await apiClient.deleteTache(task.id);
      
      if (response.success) {
        toast({
          title: "Succès",
          description: "Tâche supprimée avec succès",
        });
        
        onSuccess?.();
        onOpenChange(false);
      }
    } catch (error: any) {
      console.error('Erreur lors de la suppression de la tâche:', error);
      toast({
        title: "Erreur",
        description: error.response?.data?.message || "Erreur lors de la suppression de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  if (!task) return null;

  return (
    <AlertDialog open={open} onOpenChange={onOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle className="flex items-center gap-2">
            <Trash2 className="h-5 w-5 text-red-500" />
            Supprimer la tâche
          </AlertDialogTitle>
          <AlertDialogDescription>
            Êtes-vous sûr de vouloir supprimer la tâche "{task.titre}" ?
            <br />
            <br />
            <strong>Attention :</strong> Cette action est irréversible et supprimera définitivement la tâche ainsi que toutes ses données associées (historique, discussions, pièces jointes).
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={loading}>
            Annuler
          </AlertDialogCancel>
          <AlertDialogAction
            onClick={handleDelete}
            disabled={loading}
            className="bg-red-600 hover:bg-red-700 text-white"
          >
            {loading ? (
              <Loader2 className="h-4 w-4 mr-2 animate-spin" />
            ) : (
              <Trash2 className="h-4 w-4 mr-2" />
            )}
            Supprimer définitivement
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
} 