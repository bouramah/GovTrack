"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/components/ui/use-toast";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { User } from "@/lib/api";
import type { Tache } from "@/types/tache";

interface NewTaskModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task?: Tache | null; // Si fourni, c'est pour modifier une tâche existante
  projet_id?: number; // ID du projet pour créer une nouvelle tâche
  onSuccess: (task: Tache) => void;
}

export default function NewTaskModal({ open, onOpenChange, task, projet_id, onSuccess }: NewTaskModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [users, setUsers] = useState<User[]>([]);
  const [loadingUsers, setLoadingUsers] = useState(false);
  
  // Formulaire
  const [formData, setFormData] = useState({
    titre: "",
    description: "",
    responsable_id: "",
    date_debut_previsionnelle: "",
    date_fin_previsionnelle: "",
  });

  // Charger les utilisateurs pour le select responsable
  useEffect(() => {
    const loadUsers = async () => {
      try {
        setLoadingUsers(true);
        const response = await apiClient.getUsersDetailed();
        if (response.success && response.data) {
          setUsers(response.data);
        }
      } catch (error) {
        console.error('Erreur chargement utilisateurs:', error);
      } finally {
        setLoadingUsers(false);
      }
    };

    if (open) {
      loadUsers();
    }
  }, [open]);

  // Initialiser le formulaire avec les données de la tâche si modification
  useEffect(() => {
    if (task) {
      setFormData({
        titre: task.titre,
        description: task.description || "",
        responsable_id: task.responsable_id?.toString() || "",
        date_debut_previsionnelle: task.date_debut_previsionnelle || "",
        date_fin_previsionnelle: task.date_fin_previsionnelle || "",
      });
    } else {
      setFormData({
        titre: "",
        description: "",
        responsable_id: "",
        date_debut_previsionnelle: "",
        date_fin_previsionnelle: "",
      });
    }
  }, [task, open]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.titre.trim()) {
      toast({
        title: "Erreur",
        description: "Le titre est obligatoire",
        variant: "destructive",
      });
      return;
    }

    // Validation des dates
    if (formData.date_debut_previsionnelle && formData.date_fin_previsionnelle) {
      const dateDebut = new Date(formData.date_debut_previsionnelle);
      const dateFin = new Date(formData.date_fin_previsionnelle);
      
      if (dateFin < dateDebut) {
        toast({
          title: "Erreur",
          description: "La date de fin ne peut pas être antérieure à la date de début",
          variant: "destructive",
        });
        return;
      }
    }

    try {
      setLoading(true);
      
      const taskData = {
        titre: formData.titre.trim(),
        description: formData.description.trim() || undefined,
        responsable_id: formData.responsable_id ? parseInt(formData.responsable_id) : undefined,
        date_debut_previsionnelle: formData.date_debut_previsionnelle || undefined,
        date_fin_previsionnelle: formData.date_fin_previsionnelle || undefined,
      };

      let response;
      
      if (task) {
        // Modification d'une tâche existante
        response = await apiClient.updateTache(task.id, taskData);
      } else {
        // Création d'une nouvelle tâche
        if (!projet_id) {
          toast({
            title: "Erreur",
            description: "ID du projet requis pour créer une tâche",
            variant: "destructive",
          });
          return;
        }
        response = await apiClient.createTache({
          ...taskData,
          projet_id: projet_id,
        });
      }

      if (response.success && response.data) {
        onSuccess(response.data);
        onOpenChange(false);
        toast({
          title: "Succès",
          description: task ? "Tâche mise à jour avec succès" : "Tâche créée avec succès",
        });
      }
    } catch (error: any) {
      console.error('Erreur sauvegarde tâche:', error);
      toast({
        title: "Erreur",
        description: error.message || "Erreur lors de la sauvegarde de la tâche",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {task ? "Modifier la tâche" : "Nouvelle tâche"}
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Titre */}
          <div className="space-y-2">
            <Label htmlFor="titre">Titre *</Label>
            <Input
              id="titre"
              value={formData.titre}
              onChange={(e) => handleInputChange('titre', e.target.value)}
              placeholder="Titre de la tâche"
              required
            />
          </div>

          {/* Description */}
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea
              id="description"
              value={formData.description}
              onChange={(e) => handleInputChange('description', e.target.value)}
              placeholder="Description détaillée de la tâche"
              rows={4}
            />
          </div>

          {/* Responsable */}
          <div className="space-y-2">
            <Label htmlFor="responsable">Responsable</Label>
            <Select
              value={formData.responsable_id || 'none'}
              onValueChange={(value) => handleInputChange('responsable_id', value === 'none' ? '' : value)}
            >
              <SelectTrigger id="responsable">
                <SelectValue placeholder="Sélectionner un responsable" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="none">Aucun responsable</SelectItem>
                {loadingUsers ? (
                  <SelectItem value="loading" disabled>Chargement...</SelectItem>
                ) : (
                  users.map((user) => (
                    <SelectItem key={user.id} value={user.id.toString()}>
                      {user.prenom} {user.nom}
                    </SelectItem>
                  ))
                )}
              </SelectContent>
            </Select>
          </div>

          {/* Dates */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="date_debut">Date de début prévisionnelle</Label>
              <Input
                id="date_debut"
                type="date"
                value={formData.date_debut_previsionnelle}
                onChange={(e) => handleInputChange('date_debut_previsionnelle', e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="date_fin">Date de fin prévisionnelle</Label>
              <Input
                id="date_fin"
                type="date"
                value={formData.date_fin_previsionnelle}
                onChange={(e) => handleInputChange('date_fin_previsionnelle', e.target.value)}
              />
            </div>
          </div>

          {/* Actions */}
          <div className="flex justify-end space-x-2 pt-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
              disabled={loading}
            >
              Annuler
            </Button>
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
              {task ? "Modifier" : "Créer"}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
} 