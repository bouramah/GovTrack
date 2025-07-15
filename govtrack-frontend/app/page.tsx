"use client";
import { ProtectedRoute } from "@/components/ProtectedRoute";
import ProjectDashboard from "@/components/ProjectDashboard";
import { Sidebar } from "@/components/sidebar";
import Topbar from "@/components/Shared/Topbar";
import { useState } from "react";

export default function Home() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  return (
    <ProtectedRoute>
      <div className="bg-gray-50">
        <Sidebar open={sidebarOpen} setOpen={setSidebarOpen} />

        {/* Main Content */}
        <div className="lg:w-[calc(100%-16rem)] lg:ml-64 flex flex-col pt-16">
          <Topbar name="Dashboard" sidebarOpen={sidebarOpen} setSidebarOpen={setSidebarOpen} />

          {/* Main Content Area */}
          <main className="flex-1 overflow-y-auto min-h-0 bg-gray-50 p-3 lg:p-6">
            <ProjectDashboard />
          </main>
        </div>
      </div>
    </ProtectedRoute>
  );
}
