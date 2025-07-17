"use client";

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "../ui/button";
import { Bell, LogOut, Menu, Plus, Settings, User, Building, LogOut as LogOutAll } from "lucide-react";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Dispatch, SetStateAction } from "react";
import { useAuth } from "@/contexts/AuthContext";
import { Badge } from "../ui/badge";

import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import NewProjectModal from "./NewProjectModal";
import MinistryLogo from "./MinistryLogo";
import Link from "next/link";

type topbarPropsT = {
  name: string;
  sidebarOpen: boolean;
  setSidebarOpen: Dispatch<SetStateAction<boolean>>;
};

const Topbar = ({ name, sidebarOpen, setSidebarOpen }: topbarPropsT) => {
  const { user, logout, logoutAll, getUserRole, getUserRoleLabel } = useAuth();

  const handleLogout = async () => {
    await logout();
  };

  const handleLogoutAll = async () => {
    await logoutAll();
  };

  const getInitials = () => {
    if (!user) return "U";
    return `${user.prenom?.charAt(0) || ""}${user.nom?.charAt(0) || ""}`.toUpperCase();
  };

  const getRoleBadgeColor = () => {
    const role = getUserRole();
    switch (role) {
      case 'admin':
        return 'bg-red-100 text-red-800 border-red-200';
      case 'director':
        return 'bg-blue-100 text-blue-800 border-blue-200';
      default:
        return 'bg-green-100 text-green-800 border-green-200';
    }
  };

  const getAffectationLabel = () => {
    if (!user?.affectation_actuelle) return null;
    return `${user.affectation_actuelle.poste} - ${user.affectation_actuelle.entite}`;
  };

  const getEntitesDirigees = () => {
    if (!user?.entites_dirigees?.length) return null;
    return user.entites_dirigees.map(ed => ed.entite_nom).join(', ');
  };

  return (
    <header className="bg-white border-b border-gray-100 shadow-sm fixed w-full lg:w-[calc(100%-16rem)] top-0 z-10">
      <div className="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        {/* Left: Menu Button (mobile), Logo and Date/Greeting */}
        <div className="flex items-center">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="mr-4 lg:hidden"
          >
            <Menu className="h-10 w-10" />
            <span className="sr-only">Toggle menu</span>
          </Button>
          
          {/* Logo du Ministère */}
          <div className="mr-6">
            <MinistryLogo size="sm" showText={true} />
          </div>
          
          <div>
            <h2 className="text-xl font-semibold text-gray-900">{name}</h2>
            {user && (
              <p className="text-sm text-gray-500">
                Bonjour, {user.prenom} {user.nom}
              </p>
            )}
          </div>
        </div>

        {/* Right: Notifications, New Project Button and Profile */}
        <div className="flex items-center space-x-4">
          {/* <Link href="/notifications" className="relative">
            <Bell className="h-5 w-5" />
            <span className="absolute top-[-6px] -right-1 h-2 w-2 bg-red-500 rounded-full"></span>
            <span className="sr-only">Notifications</span>
          </Link> */}
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                <Avatar className="h-8 w-8">
                  {user?.photo ? (
                    <AvatarImage src={user.photo} alt={`${user.prenom} ${user.nom}`} />
                  ) : null}
                  <AvatarFallback className="bg-blue-600 text-white">
                    {getInitials()}
                  </AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
              <DropdownMenuLabel>
                <div className="flex flex-col space-y-2">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium leading-none">
                        {user?.prenom} {user?.nom}
                      </p>
                      <p className="text-xs leading-none text-muted-foreground mt-1">
                        {user?.email}
                      </p>
                      {user?.matricule && (
                        <p className="text-xs leading-none text-muted-foreground">
                          Matricule: {user.matricule}
                        </p>
                      )}
                    </div>
                    <Badge className={`text-xs ${getRoleBadgeColor()}`}>
                      {getUserRoleLabel()}
                    </Badge>
                  </div>
                  
                  {getAffectationLabel() && (
                    <div className="flex items-center gap-2">
                      <Building className="w-3 h-3 text-gray-500" />
                      <span className="text-xs text-gray-600">
                        {getAffectationLabel()}
                      </span>
                    </div>
                  )}

                  {getEntitesDirigees() && (
                    <div className="flex items-center gap-2">
                      <Settings className="w-3 h-3 text-blue-500" />
                      <span className="text-xs text-blue-600">
                        Dirige: {getEntitesDirigees()}
                      </span>
                    </div>
                  )}

                  {user?.permissions && user.permissions.length > 0 && (
                    <div className="flex flex-wrap gap-1 mt-2">
                      {user.permissions.slice(0, 3).map((permission, index) => (
                        <Badge key={index} variant="outline" className="text-xs">
                          {permission.replace('_', ' ')}
                        </Badge>
                      ))}
                      {user.permissions.length > 3 && (
                        <Badge variant="outline" className="text-xs">
                          +{user.permissions.length - 3} autres
                        </Badge>
                      )}
                    </div>
                  )}
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem asChild>
                <Link href="/profile">
                  <User className="mr-2 h-4 w-4" />
                  <span>Profil</span>
                </Link>
              </DropdownMenuItem>
              {/* <DropdownMenuItem>
                <Settings className="mr-2 h-4 w-4" />
                <span>Paramètres</span>
              </DropdownMenuItem> */}
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={handleLogout} className="text-red-600">
                <LogOut className="mr-2 h-4 w-4" />
                <span>Déconnexion</span>
              </DropdownMenuItem>
              <DropdownMenuItem onClick={handleLogoutAll} className="text-red-600">
                <LogOutAll className="mr-2 h-4 w-4" />
                <span>Déconnexion de tous les appareils</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </header>
  );
};

export default Topbar;
