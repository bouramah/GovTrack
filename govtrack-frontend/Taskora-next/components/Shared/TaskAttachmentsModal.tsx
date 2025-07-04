"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { SearchableSelect, SearchableSelectOption } from "@/components/ui/searchable-select";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/components/ui/use-toast";
import { 
  Loader2, 
  Upload, 
  Download, 
  Trash2, 
  File, 
  FileText, 
  Image, 
  Archive,
  Video,
  Music,
  Paperclip,
  X,
  Check,
  AlertCircle
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Tache } from "@/types/tache";
import type { TaskAttachment, AttachmentStats } from "@/types/attachment";
import { ATTACHMENT_TYPES } from "@/types/attachment";
import { formatFileSize, getFileIconName } from "@/lib/utils";

interface TaskAttachmentsModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  task: Tache | null;
  onSuccess?: () => void;
}

export default function TaskAttachmentsModal({
  open,
  onOpenChange,
  task,
  onSuccess
}: TaskAttachmentsModalProps) {
  const { toast } = useToast();
  const [attachments, setAttachments] = useState<any[]>([]);
  const [stats, setStats] = useState<AttachmentStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [uploadForm, setUploadForm] = useState({
    files: [] as File[],
    description: "",
    type_document: "piece_jointe" as const,
    est_justificatif: false
  });

  // Charger les pièces jointes
  const loadAttachments = async () => {
    if (!task) return;
    
    try {
      setLoading(true);
      const [attachmentsResponse, statsResponse] = await Promise.all([
        apiClient.getTaskAttachments(task.id),
        apiClient.getTaskAttachmentsStats(task.id)
      ]);

      if (attachmentsResponse.success && attachmentsResponse.data) {
        setAttachments(attachmentsResponse.data);
      }

      if (statsResponse.success && statsResponse.data) {
        setStats(statsResponse.data);
      }
    } catch (error: any) {
      console.error('Erreur chargement pièces jointes:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les pièces jointes",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (open && task) {
      loadAttachments();
    }
  }, [open, task]);

  // Upload de fichiers
  const handleUpload = async () => {
    if (!task || uploadForm.files.length === 0) return;

    try {
      setUploading(true);
      
      for (const file of uploadForm.files) {
        const formData = new FormData();
        formData.append('fichier', file);
        formData.append('description', uploadForm.description);
        formData.append('type_document', uploadForm.type_document);
        formData.append('est_justificatif', uploadForm.est_justificatif ? '1' : '0');

        const response = await apiClient.uploadTaskAttachment(task.id, formData);
        
        if (response.success && response.data) {
          setAttachments(prev => [response.data, ...prev]);
        }
      }

      // Réinitialiser le formulaire
      setUploadForm({
        files: [],
        description: "",
        type_document: "piece_jointe",
        est_justificatif: false
      });

      // Recharger les statistiques
      loadAttachments();

      toast({
        title: "Succès",
        description: `${uploadForm.files.length} fichier(s) uploadé(s) avec succès`,
      });

      onSuccess?.();
    } catch (error: any) {
      console.error('Erreur upload:', error);
      toast({
        title: "Erreur",
        description: error.response?.data?.message || "Erreur lors de l'upload",
        variant: "destructive",
      });
    } finally {
      setUploading(false);
    }
  };

  // Télécharger un fichier
  const handleDownload = async (attachment: TaskAttachment) => {
    if (!task) return;

    try {
      const blob = await apiClient.downloadTaskAttachment(task.id, attachment.id);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = attachment.nom_original;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error: any) {
      console.error('Erreur téléchargement:', error);
      toast({
        title: "Erreur",
        description: "Impossible de télécharger le fichier",
        variant: "destructive",
      });
    }
  };

  // Supprimer un fichier
  const handleDelete = async (attachment: TaskAttachment) => {
    if (!task) return;

    if (!confirm(`Êtes-vous sûr de vouloir supprimer "${attachment.nom_original}" ?`)) {
      return;
    }

    try {
      const response = await apiClient.deleteTaskAttachment(task.id, attachment.id);
      
      if (response.success) {
        setAttachments(prev => prev.filter(a => a.id !== attachment.id));
        loadAttachments(); // Recharger les stats
        toast({
          title: "Succès",
          description: "Fichier supprimé avec succès",
        });
        onSuccess?.();
      }
    } catch (error: any) {
      console.error('Erreur suppression:', error);
      toast({
        title: "Erreur",
        description: "Impossible de supprimer le fichier",
        variant: "destructive",
      });
    }
  };

  // Gestion des fichiers sélectionnés
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    setUploadForm(prev => ({ ...prev, files }));
  };

  // Fonction pour obtenir l'icône selon le type MIME
  const getFileIcon = (mimeType: string) => {
    const iconName = getFileIconName(mimeType);
    const iconProps = { className: "h-8 w-8" };
    
    switch (iconName) {
      case 'Image':
        return <Image {...iconProps} className="h-8 w-8 text-blue-500" />;
      case 'Video':
        return <Video {...iconProps} className="h-8 w-8 text-purple-500" />;
      case 'Music':
        return <Music {...iconProps} className="h-8 w-8 text-green-500" />;
      case 'FileText':
        return <FileText {...iconProps} className="h-8 w-8 text-red-500" />;
      case 'Archive':
        return <Archive {...iconProps} className="h-8 w-8 text-orange-500" />;
      default:
        return <File {...iconProps} className="h-8 w-8 text-gray-500" />;
    }
  };

  if (!task) return null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Paperclip className="h-5 w-5" />
            Pièces jointes - {task.titre}
          </DialogTitle>
        </DialogHeader>

        <div className="space-y-6">
          {/* Statistiques */}
          {stats && (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">{stats.total_fichiers}</div>
                <div className="text-sm text-gray-600">Fichiers</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">{stats.total_justificatifs}</div>
                <div className="text-sm text-gray-600">Justificatifs</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-purple-600">{formatFileSize(stats.taille_totale)}</div>
                <div className="text-sm text-gray-600">Taille totale</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-orange-600">
                  {stats.dernier_upload ? 'Oui' : 'Non'}
                </div>
                <div className="text-sm text-gray-600">Dernier upload</div>
              </div>
            </div>
          )}

          {/* Formulaire d'upload */}
          <div className="border border-gray-200 rounded-lg p-4">
            <h3 className="font-medium text-gray-900 mb-4">Ajouter des fichiers</h3>
            
            <div className="space-y-4">
              {/* Sélection de fichiers */}
              <div>
                <Label htmlFor="files">Fichiers</Label>
                <Input
                  id="files"
                  type="file"
                  multiple
                  onChange={handleFileChange}
                  accept="*/*"
                  className="mt-1"
                />
                {uploadForm.files.length > 0 && (
                  <div className="mt-2 space-y-1">
                    {uploadForm.files.map((file, index) => (
                      <div key={index} className="flex items-center gap-2 text-sm text-gray-600">
                        <File className="h-4 w-4" />
                        <span>{file.name}</span>
                        <span className="text-gray-400">({formatFileSize(file.size)})</span>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Type de document */}
              <div>
                <Label htmlFor="type_document">Type de document</Label>
                <SearchableSelect
                  options={ATTACHMENT_TYPES.map((type) => ({
                    value: type.value,
                    label: type.label
                  }))}
                  value={uploadForm.type_document}
                  onValueChange={(value: any) => setUploadForm(prev => ({ ...prev, type_document: value }))}
                  placeholder="Sélectionner un type"
                  searchPlaceholder="Rechercher un type..."
                />
              </div>

              {/* Description */}
              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={uploadForm.description}
                  onChange={(e) => setUploadForm(prev => ({ ...prev, description: e.target.value }))}
                  placeholder="Description des fichiers..."
                  rows={2}
                />
              </div>

              {/* Justificatif */}
              <div className="flex items-center space-x-2">
                <input
                  type="checkbox"
                  id="est_justificatif"
                  checked={uploadForm.est_justificatif}
                  onChange={(e) => setUploadForm(prev => ({ ...prev, est_justificatif: e.target.checked }))}
                  className="rounded"
                />
                <Label htmlFor="est_justificatif">Marquer comme justificatif</Label>
              </div>

              {/* Bouton upload */}
              <Button
                onClick={handleUpload}
                disabled={uploading || uploadForm.files.length === 0}
                className="w-full"
              >
                {uploading ? (
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                ) : (
                  <Upload className="h-4 w-4 mr-2" />
                )}
                {uploading ? "Upload en cours..." : `Uploader ${uploadForm.files.length} fichier(s)`}
              </Button>
            </div>
          </div>

          {/* Liste des pièces jointes */}
          <div>
            <h3 className="font-medium text-gray-900 mb-4">
              Pièces jointes ({attachments.length})
            </h3>

            {loading ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
              </div>
            ) : attachments.length === 0 ? (
              <div className="text-center py-8 text-gray-500">
                <Paperclip className="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>Aucune pièce jointe</p>
              </div>
            ) : (
              <div className="space-y-2">
                {attachments.map((attachment) => (
                  <div
                    key={attachment.id}
                    className="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                  >
                    <div className="flex items-center gap-3 flex-1">
                      {getFileIcon(attachment.mime_type || '')}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                          <p className="font-medium text-gray-900 truncate">
                            {attachment.nom_original}
                          </p>
                          {attachment.est_justificatif && (
                            <Badge variant="secondary" className="text-xs">
                              Justificatif
                            </Badge>
                          )}
                          <Badge variant="outline" className="text-xs">
                            {ATTACHMENT_TYPES.find(t => t.value === attachment.type_document)?.label || 'Autre'}
                          </Badge>
                        </div>
                        <div className="flex items-center gap-4 text-sm text-gray-500 mt-1">
                          <span>{formatFileSize(attachment.taille)}</span>
                          <span>•</span>
                          <span>{attachment.user ? `${attachment.user.prenom} ${attachment.user.nom}` : 'Utilisateur inconnu'}</span>
                          <span>•</span>
                          <span>{new Date(attachment.date_creation).toLocaleDateString('fr-FR')}</span>
                        </div>
                        {attachment.description && (
                          <p className="text-sm text-gray-600 mt-1">{attachment.description}</p>
                        )}
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleDownload(attachment)}
                      >
                        <Download className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleDelete(attachment)}
                        className="text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
} 