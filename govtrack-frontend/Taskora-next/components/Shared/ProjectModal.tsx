'use client';

import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { AlertCircle, CalendarIcon, Loader2, ChevronDown } from 'lucide-react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { cn } from '@/lib/utils';
import { apiClient, Project, ProjectCreateRequest, ProjectUpdateRequest, TypeProjet, User } from '@/lib/api';
import { useToast } from '@/components/ui/use-toast';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';

interface ProjectModalProps {
  isOpen: boolean;
  onClose: () => void;
  project?: Project | null;
  onSuccess: () => void;
}

export default function ProjectModal({ isOpen, onClose, project, onSuccess }: ProjectModalProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [typeProjets, setTypeProjets] = useState<TypeProjet[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});
  const [formData, setFormData] = useState<ProjectCreateRequest>({
    titre: '',
    description: '',
    type_projet_id: 0,
    porteur_id: 0,
    donneur_ordre_id: 0,
    date_debut_previsionnelle: '',
    date_fin_previsionnelle: '',
    justification_modification_dates: ''
  });

  const isEditing = !!project;

  // Charger les données nécessaires
  useEffect(() => {
    if (isOpen) {
      loadFormData();
    }
  }, [isOpen, project]);

  const loadFormData = async () => {
    try {
      setLoading(true);
      
      // Charger les types de projets
      const typeProjetsResponse = await apiClient.getTypeProjets({ per_page: 100 });
      setTypeProjets(typeProjetsResponse.data || []);

      // Charger les utilisateurs
      const usersResponse = await apiClient.getUsersDetailed({ per_page: 1000 });
      setUsers(usersResponse.data || []);

      // Si on édite, pré-remplir le formulaire
      if (project) {
        setFormData({
          titre: project.titre,
          description: project.description,
          type_projet_id: project.type_projet.id,
          porteur_id: project.porteur.id,
          donneur_ordre_id: project.donneur_ordre.id,
          date_debut_previsionnelle: project.date_debut_previsionnelle.split('T')[0],
          date_fin_previsionnelle: project.date_fin_previsionnelle?.split('T')[0] || '',
          justification_modification_dates: project.justification_modification_dates || ''
        });
      } else {
        // Réinitialiser le formulaire pour la création
        setFormData({
          titre: '',
          description: '',
          type_projet_id: 0,
          porteur_id: 0,
          donneur_ordre_id: 0,
          date_debut_previsionnelle: '',
          date_fin_previsionnelle: '',
          justification_modification_dates: ''
        });
      }
    } catch (error: any) {
      toast({
        title: "Erreur",
        description: 'Erreur lors du chargement des données',
        variant: "destructive",
      });
      console.error('Erreur loadFormData:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Effacer les erreurs précédentes
    setServerErrors({});
    
    // Validation côté client
    if (!formData.titre.trim()) {
      toast({
        title: "Erreur",
        description: 'Le titre est obligatoire',
        variant: "destructive",
      });
      return;
    }
    if (!formData.description.trim()) {
      toast({
        title: "Erreur",
        description: 'La description est obligatoire',
        variant: "destructive",
      });
      return;
    }
    if (!formData.type_projet_id) {
      toast({
        title: "Erreur",
        description: 'Le type d\'instruction est obligatoire',
        variant: "destructive",
      });
      return;
    }
    if (!formData.porteur_id) {
      toast({
        title: "Erreur",
        description: 'Le porteur est obligatoire',
        variant: "destructive",
      });
      return;
    }
    if (!formData.donneur_ordre_id) {
      toast({
        title: "Erreur",
        description: 'Le donneur d\'ordre est obligatoire',
        variant: "destructive",
      });
      return;
    }
    if (!formData.date_debut_previsionnelle) {
      toast({
        title: "Erreur",
        description: 'La date de début est obligatoire',
        variant: "destructive",
      });
      return;
    }

    try {
      setLoading(true);

      if (isEditing && project) {
        // Mise à jour
        await apiClient.updateProject(project.id, formData as ProjectUpdateRequest);
        toast({
          title: "Succès",
          description: 'Instruction mise à jour avec succès',
        });
      } else {
        // Création
        await apiClient.createProject(formData);
        toast({
          title: "Succès",
          description: 'Instruction créée avec succès',
        });
      }

      onSuccess();
      onClose();
    } catch (error: any) {
      console.error('Erreur submit:', error);
      
      // Gestion des erreurs de validation du serveur
      if (error.response?.status === 422 && error.response?.data?.errors) {
        setServerErrors(error.response.data.errors);
        toast({
          title: "Erreur de validation",
          description: 'Veuillez corriger les erreurs de validation ci-dessous',
          variant: "destructive",
        });
      } else if (error.message.includes('Justification requise')) {
        toast({
          title: "Erreur",
          description: 'Justification requise pour modifier les dates par rapport au SLA',
          variant: "destructive",
        });
      } else if (error.message.includes('niveau d\'exécution')) {
        toast({
          title: "Erreur",
          description: error.message,
          variant: "destructive",
        });
      } else if (error.response?.data?.message) {
        toast({
          title: "Erreur",
          description: error.response.data.message,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Erreur",
          description: error.message || 'Une erreur est survenue',
          variant: "destructive",
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: keyof ProjectCreateRequest, value: any) => {
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

  const handleDateChange = (field: 'date_debut_previsionnelle' | 'date_fin_previsionnelle', date: Date | undefined) => {
    if (date) {
      const formattedDate = format(date, 'yyyy-MM-dd');
      handleInputChange(field, formattedDate);
    } else {
      handleInputChange(field, '');
    }
  };

  // Préparer les options pour les selects
  const typeProjetOptions: SearchableSelectOption[] = typeProjets.map((type) => ({
    value: type.id.toString(),
    label: type.nom,
    description: type.description,
    badge: `${type.duree_previsionnelle_jours} jours`
  }));

  const userOptions: SearchableSelectOption[] = users.map((user) => ({
    value: user.id.toString(),
    label: `${user.prenom} ${user.nom}`,
    description: user.email,
    badge: user.matricule
  }));

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {isEditing ? 'Modifier l\'instruction' : 'Créer une nouvelle instruction'}
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Résumé des erreurs */}
          {Object.keys(serverErrors).length > 0 && (
            <div className="bg-red-50 border border-red-200 rounded-md p-4">
              <div className="flex">
                <div className="flex-shrink-0">
                  <AlertCircle className="h-5 w-5 text-red-400" />
                </div>
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
          
          {/* Titre */}
          <div className="space-y-2">
            <Label htmlFor="titre">Titre *</Label>
            <Input
              id="titre"
              value={formData.titre}
              onChange={(e) => handleInputChange('titre', e.target.value)}
              placeholder="Titre de l'instruction"
              required
              className={serverErrors.titre ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.titre && (
              <p className="text-sm text-red-600">{serverErrors.titre[0]}</p>
            )}
          </div>

          {/* Description */}
          <div className="space-y-2">
            <Label htmlFor="description">Description *</Label>
            <Textarea
              id="description"
              value={formData.description}
              onChange={(e) => handleInputChange('description', e.target.value)}
              placeholder="Description détaillée de l'instruction"
              rows={4}
              required
              className={serverErrors.description ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.description && (
              <p className="text-sm text-red-600">{serverErrors.description[0]}</p>
            )}
          </div>

          {/* Type de projet */}
          <div className="space-y-2">
            <Label htmlFor="type_projet">Type d'instruction *</Label>
            <SearchableSelect
              options={typeProjetOptions}
              value={formData.type_projet_id ? formData.type_projet_id.toString() : undefined}
              onValueChange={(value) => handleInputChange('type_projet_id', parseInt(value))}
              placeholder="Sélectionner un type d'instruction..."
              searchPlaceholder="Rechercher un type d'instruction..."
              emptyMessage="Aucun type d'instruction trouvé."
              className={serverErrors.type_projet_id ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.type_projet_id && (
              <p className="text-sm text-red-600">{serverErrors.type_projet_id[0]}</p>
            )}
          </div>

          {/* Porteur */}
          <div className="space-y-2">
            <Label htmlFor="porteur">Porteur *</Label>
            <SearchableSelect
              options={userOptions}
              value={formData.porteur_id ? formData.porteur_id.toString() : undefined}
              onValueChange={(value) => handleInputChange('porteur_id', parseInt(value))}
              placeholder="Sélectionner le porteur..."
              searchPlaceholder="Rechercher un utilisateur..."
              emptyMessage="Aucun utilisateur trouvé."
              className={serverErrors.porteur_id ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.porteur_id && (
              <p className="text-sm text-red-600">{serverErrors.porteur_id[0]}</p>
            )}
          </div>

          {/* Donneur d'ordre */}
          <div className="space-y-2">
            <Label htmlFor="donneur_ordre">Donneur d'ordre *</Label>
            <SearchableSelect
              options={userOptions}
              value={formData.donneur_ordre_id ? formData.donneur_ordre_id.toString() : undefined}
              onValueChange={(value) => handleInputChange('donneur_ordre_id', parseInt(value))}
              placeholder="Sélectionner le donneur d'ordre..."
              searchPlaceholder="Rechercher un utilisateur..."
              emptyMessage="Aucun utilisateur trouvé."
              className={serverErrors.donneur_ordre_id ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.donneur_ordre_id && (
              <p className="text-sm text-red-600">{serverErrors.donneur_ordre_id[0]}</p>
            )}
          </div>

          {/* Date de début */}
          <div className="space-y-2">
            <Label>Date de début prévisionnelle *</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-full justify-start text-left font-normal",
                    !formData.date_debut_previsionnelle && "text-muted-foreground",
                    serverErrors.date_debut_previsionnelle ? "border-red-500 focus:border-red-500" : ""
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {formData.date_debut_previsionnelle ? (
                    format(new Date(formData.date_debut_previsionnelle), 'PPP', { locale: fr })
                  ) : (
                    <span>Sélectionner une date</span>
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  mode="single"
                  selected={formData.date_debut_previsionnelle ? new Date(formData.date_debut_previsionnelle) : undefined}
                  onSelect={(date) => handleDateChange('date_debut_previsionnelle', date)}
                  initialFocus
                  locale={fr}
                />
              </PopoverContent>
            </Popover>
            {serverErrors.date_debut_previsionnelle && (
              <p className="text-sm text-red-600">{serverErrors.date_debut_previsionnelle[0]}</p>
            )}
          </div>

          {/* Date de fin */}
          <div className="space-y-2">
            <Label>Date de fin prévisionnelle</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-full justify-start text-left font-normal",
                    !formData.date_fin_previsionnelle && "text-muted-foreground",
                    serverErrors.date_fin_previsionnelle ? "border-red-500 focus:border-red-500" : ""
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {formData.date_fin_previsionnelle ? (
                    format(new Date(formData.date_fin_previsionnelle), 'PPP', { locale: fr })
                  ) : (
                    <span>Sélectionner une date</span>
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  mode="single"
                  selected={formData.date_fin_previsionnelle ? new Date(formData.date_fin_previsionnelle) : undefined}
                  onSelect={(date) => handleDateChange('date_fin_previsionnelle', date)}
                  initialFocus
                  locale={fr}
                  disabled={(date) => {
                    if (!formData.date_debut_previsionnelle) return false;
                    return date < new Date(formData.date_debut_previsionnelle);
                  }}
                />
              </PopoverContent>
            </Popover>
            {serverErrors.date_fin_previsionnelle && (
              <p className="text-sm text-red-600">{serverErrors.date_fin_previsionnelle[0]}</p>
            )}
          </div>

          {/* Justification des modifications */}
          <div className="space-y-2">
            <Label htmlFor="justification_modification_dates">Justification des modifications de dates (optionnel)</Label>
            <Textarea
              id="justification_modification_dates"
              value={formData.justification_modification_dates}
              onChange={(e) => handleInputChange('justification_modification_dates', e.target.value)}
              placeholder="Justification des modifications de dates par rapport au SLA..."
              rows={3}
              className={serverErrors.justification_modification_dates ? "border-red-500 focus:border-red-500" : ""}
            />
            {serverErrors.justification_modification_dates && (
              <p className="text-sm text-red-600">{serverErrors.justification_modification_dates[0]}</p>
            )}
          </div>

          {/* Boutons d'action */}
          <div className="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Annuler
            </Button>
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {isEditing ? 'Mettre à jour' : 'Créer'}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
} 