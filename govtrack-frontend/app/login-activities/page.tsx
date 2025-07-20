"use client"; 

import { useState } from "react";
import { Sidebar } from "@/components/sidebar";
import LoginActivitiesPage from "@/components/login-activities-page";
import Topbar from "@/components/Shared/Topbar";
import { Toaster } from "@/components/ui/toaster";
import { ProtectedPage } from "@/components/ProtectedPage";

export default function LoginActivities() {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <ProtectedPage permission="view_global_login_activities">
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

        {/* Main Content */}
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col overflow-hidden pt-16">
          <Topbar
            name="Activités de Connexion"
            sidebarOpen={sidebarOpen}
            setSidebarOpen={setSidebarOpen}
          />

          {/* Main Content Area */}
          <main className="flex-1 overflow-y-auto p-3 lg:p-6">
            <LoginActivitiesPage />
          </main>
        </div>
        {/* Toast notifications */}
        <Toaster />
      </div>
    </ProtectedPage>
  );
} 