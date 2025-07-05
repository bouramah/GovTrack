"use client";

import { useState } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useProjetPermissions } from "@/hooks/useProjetPermissions";
import { Button } from "@/components/ui/button";
import { Plus, FileText, MessageSquare, History, Paperclip } from "lucide-react";

interface ProjectTabsProps {
  projectId: string;
  project: any;
}

export default function ProjectTabs({ projectId, project }: ProjectTabsProps) {
  const permissions = useProjetPermissions();
  const [activeTab, setActiveTab] = useState("overview");

  // Déterminer quels onglets sont accessibles
  const availableTabs = [
    {
      id: "overview",
      label: "Vue d'ensemble",
      icon: FileText,
      accessible: permissions.canViewDetails,
    },
    {
      id: "tasks",
      label: "Tâches",
      icon: FileText,
      accessible: permissions.canAccessTasksTab,
    },
    {
      id: "attachments",
      label: "Pièces jointes",
      icon: Paperclip,
      accessible: permissions.canAccessAttachmentsTab,
    },
    {
      id: "comments",
      label: "Commentaires",
      icon: MessageSquare,
      accessible: permissions.canAccessCommentsTab,
    },
    {
      id: "history",
      label: "Historique",
      icon: History,
      accessible: permissions.canAccessHistoryTab,
    },
  ].filter(tab => tab.accessible);

  if (availableTabs.length === 0) {
    return (
      <div className="text-center py-8">
        <p className="text-gray-500">
          Vous n'avez pas les permissions nécessaires pour consulter ce projet.
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-5">
          {availableTabs.map((tab) => (
            <TabsTrigger
              key={tab.id}
              value={tab.id}
              className="flex items-center gap-2"
            >
              <tab.icon className="h-4 w-4" />
              {tab.label}
            </TabsTrigger>
          ))}
        </TabsList>

        {/* Vue d'ensemble */}
        {permissions.canViewDetails && (
          <TabsContent value="overview" className="space-y-4">
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-4">Informations du projet</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-500">Titre</p>
                  <p className="font-medium">{project?.titre}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Statut</p>
                  <p className="font-medium">{project?.statut}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Niveau d'exécution</p>
                  <p className="font-medium">{project?.niveau_execution}%</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Porteur</p>
                  <p className="font-medium">
                    {project?.porteur?.prenom} {project?.porteur?.nom}
                  </p>
                </div>
              </div>
              
              {/* Actions rapides */}
              <div className="mt-6 flex gap-2">
                {permissions.canChangeStatus && (
                  <Button variant="outline" size="sm">
                    Changer le statut
                  </Button>
                )}
                {permissions.canUpdateExecutionLevel && (
                  <Button variant="outline" size="sm">
                    Mettre à jour le niveau
                  </Button>
                )}
                {permissions.canEdit && (
                  <Button variant="outline" size="sm">
                    Modifier le projet
                  </Button>
                )}
              </div>
            </div>
          </TabsContent>
        )}

        {/* Tâches */}
        {permissions.canAccessTasksTab && (
          <TabsContent value="tasks" className="space-y-4">
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Tâches du projet</h3>
                {permissions.canCreateTask && (
                  <Button size="sm">
                    <Plus className="h-4 w-4 mr-2" />
                    Nouvelle tâche
                  </Button>
                )}
              </div>
              <p className="text-gray-500">
                Liste des tâches du projet (à implémenter)
              </p>
            </div>
          </TabsContent>
        )}

        {/* Pièces jointes */}
        {permissions.canAccessAttachmentsTab && (
          <TabsContent value="attachments" className="space-y-4">
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Pièces jointes</h3>
                {permissions.canAddAttachment && (
                  <Button size="sm">
                    <Plus className="h-4 w-4 mr-2" />
                    Ajouter un fichier
                  </Button>
                )}
              </div>
              <p className="text-gray-500">
                Liste des pièces jointes (à implémenter)
              </p>
            </div>
          </TabsContent>
        )}

        {/* Commentaires */}
        {permissions.canAccessCommentsTab && (
          <TabsContent value="comments" className="space-y-4">
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Commentaires</h3>
                {permissions.canAddComment && (
                  <Button size="sm">
                    <Plus className="h-4 w-4 mr-2" />
                    Nouveau commentaire
                  </Button>
                )}
              </div>
              <p className="text-gray-500">
                Liste des commentaires (à implémenter)
              </p>
            </div>
          </TabsContent>
        )}

        {/* Historique */}
        {permissions.canAccessHistoryTab && (
          <TabsContent value="history" className="space-y-4">
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-4">Historique du projet</h3>
              <p className="text-gray-500">
                Historique des modifications (à implémenter)
              </p>
            </div>
          </TabsContent>
        )}
      </Tabs>
    </div>
  );
} 