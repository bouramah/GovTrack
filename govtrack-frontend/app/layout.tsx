import type React from "react";
import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { AuthProvider } from "@/contexts/AuthContext";
import { Toaster } from "@/components/ui/toaster";
import GlobalNotification from "@/components/global-notification";

const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "GovTrack - Système de Gestion d'Instructions ministérielles",
  description: "Plateforme moderne de gestion des instructions ministérielles avec système de permissions",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="fr">
      <body className={inter.className}>
        <AuthProvider>
          {children}
          <Toaster />
          <GlobalNotification />
        </AuthProvider>
      </body>
    </html>
  );
}
