"use client"

import { useState } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import { useAuth } from "@/contexts/AuthContext"
import { usePermissions } from "@/hooks/use-permissions"
import { useRolePermissions } from "@/hooks/useRolePermissions"
import { usePermissionPermissions } from "@/hooks/usePermissionPermissions"
import {
  LayoutDashboard,
  CheckSquare,
  Calendar,
  Users,
  FolderKanban,
  Bell,
  Settings,
  Home,
  MessageSquare,
  Menu,
  BadgeCheck,
  ChevronsUpDown,
  CreditCard,
  LogOut,
  Sparkles,
  User,
  UserCog,
  Building,
  Shield,
  Activity,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuGroup,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar"
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet"

export function AppSidebar() {
  const [collapsed, setCollapsed] = useState(false)
  const pathname = usePathname()
  const { user, logout } = useAuth()
  const { hasPermission } = usePermissions()
  const rolePermissions = useRolePermissions()
  const permissionPermissions = usePermissionPermissions()

  const navItems = [
    { href: "/", label: "Dashboard", icon: LayoutDashboard },
    { href: "/my-tasks", label: "Mes Tâches", icon: CheckSquare },
    { href: "/projects", label: "Projets", icon: FolderKanban },
    { href: "/kanban", label: "Kanban", icon: FolderKanban },
    { href: "/calendar", label: "Calendrier", icon: Calendar },
    { href: "/contacts", label: "Contacts", icon: Users },
    { href: "/messages", label: "Messages", icon: MessageSquare },
    { href: "/notifications", label: "Notifications", icon: Bell },
    ...(hasPermission('view_users_list') ? [{ href: "/users", label: "Gestion Utilisateurs", icon: UserCog }] : []),
    ...(hasPermission('view_entities_list') ? [{ href: "/entities", label: "Entités", icon: Building }] : []),
    ...((rolePermissions.canViewList || permissionPermissions.canViewList) ? [{ href: "/roles", label: "Rôles & Permissions", icon: Shield }] : []),
    ...(hasPermission('view_audit_logs') ? [{ href: "/audit", label: "Audit & Traçabilité", icon: Activity }] : []),
    { href: "/settings", label: "Paramètres", icon: Settings },
  ]

  const handleLogout = async () => {
    await logout()
  }

  return (
    <div
      className={cn(
        "flex flex-col border-r bg-background h-screen transition-all duration-300",
        collapsed ? "w-16" : "w-64",
      )}
    >
      <div className="flex h-14 items-center px-4 border-b">
        <Link href="/" className="flex items-center gap-2">
          <Home className="h-6 w-6" />
          {!collapsed && <span className="font-bold text-xl">GovTrack</span>}
        </Link>
        <Button variant="ghost" size="icon" className="ml-auto" onClick={() => setCollapsed(!collapsed)}>
          <Menu className="h-5 w-5" />
        </Button>
      </div>
      <ScrollArea className="flex-1 py-2">
        <nav className="grid gap-1 px-2">
          {navItems.map((item, index) => {
            const Icon = item.icon
            const isActive = pathname === item.href

            return (
              <Button
                key={index}
                variant={isActive ? "secondary" : "ghost"}
                className={cn("flex items-center gap-3 justify-start", collapsed ? "px-2" : "px-3")}
                asChild
              >
                <Link href={item.href}>
                  <Icon className={cn("h-5 w-5", isActive ? "text-primary" : "text-muted-foreground")} />
                  {!collapsed && <span>{item.label}</span>}
                </Link>
              </Button>
            )
          })}
        </nav>
      </ScrollArea>
      
      {/* Menu utilisateur */}
      {!collapsed && user && (
        <div className="border-t p-2">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button
                variant="ghost"
                className="w-full justify-start h-auto p-2"
              >
                <Avatar className="h-8 w-8 rounded-lg">
                  <AvatarImage src={user?.photo} alt={`${user?.prenom} ${user?.nom}`} />
                  <AvatarFallback className="rounded-lg">
                    {user?.prenom?.charAt(0)}{user?.nom?.charAt(0)}
                  </AvatarFallback>
                </Avatar>
                <div className="ml-2 flex-1 text-left text-sm">
                  <div className="truncate font-semibold">{user?.prenom} {user?.nom}</div>
                  <div className="truncate text-xs text-muted-foreground">{user?.email}</div>
                </div>
                <ChevronsUpDown className="ml-auto h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              className="w-56"
              side="right"
              align="end"
              sideOffset={8}
            >
              <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                  <Avatar className="h-8 w-8 rounded-lg">
                    <AvatarImage src={user?.photo} alt={`${user?.prenom} ${user?.nom}`} />
                    <AvatarFallback className="rounded-lg">
                      {user?.prenom?.charAt(0)}{user?.nom?.charAt(0)}
                    </AvatarFallback>
                  </Avatar>
                  <div className="grid flex-1 text-left text-sm leading-tight">
                    <span className="truncate font-semibold">{user?.prenom} {user?.nom}</span>
                    <span className="truncate text-xs">{user?.email}</span>
                  </div>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                  <Link href="/profile" className="cursor-pointer">
                    <User className="mr-2 h-4 w-4" />
                    Mon Profil
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <BadgeCheck className="mr-2 h-4 w-4" />
                  Compte
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <Bell className="mr-2 h-4 w-4" />
                  Notifications
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={handleLogout} className="cursor-pointer">
                <LogOut className="mr-2 h-4 w-4" />
                Déconnexion
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      )}
    </div>
  )
}
