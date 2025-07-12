'use client';

import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { X } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import ProjectAttachmentUpload from './ProjectAttachmentUpload';

interface ProjectAttachmentUploadModalProps {
  projectId: number;
  projectTitle: string;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onRefresh?: () => void;
}

export default function ProjectAttachmentUploadModal({
  projectId,
  projectTitle,
  open,
  onOpenChange,
  onRefresh
}: ProjectAttachmentUploadModalProps) {
  const handleUploadSuccess = () => {
    onOpenChange(false);
    onRefresh?.();
  };

  const handleClose = () => {
    onOpenChange(false);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        <DialogHeader>
          <DialogTitle className="flex items-center justify-between">
            <span>Ajouter un fichier - {projectTitle}</span>
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
            Téléchargez un nouveau fichier pour ce projet
          </DialogDescription>
        </DialogHeader>

        <div className="mt-4 flex-1 overflow-y-auto">
          <ProjectAttachmentUpload
            projectId={projectId}
            onUploadSuccess={handleUploadSuccess}
            onCancel={handleClose}
          />
        </div>
      </DialogContent>
    </Dialog>
  );
} 