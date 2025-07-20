'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Calendar, Clock, Monitor, Smartphone, Tablet, Globe, Users, Activity, Shield, AlertTriangle } from 'lucide-react';
import { apiClient } from '@/lib/api';
import { useToast } from '@/hooks/use-toast';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { LoginActivityStats } from '@/components/login-activity-stats';

interface LoginActivity {
  id: number;
  action: string;
  ip_address: string;
  user_agent: string;
  location: string | null;
  device_type: string;
  browser: string;
  os: string;
  session_id: string | null;
  created_at: string;
  session_duration: string;
  user: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
}

interface GlobalStats {
  total_logins: number;
  total_logouts: number;
  failed_logins: number;
  password_resets: number;
  session_expired: number;
  unique_users: number;
  unique_ips: number;
  top_devices: Record<string, number>;
  top_browsers: Record<string, number>;
  top_os: Record<string, number>;
  daily_activity: Record<string, {
    logins: number;
    logouts: number;
    failed_logins: number;
  }>;
}

export default function LoginActivitiesPage() {
  const [activities, setActivities] = useState<LoginActivity[]>([]);
  const [globalStats, setGlobalStats] = useState<GlobalStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    action: '',
    date_from: '',
    date_to: '',
    ip_address: '',
    user_id: '',
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
  });
  const { toast } = useToast();

  useEffect(() => {
    loadGlobalStats();
    loadActivities();
  }, [filters, pagination.current_page]);

  const loadGlobalStats = async () => {
    try {
      const response = await apiClient.getGlobalLoginStats(30);
      if (response.success && response.data) {
        setGlobalStats(response.data);
      }
    } catch (error) {
      console.error('Erreur lors du chargement des statistiques:', error);
    }
  };

  const loadActivities = async () => {
    try {
      setLoading(true);
      const params = {
        ...filters,
        page: pagination.current_page,
        per_page: pagination.per_page,
        user_id: filters.user_id ? parseInt(filters.user_id) : undefined,
      };
      
      const response = await apiClient.getGlobalLoginActivities(params);
      if (response.success && response.data) {
        setActivities(response.data);
        // Mettre à jour la pagination si disponible
        if ('pagination' in response && response.pagination) {
          setPagination(response.pagination as typeof pagination);
        }
      }
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de charger les activités de connexion",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const getActionIcon = (action: string) => {
    switch (action) {
      case 'login':
        return <Activity className="h-4 w-4 text-green-500" />;
      case 'logout':
        return <Activity className="h-4 w-4 text-blue-500" />;
      case 'failed_login':
        return <AlertTriangle className="h-4 w-4 text-red-500" />;
      case 'password_reset':
        return <Shield className="h-4 w-4 text-orange-500" />;
      case 'session_expired':
        return <Clock className="h-4 w-4 text-gray-500" />;
      default:
        return <Activity className="h-4 w-4" />;
    }
  };

  const getActionBadge = (action: string) => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
      login: "default",
      logout: "secondary",
      failed_login: "destructive",
      password_reset: "outline",
      session_expired: "secondary",
    };

    const labels: Record<string, string> = {
      login: "Connexion",
      logout: "Déconnexion",
      failed_login: "Échec",
      password_reset: "Reset MDP",
      session_expired: "Session expirée",
    };

    return (
      <Badge variant={variants[action] || "default"}>
        {labels[action] || action}
      </Badge>
    );
  };

  const getDeviceIcon = (deviceType: string) => {
    switch (deviceType) {
      case 'desktop':
        return <Monitor className="h-4 w-4" />;
      case 'mobile':
        return <Smartphone className="h-4 w-4" />;
      case 'tablet':
        return <Tablet className="h-4 w-4" />;
      default:
        return <Globe className="h-4 w-4" />;
    }
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value }));
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  const clearFilters = () => {
    setFilters({
      action: '',
      date_from: '',
      date_to: '',
      ip_address: '',
      user_id: '',
    });
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <p className="text-muted-foreground">
            Surveillez les connexions et déconnexions des utilisateurs
          </p>
        </div>
      </div>

      {/* Statistiques Globales */}
      {globalStats && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Connexions</CardTitle>
              <Activity className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{globalStats.total_logins}</div>
              <p className="text-xs text-muted-foreground">
                {globalStats.total_logouts} déconnexions
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tentatives Échouées</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{globalStats.failed_logins}</div>
              <p className="text-xs text-muted-foreground">
                {globalStats.password_resets} reset MDP
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Utilisateurs Uniques</CardTitle>
              <Users className="h-4 w-4 text-blue-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{globalStats.unique_users}</div>
              <p className="text-xs text-muted-foreground">
                {globalStats.unique_ips} IPs uniques
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Sessions Expirées</CardTitle>
              <Clock className="h-4 w-4 text-gray-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{globalStats.session_expired}</div>
              <p className="text-xs text-muted-foreground">
                Sessions automatiquement fermées
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Filtres */}
      <Card>
        <CardHeader>
          <CardTitle>Filtres</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <Label htmlFor="action">Action</Label>
              <Select value={filters.action || "all"} onValueChange={(value) => handleFilterChange('action', value === "all" ? "" : value)}>
                <SelectTrigger>
                  <SelectValue placeholder="Toutes les actions" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes les actions</SelectItem>
                  <SelectItem value="login">Connexion</SelectItem>
                  <SelectItem value="logout">Déconnexion</SelectItem>
                  <SelectItem value="failed_login">Échec de connexion</SelectItem>
                  <SelectItem value="password_reset">Reset mot de passe</SelectItem>
                  <SelectItem value="session_expired">Session expirée</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label htmlFor="date_from">Date de début</Label>
              <Input
                type="date"
                value={filters.date_from}
                onChange={(e) => handleFilterChange('date_from', e.target.value)}
              />
            </div>

            <div>
              <Label htmlFor="date_to">Date de fin</Label>
              <Input
                type="date"
                value={filters.date_to}
                onChange={(e) => handleFilterChange('date_to', e.target.value)}
              />
            </div>

            <div>
              <Label htmlFor="ip_address">Adresse IP</Label>
              <Input
                placeholder="192.168.1.1"
                value={filters.ip_address}
                onChange={(e) => handleFilterChange('ip_address', e.target.value)}
              />
            </div>
          </div>

          <div className="flex justify-end mt-4 space-x-2">
            <Button variant="outline" onClick={clearFilters}>
              Effacer les filtres
            </Button>
            <Button onClick={loadActivities}>
              Appliquer
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Onglets pour Activités et Statistiques */}
      <Tabs defaultValue="activities" className="space-y-4">
        <TabsList>
          <TabsTrigger value="activities">Activités Récentes</TabsTrigger>
          <TabsTrigger value="stats">Statistiques Détaillées</TabsTrigger>
        </TabsList>

        <TabsContent value="activities">
          <Card>
            <CardHeader>
              <CardTitle>Activités Récentes</CardTitle>
              <CardDescription>
                {pagination.total} activités trouvées
              </CardDescription>
            </CardHeader>
            <CardContent>
          {loading ? (
            <div className="flex justify-center items-center h-32">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
          ) : activities.length === 0 ? (
            <div className="text-center py-8 text-muted-foreground">
              Aucune activité trouvée
            </div>
          ) : (
            <div className="space-y-4">
              {activities.map((activity) => (
                <div key={activity.id} className="flex items-center justify-between p-4 border rounded-lg">
                  <div className="flex items-center space-x-4">
                    {getActionIcon(activity.action)}
                    <div>
                      <div className="flex items-center space-x-2">
                        <span className="font-medium">
                          {activity.user.prenom} {activity.user.nom}
                        </span>
                        {getActionBadge(activity.action)}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        {activity.user.email} • {activity.ip_address}
                      </div>
                      <div className="text-xs text-muted-foreground flex items-center space-x-2">
                        {getDeviceIcon(activity.device_type)}
                        <span>{activity.browser} sur {activity.os}</span>
                        {activity.session_duration && (
                          <>
                            <span>•</span>
                            <span>Session: {activity.session_duration}</span>
                          </>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm font-medium">
                      {format(new Date(activity.created_at), 'dd/MM/yyyy HH:mm', { locale: fr })}
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {format(new Date(activity.created_at), 'EEEE', { locale: fr })}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <div className="flex justify-center items-center space-x-2 mt-6">
              <Button
                variant="outline"
                disabled={pagination.current_page === 1}
                onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page - 1 }))}
              >
                Précédent
              </Button>
              <span className="text-sm">
                Page {pagination.current_page} sur {pagination.last_page}
              </span>
              <Button
                variant="outline"
                disabled={pagination.current_page === pagination.last_page}
                onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page + 1 }))}
              >
                Suivant
              </Button>
            </div>
          )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="stats">
          {globalStats && <LoginActivityStats stats={globalStats} />}
        </TabsContent>
      </Tabs>
    </div>
  );
} 