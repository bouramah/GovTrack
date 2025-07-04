"use client";

import { useDrop } from "react-dnd";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";
import type { Tache, TacheStatut } from "@/types/tache";
import TacheKanbanCard from "./tache-kanban-card";

interface TacheKanbanColumnProps {
  title: string;
  statut: TacheStatut;
  taches: Tache[];
  onTacheMove: (tacheId: number, nouveauStatut: TacheStatut) => void;
  onTaskUpdate?: (task: Tache) => void;
  onTaskDelete?: (taskId: number) => void;
  colorClass: string;
}

export default function TacheKanbanColumn({
  title,
  statut,
  taches,
  onTacheMove,
  onTaskUpdate,
  onTaskDelete,
  colorClass,
}: TacheKanbanColumnProps) {
  // Configuration du drop target
  const [{ isOver }, drop] = useDrop({
    accept: "tache-card",
    drop: (item: { id: number }) => {
      onTacheMove(item.id, statut);
    },
    collect: (monitor) => ({
      isOver: !!monitor.isOver(),
    }),
  });

  return (
    <div
      ref={drop as any}
      className={cn(
        "rounded-lg border border-gray-200 flex flex-col h-[calc(100vh-13rem)]",
        isOver ? "ring-2 ring-blue-400 ring-opacity-50" : "",
        colorClass.replace('text-', 'bg-').replace('-800', '-50').replace('-200', '-100')
      )}
    >
      {/* En-tête de la colonne */}
      <div className="p-3 border-b border-gray-200 bg-white bg-opacity-80">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <h3 className="font-medium text-gray-900">{title}</h3>
            <Badge 
              variant="secondary" 
              className="ml-2 text-xs font-medium"
            >
              {taches.length}
            </Badge>
          </div>
        </div>
      </div>

      {/* Liste des tâches */}
      <div className="flex-1 overflow-y-auto p-3 space-y-3">
        {taches.map((tache) => (
          <TacheKanbanCard 
            key={tache.id} 
            tache={tache}
            onTaskUpdate={onTaskUpdate}
            onTaskDelete={() => onTaskDelete?.(tache.id)}
          />
        ))}
        
        {taches.length === 0 && (
          <div className="flex items-center justify-center h-20 border border-dashed border-gray-300 rounded-lg bg-white bg-opacity-50">
            <p className="text-sm text-gray-500">Aucune tâche</p>
          </div>
        )}
      </div>
    </div>
  );
} 