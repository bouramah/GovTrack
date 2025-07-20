"use client";

import { useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useToast } from '@/components/ui/use-toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye, EyeOff, Loader2 } from 'lucide-react';
import MinistryLogo from '@/components/Shared/MinistryLogo';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { login, loading } = useAuth();
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email.trim() || !password.trim()) {
      toast({
        title: '❌ Champs requis',
        description: 'Veuillez remplir tous les champs.',
        variant: 'destructive',
      });
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      await login({ email: email.trim(), password });
      
      toast({
        title: '✅ Connexion réussie',
        description: 'Redirection en cours...',
        variant: 'default',
      });
      
    } catch (error: any) {
      console.error('Erreur de connexion:', error);
      
      let errorMessage = 'Erreur de connexion. Vérifiez vos identifiants.';
      if (error.message) {
        errorMessage = error.message;
      }
      
      toast({
        title: '❌ Échec de la connexion',
        description: errorMessage,
        variant: 'destructive',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const comptesDemoData = [
    {
      nom: "Super Admin",
      email: "admin@govtrack.gov",
      password: "password",
      role: "Administrateur",
      description: "Accès complet à toutes les instructions et fonctionnalités administratives",
      matricule: "ADM001",
      permissions: "view_all_projects, manage_users, manage_entities"
    }
  ];

  const handleDemoLogin = (email: string, password: string) => {
    setEmail(email);
    setPassword(password);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
      <div className="w-full max-w-7xl grid lg:grid-cols-2 gap-8">
        {/* Formulaire de connexion */}
        <div className="flex items-center justify-center">
          <Card className="w-full max-w-md shadow-lg border-0">
            <CardHeader className="space-y-1">
              <div className="flex justify-center mb-6">
                <MinistryLogo size="lg" showText={true} />
              </div>
              <div className="border-t border-gray-200 pt-6">
                <CardTitle className="text-2xl font-bold text-center text-gray-900">GovTrack</CardTitle>
                <CardDescription className="text-center text-gray-600 mt-2">
                  Système de Gestion des Instructions Ministérielles
                </CardDescription>
              </div>
              
              {/* Branding Simandou */}
              <div className="flex justify-center items-center space-x-4 mt-6 pt-4 border-t border-gray-100">
                <div className="text-center">
                  <img 
                    src="/branding.png" 
                    alt="Branding Guinée" 
                    className="h-12 w-auto mx-auto mb-2"
                  />
                </div>
                <div className="text-center">
                  <img 
                    src="/simandou.jpeg" 
                    alt="Programme Simandou 2040" 
                    className="h-16 w-auto mx-auto mb-2"
                  />
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="votre.email@govtrack.gov"
                    disabled={isSubmitting || loading}
                    required
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="password">Mot de passe</Label>
                  <div className="relative">
                    <Input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      disabled={isSubmitting || loading}
                      required
                    />
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                      onClick={() => setShowPassword(!showPassword)}
                      disabled={isSubmitting || loading}
                    >
                      {showPassword ? (
                        <EyeOff className="h-4 w-4" />
                      ) : (
                        <Eye className="h-4 w-4" />
                      )}
                    </Button>
                  </div>
                </div>
                
                <Button 
                  type="submit" 
                  className="w-full" 
                  disabled={isSubmitting || loading}
                >
                  {isSubmitting || loading ? (
                    <>
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                      Connexion...
                    </>
                  ) : (
                    'Se connecter'
                  )}
                </Button>

                <div className="text-center mt-2">
                  <a href="/forgot-password" className="text-sm text-blue-600 hover:underline">
                    Mot de passe oublié ?
                  </a>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>

        {/* Comptes de démonstration */}
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">🧪 Compte de démonstration</CardTitle>
              <CardDescription>
                Utilisez ce compte pour tester l'application en tant qu'administrateur
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {comptesDemoData.map((compte, index) => (
                <Card key={index} className="p-4 hover:shadow-md transition-shadow cursor-pointer" 
                      onClick={() => handleDemoLogin(compte.email, compte.password)}>
                  <div className="flex justify-between items-start mb-2">
                    <div>
                      <h4 className="font-semibold">{compte.nom}</h4>
                      <p className="text-xs text-gray-500 mt-1">Matricule: {compte.matricule}</p>
                    </div>
                    <span className={`text-xs px-2 py-1 rounded ${
                      compte.role === 'Administrateur' ? 'bg-red-100 text-red-800' :
                      compte.role === 'Directeur DSI' ? 'bg-blue-100 text-blue-800' :
                      'bg-green-100 text-green-800'
                    }`}>
                      {compte.role}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-3">{compte.description}</p>
                  
                  <div className="space-y-2">
                    <div className="text-xs text-gray-500">
                      <div className="flex items-center gap-2">
                        <span className="font-medium">📧</span>
                        <span className="font-mono">{compte.email}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className="font-medium">🔑</span>
                        <span className="font-mono">{compte.password}</span>
                      </div>
                    </div>
                    
                    <div className="border-t pt-2">
                      <p className="text-xs text-gray-500 font-medium mb-1">Permissions principales:</p>
                      <div className="flex flex-wrap gap-1">
                        {compte.permissions.split(', ').map((perm, i) => (
                          <span key={i} className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                            {perm.replace('_', ' ')}
                          </span>
                        ))}
                      </div>
                    </div>
                  </div>
                </Card>
              ))}
            </CardContent>
            <CardFooter className="text-center">
              <div className="w-full space-y-2">
                <p className="text-xs text-gray-500">
                  💡 Cliquez sur ce compte pour pré-remplir le formulaire
                </p>
              </div>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  );
}
