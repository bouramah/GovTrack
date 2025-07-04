"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Label } from "@/components/ui/label";
import { MessageSquare, Send, Edit, Trash2, Reply } from "lucide-react";
import { useToast } from "@/components/ui/use-toast";
import { useAuth } from "@/contexts/AuthContext";
import { apiClient } from "@/lib/api";
import type { TacheDiscussion } from "@/types/tache";
import type { DiscussionCreateRequest, DiscussionUpdateRequest } from "@/types/discussion";

interface TaskDiscussionsListProps {
  taskId: number;
  onRefresh?: () => void;
}

export default function TaskDiscussionsList({ taskId, onRefresh }: TaskDiscussionsListProps) {
  const { toast } = useToast();
  const { user } = useAuth();
  const [discussions, setDiscussions] = useState<TacheDiscussion[]>([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<any>(null);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [editDialogOpen, setEditDialogOpen] = useState(false);
  const [replyDialogOpen, setReplyDialogOpen] = useState(false);
  const [selectedDiscussion, setSelectedDiscussion] = useState<TacheDiscussion | null>(null);
  const [newMessage, setNewMessage] = useState('');
  const [replyMessage, setReplyMessage] = useState('');
  const [editMessage, setEditMessage] = useState('');

  useEffect(() => {
    loadDiscussions();
    loadStats();
  }, [taskId]);

  const loadDiscussions = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getTacheDiscussions(taskId, {
        sort_order: 'desc',
        per_page: 50
      });
      if (response.success && response.data) {
        setDiscussions(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement discussions:', error);
      toast({
        title: '❌ Erreur',
        description: 'Impossible de charger les discussions.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const loadStats = async () => {
    try {
      const response = await apiClient.getTaskDiscussionsStats(taskId);
      if (response.success && response.data) {
        setStats(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement statistiques:', error);
    }
  };

  const handleCreateDiscussion = async () => {
    if (!newMessage.trim()) {
      toast({
        title: '❌ Erreur',
        description: 'Le message ne peut pas être vide.',
        variant: 'destructive',
      });
      return;
    }

    try {
      const data: DiscussionCreateRequest = {
        message: newMessage.trim()
      };

      await apiClient.createTacheDiscussion(taskId, data);
      
      toast({
        title: '✅ Message posté',
        description: 'Votre message a été publié avec succès.',
      });

      setNewMessage('');
      loadDiscussions();
      loadStats();
      onRefresh?.();
    } catch (error: any) {
      console.error('Erreur création discussion:', error);
      
      let errorMessage = 'Erreur lors de la publication du message.';
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

  const handleReply = async () => {
    if (!selectedDiscussion || !replyMessage.trim()) {
      toast({
        title: '❌ Erreur',
        description: 'Le message ne peut pas être vide.',
        variant: 'destructive',
      });
      return;
    }

    try {
      const data: DiscussionCreateRequest = {
        message: replyMessage.trim(),
        parent_id: selectedDiscussion.id
      };

      await apiClient.createTacheDiscussion(taskId, data);
      
      toast({
        title: '✅ Réponse postée',
        description: 'Votre réponse a été publiée avec succès.',
      });

      setReplyMessage('');
      setReplyDialogOpen(false);
      setSelectedDiscussion(null);
      loadDiscussions();
      loadStats();
      onRefresh?.();
    } catch (error: any) {
      console.error('Erreur réponse:', error);
      
      let errorMessage = 'Erreur lors de la publication de la réponse.';
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

  const handleEdit = (discussion: TacheDiscussion) => {
    setSelectedDiscussion(discussion);
    setEditMessage(discussion.message);
    setEditDialogOpen(true);
  };

  const handleEditSubmit = async () => {
    if (!selectedDiscussion || !editMessage.trim()) return;

    try {
      await apiClient.updateTaskDiscussion(taskId, selectedDiscussion.id, {
        message: editMessage.trim()
      });
      
      toast({
        title: '✅ Modifié',
        description: 'Le message a été modifié avec succès.',
      });

      setEditDialogOpen(false);
      setSelectedDiscussion(null);
      setEditMessage('');
      loadDiscussions();
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

  const handleDelete = (discussion: TacheDiscussion) => {
    setSelectedDiscussion(discussion);
    setDeleteDialogOpen(true);
  };

  const handleDeleteConfirm = async () => {
    if (!selectedDiscussion) return;

    try {
      await apiClient.deleteTaskDiscussion(taskId, selectedDiscussion.id);
      
      toast({
        title: '✅ Supprimé',
        description: 'Le message a été supprimé avec succès.',
      });

      setDeleteDialogOpen(false);
      setSelectedDiscussion(null);
      loadDiscussions();
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

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const canEdit = (discussion: TacheDiscussion) => {
    return user && discussion.user_id === user.id;
  };

  const canDelete = (discussion: TacheDiscussion) => {
    return user && discussion.user_id === user.id && (!discussion.reponses || discussion.reponses.length === 0);
  };

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-2 text-sm text-gray-600">Chargement des discussions...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Statistiques */}
      {stats && (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Statistiques des discussions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">{stats.total_messages}</div>
                <div className="text-sm text-gray-600">Messages totaux</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">{stats.messages_racine}</div>
                <div className="text-sm text-gray-600">Messages principaux</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-purple-600">{stats.reponses}</div>
                <div className="text-sm text-gray-600">Réponses</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-orange-600">{stats.participants}</div>
                <div className="text-sm text-gray-600">Participants</div>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Nouveau message */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <MessageSquare className="h-5 w-5" />
            Nouveau message
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div>
              <Label htmlFor="new-message">Votre message</Label>
              <Textarea
                id="new-message"
                placeholder="Partagez vos idées, questions ou commentaires..."
                value={newMessage}
                onChange={(e) => setNewMessage(e.target.value)}
                rows={3}
              />
            </div>
            <div className="flex justify-end">
              <Button onClick={handleCreateDiscussion} disabled={!newMessage.trim()}>
                <Send className="h-4 w-4 mr-2" />
                Publier
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Liste des discussions */}
      <Card>
        <CardHeader>
          <CardTitle>Discussions ({discussions.length})</CardTitle>
          <CardDescription>
            Messages et réponses sur cette tâche
          </CardDescription>
        </CardHeader>
        <CardContent>
          {discussions.length > 0 ? (
            <div className="space-y-6">
              {discussions.map((discussion) => (
                <div key={discussion.id} className="space-y-4">
                  {/* Message principal */}
                  <div className="border rounded-lg p-4 bg-white">
                    <div className="flex items-start space-x-3">
                      <Avatar className="h-10 w-10">
                        <AvatarFallback className="bg-blue-100 text-blue-700">
                          {discussion.user ? getInitials(`${discussion.user.prenom} ${discussion.user.nom}`) : 'U'}
                        </AvatarFallback>
                      </Avatar>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between mb-2">
                          <div className="flex items-center space-x-2">
                            <span className="font-medium">
                              {discussion.user ? `${discussion.user.prenom} ${discussion.user.nom}` : 'Utilisateur'}
                            </span>
                            {discussion.est_modifie && (
                              <Badge variant="outline" className="text-xs">
                                Modifié
                              </Badge>
                            )}
                          </div>
                          <div className="flex items-center space-x-2">
                            <span className="text-sm text-gray-500">
                              {formatDate(discussion.date_creation)}
                            </span>
                            <div className="flex items-center space-x-1">
                              {canEdit(discussion) && (
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  onClick={() => handleEdit(discussion)}
                                  className="h-8 w-8 p-0"
                                >
                                  <Edit className="h-4 w-4" />
                                </Button>
                              )}
                              {canDelete(discussion) && (
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  onClick={() => handleDelete(discussion)}
                                  className="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                >
                                  <Trash2 className="h-4 w-4" />
                                </Button>
                              )}
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => {
                                  setSelectedDiscussion(discussion);
                                  setReplyDialogOpen(true);
                                }}
                                className="h-8 w-8 p-0"
                              >
                                <Reply className="h-4 w-4" />
                              </Button>
                            </div>
                          </div>
                        </div>
                        <div className="text-sm text-gray-700 whitespace-pre-wrap">
                          {discussion.message}
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Réponses */}
                  {discussion.reponses && discussion.reponses.length > 0 && (
                    <div className="ml-8 space-y-3">
                      {discussion.reponses.map((reply) => (
                        <div key={reply.id} className="border rounded-lg p-3 bg-gray-50">
                          <div className="flex items-start space-x-3">
                            <Avatar className="h-8 w-8">
                              <AvatarFallback className="bg-gray-200 text-gray-600 text-xs">
                                {reply.user ? getInitials(`${reply.user.prenom} ${reply.user.nom}`) : 'U'}
                              </AvatarFallback>
                            </Avatar>
                            <div className="flex-1 min-w-0">
                              <div className="flex items-center justify-between mb-1">
                                <div className="flex items-center space-x-2">
                                  <span className="font-medium text-sm">
                                    {reply.user ? `${reply.user.prenom} ${reply.user.nom}` : 'Utilisateur'}
                                  </span>
                                  {reply.est_modifie && (
                                    <Badge variant="outline" className="text-xs">
                                      Modifié
                                    </Badge>
                                  )}
                                </div>
                                <div className="flex items-center space-x-2">
                                  <span className="text-xs text-gray-500">
                                    {formatDate(reply.date_creation)}
                                  </span>
                                  <div className="flex items-center space-x-1">
                                    {canEdit(reply) && (
                                      <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleEdit(reply)}
                                        className="h-6 w-6 p-0"
                                      >
                                        <Edit className="h-3 w-3" />
                                      </Button>
                                    )}
                                    {canDelete(reply) && (
                                      <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleDelete(reply)}
                                        className="h-6 w-6 p-0 text-red-600 hover:text-red-700"
                                      >
                                        <Trash2 className="h-3 w-3" />
                                      </Button>
                                    )}
                                  </div>
                                </div>
                              </div>
                              <div className="text-sm text-gray-700 whitespace-pre-wrap">
                                {reply.message}
                              </div>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8">
              <MessageSquare className="h-12 w-12 text-gray-300 mx-auto mb-4" />
              <p className="text-gray-500 mb-2">Aucune discussion pour cette tâche</p>
              <p className="text-sm text-gray-400">
                Commencez la conversation en publiant le premier message
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Modal de réponse */}
      <Dialog open={replyDialogOpen} onOpenChange={setReplyDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Répondre au message</DialogTitle>
            <DialogDescription>
              Répondez au message de {selectedDiscussion?.user ? `${selectedDiscussion.user.prenom} ${selectedDiscussion.user.nom}` : 'l\'utilisateur'}
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="reply-message">Votre réponse</Label>
              <Textarea
                id="reply-message"
                placeholder="Écrivez votre réponse..."
                value={replyMessage}
                onChange={(e) => setReplyMessage(e.target.value)}
                rows={3}
              />
            </div>
            <div className="flex justify-end space-x-2">
              <Button variant="outline" onClick={() => setReplyDialogOpen(false)}>
                Annuler
              </Button>
              <Button onClick={handleReply} disabled={!replyMessage.trim()}>
                <Send className="h-4 w-4 mr-2" />
                Répondre
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal de modification */}
      <Dialog open={editDialogOpen} onOpenChange={setEditDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Modifier le message</DialogTitle>
            <DialogDescription>
              Modifiez votre message
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="edit-message">Message</Label>
              <Textarea
                id="edit-message"
                placeholder="Modifiez votre message..."
                value={editMessage}
                onChange={(e) => setEditMessage(e.target.value)}
                rows={3}
              />
            </div>
            <div className="flex justify-end space-x-2">
              <Button variant="outline" onClick={() => setEditDialogOpen(false)}>
                Annuler
              </Button>
              <Button onClick={handleEditSubmit} disabled={!editMessage.trim()}>
                <Edit className="h-4 w-4 mr-2" />
                Modifier
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal de suppression */}
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Supprimer le message</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction onClick={handleDeleteConfirm} className="bg-red-600 hover:bg-red-700">
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
} 