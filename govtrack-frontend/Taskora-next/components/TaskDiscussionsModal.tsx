"use client";

import { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { MessageSquare, Send, Edit, Trash2, Reply, X } from "lucide-react";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import { cn } from "@/lib/utils";
import { apiClient } from "@/lib/api";
import { useAuth } from "@/contexts/AuthContext";
import type { TacheDiscussion } from "@/types/tache";

interface TaskDiscussionsModalProps {
  isOpen: boolean;
  onClose: () => void;
  tacheId: number;
  tacheTitre: string;
}

interface DiscussionWithReplies extends TacheDiscussion {
  reponses?: DiscussionWithReplies[];
}

export default function TaskDiscussionsModal({ 
  isOpen, 
  onClose, 
  tacheId, 
  tacheTitre 
}: TaskDiscussionsModalProps) {
  const { user } = useAuth();
  const [discussions, setDiscussions] = useState<DiscussionWithReplies[]>([]);
  const [loading, setLoading] = useState(false);
  const [sending, setSending] = useState(false);
  const [newMessage, setNewMessage] = useState("");
  const [editingMessage, setEditingMessage] = useState<number | null>(null);
  const [editText, setEditText] = useState("");
  const [replyingTo, setReplyingTo] = useState<number | null>(null);
  const [replyText, setReplyText] = useState("");

  // Fonction pour obtenir les initiales
  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  };

  // Charger les discussions
  const loadDiscussions = async () => {
    setLoading(true);
    try {
      const response = await apiClient.getTacheDiscussions(tacheId);
      if (response.success) {
        setDiscussions(response.data || []);
      }
    } catch (error) {
      console.error("Erreur lors du chargement des discussions:", error);
    } finally {
      setLoading(false);
    }
  };

  // Poster un nouveau message
  const postMessage = async () => {
    if (!newMessage.trim()) return;
    
    setSending(true);
    try {
      const response = await apiClient.createTacheDiscussion(tacheId, {
        message: newMessage.trim()
      });
      
      if (response.success) {
        setNewMessage("");
        await loadDiscussions(); // Recharger les discussions
      }
    } catch (error) {
      console.error("Erreur lors de l'envoi du message:", error);
    } finally {
      setSending(false);
    }
  };

  // Poster une réponse
  const postReply = async (parentId: number) => {
    if (!replyText.trim()) return;
    
    setSending(true);
    try {
      const response = await apiClient.createTacheDiscussion(tacheId, {
        message: replyText.trim(),
        parent_id: parentId
      });
      
      if (response.success) {
        setReplyText("");
        setReplyingTo(null);
        await loadDiscussions(); // Recharger les discussions
      }
    } catch (error) {
      console.error("Erreur lors de l'envoi de la réponse:", error);
    } finally {
      setSending(false);
    }
  };

  // Modifier un message
  const updateMessage = async (messageId: number) => {
    if (!editText.trim()) return;
    
    setSending(true);
    try {
      const response = await apiClient.updateTaskDiscussion(tacheId, messageId, {
        message: editText.trim()
      });
      
      if (response.success) {
        setEditText("");
        setEditingMessage(null);
        await loadDiscussions(); // Recharger les discussions
      }
    } catch (error) {
      console.error("Erreur lors de la modification du message:", error);
    } finally {
      setSending(false);
    }
  };

  // Supprimer un message
  const deleteMessage = async (messageId: number) => {
    if (!confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) return;
    
    try {
      const response = await apiClient.deleteTaskDiscussion(tacheId, messageId);
      
      if (response.success) {
        await loadDiscussions(); // Recharger les discussions
      }
    } catch (error) {
      console.error("Erreur lors de la suppression du message:", error);
    }
  };

  // Charger les discussions au montage et quand le modal s'ouvre
  useEffect(() => {
    if (isOpen) {
      loadDiscussions();
    }
  }, [isOpen, tacheId]);

  // Rendu d'un message avec ses réponses
  const renderMessage = (message: DiscussionWithReplies, isReply = false) => {
    const isEditing = editingMessage === message.id;
    const isReplying = replyingTo === message.id;

    return (
      <div key={message.id} className={cn("space-y-3", isReply && "ml-8 border-l-2 border-gray-200 pl-4")}>
        <div className="flex items-start space-x-3">
          <Avatar className="h-8 w-8 flex-shrink-0">
            <AvatarFallback className="bg-blue-100 text-blue-700 text-sm">
              {message.user && getInitials(`${message.user.prenom} ${message.user.nom}`)}
            </AvatarFallback>
          </Avatar>
          
          <div className="flex-1 min-w-0">
            <div className="flex items-center space-x-2 mb-1">
              <span className="text-sm font-medium text-gray-900">
                {message.user ? `${message.user.prenom} ${message.user.nom}` : 'Utilisateur'}
              </span>
              <span className="text-xs text-gray-500">
                {format(new Date(message.date_creation), "dd/MM/yyyy à HH:mm", { locale: fr })}
              </span>
              {message.est_modifie && (
                <Badge variant="outline" className="text-xs">
                  Modifié
                </Badge>
              )}
            </div>
            
            {isEditing ? (
              <div className="space-y-2">
                <Textarea
                  value={editText}
                  onChange={(e) => setEditText(e.target.value)}
                  placeholder="Modifier votre message..."
                  className="min-h-[80px]"
                />
                <div className="flex space-x-2">
                  <Button 
                    size="sm" 
                    onClick={() => updateMessage(message.id)}
                    disabled={sending}
                  >
                    {sending ? "Modification..." : "Modifier"}
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline"
                    onClick={() => {
                      setEditingMessage(null);
                      setEditText("");
                    }}
                  >
                    Annuler
                  </Button>
                </div>
              </div>
            ) : (
              <div className="space-y-2">
                <p className="text-sm text-gray-700 whitespace-pre-wrap">{message.message}</p>
                
                {!isReply && (
                  <div className="flex items-center space-x-2">
                    <Button
                      size="sm"
                      variant="ghost"
                      onClick={() => setReplyingTo(message.id)}
                      className="h-6 px-2 text-xs"
                    >
                      <Reply className="h-3 w-3 mr-1" />
                      Répondre
                    </Button>
                                         {message.user?.id === user?.id && (
                      <>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => {
                            setEditingMessage(message.id);
                            setEditText(message.message);
                          }}
                          className="h-6 px-2 text-xs"
                        >
                          <Edit className="h-3 w-3 mr-1" />
                          Modifier
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => deleteMessage(message.id)}
                          className="h-6 px-2 text-xs text-red-600 hover:text-red-700"
                        >
                          <Trash2 className="h-3 w-3 mr-1" />
                          Supprimer
                        </Button>
                      </>
                    )}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>

        {/* Zone de réponse */}
        {isReplying && (
          <div className="ml-11 space-y-2">
            <Textarea
              value={replyText}
              onChange={(e) => setReplyText(e.target.value)}
              placeholder="Écrire une réponse..."
              className="min-h-[80px]"
            />
            <div className="flex space-x-2">
              <Button 
                size="sm" 
                onClick={() => postReply(message.id)}
                disabled={sending}
              >
                {sending ? "Envoi..." : "Répondre"}
              </Button>
              <Button 
                size="sm" 
                variant="outline"
                onClick={() => {
                  setReplyingTo(null);
                  setReplyText("");
                }}
              >
                Annuler
              </Button>
            </div>
          </div>
        )}

        {/* Réponses */}
        {message.reponses && message.reponses.length > 0 && (
          <div className="space-y-3">
            {message.reponses.map((reply) => renderMessage(reply, true))}
          </div>
        )}
      </div>
    );
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl h-[90vh] flex flex-col p-0">
        <DialogHeader className="px-6 py-4 border-b border-gray-200 flex-shrink-0">
          <DialogTitle className="flex items-center space-x-2">
            <MessageSquare className="h-5 w-5" />
            <span>Discussions - {tacheTitre}</span>
          </DialogTitle>
        </DialogHeader>

        <div className="flex-1 flex flex-col min-h-0">
          {/* Zone de saisie du nouveau message */}
          <div className="border-b border-gray-200 p-4 flex-shrink-0">
            <div className="space-y-2">
              <Textarea
                value={newMessage}
                onChange={(e) => setNewMessage(e.target.value)}
                placeholder="Écrire un nouveau message..."
                className="min-h-[80px] max-h-[120px] resize-none"
                onKeyDown={(e) => {
                  if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
                    e.preventDefault();
                    postMessage();
                  }
                }}
              />
              <div className="flex justify-between items-center">
                <span className="text-xs text-gray-500">
                  Appuyez sur Cmd+Entrée pour envoyer
                </span>
                <Button 
                  onClick={postMessage}
                  disabled={!newMessage.trim() || sending}
                  className="flex items-center space-x-2"
                >
                  <Send className="h-4 w-4" />
                  <span>{sending ? "Envoi..." : "Envoyer"}</span>
                </Button>
              </div>
            </div>
          </div>

          {/* Liste des discussions */}
          <div className="flex-1 overflow-y-auto p-4 space-y-4 min-h-0">
            {loading ? (
              <div className="text-center py-8 text-gray-500">
                <MessageSquare className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                <p>Chargement des discussions...</p>
              </div>
            ) : discussions.length > 0 ? (
              <div className="space-y-4">
                {discussions.map((discussion) => renderMessage(discussion))}
              </div>
            ) : (
              <div className="text-center py-8 text-gray-500">
                <MessageSquare className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                <p>Aucune discussion</p>
                <p className="text-xs text-gray-400 mt-1">Soyez le premier à poster un message</p>
              </div>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
} 