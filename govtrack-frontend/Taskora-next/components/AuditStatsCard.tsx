"use client";

import { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Activity, Trash2, AlertTriangle, RotateCcw, Database } from "lucide-react";
import { auditApi, AuditStats } from "@/lib/api";
import { toast } from "sonner";
import Link from "next/link";

export function AuditStatsCard() {
  const [stats, setStats] = useState<AuditStats | null>(null);
  const [loading, setLoading] = useState(true);

  const loadStats = async () => {
    try {
      const response = await auditApi.getAuditStats();
      setStats(response.data);
    } catch (error: any) {
      console.error("Erreur lors du chargement des statistiques d'audit:", error);
      toast.error("Erreur lors du chargement des statistiques d'audit");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadStats();
  }, []);

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Activity className="h-5 w-5" />
            Audit & Traçabilité
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center h-20">
            <Activity className="h-6 w-6 animate-spin" />
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!stats) {
    return null;
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Activity className="h-5 w-5" />
          Audit & Traçabilité
        </CardTitle>
        <CardDescription>
          Statistiques des actions d'audit récentes
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div className="flex items-center gap-2">
            <Database className="h-4 w-4 text-muted-foreground" />
            <div>
              <p className="text-sm font-medium">Total</p>
              <p className="text-2xl font-bold">{stats.total_logs}</p>
            </div>
          </div>
          
          <div className="flex items-center gap-2">
            <Trash2 className="h-4 w-4 text-destructive" />
            <div>
              <p className="text-sm font-medium">Suppressions</p>
              <p className="text-2xl font-bold text-destructive">{stats.deletions}</p>
            </div>
          </div>
          
          <div className="flex items-center gap-2">
            <AlertTriangle className="h-4 w-4 text-destructive" />
            <div>
              <p className="text-sm font-medium">Définitives</p>
              <p className="text-2xl font-bold text-destructive">{stats.force_deletions}</p>
            </div>
          </div>
          
          <div className="flex items-center gap-2">
            <RotateCcw className="h-4 w-4 text-muted-foreground" />
            <div>
              <p className="text-sm font-medium">Restaurations</p>
              <p className="text-2xl font-bold">{stats.restorations}</p>
            </div>
          </div>
        </div>

        <div className="flex items-center justify-between pt-2 border-t">
          <div className="space-y-1">
            <p className="text-sm text-muted-foreground">Aujourd'hui</p>
            <Badge variant="secondary">{stats.today_logs} actions</Badge>
          </div>
          <Button asChild size="sm" variant="outline">
            <Link href="/audit">
              Voir tous les logs
            </Link>
          </Button>
        </div>
      </CardContent>
    </Card>
  );
} 