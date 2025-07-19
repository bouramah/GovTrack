import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useToast } from '@/hooks/use-toast';

interface FavoriteButtonProps {
  projectId: number;
  isFavorite: boolean;
  onToggle?: (isFavorite: boolean) => void;
  size?: 'sm' | 'default' | 'lg';
  variant?: 'ghost' | 'outline' | 'default';
  className?: string;
  showText?: boolean;
}

export const FavoriteButton: React.FC<FavoriteButtonProps> = ({
  projectId,
  isFavorite,
  onToggle,
  size = 'default',
  variant = 'ghost',
  className = '',
  showText = false
}) => {
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleToggle = async () => {
    if (loading) return;
    
    setLoading(true);
    try {
      // Simplement appeler le callback avec le nouvel état
      const newFavoriteState = !isFavorite;
      onToggle?.(newFavoriteState);
    } catch (error: any) {
      toast({
        title: 'Erreur',
        description: error.message || 'Erreur lors de la modification des favoris',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const sizeClasses = {
    sm: 'h-8 w-8',
    default: 'h-10 w-10',
    lg: 'h-12 w-12'
  };

  const iconSizes = {
    sm: 'h-4 w-4',
    default: 'h-5 w-5',
    lg: 'h-6 w-6'
  };

  return (
    <Button
      variant={variant}
      size={size}
      onClick={handleToggle}
      disabled={loading}
      className={cn(
        'transition-all duration-200',
        isFavorite && 'text-yellow-600 hover:text-yellow-700',
        !isFavorite && 'text-gray-400 hover:text-yellow-500',
        className
      )}
    >
      <Star 
        className={cn(
          iconSizes[size],
          isFavorite ? 'fill-current' : 'fill-none',
          'transition-all duration-200'
        )} 
      />
      {showText && (
        <span className="ml-2">
          {isFavorite ? 'Favori' : 'Ajouter aux favoris'}
        </span>
      )}
    </Button>
  );
};

export default FavoriteButton; 