import React from 'react';
import { Badge } from '@/components/ui/badge';
import { 
  ArrowDown, 
  Minus, 
  ArrowUp, 
  AlertTriangle 
} from 'lucide-react';

interface PriorityBadgeProps {
  priorite: string;
  showIcon?: boolean;
  variant?: 'default' | 'secondary' | 'destructive' | 'outline';
  size?: 'sm' | 'default' | 'lg';
  className?: string;
}

const priorityConfig = {
  faible: {
    label: 'Faible',
    color: 'bg-gray-100 text-gray-800 border-gray-200',
    icon: ArrowDown,
    iconColor: 'text-gray-600'
  },
  normale: {
    label: 'Normale',
    color: 'bg-blue-100 text-blue-800 border-blue-200',
    icon: Minus,
    iconColor: 'text-blue-600'
  },
  elevee: {
    label: 'Élevée',
    color: 'bg-orange-100 text-orange-800 border-orange-200',
    icon: ArrowUp,
    iconColor: 'text-orange-600'
  },
  critique: {
    label: 'Critique',
    color: 'bg-red-100 text-red-800 border-red-200',
    icon: AlertTriangle,
    iconColor: 'text-red-600'
  }
};

export const PriorityBadge: React.FC<PriorityBadgeProps> = ({
  priorite,
  showIcon = true,
  variant = 'outline',
  size = 'default',
  className = ''
}) => {
  const config = priorityConfig[priorite as keyof typeof priorityConfig] || priorityConfig.normale;
  const IconComponent = config.icon;

  return (
    <Badge 
      variant={variant}
      className={`${config.color} ${className}`}
    >
      {showIcon && (
        <IconComponent className={`h-3 w-3 mr-1 ${config.iconColor}`} />
      )}
      {config.label}
    </Badge>
  );
};

export default PriorityBadge; 