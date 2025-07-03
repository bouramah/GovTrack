'use client';

import { useState, useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useToast } from '@/components/ui/use-toast';
import { Sidebar } from '@/components/sidebar';
import Topbar from '@/components/Shared/Topbar';
import { 
  User, 
  Mail, 
  Phone, 
  MapPin, 
  Calendar, 
  Building, 
  Briefcase, 
  Shield, 
  Edit, 
  Save, 
  X, 
  Camera,
  Eye,
  EyeOff,
  Loader2,
  LogOut,
  RefreshCw,
  AlertTriangle
} from 'lucide-react';

export default function ProfilePage() {
  const { user, refreshUser, updateProfile, uploadProfilePhoto, logoutAll, refresh } = useAuth();
  const { toast } = useToast();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [isUploadingPhoto, setIsUploadingPhoto] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    matricule: '',
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    adresse: '',
    password: '',
  });

  useEffect(() => {
    if (user) {
      setFormData({
        matricule: user.matricule || '',
        nom: user.nom || '',
        prenom: user.prenom || '',
        email: user.email || '',
        telephone: user.telephone || '',
        adresse: user.adresse || '',
        password: '',
      });
    }
  }, [user]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSaveProfile = async () => {
    if (!user) return;

    setIsLoading(true);
    try {
      // Créer un objet avec seulement les champs modifiés (SANS le matricule)
      const updatedData: any = {};
      if (formData.nom !== user.nom && formData.nom.trim()) updatedData.nom = formData.nom.trim();
      if (formData.prenom !== user.prenom && formData.prenom.trim()) updatedData.prenom = formData.prenom.trim();
      if (formData.email !== user.email && formData.email.trim()) updatedData.email = formData.email.trim();
      if (formData.telephone !== (user.telephone || '') && formData.telephone.trim()) updatedData.telephone = formData.telephone.trim();
      if (formData.adresse !== (user.adresse || '') && formData.adresse.trim()) updatedData.adresse = formData.adresse.trim();
      if (formData.password.trim()) updatedData.password = formData.password;

      if (Object.keys(updatedData).length === 0) {
        toast({
          title: 'Aucun changement',
          description: 'Aucune modification n\'a été détectée.',
          variant: 'default',
        });
        setIsEditing(false);
        return;
      }

      await updateProfile(updatedData);
      setIsEditing(false);
      setFormData(prev => ({ ...prev, password: '' }));
      
      toast({
        title: '✅ Succès',
        description: `Votre profil a été mis à jour avec succès. ${Object.keys(updatedData).length} champ(s) modifié(s).`,
        variant: 'default',
      });
    } catch (error: any) {
      console.error('Erreur mise à jour profil:', error);
      
      let errorMessage = 'Erreur lors de la mise à jour du profil.';
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat();
        errorMessage = errors.join(', ');
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
      setIsLoading(false);
    }
  };

  const handlePhotoUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    // Vérifier la taille du fichier (max 2MB comme configuré dans le backend)
    if (file.size > 2 * 1024 * 1024) {
      toast({
        title: '❌ Fichier trop volumineux',
        description: 'La photo ne doit pas dépasser 2MB.',
        variant: 'destructive',
      });
      return;
    }

    // Vérifier le type de fichier
    if (!file.type.startsWith('image/')) {
      toast({
        title: '❌ Format invalide',
        description: 'Veuillez sélectionner une image (JPEG, PNG, JPG, GIF, WebP).',
        variant: 'destructive',
      });
      return;
    }

    const formData = new FormData();
    formData.append('photo', file);

    try {
      setIsUploadingPhoto(true);
      
      toast({
        title: '📤 Upload en cours...',
        description: 'Votre photo est en cours de téléchargement.',
        variant: 'default',
      });

      await uploadProfilePhoto(formData);
      
      toast({
        title: '✅ Photo mise à jour',
        description: 'Votre photo de profil a été mise à jour avec succès.',
        variant: 'default',
      });
    } catch (error: any) {
      console.error('Erreur upload photo:', error);
      
      let errorMessage = 'Erreur lors de l\'upload de la photo.';
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat();
        errorMessage = errors.join(', ');
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.message) {
        errorMessage = error.message;
      }

      toast({
        title: '❌ Erreur Upload',
        description: errorMessage,
        variant: 'destructive',
      });
    } finally {
      setIsUploadingPhoto(false);
      // Reset l'input file
      event.target.value = '';
    }
  };

  const handleRefreshToken = async () => {
    try {
      await refresh();
      toast({
        title: '✅ Token rafraîchi',
        description: 'Votre session a été rafraîchie avec succès.',
        variant: 'default',
      });
    } catch (error: any) {
      console.error('Erreur rafraîchissement token:', error);
      toast({
        title: '❌ Erreur',
        description: 'Impossible de rafraîchir votre session.',
        variant: 'destructive',
      });
    }
  };

  const handleLogoutAll = async () => {
    try {
      await logoutAll();
      toast({
        title: '✅ Déconnexion réussie',
        description: 'Vous avez été déconnecté de tous les appareils.',
        variant: 'default',
      });
    } catch (error: any) {
      console.error('Erreur déconnexion tous appareils:', error);
      toast({
        title: '❌ Erreur',
        description: 'Erreur lors de la déconnexion de tous les appareils.',
        variant: 'destructive',
      });
    }
  };

  const getRoleColor = (roleName: string) => {
    switch (roleName.toLowerCase()) {
      case 'administrateur': return 'bg-red-100 text-red-800 border-red-200';
      case 'directeur': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'manager': return 'bg-green-100 text-green-800 border-green-200';
      case 'employé': return 'bg-gray-100 text-gray-800 border-gray-200';
      default: return 'bg-purple-100 text-purple-800 border-purple-200';
    }
  };

  if (!user) {
    return <div className="flex items-center justify-center min-h-screen">Chargement...</div>;
  }

  return (
    <div className="bg-gray-50">
      <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

      <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
        <Topbar
          name="Mon Profil"
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
        />

        <main className="flex-1 overflow-y-auto p-3 lg:p-6">
          <div className="max-w-6xl mx-auto">
            <div className="flex flex-col lg:flex-row gap-6">
              {/* Colonne de gauche - Informations principales */}
              <div className="lg:w-1/3 space-y-6">
                {/* Photo et informations de base */}
                <Card>
                  <CardContent className="pt-6">
                    <div className="flex flex-col items-center space-y-4">
                      {/* Photo de profil */}
                      <div className="relative">
                        <Avatar className="h-32 w-32">
                          <AvatarImage src={user.photo} alt={`${user.prenom} ${user.nom}`} />
                          <AvatarFallback className="text-2xl">
                            {user.prenom?.charAt(0)}{user.nom?.charAt(0)}
                          </AvatarFallback>
                        </Avatar>
                        <Label 
                          htmlFor="photo-upload" 
                          className={`absolute bottom-0 right-0 bg-primary text-primary-foreground rounded-full p-2 transition-colors ${
                            isUploadingPhoto 
                              ? 'cursor-wait opacity-50' 
                              : 'cursor-pointer hover:bg-primary/90'
                          }`}
                        >
                          {isUploadingPhoto ? (
                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                          ) : (
                            <Camera className="h-4 w-4" />
                          )}
                          <input
                            id="photo-upload"
                            type="file"
                            accept="image/*"
                            onChange={handlePhotoUpload}
                            className="hidden"
                            disabled={isUploadingPhoto}
                          />
                        </Label>
                      </div>

                      {/* Nom complet */}
                      <div className="text-center">
                        <h1 className="text-2xl font-bold">{user.prenom} {user.nom}</h1>
                        <p className="text-muted-foreground">Matricule: {user.matricule}</p>
                      </div>

                      {/* Rôles */}
                      <div className="flex flex-wrap gap-2 justify-center">
                        {user.roles?.map((role) => (
                          <Badge 
                            key={role.id} 
                            variant="outline"
                            className={getRoleColor(role.nom)}
                          >
                            <Shield className="w-3 h-3 mr-1" />
                            {role.nom}
                          </Badge>
                        ))}
                      </div>
                    </div>
                  </CardContent>
                </Card>

                {/* Affectation actuelle */}
                {user.affectation_actuelle && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Briefcase className="h-5 w-5" />
                        Affectation Actuelle
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2">
                      <div className="flex items-center gap-2">
                        <Building className="h-4 w-4 text-muted-foreground" />
                        <span className="font-medium">{user.affectation_actuelle.entite}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <User className="h-4 w-4 text-muted-foreground" />
                        <span>{user.affectation_actuelle.poste}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                        <span className="text-sm">Depuis le {new Date(user.affectation_actuelle.date_debut).toLocaleDateString('fr-FR')}</span>
                      </div>
                    </CardContent>
                  </Card>
                )}

                {/* Entités dirigées */}
                {user.entites_dirigees && user.entites_dirigees.length > 0 && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Building className="h-5 w-5" />
                        Entités Dirigées
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-2">
                        {user.entites_dirigees.map((entite, index) => (
                          <div key={index} className="flex items-center justify-between p-2 border rounded">
                            <span className="font-medium">{entite.entite_nom}</span>
                            <Badge variant="secondary">Directeur</Badge>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                )}
              </div>

              {/* Colonne de droite - Informations détaillées */}
              <div className="lg:w-2/3 space-y-6">
                {/* Informations personnelles */}
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between">
                    <div>
                      <CardTitle>Informations Personnelles</CardTitle>
                      <CardDescription>
                        Gérez vos informations personnelles et de contact
                      </CardDescription>
                    </div>
                    <Button
                      onClick={() => isEditing ? setIsEditing(false) : setIsEditing(true)}
                      variant="outline"
                      disabled={isLoading}
                    >
                      {isEditing ? (
                        <>
                          <X className="h-4 w-4 mr-2" />
                          Annuler
                        </>
                      ) : (
                        <>
                          <Edit className="h-4 w-4 mr-2" />
                          Modifier
                        </>
                      )}
                    </Button>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="matricule">Matricule</Label>
                        <Input
                          id="matricule"
                          name="matricule"
                          value={formData.matricule}
                          disabled={true}
                          className="bg-gray-100 cursor-not-allowed"
                          title="Le matricule ne peut pas être modifié"
                        />
                        <p className="text-xs text-muted-foreground">
                          🔒 Le matricule ne peut pas être modifié
                        </p>
                      </div>

                      <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                          id="email"
                          name="email"
                          type="email"
                          value={formData.email}
                          onChange={handleInputChange}
                          disabled={!isEditing || isLoading}
                          className="bg-background"
                        />
                      </div>

                      <div className="space-y-2">
                        <Label htmlFor="nom">Nom</Label>
                        <Input
                          id="nom"
                          name="nom"
                          value={formData.nom}
                          onChange={handleInputChange}
                          disabled={!isEditing || isLoading}
                          className="bg-background"
                        />
                      </div>

                      <div className="space-y-2">
                        <Label htmlFor="prenom">Prénom</Label>
                        <Input
                          id="prenom"
                          name="prenom"
                          value={formData.prenom}
                          onChange={handleInputChange}
                          disabled={!isEditing || isLoading}
                          className="bg-background"
                        />
                      </div>

                      <div className="space-y-2">
                        <Label htmlFor="telephone">Téléphone</Label>
                        <Input
                          id="telephone"
                          name="telephone"
                          type="tel"
                          value={formData.telephone}
                          onChange={handleInputChange}
                          disabled={!isEditing || isLoading}
                          className="bg-background"
                          placeholder="Ex: +221 77 123 45 67"
                        />
                      </div>

                      {isEditing && (
                        <div className="space-y-2">
                          <Label htmlFor="password">Nouveau mot de passe (optionnel)</Label>
                          <div className="relative">
                            <Input
                              id="password"
                              name="password"
                              type={showPassword ? "text" : "password"}
                              value={formData.password}
                              onChange={handleInputChange}
                              disabled={isLoading}
                              className="bg-background pr-10"
                              placeholder="Laisser vide pour ne pas changer"
                            />
                            <Button
                              type="button"
                              variant="ghost"
                              size="sm"
                              className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                              onClick={() => setShowPassword(!showPassword)}
                            >
                              {showPassword ? (
                                <EyeOff className="h-4 w-4" />
                              ) : (
                                <Eye className="h-4 w-4" />
                              )}
                            </Button>
                          </div>
                        </div>
                      )}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="adresse">Adresse</Label>
                      <Textarea
                        id="adresse"
                        name="adresse"
                        value={formData.adresse}
                        onChange={handleInputChange}
                        disabled={!isEditing || isLoading}
                        className="bg-background"
                        placeholder="Votre adresse complète"
                        rows={3}
                      />
                    </div>

                    {isEditing && (
                      <div className="flex justify-end pt-4">
                        <Button
                          onClick={handleSaveProfile}
                          disabled={isLoading}
                          className="min-w-[120px]"
                        >
                          {isLoading ? (
                            'Enregistrement...'
                          ) : (
                            <>
                              <Save className="h-4 w-4 mr-2" />
                              Enregistrer
                            </>
                          )}
                        </Button>
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Permissions */}
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Shield className="h-5 w-5" />
                      Permissions
                    </CardTitle>
                    <CardDescription>
                      Vos permissions actuelles dans le système
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    {user.permissions && user.permissions.length > 0 ? (
                      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        {user.permissions.map((permission) => (
                          <Badge 
                            key={permission} 
                            variant="secondary"
                            className="justify-start text-xs"
                          >
                            {permission.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                          </Badge>
                        ))}
                      </div>
                    ) : (
                      <p className="text-muted-foreground">Aucune permission spécifique assignée.</p>
                    )}
                  </CardContent>
                </Card>

                {/* Sécurité */}
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <AlertTriangle className="h-5 w-5" />
                      Sécurité
                    </CardTitle>
                    <CardDescription>
                      Gérer votre session et la sécurité de votre compte
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label className="text-sm font-medium">Rafraîchir la session</Label>
                        <p className="text-xs text-muted-foreground">
                          Renouveler votre token d'authentification pour maintenir une session sécurisée
                        </p>
                        <Button
                          onClick={handleRefreshToken}
                          variant="outline"
                          size="sm"
                          className="w-full"
                        >
                          <RefreshCw className="h-4 w-4 mr-2" />
                          Rafraîchir la session
                        </Button>
                      </div>

                      <div className="space-y-2">
                        <Label className="text-sm font-medium">Déconnexion de tous les appareils</Label>
                        <p className="text-xs text-muted-foreground">
                          Déconnecter votre compte de tous les appareils connectés
                        </p>
                        <Button
                          onClick={handleLogoutAll}
                          variant="destructive"
                          size="sm"
                          className="w-full"
                        >
                          <LogOut className="h-4 w-4 mr-2" />
                          Déconnexion globale
                        </Button>
                      </div>
                    </div>

                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                      <div className="flex items-start gap-3">
                        <AlertTriangle className="h-5 w-5 text-yellow-600 mt-0.5" />
                        <div>
                          <h4 className="text-sm font-medium text-yellow-800">
                            Conseils de sécurité
                          </h4>
                          <ul className="mt-2 text-xs text-yellow-700 space-y-1">
                            <li>• Rafraîchissez régulièrement votre session</li>
                            <li>• Déconnectez-vous de tous les appareils si vous suspectez une intrusion</li>
                            <li>• Ne partagez jamais vos identifiants</li>
                            <li>• Utilisez un mot de passe fort et unique</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
} 