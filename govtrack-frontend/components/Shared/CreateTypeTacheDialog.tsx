'use client';

import React, { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/components/ui/use-toast';
import { TypeTacheCreateRequest } from '@/lib/api';

interface CreateTypeTacheDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSubmit: (data: TypeTacheCreateRequest) => Promise<void>;
}

export function CreateTypeTacheDialog({ open, onOpenChange, onSubmit }: CreateTypeTacheDialogProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<TypeTacheCreateRequest>({
    nom: '',
    description: '',
    couleur: '#3B82F6', // Bleu par défaut
    actif: true,
    ordre: 1,
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.nom.trim()) {
      toast({
        title: 'Erreur',
        description: 'Le nom du type de tâche est obligatoire',
        variant: 'destructive',
      });
      return;
    }

    try {
      setLoading(true);
      await onSubmit(formData);
      // Réinitialiser le formulaire
      setFormData({
        nom: '',
        description: '',
        couleur: '#3B82F6',
        actif: true,
        ordre: 1,
      });
    } catch (error) {
      // L'erreur est déjà gérée dans le composant parent
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: keyof TypeTacheCreateRequest, value: any) => {
    setFormData(prev => ({
      ...prev,
      [field]: value,
    }));
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Créer un nouveau type de tâche</DialogTitle>
          <DialogDescription>
            Définissez les propriétés du nouveau type de tâche. Tous les champs marqués d'un * sont obligatoires.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Nom */}
          <div className="space-y-2">
            <Label htmlFor="nom">
              Nom du type * <span className="text-red-500">*</span>
            </Label>
            <Input
              id="nom"
              value={formData.nom}
              onChange={(e) => handleInputChange('nom', e.target.value)}
              placeholder="Ex: Tâche ordinaire, Recommandation, Urgente..."
              required
            />
          </div>

          {/* Description */}
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea
              id="description"
              value={formData.description || ''}
              onChange={(e) => handleInputChange('description', e.target.value)}
              placeholder="Description optionnelle du type de tâche..."
              rows={3}
            />
          </div>

          {/* Couleur */}
          <div className="space-y-2">
            <Label htmlFor="couleur">Couleur d'identification</Label>
            <div className="flex items-center gap-3">
              <Input
                id="couleur"
                type="color"
                value={formData.couleur}
                onChange={(e) => handleInputChange('couleur', e.target.value)}
                className="w-16 h-10 p-1 border rounded"
              />
              <Input
                value={formData.couleur}
                onChange={(e) => handleInputChange('couleur', e.target.value)}
                placeholder="#3B82F6"
                className="flex-1"
              />
            </div>
            <p className="text-sm text-gray-500">
              Cette couleur sera utilisée pour identifier visuellement ce type de tâche
            </p>
          </div>

          {/* Ordre */}
          <div className="space-y-2">
            <Label htmlFor="ordre">Ordre d'affichage</Label>
            <Input
              id="ordre"
              type="number"
              min="1"
              value={formData.ordre}
              onChange={(e) => handleInputChange('ordre', parseInt(e.target.value) || 1)}
              placeholder="1"
            />
            <p className="text-sm text-gray-500">
              L'ordre détermine la position d'affichage dans les listes (1 = premier)
            </p>
          </div>

          {/* Statut actif */}
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="actif">Type actif</Label>
              <p className="text-sm text-gray-500">
                Les types inactifs ne peuvent pas être attribués aux nouvelles tâches
              </p>
            </div>
            <Switch
              id="actif"
              checked={formData.actif}
              onCheckedChange={(checked) => handleInputChange('actif', checked)}
            />
          </div>

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
              disabled={loading}
            >
              Annuler
            </Button>
            <Button type="submit" disabled={loading}>
              {loading ? 'Création...' : 'Créer le type'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
} 