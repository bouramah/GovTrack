"use client";

import { Menu, Plus, Search, Filter, LayoutGrid, ListIcon } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { cn } from "@/lib/utils";
import Topbar from "./Shared/Topbar";
import { Dispatch, SetStateAction } from "react";

interface ProjectsHeaderProps {
  sidebarOpen: boolean;
  setSidebarOpen: Dispatch<SetStateAction<boolean>>;
  viewMode: "grid" | "list";
  onViewModeChange: (mode: "grid" | "list") => void;
  filterStatus: string | null;
  onFilterStatusChange: (status: string | null) => void;
}

export default function ProjectsHeader({
  sidebarOpen,
  setSidebarOpen,
  viewMode,
  onViewModeChange,
  filterStatus,
  onFilterStatusChange,
}: ProjectsHeaderProps) {
  return (
    <div className="bg-white border-b border-gray-200 sticky top-0 z-10">
      <Topbar
        name="Instruction"
        sidebarOpen={sidebarOpen}
        setSidebarOpen={setSidebarOpen}
      />

      {/* Filters Bar */}
      <div className="border-t border-gray-100 px-4 sm:px-6 lg:px-8 py-3">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">

          <div className="flex items-center space-x-2 w-full sm:w-auto justify-between sm:justify-end">
            <div className="bg-gray-100 rounded-lg p-1 flex items-center">
              <Button
                variant="ghost"
                size="sm"
                className={cn(
                  "h-8 px-3 text-sm font-medium",
                  viewMode === "grid"
                    ? "bg-white text-gray-900 shadow-sm"
                    : "text-gray-600 hover:text-gray-900 hover:bg-gray-200/50"
                )}
                onClick={() => onViewModeChange("grid")}
              >
                <LayoutGrid className="h-4 w-4 mr-2" />
                Grid
              </Button>
              <Button
                variant="ghost"
                size="sm"
                className={cn(
                  "h-8 px-3 text-sm font-medium",
                  viewMode === "list"
                    ? "bg-white text-gray-900 shadow-sm"
                    : "text-gray-600 hover:text-gray-900 hover:bg-gray-200/50"
                )}
                onClick={() => onViewModeChange("list")}
              >
                <ListIcon className="h-4 w-4 mr-2" />
                List
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
