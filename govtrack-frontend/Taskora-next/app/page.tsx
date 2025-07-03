"use client";
import { ProtectedRoute } from "@/components/ProtectedRoute";
import ProjectDashboard from "@/components/ProjectDashboard";
import LayoutWithSidebar from "@/components/layout-with-sidebar";
import Topbar from "@/components/Shared/Topbar";
import { useState } from "react";

export default function Home() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  return (
    <ProtectedRoute>
      <LayoutWithSidebar>
        <div className="flex flex-col min-h-screen">
          <Topbar name="Dashboard" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />
          <main className="flex-1 pt-16 px-4 md:px-8 bg-gray-50">
            <ProjectDashboard />
          </main>
        </div>
      </LayoutWithSidebar>
    </ProtectedRoute>
  );
}
