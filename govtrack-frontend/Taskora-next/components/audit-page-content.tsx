"use client";

import { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";
import { 
  Search, 
  Filter, 
  Download, 
  Eye, 
  Calendar, 
  User, 
  Database, 
  Activity,
  Trash2,
  RotateCcw,
  AlertTriangle,
  Info,
  Clock,
  MapPin,
  Monitor
} from "lucide-react";
import { auditApi } from "@/lib/api";
import { toast } from "sonner";
import { format } from "date-fns";
import { fr } from "date-fns/locale";

interface AuditLog {
  id: number;
  action: string;
  table_name: string;
  record_id: number;
  record_type: string;
  deleted_data: any;
  deleted_data_summary: string;
  user_id: number;
  user_name: string;
  user_email: string;
  ip_address: string;
  user_agent: string;
  request_url: string;
  request_method: string;
  reason: string;
  metadata: any;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
}

interface AuditStats {
  total_logs: number;
  deletions: number;
  force_deletions: number;
  restorations: number;
  today_logs: number;
  this_week_logs: number;
  this_month_logs: number;
}

interface TopTable {
  table_name: string;
  count: number;
}

interface TopUser {
  user_id: number;
  user_name: string;
  count: number;
}

interface AuditResponse {
  data: AuditLog[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  statistiques: AuditStats;
  top_tables: TopTable[];
  top_users: TopUser[];
}

export default function AuditPageContent() {
  const [logs, setLogs] = useState<AuditLog[]>([]);
  const [stats, setStats] = useState<AuditStats | null>(null);
  const [topTables, setTopTables] = useState<TopTable[]>([]);
  const [topUsers, setTopUsers] = useState<TopUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedLog, setSelectedLog] = useState<AuditLog | null>(null);
  const [showDetails, setShowDetails] = useState(false);
  
  // Filtres
  const [filters, setFilters] = useState({
    action: "all",
    table_name: "all",
    user_id: "",
    search: "",
    date_debut: "",
    date_fin: "",
  });

  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  // Charger les logs d'audit
  const loadAuditLogs = async (page = 1) => {
    try {
      setLoading(true);
      
      // Convertir les valeurs "all" en chaînes vides pour l'API
      const apiFilters = {
        ...filters,
        action: filters.action === "all" ? "" : filters.action,
        table_name: filters.table_name === "all" ? "" : filters.table_name,
        page: page.toString(),
        per_page: "10",
      };

      const response = await auditApi.getAuditLogs(apiFilters);
      setLogs(response.data);
      setPagination(response.pagination);
      setStats(response.statistiques);
      setTopTables(response.top_tables);
      setTopUsers(response.top_users);
    } catch (error: any) {
      console.error("Erreur lors du chargement des logs d'audit:", error);
      toast.error("Erreur lors du chargement des logs d'audit");
    } finally {
      setLoading(false);
    }
  };

  // Charger les statistiques
  const loadStats = async () => {
    try {
      const response = await auditApi.getAuditStats();
      setStats(response.data);
    } catch (error: any) {
      console.error("Erreur lors du chargement des statistiques:", error);
    }
  };

  // Exporter les logs
  const exportLogs = async () => {
    try {
      // Convertir les valeurs "all" en chaînes vides pour l'API
      const apiFilters = {
        ...filters,
        action: filters.action === "all" ? "" : filters.action,
        table_name: filters.table_name === "all" ? "" : filters.table_name,
      };

      const response = await auditApi.exportAuditLogs(apiFilters);
      const data = response.data;

      // Créer le fichier CSV
      const headers = [
        "ID", "Action", "Table", "ID Enregistrement", "Résumé", 
        "Utilisateur", "Email Utilisateur", "Adresse IP", "URL", 
        "Méthode", "Raison", "Date"
      ];

      const csvContent = [
        headers.join(","),
        ...data.map((log: any) => [
          log.ID,
          log.Action,
          log.Table,
          log["ID Enregistrement"],
          `"${log.Résumé}"`,
          log.Utilisateur,
          log["Email Utilisateur"],
          log["Adresse IP"],
          log.URL,
          log.Méthode,
          log.Raison || "",
          log.Date
        ].join(","))
      ].join("\n");

      // Télécharger le fichier
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      const url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      link.setAttribute("download", `audit_logs_${format(new Date(), "yyyy-MM-dd_HH-mm")}.csv`);
      link.style.visibility = "hidden";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      toast.success("Export des logs d'audit réussi");
    } catch (error: any) {
      console.error("Erreur lors de l'export:", error);
      toast.error("Erreur lors de l'export des logs");
    }
  };

  // Appliquer les filtres
  const applyFilters = () => {
    setPagination(prev => ({ ...prev, current_page: 1 }));
    loadAuditLogs(1);
  };

  // Réinitialiser les filtres
  const resetFilters = () => {
    setFilters({
      action: "all",
      table_name: "all",
      user_id: "",
      search: "",
      date_debut: "",
      date_fin: "",
    });
    setPagination(prev => ({ ...prev, current_page: 1 }));
    loadAuditLogs(1);
  };

  // Obtenir le label de l'action
  const getActionLabel = (action: string) => {
    const labels: { [key: string]: { label: string; color: string; icon: any } } = {
      delete: { label: "Suppression", color: "destructive", icon: Trash2 },
      force_delete: { label: "Suppression définitive", color: "destructive", icon: AlertTriangle },
      restore: { label: "Restauration", color: "default", icon: RotateCcw },
      test: { label: "Test", color: "secondary", icon: Info },
    };
    return labels[action] || { label: action, color: "default", icon: Activity };
  };

  // Obtenir le label de la table
  const getTableLabel = (tableName: string) => {
    const labels: { [key: string]: string } = {
      users: "Utilisateurs",
      entites: "Entités",
      postes: "Postes",
      roles: "Rôles",
      permissions: "Permissions",
      type_entites: "Types d'entités",
      type_projets: "Types de projets",
      projets: "Projets",
      taches: "Tâches",
      discussion_projets: "Discussions projets",
      discussion_taches: "Discussions tâches",
      piece_jointe_projets: "Pièces jointes projets",
      piece_jointe_taches: "Pièces jointes tâches",
      projet_historique_statuts: "Historique statuts projets",
      tache_historique_statuts: "Historique statuts tâches",
      entite_chef_histories: "Historique chefs entités",
      utilisateur_entite_histories: "Historique affectations",
    };
    return labels[tableName] || tableName;
  };

  useEffect(() => {
    loadAuditLogs();
    loadStats();
  }, []);

  if (loading && logs.length === 0) {
    return (
      <div className="p-6">
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <Activity className="h-8 w-8 animate-spin mx-auto mb-4" />
            <p>Chargement des logs d'audit...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6 space-y-6">
      {/* En-tête */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold">Logs d'Audit</h2>
          <p className="text-muted-foreground">
            Consultez l'historique complet des actions de suppression dans l'application
          </p>
        </div>
        <Button onClick={exportLogs} className="flex items-center gap-2">
          <Download className="h-4 w-4" />
          Exporter
        </Button>
      </div>

      <Tabs defaultValue="logs" className="space-y-6">
        <TabsList>
          <TabsTrigger value="logs">Logs d'Audit</TabsTrigger>
          <TabsTrigger value="stats">Statistiques</TabsTrigger>
        </TabsList>

        <TabsContent value="logs" className="space-y-6">
          {/* Filtres */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Filter className="h-5 w-5" />
                Filtres
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="action">Action</Label>
                  <Select value={filters.action} onValueChange={(value) => setFilters(prev => ({ ...prev, action: value }))}>
                    <SelectTrigger>
                      <SelectValue placeholder="Toutes les actions" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Toutes les actions</SelectItem>
                      <SelectItem value="delete">Suppression</SelectItem>
                      <SelectItem value="force_delete">Suppression définitive</SelectItem>
                      <SelectItem value="restore">Restauration</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="table">Table</Label>
                  <Select value={filters.table_name} onValueChange={(value) => setFilters(prev => ({ ...prev, table_name: value }))}>
                    <SelectTrigger>
                      <SelectValue placeholder="Toutes les tables" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Toutes les tables</SelectItem>
                      <SelectItem value="users">Utilisateurs</SelectItem>
                      <SelectItem value="entites">Entités</SelectItem>
                      <SelectItem value="projets">Projets</SelectItem>
                      <SelectItem value="taches">Tâches</SelectItem>
                      <SelectItem value="roles">Rôles</SelectItem>
                      <SelectItem value="permissions">Permissions</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="search">Recherche</Label>
                  <Input
                    id="search"
                    placeholder="Rechercher dans les logs..."
                    value={filters.search}
                    onChange={(e) => setFilters(prev => ({ ...prev, search: e.target.value }))}
                  />
                </div>

                <div>
                  <Label htmlFor="date_debut">Date début</Label>
                  <Input
                    id="date_debut"
                    type="date"
                    value={filters.date_debut}
                    onChange={(e) => setFilters(prev => ({ ...prev, date_debut: e.target.value }))}
                  />
                </div>

                <div>
                  <Label htmlFor="date_fin">Date fin</Label>
                  <Input
                    id="date_fin"
                    type="date"
                    value={filters.date_fin}
                    onChange={(e) => setFilters(prev => ({ ...prev, date_fin: e.target.value }))}
                  />
                </div>

                <div className="flex items-end gap-2">
                  <Button onClick={applyFilters} className="flex-1">
                    Appliquer
                  </Button>
                  <Button variant="outline" onClick={resetFilters}>
                    Réinitialiser
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Tableau des logs */}
          <Card>
            <CardHeader>
              <CardTitle>Logs d'Audit</CardTitle>
              <CardDescription>
                {pagination.total} log(s) trouvé(s) - Page {pagination.current_page} sur {pagination.last_page}
              </CardDescription>
            </CardHeader>
            <CardContent>
              {logs.length === 0 ? (
                <div className="text-center py-8">
                  <Database className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                  <p className="text-muted-foreground">Aucun log d'audit trouvé</p>
                </div>
              ) : (
                <div className="space-y-4">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Action</TableHead>
                        <TableHead>Table</TableHead>
                        <TableHead>Résumé</TableHead>
                        <TableHead>Utilisateur</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {logs.map((log) => {
                        const actionInfo = getActionLabel(log.action);
                        const ActionIcon = actionInfo.icon;
                        
                        return (
                          <TableRow key={log.id}>
                            <TableCell>
                              <Badge variant={actionInfo.color as any} className="flex items-center gap-1 w-fit">
                                <ActionIcon className="h-3 w-3" />
                                {actionInfo.label}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              <Badge variant="outline">
                                {getTableLabel(log.table_name)}
                              </Badge>
                            </TableCell>
                            <TableCell className="max-w-xs truncate">
                              {log.deleted_data_summary || "Aucun résumé"}
                            </TableCell>
                            <TableCell>
                              <div className="flex items-center gap-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <div>
                                  <p className="font-medium">{log.user_name}</p>
                                  <p className="text-sm text-muted-foreground">{log.user_email}</p>
                                </div>
                              </div>
                            </TableCell>
                            <TableCell>
                              <div className="flex items-center gap-2">
                                <Clock className="h-4 w-4 text-muted-foreground" />
                                <span className="text-sm">
                                  {format(new Date(log.created_at), "dd/MM/yyyy HH:mm", { locale: fr })}
                                </span>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => {
                                  setSelectedLog(log);
                                  setShowDetails(true);
                                }}
                              >
                                <Eye className="h-4 w-4" />
                              </Button>
                            </TableCell>
                          </TableRow>
                        );
                      })}
                    </TableBody>
                  </Table>

                  {/* Pagination */}
                  {pagination.last_page > 1 && (
                    <div className="flex items-center justify-between">
                      <p className="text-sm text-muted-foreground">
                        Affichage de {((pagination.current_page - 1) * pagination.per_page) + 1} à{" "}
                        {Math.min(pagination.current_page * pagination.per_page, pagination.total)} sur {pagination.total} résultats
                      </p>
                      <div className="flex gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          disabled={pagination.current_page === 1}
                          onClick={() => loadAuditLogs(pagination.current_page - 1)}
                        >
                          Précédent
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          disabled={pagination.current_page === pagination.last_page}
                          onClick={() => loadAuditLogs(pagination.current_page + 1)}
                        >
                          Suivant
                        </Button>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="stats" className="space-y-6">
          {/* Statistiques générales */}
          {stats && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total des Logs</CardTitle>
                  <Database className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.total_logs}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Suppressions</CardTitle>
                  <Trash2 className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold text-destructive">{stats.deletions}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Suppressions Définitives</CardTitle>
                  <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold text-destructive">{stats.force_deletions}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Restaurations</CardTitle>
                  <RotateCcw className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.restorations}</div>
                </CardContent>
              </Card>
            </div>
          )}

          {/* Top des tables et utilisateurs */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Tables les Plus Affectées</CardTitle>
              </CardHeader>
              <CardContent>
                {topTables.length === 0 ? (
                  <p className="text-muted-foreground">Aucune donnée disponible</p>
                ) : (
                  <div className="space-y-2">
                    {topTables.map((table, index) => (
                      <div key={table.table_name} className="flex items-center justify-between">
                        <span className="text-sm">{getTableLabel(table.table_name)}</span>
                        <Badge variant="secondary">{table.count}</Badge>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Utilisateurs les Plus Actifs</CardTitle>
              </CardHeader>
              <CardContent>
                {topUsers.length === 0 ? (
                  <p className="text-muted-foreground">Aucune donnée disponible</p>
                ) : (
                  <div className="space-y-2">
                    {topUsers.map((user, index) => (
                      <div key={user.user_id} className="flex items-center justify-between">
                        <span className="text-sm">{user.user_name}</span>
                        <Badge variant="secondary">{user.count}</Badge>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>

      {/* Modal de détails */}
      <Dialog open={showDetails} onOpenChange={setShowDetails}>
        <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Détails du Log d'Audit</DialogTitle>
            <DialogDescription>
              Informations complètes sur cette action d'audit
            </DialogDescription>
          </DialogHeader>

          {selectedLog && (
            <div className="space-y-6">
              {/* Informations générales */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Action</Label>
                  <div className="mt-1">
                    <Badge variant={getActionLabel(selectedLog.action).color as any}>
                      {getActionLabel(selectedLog.action).label}
                    </Badge>
                  </div>
                </div>

                <div>
                  <Label className="text-sm font-medium">Table</Label>
                  <div className="mt-1">
                    <Badge variant="outline">
                      {getTableLabel(selectedLog.table_name)}
                    </Badge>
                  </div>
                </div>

                <div>
                  <Label className="text-sm font-medium">ID Enregistrement</Label>
                  <p className="mt-1 text-sm">{selectedLog.record_id}</p>
                </div>

                <div>
                  <Label className="text-sm font-medium">Date et Heure</Label>
                  <p className="mt-1 text-sm">
                    {format(new Date(selectedLog.created_at), "dd/MM/yyyy HH:mm:ss", { locale: fr })}
                  </p>
                </div>
              </div>

              <Separator />

              {/* Utilisateur responsable */}
              <div>
                <Label className="text-sm font-medium">Utilisateur Responsable</Label>
                <div className="mt-2 p-3 bg-muted rounded-lg">
                  <div className="flex items-center gap-3">
                    <User className="h-5 w-5 text-muted-foreground" />
                    <div>
                      <p className="font-medium">{selectedLog.user_name}</p>
                      <p className="text-sm text-muted-foreground">{selectedLog.user_email}</p>
                    </div>
                  </div>
                </div>
              </div>

              <Separator />

              {/* Contexte de la requête */}
              <div>
                <Label className="text-sm font-medium">Contexte de la Requête</Label>
                <div className="mt-2 space-y-2">
                  <div className="flex items-center gap-2">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm">IP: {selectedLog.ip_address}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Monitor className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm">Méthode: {selectedLog.request_method}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="text-sm">URL: {selectedLog.request_url}</span>
                  </div>
                  {selectedLog.user_agent && (
                    <div className="flex items-center gap-2">
                      <span className="text-sm">User-Agent: {selectedLog.user_agent}</span>
                    </div>
                  )}
                </div>
              </div>

              <Separator />

              {/* Résumé des données */}
              <div>
                <Label className="text-sm font-medium">Résumé des Données</Label>
                <div className="mt-2 p-3 bg-muted rounded-lg">
                  <p className="text-sm">{selectedLog.deleted_data_summary || "Aucun résumé disponible"}</p>
                </div>
              </div>

              {/* Raison de la suppression */}
              {selectedLog.reason && (
                <>
                  <Separator />
                  <div>
                    <Label className="text-sm font-medium">Raison</Label>
                    <div className="mt-2 p-3 bg-muted rounded-lg">
                      <p className="text-sm">{selectedLog.reason}</p>
                    </div>
                  </div>
                </>
              )}

              {/* Données complètes (si disponibles) */}
              {selectedLog.deleted_data && (
                <>
                  <Separator />
                  <div>
                    <Label className="text-sm font-medium">Données Complètes</Label>
                    <div className="mt-2 p-3 bg-muted rounded-lg">
                      <pre className="text-xs overflow-auto">
                        {JSON.stringify(selectedLog.deleted_data, null, 2)}
                      </pre>
                    </div>
                  </div>
                </>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
} 