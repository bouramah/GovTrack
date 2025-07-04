'use client';

import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Plus, X } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import ProjectAttachmentUpload from './ProjectAttachmentUpload';
import ProjectAttachmentsList from './ProjectAttachmentsList';

interface ProjectAttachmentsModalProps {
  projectId: number;
  projectTitle: string;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onRefresh?: () => void;
}

export default function ProjectAttachmentsModal({
  projectId,
  projectTitle,
  open,
  onOpenChange,
  onRefresh
}: ProjectAttachmentsModalProps) {
  const [activeTab, setActiveTab] = useState('list');

  const handleUploadSuccess = () => {
    setActiveTab('list');
    onRefresh?.();
  };

  const handleClose = () => {
    setActiveTab('list');
    onOpenChange(false);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-hidden">
        <DialogHeader>
          <DialogTitle className="flex items-center justify-between">
            <span>Pièces jointes - {projectTitle}</span>
            <Button
              variant="ghost"
              size="sm"
              onClick={handleClose}
              className="h-8 w-8 p-0"
            >
              <X className="h-4 w-4" />
            </Button>
          </DialogTitle>
          <DialogDescription>
            Gérez les fichiers attachés à ce projet
          </DialogDescription>
        </DialogHeader>

        <Tabs value={activeTab} onValueChange={setActiveTab} className="flex-1 overflow-hidden">
          <TabsList className="grid w-full grid-cols-2">
            <TabsTrigger value="list">Liste des fichiers</TabsTrigger>
            <TabsTrigger value="upload" className="flex items-center gap-2">
              <Plus className="h-4 w-4" />
              Ajouter un fichier
            </TabsTrigger>
          </TabsList>

          <div className="mt-4 overflow-hidden">
            <TabsContent value="list" className="h-full overflow-y-auto">
              <ProjectAttachmentsList 
                projectId={projectId} 
                onRefresh={onRefresh}
              />
            </TabsContent>

            <TabsContent value="upload" className="h-full overflow-y-auto">
              <ProjectAttachmentUpload
                projectId={projectId}
                onUploadSuccess={handleUploadSuccess}
                onCancel={() => setActiveTab('list')}
              />
            </TabsContent>
          </div>
        </Tabs>
      </DialogContent>
    </Dialog>
  );
} 