"use client";

import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import type { Tache } from "@/types/tache";
import TacheDetailModal from "../tache-detail-modal";

interface TacheModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  tache: Tache;
  onTacheUpdate?: (tache: Tache) => void;
  onTacheDelete?: (tacheId: number) => void;
}

export default function TacheModal({
  open,
  onOpenChange,
  tache,
  onTacheUpdate,
  onTacheDelete,
}: TacheModalProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-xl font-semibold">
            {tache.titre}
          </DialogTitle>
        </DialogHeader>
        <TacheDetailModal tache={tache} />
      </DialogContent>
    </Dialog>
  );
} 