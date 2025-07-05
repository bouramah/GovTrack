"use client";

import { useEffect, useState } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { X } from 'lucide-react';

interface NotificationData {
  title: string;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
}

export default function GlobalNotification() {
  const [notification, setNotification] = useState<NotificationData | null>(null);

  useEffect(() => {
    const handleNotification = (event: CustomEvent<NotificationData>) => {
      setNotification(event.detail);
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        setNotification(null);
      }, 5000);
    };

    window.addEventListener('showNotification', handleNotification as EventListener);
    
    return () => {
      window.removeEventListener('showNotification', handleNotification as EventListener);
    };
  }, []);

  if (!notification) return null;

  const getAlertVariant = (type: string) => {
    switch (type) {
      case 'success': return 'default';
      case 'error': return 'destructive';
      case 'warning': return 'default';
      case 'info': return 'default';
      default: return 'default';
    }
  };

  const getIcon = (type: string) => {
    switch (type) {
      case 'success': return '✅';
      case 'error': return '❌';
      case 'warning': return '⚠️';
      case 'info': return 'ℹ️';
      default: return 'ℹ️';
    }
  };

  return (
    <div className="fixed top-4 right-4 z-50 max-w-sm">
      <Alert variant={getAlertVariant(notification.type)}>
        <div className="flex items-start justify-between">
          <div className="flex items-start gap-2">
            <span className="text-lg">{getIcon(notification.type)}</span>
            <div>
              <h4 className="font-semibold">{notification.title}</h4>
              <AlertDescription>{notification.message}</AlertDescription>
            </div>
          </div>
          <button
            onClick={() => setNotification(null)}
            className="text-muted-foreground hover:text-foreground transition-colors"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      </Alert>
    </div>
  );
} 