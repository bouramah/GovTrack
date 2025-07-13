'use client';

import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useToast } from '@/components/ui/use-toast';
import { Upload, File, X, AlertCircle } from 'lucide-react';
import { apiClient } from '@/lib/api';
import { ATTACHMENT_TYPES } from '@/types/attachment';

interface ProjectAttachmentUploadProps {
  projectId: number;
  onUploadSuccess?: () => void;
  onCancel?: () => void;
}

export default function ProjectAttachmentUpload({ 
  projectId, 
  onUploadSuccess, 
  onCancel 
}: ProjectAttachmentUploadProps) {
  const { toast } = useToast();
  const [isUploading, setIsUploading] = useState(false);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [description, setDescription] = useState('');
  const [typeDocument, setTypeDocument] = useState<string>('piece_jointe');
  const [estJustificatif, setEstJustificatif] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    // Vérifier la taille du fichier (max 10MB comme configuré dans le backend)
    if (file.size > 10 * 1024 * 1024) {
      toast({
        title: '❌ Fichier trop volumineux',
        description: 'Le fichier ne doit pas dépasser 10MB.',
        variant: 'destructive',
      });
      return;
    }

    setSelectedFile(file);
    setErrors(prev => ({ ...prev, fichier: '' }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedFile) {
      setErrors(prev => ({ ...prev, fichier: 'Veuillez sélectionner un fichier' }));
      return;
    }

    setIsUploading(true);
    setErrors({});

    try {
      const formData = new FormData();
      formData.append('fichier', selectedFile);
      formData.append('description', description);
      if (estJustificatif) {
        formData.append('est_justificatif', 'true');
      }
      formData.append('type_document', typeDocument);

      await apiClient.uploadProjectAttachment(projectId, formData);

      toast({
        title: '✅ Fichier uploadé',
        description: 'Le fichier a été ajouté avec succès au projet.',
      });

      // Réinitialiser le formulaire
      setSelectedFile(null);
      setDescription('');
      setTypeDocument('piece_jointe');
      setEstJustificatif(false);
      
      // Appeler le callback de succès
      onUploadSuccess?.();

    } catch (error: any) {
      console.error('Erreur upload fichier:', error);
      
      let errorMessage = 'Erreur lors du chargement du fichier.';
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        setErrors(errors);
        
        // Afficher le premier message d'erreur dans le toast
        const firstError = Object.values(errors)[0];
        if (Array.isArray(firstError) && firstError.length > 0) {
          errorMessage = firstError[0];
        }
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.message) {
        errorMessage = error.message;
      }

      toast({
        title: '❌ Erreur',
        description: errorMessage,
        variant: 'destructive',
      });
    } finally {
      setIsUploading(false);
    }
  };

  const handleCancel = () => {
    if (selectedFile || description || typeDocument !== 'piece_jointe' || estJustificatif) {
      if (confirm('Êtes-vous sûr de vouloir annuler ? Les données saisies seront perdues.')) {
        onCancel?.();
      }
    } else {
      onCancel?.();
    }
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Upload className="h-5 w-5" />
          Ajouter une pièce jointe
        </CardTitle>
        <CardDescription>
          Téléchargez un fichier pour l'ajouter au projet. Taille maximale : 10MB
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Sélection du fichier */}
          <div className="space-y-2">
            <Label htmlFor="file-upload">Fichier *</Label>
            <div className="flex items-center gap-2">
              <Input
                id="file-upload"
                type="file"
                onChange={handleFileSelect}
                disabled={isUploading}
                className="flex-1"
              />
              {selectedFile && (
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setSelectedFile(null)}
                  disabled={isUploading}
                >
                  <X className="h-4 w-4" />
                </Button>
              )}
            </div>
            {selectedFile && (
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <File className="h-4 w-4" />
                <span>{selectedFile.name}</span>
                <span>({formatFileSize(selectedFile.size)})</span>
              </div>
            )}
            {errors.fichier && (
              <Alert variant="destructive" className="py-2">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>{errors.fichier}</AlertDescription>
              </Alert>
            )}
          </div>

          {/* Description */}
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea
              id="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Description du fichier (optionnel)"
              disabled={isUploading}
              rows={3}
            />
            {errors.description && (
              <Alert variant="destructive" className="py-2">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>{errors.description}</AlertDescription>
              </Alert>
            )}
          </div>

          {/* Type de document */}
          <div className="space-y-2">
            <Label htmlFor="type-document">Type de document</Label>
            <SearchableSelect
              options={ATTACHMENT_TYPES.map((type) => ({
                value: type.value,
                label: type.label
              }))}
              value={typeDocument}
              onValueChange={setTypeDocument}
              placeholder="Sélectionner un type"
              searchPlaceholder="Rechercher un type..."
              disabled={isUploading}
            />
            {errors.type_document && (
              <Alert variant="destructive" className="py-2">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>{errors.type_document}</AlertDescription>
              </Alert>
            )}
          </div>

          {/* Justificatif */}
          <div className="flex items-center space-x-2">
            <Checkbox
              id="est-justificatif"
              checked={estJustificatif}
              onCheckedChange={(checked) => setEstJustificatif(checked as boolean)}
              disabled={isUploading}
            />
            <Label htmlFor="est-justificatif" className="text-sm font-normal">
              Marquer comme justificatif (obligatoire pour la clôture du projet)
            </Label>
          </div>
          {errors.est_justificatif && (
            <Alert variant="destructive" className="py-2">
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>{errors.est_justificatif}</AlertDescription>
            </Alert>
          )}

          {/* Boutons d'action */}
          <div className="flex justify-end gap-2 pt-4">
            <Button
              type="button"
              variant="outline"
              onClick={handleCancel}
              disabled={isUploading}
            >
              Annuler
            </Button>
            <Button
              type="submit"
              disabled={!selectedFile || isUploading}
              className="min-w-[120px]"
            >
              {isUploading ? (
                <>
                  <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                  Chargement...
                </>
              ) : (
                <>
                  <Upload className="mr-2 h-4 w-4" />
                  Charger
                </>
              )}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
} 