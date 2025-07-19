import React from 'react';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { PriorityBadge } from './PriorityBadge';

interface PrioritySelectProps {
  value?: string;
  onValueChange?: (value: string) => void;
  placeholder?: string;
  disabled?: boolean;
  className?: string;
}

const priorities = [
  { value: 'faible', label: 'Faible' },
  { value: 'normale', label: 'Normale' },
  { value: 'elevee', label: 'Élevée' },
  { value: 'critique', label: 'Critique' },
];

export const PrioritySelect: React.FC<PrioritySelectProps> = ({
  value,
  onValueChange,
  placeholder = 'Sélectionner une priorité',
  disabled = false,
  className = ''
}) => {
  return (
    <Select value={value} onValueChange={onValueChange} disabled={disabled}>
      <SelectTrigger className={className}>
        <SelectValue placeholder={placeholder}>
          {value && <PriorityBadge priorite={value} />}
        </SelectValue>
      </SelectTrigger>
      <SelectContent>
        {priorities.map((priority) => (
          <SelectItem key={priority.value} value={priority.value}>
            <div className="flex items-center space-x-2">
              <PriorityBadge priorite={priority.value} />
            </div>
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
};

export default PrioritySelect; 