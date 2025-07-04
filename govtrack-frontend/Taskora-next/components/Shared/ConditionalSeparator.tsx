import React from 'react';
import { Separator } from '@/components/ui/separator';

interface ConditionalSeparatorProps {
  showIfAnyVisible: boolean;
}

export const ConditionalSeparator: React.FC<ConditionalSeparatorProps> = ({ showIfAnyVisible }) => {
  return showIfAnyVisible ? <Separator className="my-2" /> : null;
}; 