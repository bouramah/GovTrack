import { ProtectedRoute } from "@/components/ProtectedRoute";
import Dashboard from "@/components/dashboard";

export default function Home() {
  return (
    <ProtectedRoute>
      <Dashboard />
    </ProtectedRoute>
  );
}
