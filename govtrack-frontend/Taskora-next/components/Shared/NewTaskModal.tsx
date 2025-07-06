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
import type { Tache, TacheStatut } from "@/types/tache";
import { TACHE_STATUTS_KANBAN } from "@/types/tache";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";

interface NewTaskModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task?: Tache | null;
  projet_id?: number;
  onSuccess: (task: Tache) => void;
}

export default function NewTaskModal({ open, onOpenChange, task, projet_id, onSuccess }: NewTaskModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [loadingUsers, setLoadingUsers] = useState(false);
  const [loadingProjects, setLoadingProjects] = useState(false);
  const [users, setUsers] = useState<any[]>([]);
  const [projects, setProjects] = useState<any[]>([]);
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});
  const [formData, setFormData] = useState({
    titre: '',
    description: '',
    projet_id: '',
    responsable_id: '',
    date_debut_previsionnelle: '',
    date_fin_previsionnelle: '',
    statut: 'a_faire' as TacheStatut,
    niveau_execution: 0
  });

  // Charger les utilisateurs
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

  // Charger les projets
  useEffect(() => {
    const loadProjects = async () => {
      try {
        setLoadingProjects(true);
        const response = await apiClient.getProjects({ per_page: 1000 });
        if (response.success && response.data) {
          setProjects(response.data);
        }
      } catch (error) {
        console.error('Erreur chargement projets:', error);
      } finally {
        setLoadingProjects(false);
      }
    };

    if (open) {
      loadProjects();
    }
  }, [open]);

  // Initialiser le formulaire avec les données de la tâche existante
  useEffect(() => {
    if (task) {
      setFormData({
        titre: task.titre,
        description: task.description || '',
        projet_id: task.projet_id?.toString() || '',
        responsable_id: task.responsable_id?.toString() || '',
        date_debut_previsionnelle: task.date_debut_previsionnelle || '',
        date_fin_previsionnelle: task.date_fin_previsionnelle || '',
        statut: task.statut,
        niveau_execution: task.niveau_execution
      });
    } else {
      setFormData({
        titre: '',
        description: '',
        projet_id: projet_id?.toString() || '',
        responsable_id: '',
        date_debut_previsionnelle: '',
        date_fin_previsionnelle: '',
        statut: 'a_faire' as TacheStatut,
        niveau_execution: 0
      });
    }
    // Réinitialiser les erreurs
    setServerErrors({});
  }, [task, open, projet_id]);

  const handleInputChange = (field: string, value: string | number) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    // Effacer l'erreur du champ modifié
    if (serverErrors[field]) {
      setServerErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validation côté client
    if (!formData.titre.trim()) {
      toast({
        title: "Erreur",
        description: "Le titre est obligatoire",
        variant: "destructive",
      });
      return;
    }

    if (!formData.projet_id) {
      toast({
        title: "Erreur",
        description: "Le projet est obligatoire",
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
      setServerErrors({});
      
      let response;
      if (task) {
        // Mise à jour - utiliser UpdateTacheRequest
        const updateData = {
          titre: formData.titre.trim(),
          description: formData.description.trim() || undefined,
          responsable_id: formData.responsable_id ? parseInt(formData.responsable_id) : undefined,
          date_debut_previsionnelle: formData.date_debut_previsionnelle || undefined,
          date_fin_previsionnelle: formData.date_fin_previsionnelle || undefined,
          niveau_execution: parseInt(formData.niveau_execution.toString())
        };
        response = await apiClient.updateTache(task.id, updateData);
      } else {
        // Création - utiliser CreateTacheRequest
        const createData = {
          titre: formData.titre.trim(),
          description: formData.description.trim() || undefined,
          projet_id: parseInt(formData.projet_id),
          responsable_id: formData.responsable_id ? parseInt(formData.responsable_id) : undefined,
          date_debut_previsionnelle: formData.date_debut_previsionnelle || undefined,
          date_fin_previsionnelle: formData.date_fin_previsionnelle || undefined
        };
        response = await apiClient.createTache(createData);
      }

      if (response.success && response.data) {
        onSuccess(response.data);
        onOpenChange(false);
        toast({
          title: "Succès",
          description: task ? "Tâche mise à jour avec succès" : "Tâche créée avec succès",
        });
      } else {
        // Gestion des erreurs backend
        if (response.errors) {
          setServerErrors(response.errors);
          toast({
            title: "Erreur de validation",
            description: "Veuillez corriger les erreurs dans le formulaire",
            variant: "destructive",
          });
        } else {
          throw new Error(response.message || 'Erreur lors de l\'opération');
        }
      }
    } catch (error: any) {
      console.error('Erreur:', error);
      toast({
        title: "Erreur",
        description: error.message || "Une erreur est survenue lors de la sauvegarde",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  // Préparer les options pour les selects searchables
  const userOptions: SearchableSelectOption[] = [
    { value: 'none', label: 'Aucun responsable' },
    ...users.map(user => ({
      value: user.id.toString(),
      label: `${user.prenom} ${user.nom}`,
      description: user.email,
      badge: user.matricule
    }))
  ];

  const projectOptions: SearchableSelectOption[] = [
    ...projects.map(project => ({
      value: project.id.toString(),
      label: project.titre,
      description: project.type_projet?.nom || 'Type non défini',
      badge: project.porteur ? `${project.porteur.prenom} ${project.porteur.nom}` : 'Porteur non défini'
    }))
  ];

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {task ? 'Modifier la tâche' : 'Nouvelle tâche'}
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
              className={serverErrors.titre ? "border-red-500" : ""}
            />
            {serverErrors.titre && (
              <p className="text-sm text-red-600">{serverErrors.titre[0]}</p>
            )}
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
              className={serverErrors.description ? "border-red-500" : ""}
            />
            {serverErrors.description && (
              <p className="text-sm text-red-600">{serverErrors.description[0]}</p>
            )}
          </div>

          {/* Projet */}
          <div className="space-y-2">
            <Label htmlFor="projet">Projet *</Label>
            <SearchableSelect
              options={projectOptions}
              value={formData.projet_id}
              onValueChange={(value) => handleInputChange('projet_id', value)}
              placeholder="Sélectionner un projet"
              searchPlaceholder="Rechercher un projet..."
              disabled={loadingProjects}
            />
            {serverErrors.projet_id && (
              <p className="text-sm text-red-600">{serverErrors.projet_id[0]}</p>
            )}
          </div>

          {/* Responsable */}
          <div className="space-y-2">
            <Label htmlFor="responsable">Responsable</Label>
            <SearchableSelect
              options={userOptions}
              value={formData.responsable_id || 'none'}
              onValueChange={(value) => handleInputChange('responsable_id', value === 'none' ? '' : value)}
              placeholder="Sélectionner un responsable"
              searchPlaceholder="Rechercher un responsable..."
              disabled={loadingUsers}
            />
            {serverErrors.responsable_id && (
              <p className="text-sm text-red-600">{serverErrors.responsable_id[0]}</p>
            )}
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
                className={serverErrors.date_debut_previsionnelle ? "border-red-500" : ""}
              />
              {serverErrors.date_debut_previsionnelle && (
                <p className="text-sm text-red-600">{serverErrors.date_debut_previsionnelle[0]}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="date_fin">Date de fin prévisionnelle</Label>
              <Input
                id="date_fin"
                type="date"
                value={formData.date_fin_previsionnelle}
                onChange={(e) => handleInputChange('date_fin_previsionnelle', e.target.value)}
                className={serverErrors.date_fin_previsionnelle ? "border-red-500" : ""}
              />
              {serverErrors.date_fin_previsionnelle && (
                <p className="text-sm text-red-600">{serverErrors.date_fin_previsionnelle[0]}</p>
              )}
            </div>
          </div>

          {/* Statut et progression */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="statut">Statut</Label>
              <Select
                value={formData.statut}
                onValueChange={(value) => handleInputChange('statut', value)}
              >
                <SelectTrigger id="statut" className={serverErrors.statut ? "border-red-500" : ""}>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(TACHE_STATUTS_KANBAN).map(([key, value]) => (
                    <SelectItem key={key} value={key}>
                      {value}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {serverErrors.statut && (
                <p className="text-sm text-red-600">{serverErrors.statut[0]}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="niveau_execution">Niveau d'exécution (%)</Label>
              <Input
                id="niveau_execution"
                type="number"
                min="0"
                max="100"
                value={formData.niveau_execution}
                onChange={(e) => handleInputChange('niveau_execution', parseInt(e.target.value) || 0)}
                className={serverErrors.niveau_execution ? "border-red-500" : ""}
              />
              {serverErrors.niveau_execution && (
                <p className="text-sm text-red-600">{serverErrors.niveau_execution[0]}</p>
              )}
            </div>
          </div>

          {/* Boutons */}
          <div className="flex justify-end gap-3 pt-4">
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
              {task ? 'Mettre à jour' : 'Créer'}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
} 