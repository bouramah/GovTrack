"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, Save, X, Check, ChevronsUpDown, Search } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache, CreateTacheRequest, UpdateTacheRequest } from "@/types/tache";
import type { Project, User } from "@/lib/api";
import { cn } from "@/lib/utils";

interface NewTaskModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task?: Tache | null;
  onSuccess?: (task: Tache) => void;
}

export default function NewTaskModal({
  open,
  onOpenChange,
  task,
  onSuccess
}: NewTaskModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(false);
  const [projects, setProjects] = useState<Project[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [projectSearchOpen, setProjectSearchOpen] = useState(false);
  const [responsableSearchOpen, setResponsableSearchOpen] = useState(false);
  const [formData, setFormData] = useState<CreateTacheRequest>({
    titre: "",
    description: "",
    projet_id: 0,
    responsable_id: 0,
    date_debut_previsionnelle: "",
    date_fin_previsionnelle: ""
  });

  const isEditing = !!task;

  // Charger les projets et utilisateurs
  const loadFormData = async () => {
    try {
      setLoadingData(true);
      
      // Charger les projets
      const projectsResponse = await apiClient.getProjects({ per_page: 100 });
      if (projectsResponse.success && projectsResponse.data) {
        setProjects(projectsResponse.data);
      }

      // Charger les utilisateurs
      const usersResponse = await apiClient.getUsersDetailed({ per_page: 100 });
      if (usersResponse.success && usersResponse.data) {
        setUsers(usersResponse.data);
      }
    } catch (error: any) {
      console.error('Erreur chargement données formulaire:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les données du formulaire",
        variant: "destructive",
      });
    } finally {
      setLoadingData(false);
    }
  };

  useEffect(() => {
    if (open) {
      loadFormData();
    }
  }, [open]);

  useEffect(() => {
    if (task) {
      setFormData({
        titre: task.titre,
        description: task.description || "",
        projet_id: task.projet_id,
        responsable_id: task.responsable_id || 0,
        date_debut_previsionnelle: task.date_debut_previsionnelle || "",
        date_fin_previsionnelle: task.date_fin_previsionnelle || ""
      });
    } else {
      setFormData({
        titre: "",
        description: "",
        projet_id: 0,
        responsable_id: 0,
        date_debut_previsionnelle: "",
        date_fin_previsionnelle: ""
      });
    }
  }, [task, open]);

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
          description: "La date de fin doit être postérieure à la date de début",
          variant: "destructive",
        });
        return;
      }
    }

    try {
      setLoading(true);
      
      let response;
      if (isEditing && task) {
        const updateData: UpdateTacheRequest = {
          titre: formData.titre,
          description: formData.description || undefined,
          responsable_id: formData.responsable_id || undefined,
          date_debut_previsionnelle: formData.date_debut_previsionnelle || undefined,
          date_fin_previsionnelle: formData.date_fin_previsionnelle || undefined,
        };
        response = await apiClient.updateTache(task.id, updateData);
      } else {
        const createData: CreateTacheRequest = {
          titre: formData.titre,
          description: formData.description || undefined,
          projet_id: formData.projet_id,
          responsable_id: formData.responsable_id || undefined,
          date_debut_previsionnelle: formData.date_debut_previsionnelle || undefined,
          date_fin_previsionnelle: formData.date_fin_previsionnelle || undefined,
        };
        response = await apiClient.createTache(createData);
      }

      if (response.success && response.data) {
        toast({
          title: "Succès",
          description: isEditing 
            ? "Tâche mise à jour avec succès" 
            : "Tâche créée avec succès",
        });
        
        onSuccess?.(response.data);
        onOpenChange(false);
      }
    } catch (error: any) {
      console.error('Erreur lors de la sauvegarde de la tâche:', error);
      
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
          description: error.response?.data?.message || "Erreur lors de la sauvegarde de la tâche",
          variant: "destructive",
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: keyof CreateTacheRequest, value: any) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  // Obtenir le projet sélectionné
  const selectedProject = projects.find(p => p.id === formData.projet_id);
  
  // Obtenir le responsable sélectionné
  const selectedResponsable = users.find(u => u.id === formData.responsable_id);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            {isEditing ? (
              <>
                <Save className="h-5 w-5" />
                Modifier la tâche
              </>
            ) : (
              <>
                <Save className="h-5 w-5" />
                Nouvelle tâche
              </>
            )}
          </DialogTitle>
        </DialogHeader>

        {loadingData ? (
          <div className="flex items-center justify-center py-8">
            <div className="text-center">
              <Loader2 className="h-8 w-8 animate-spin mx-auto text-blue-600" />
              <p className="mt-2 text-gray-600">Chargement des données...</p>
            </div>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Titre */}
            <div className="space-y-2">
              <Label htmlFor="titre">Titre *</Label>
              <Input
                id="titre"
                value={formData.titre}
                onChange={(e) => handleInputChange('titre', e.target.value)}
                placeholder="Titre de la tâche"
                required
                maxLength={255}
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
                rows={3}
              />
            </div>

            {/* Projet avec recherche */}
            <div className="space-y-2">
              <Label htmlFor="projet">Projet *</Label>
              <Popover open={projectSearchOpen} onOpenChange={setProjectSearchOpen}>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    role="combobox"
                    aria-expanded={projectSearchOpen}
                    className="w-full justify-between"
                    disabled={isEditing}
                  >
                    {selectedProject ? (
                      <div className="flex flex-col items-start">
                        <span className="font-medium">{selectedProject.titre}</span>
                        <span className="text-xs text-gray-500">
                          {selectedProject.type_projet?.nom} • {selectedProject.statut_libelle}
                        </span>
                      </div>
                    ) : (
                      "Sélectionner un projet..."
                    )}
                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-full p-0" align="start">
                  <Command>
                    <CommandInput placeholder="Rechercher un projet..." />
                    <CommandList>
                      <CommandEmpty>Aucun projet trouvé.</CommandEmpty>
                      <CommandGroup>
                        {projects.map((project) => (
                          <CommandItem
                            key={project.id}
                            value={`${project.titre} ${project.type_projet?.nom} ${project.statut_libelle}`}
                            onSelect={() => {
                              handleInputChange('projet_id', project.id);
                              setProjectSearchOpen(false);
                            }}
                          >
                            <Check
                              className={cn(
                                "mr-2 h-4 w-4",
                                formData.projet_id === project.id ? "opacity-100" : "opacity-0"
                              )}
                            />
                            <div className="flex flex-col">
                              <span className="font-medium">{project.titre}</span>
                              <span className="text-xs text-gray-500">
                                {project.type_projet?.nom} • {project.statut_libelle}
                              </span>
                            </div>
                          </CommandItem>
                        ))}
                      </CommandGroup>
                    </CommandList>
                  </Command>
                </PopoverContent>
              </Popover>
              {projects.length === 0 && (
                <p className="text-sm text-gray-500">Aucun projet disponible</p>
              )}
            </div>

            {/* Responsable avec recherche */}
            <div className="space-y-2">
              <Label htmlFor="responsable">Responsable</Label>
              <Popover open={responsableSearchOpen} onOpenChange={setResponsableSearchOpen}>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    role="combobox"
                    aria-expanded={responsableSearchOpen}
                    className="w-full justify-between"
                  >
                    {selectedResponsable ? (
                      <div className="flex flex-col items-start">
                        <span className="font-medium">{selectedResponsable.prenom} {selectedResponsable.nom}</span>
                        <span className="text-xs text-gray-500">
                          {selectedResponsable.matricule} • {selectedResponsable.email}
                        </span>
                      </div>
                    ) : (
                      "Sélectionner un responsable..."
                    )}
                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-full p-0" align="start">
                  <Command>
                    <CommandInput placeholder="Rechercher un responsable..." />
                    <CommandList>
                      <CommandEmpty>Aucun responsable trouvé.</CommandEmpty>
                      <CommandGroup>
                        <CommandItem
                          value="aucun"
                          onSelect={() => {
                            handleInputChange('responsable_id', 0);
                            setResponsableSearchOpen(false);
                          }}
                        >
                          <Check
                            className={cn(
                              "mr-2 h-4 w-4",
                              formData.responsable_id === 0 ? "opacity-100" : "opacity-0"
                            )}
                          />
                          Aucun responsable
                        </CommandItem>
                        {users.map((user) => (
                          <CommandItem
                            key={user.id}
                            value={`${user.prenom} ${user.nom} ${user.matricule} ${user.email}`}
                            onSelect={() => {
                              handleInputChange('responsable_id', user.id);
                              setResponsableSearchOpen(false);
                            }}
                          >
                            <Check
                              className={cn(
                                "mr-2 h-4 w-4",
                                formData.responsable_id === user.id ? "opacity-100" : "opacity-0"
                              )}
                            />
                            <div className="flex flex-col">
                              <span className="font-medium">{user.prenom} {user.nom}</span>
                              <span className="text-xs text-gray-500">
                                {user.matricule} • {user.email}
                              </span>
                            </div>
                          </CommandItem>
                        ))}
                      </CommandGroup>
                    </CommandList>
                  </Command>
                </PopoverContent>
              </Popover>
            </div>

            {/* Dates */}
            <div className="grid grid-cols-2 gap-4">
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
                  min={formData.date_debut_previsionnelle}
                />
              </div>
            </div>

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
              <Button type="submit" disabled={loading || loadingData}>
                {loading ? (
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                ) : (
                  <Save className="h-4 w-4 mr-2" />
                )}
                {isEditing ? "Modifier" : "Créer"}
              </Button>
            </DialogFooter>
          </form>
        )}
      </DialogContent>
    </Dialog>
  );
} 