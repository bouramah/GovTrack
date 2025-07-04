'use client';

import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/use-toast';
import { 
  Download, 
  Edit, 
  Trash2, 
  File, 
  FileText, 
  Image, 
  Archive, 
  Film,
  Calendar,
  User,
  AlertCircle,
  CheckCircle,
  Eye
} from 'lucide-react';
import { apiClient } from '@/lib/api';
import { ProjectAttachment, ATTACHMENT_TYPES } from '@/types/attachment';
import { useAuth } from '@/contexts/AuthContext';
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SearchableSelect, SearchableSelectOption } from '@/components/ui/searchable-select';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface ProjectAttachmentsListProps {
  projectId: number;
  onRefresh?: () => void;
}

export default function ProjectAttachmentsList({ projectId, onRefresh }: ProjectAttachmentsListProps) {
  const { toast } = useToast();
  const { user } = useAuth();
  const [attachments, setAttachments] = useState<ProjectAttachment[]>([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<any>(null);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [editDialogOpen, setEditDialogOpen] = useState(false);
  const [selectedAttachment, setSelectedAttachment] = useState<ProjectAttachment | null>(null);
  const [editForm, setEditForm] = useState({
    description: '',
    type_document: 'piece_jointe' as string
  });
  const [downloading, setDownloading] = useState<number | null>(null);

  useEffect(() => {
    loadAttachments();
    loadStats();
  }, [projectId]);

  const loadAttachments = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getProjectAttachments(projectId);
      if (response.success && response.data) {
        setAttachments(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement pièces jointes:', error);
      toast({
        title: '❌ Erreur',
        description: 'Impossible de charger les pièces jointes.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const loadStats = async () => {
    try {
      const response = await apiClient.getProjectAttachmentsStats(projectId);
      if (response.success && response.data) {
        setStats(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement statistiques:', error);
    }
  };

  const handleDownload = async (attachment: ProjectAttachment) => {
    try {
      setDownloading(attachment.id);
      const blob = await apiClient.downloadProjectAttachment(projectId, attachment.id);
      
      // Créer un lien de téléchargement
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = attachment.nom_original;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      toast({
        title: '✅ Téléchargement',
        description: 'Le fichier a été téléchargé avec succès.',
      });
    } catch (error) {
      console.error('Erreur téléchargement:', error);
      toast({
        title: '❌ Erreur',
        description: 'Impossible de télécharger le fichier.',
        variant: 'destructive',
      });
    } finally {
      setDownloading(null);
    }
  };

  const handleEdit = (attachment: ProjectAttachment) => {
    setSelectedAttachment(attachment);
    setEditForm({
      description: attachment.description || '',
      type_document: attachment.type_document || 'piece_jointe'
    });
    setEditDialogOpen(true);
  };

  const handleEditSubmit = async () => {
    if (!selectedAttachment) return;

    try {
      await apiClient.updateProjectAttachment(projectId, selectedAttachment.id, editForm);
      
      toast({
        title: '✅ Modifié',
        description: 'La pièce jointe a été modifiée avec succès.',
      });

      setEditDialogOpen(false);
      setSelectedAttachment(null);
      loadAttachments();
      onRefresh?.();
    } catch (error: any) {
      console.error('Erreur modification:', error);
      
      let errorMessage = 'Erreur lors de la modification.';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      }

      toast({
        title: '❌ Erreur',
        description: errorMessage,
        variant: 'destructive',
      });
    }
  };

  const handleDelete = (attachment: ProjectAttachment) => {
    setSelectedAttachment(attachment);
    setDeleteDialogOpen(true);
  };

  const handleDeleteConfirm = async () => {
    if (!selectedAttachment) return;

    try {
      await apiClient.deleteProjectAttachment(projectId, selectedAttachment.id);
      
      toast({
        title: '✅ Supprimé',
        description: 'La pièce jointe a été supprimée avec succès.',
      });

      setDeleteDialogOpen(false);
      setSelectedAttachment(null);
      loadAttachments();
      loadStats();
      onRefresh?.();
    } catch (error: any) {
      console.error('Erreur suppression:', error);
      
      let errorMessage = 'Erreur lors de la suppression.';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      }

      toast({
        title: '❌ Erreur',
        description: errorMessage,
        variant: 'destructive',
      });
    }
  };

  const getFileIcon = (mimeType: string | null) => {
    if (!mimeType) return <File className="h-5 w-5" />;
    
    if (mimeType.startsWith('image/')) return <Image className="h-5 w-5" />;
    if (mimeType.startsWith('text/') || mimeType.includes('pdf') || mimeType.includes('document')) {
      return <FileText className="h-5 w-5" />;
    }
    if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('tar')) {
      return <Archive className="h-5 w-5" />;
    }
    if (mimeType.startsWith('video/')) return <Film className="h-5 w-5" />;
    
    return <File className="h-5 w-5" />;
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const canEdit = (attachment: ProjectAttachment) => {
    return user && attachment.user_id === user.id;
  };

  const canDelete = (attachment: ProjectAttachment) => {
    return user && attachment.user_id === user.id;
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Pièces jointes</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center py-8">
            <div className="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent" />
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <>
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            <span>Pièces jointes</span>
            {stats && (
              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <span>{stats.total_fichiers} fichier(s)</span>
                <span>{formatFileSize(stats.taille_totale)}</span>
                {stats.total_justificatifs > 0 && (
                  <Badge variant="secondary" className="flex items-center gap-1">
                    <CheckCircle className="h-3 w-3" />
                    {stats.total_justificatifs} justificatif(s)
                  </Badge>
                )}
              </div>
            )}
          </CardTitle>
          <CardDescription>
            Fichiers attachés au projet
          </CardDescription>
        </CardHeader>
        <CardContent>
          {attachments.length === 0 ? (
            <div className="text-center py-8 text-muted-foreground">
              <File className="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>Aucune pièce jointe pour ce projet</p>
            </div>
          ) : (
            <div className="space-y-3">
              {attachments.map((attachment) => (
                <div key={attachment.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3 flex-1 min-w-0">
                    <div className="text-muted-foreground">
                      {getFileIcon(attachment.mime_type)}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <p className="font-medium truncate">{attachment.nom_original}</p>
                        {attachment.est_justificatif && (
                          <Badge variant="default" className="flex items-center gap-1">
                            <CheckCircle className="h-3 w-3" />
                            Justificatif
                          </Badge>
                        )}
                        {attachment.type_document && (
                          <Badge variant="outline">
                            {ATTACHMENT_TYPES.find(t => t.value === attachment.type_document)?.label || attachment.type_document}
                          </Badge>
                        )}
                      </div>
                      <div className="flex items-center gap-4 text-sm text-muted-foreground mt-1">
                        <span className="flex items-center gap-1">
                          <User className="h-3 w-3" />
                          {attachment.user?.prenom} {attachment.user?.nom}
                        </span>
                        <span className="flex items-center gap-1">
                          <Calendar className="h-3 w-3" />
                          {formatDate(attachment.date_creation)}
                        </span>
                        <span>{formatFileSize(attachment.taille)}</span>
                      </div>
                      {attachment.description && (
                        <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
                          {attachment.description}
                        </p>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center gap-1">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDownload(attachment)}
                      disabled={downloading === attachment.id}
                    >
                      {downloading === attachment.id ? (
                        <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                      ) : (
                        <Download className="h-4 w-4" />
                      )}
                    </Button>
                    {canEdit(attachment) && (
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleEdit(attachment)}
                      >
                        <Edit className="h-4 w-4" />
                      </Button>
                    )}
                    {canDelete(attachment) && (
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDelete(attachment)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Dialog de modification */}
      <Dialog open={editDialogOpen} onOpenChange={setEditDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Modifier la pièce jointe</DialogTitle>
            <DialogDescription>
              Modifiez les informations de la pièce jointe
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="edit-description">Description</Label>
              <Textarea
                id="edit-description"
                value={editForm.description}
                onChange={(e) => setEditForm(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Description du fichier"
                rows={3}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="edit-type">Type de document</Label>
              <SearchableSelect
                options={ATTACHMENT_TYPES.map((type) => ({
                  value: type.value,
                  label: type.label
                }))}
                value={editForm.type_document}
                onValueChange={(value) => setEditForm(prev => ({ ...prev, type_document: value }))}
                placeholder="Sélectionner un type"
                searchPlaceholder="Rechercher un type..."
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setEditDialogOpen(false)}>
                Annuler
              </Button>
              <Button onClick={handleEditSubmit}>
                Modifier
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Dialog de confirmation de suppression */}
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer la pièce jointe "{selectedAttachment?.nom_original}" ? 
              Cette action est irréversible.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction onClick={handleDeleteConfirm} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </>
  );
} 