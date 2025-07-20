"use client";

import { useEffect, useState } from 'react';

export default function SimpleFooter() {
  const [hasSidebar, setHasSidebar] = useState(false);

  useEffect(() => {
    // Détecter si on est sur une page avec sidebar (pages protégées)
    const isProtectedPage = window.location.pathname !== '/login' && 
                           window.location.pathname !== '/forgot-password' && 
                           window.location.pathname !== '/reset-password';
    setHasSidebar(isProtectedPage);
  }, []);

  return (
    <footer className={`bg-white border-t border-gray-200 py-3 ${hasSidebar ? 'lg:ml-64' : ''}`}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row justify-between items-center">
          <div className="flex items-center space-x-4 mb-2 md:mb-0">
            <img 
              src="/branding.png" 
              alt="Branding Guinée" 
              className="h-12 w-auto"
            />
            <img 
              src="/simandou.jpeg" 
              alt="Programme Simandou 2040" 
              className="h-12 w-auto"
            />
          </div>
          <div className="text-center md:text-right">
            <p className="text-xs text-gray-500">
              © 2025 République de Guinée • GovTrack
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
} 