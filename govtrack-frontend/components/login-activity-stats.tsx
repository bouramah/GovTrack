'use client';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Monitor, Smartphone, Tablet, Globe, Chrome, Circle } from 'lucide-react';

interface LoginActivityStatsProps {
  stats: {
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
  };
}

export function LoginActivityStats({ stats }: LoginActivityStatsProps) {
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

  const getBrowserIcon = (browser: string) => {
    switch (browser.toLowerCase()) {
      case 'chrome':
        return <Chrome className="h-4 w-4" />;
      case 'firefox':
        return <Circle className="h-4 w-4" />; // Utiliser Circle pour Firefox
      case 'safari':
        return <Circle className="h-4 w-4" />; // Utiliser Circle pour Safari
      case 'edge':
        return <Globe className="h-4 w-4" />; // Utiliser Globe pour Edge
      default:
        return <Globe className="h-4 w-4" />;
    }
  };

  const getDeviceLabel = (deviceType: string) => {
    switch (deviceType) {
      case 'desktop':
        return 'Ordinateur';
      case 'mobile':
        return 'Mobile';
      case 'tablet':
        return 'Tablette';
      default:
        return 'Autre';
    }
  };

  return (
    <div className="space-y-6">
      {/* Top Devices */}
      <Card>
        <CardHeader>
          <CardTitle>Appareils Utilisés</CardTitle>
          <CardDescription>Types d'appareils les plus utilisés</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {Object.entries(stats.top_devices)
              .sort(([, a], [, b]) => b - a)
              .map(([device, count]) => (
                <div key={device} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    {getDeviceIcon(device)}
                    <span className="font-medium">{getDeviceLabel(device)}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Badge variant="secondary">{count}</Badge>
                    <span className="text-sm text-muted-foreground">
                      {((count / stats.total_logins) * 100).toFixed(1)}%
                    </span>
                  </div>
                </div>
              ))}
          </div>
        </CardContent>
      </Card>

      {/* Top Browsers */}
      <Card>
        <CardHeader>
          <CardTitle>Navigateurs</CardTitle>
          <CardDescription>Navigateurs les plus utilisés</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {Object.entries(stats.top_browsers)
              .sort(([, a], [, b]) => b - a)
              .map(([browser, count]) => (
                <div key={browser} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    {getBrowserIcon(browser)}
                    <span className="font-medium">{browser}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Badge variant="secondary">{count}</Badge>
                    <span className="text-sm text-muted-foreground">
                      {((count / stats.total_logins) * 100).toFixed(1)}%
                    </span>
                  </div>
                </div>
              ))}
          </div>
        </CardContent>
      </Card>

      {/* Top Operating Systems */}
      <Card>
        <CardHeader>
          <CardTitle>Systèmes d'Exploitation</CardTitle>
          <CardDescription>OS les plus utilisés</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {Object.entries(stats.top_os)
              .sort(([, a], [, b]) => b - a)
              .map(([os, count]) => (
                <div key={os} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <Globe className="h-4 w-4" />
                    <span className="font-medium">{os}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Badge variant="secondary">{count}</Badge>
                    <span className="text-sm text-muted-foreground">
                      {((count / stats.total_logins) * 100).toFixed(1)}%
                    </span>
                  </div>
                </div>
              ))}
          </div>
        </CardContent>
      </Card>

      {/* Daily Activity */}
      <Card>
        <CardHeader>
          <CardTitle>Activité Quotidienne</CardTitle>
          <CardDescription>Répartition des activités par jour</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {Object.entries(stats.daily_activity)
              .sort(([a], [b]) => new Date(b).getTime() - new Date(a).getTime())
              .slice(0, 7) // Afficher seulement les 7 derniers jours
              .map(([date, activity]) => (
                <div key={date} className="flex items-center justify-between p-3 border rounded-lg">
                  <div>
                    <div className="font-medium">
                      {new Date(date).toLocaleDateString('fr-FR', { 
                        weekday: 'long', 
                        day: 'numeric', 
                        month: 'long' 
                      })}
                    </div>
                    <div className="text-sm text-muted-foreground">
                      {new Date(date).toLocaleDateString('fr-FR')}
                    </div>
                  </div>
                  <div className="flex items-center space-x-4">
                    <div className="text-center">
                      <div className="text-sm font-medium text-green-600">{activity.logins}</div>
                      <div className="text-xs text-muted-foreground">Connexions</div>
                    </div>
                    <div className="text-center">
                      <div className="text-sm font-medium text-blue-600">{activity.logouts}</div>
                      <div className="text-xs text-muted-foreground">Déconnexions</div>
                    </div>
                    <div className="text-center">
                      <div className="text-sm font-medium text-red-600">{activity.failed_logins}</div>
                      <div className="text-xs text-muted-foreground">Échecs</div>
                    </div>
                  </div>
                </div>
              ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
} 