"use client";

import * as React from "react";
import * as SelectPrimitive from "@radix-ui/react-select";
import { Check, ChevronDown, ChevronUp, Search, X } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

export interface SearchableMultiSelectOption {
  value: string;
  label: string;
  description?: string;
  badge?: string;
  disabled?: boolean;
}

interface SearchableMultiSelectProps {
  options: SearchableMultiSelectOption[];
  value: string[];
  onValueChange: (value: string[]) => void;
  placeholder?: string;
  searchPlaceholder?: string;
  emptyMessage?: string;
  disabled?: boolean;
  className?: string;
  maxHeight?: string;
  maxSelectedItems?: number;
}

export function SearchableMultiSelect({
  options,
  value = [],
  onValueChange,
  placeholder = "Sélectionner...",
  searchPlaceholder = "Rechercher...",
  emptyMessage = "Aucun résultat trouvé.",
  disabled = false,
  className,
  maxHeight = "384px",
  maxSelectedItems = 3
}: SearchableMultiSelectProps) {
  const [open, setOpen] = React.useState(false);
  const [searchValue, setSearchValue] = React.useState("");

  const selectedOptions = options.filter(option => value.includes(option.value));

  // Filtrer les options invalides et s'assurer que toutes les propriétés nécessaires existent
  const validOptions = options.filter(option => 
    option && 
    typeof option.value === 'string' && 
    typeof option.label === 'string'
  );

  // Filtrer les options basé sur la recherche
  const filteredOptions = validOptions.filter(option =>
    (option.label || "").toLowerCase().includes(searchValue.toLowerCase()) ||
    (option.description || "").toLowerCase().includes(searchValue.toLowerCase()) ||
    (option.badge || "").toLowerCase().includes(searchValue.toLowerCase())
  );

  const handleSelect = (selectedValue: string) => {
    const newValue = value.includes(selectedValue)
      ? value.filter(v => v !== selectedValue)
      : [...value, selectedValue];
    onValueChange(newValue);
  };

  const removeItem = (itemValue: string) => {
    onValueChange(value.filter(v => v !== itemValue));
  };

  const getDisplayText = () => {
    if (selectedOptions.length === 0) return placeholder;
    if (selectedOptions.length === 1) return selectedOptions[0].label;
    if (selectedOptions.length <= maxSelectedItems) {
      return `${selectedOptions.length} élément(s) sélectionné(s)`;
    }
    return `${selectedOptions.length} éléments sélectionnés`;
  };

  return (
    <div className="relative">
      <SelectPrimitive.Root
        open={open}
        onOpenChange={setOpen}
        disabled={disabled}
      >
        <SelectPrimitive.Trigger asChild>
          <Button
            variant="outline"
            role="combobox"
            aria-expanded={open}
            className={cn(
              "w-full justify-between min-h-[40px] h-auto",
              selectedOptions.length === 0 && "text-muted-foreground",
              disabled && "opacity-50 cursor-not-allowed",
              className
            )}
            disabled={disabled}
          >
            <div className="flex flex-wrap gap-1 flex-1 min-w-0">
              {selectedOptions.length === 0 ? (
                <span className="text-muted-foreground">{placeholder}</span>
              ) : (
                <>
                  {selectedOptions.slice(0, maxSelectedItems).map((option) => (
                                         <Badge
                       key={option.value}
                       variant="secondary"
                       className="text-xs shrink-0 flex items-center gap-1"
                     >
                       <span className="truncate max-w-[120px]">{option.label}</span>
                       <div
                         onClick={(e) => {
                           e.stopPropagation();
                           removeItem(option.value);
                         }}
                         className="ml-1 hover:bg-secondary-foreground/20 rounded-full p-0.5 cursor-pointer"
                         role="button"
                         tabIndex={0}
                         onKeyDown={(e) => {
                           if (e.key === 'Enter' || e.key === ' ') {
                             e.preventDefault();
                             e.stopPropagation();
                             removeItem(option.value);
                           }
                         }}
                       >
                         <X className="h-3 w-3" />
                       </div>
                     </Badge>
                  ))}
                  {selectedOptions.length > maxSelectedItems && (
                    <Badge variant="outline" className="text-xs">
                      +{selectedOptions.length - maxSelectedItems} autres
                    </Badge>
                  )}
                </>
              )}
            </div>
            <SelectPrimitive.Icon asChild>
              <ChevronDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </SelectPrimitive.Icon>
          </Button>
        </SelectPrimitive.Trigger>

        <SelectPrimitive.Portal>
          <SelectPrimitive.Content
            className={cn(
              "relative z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",
              "data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1"
            )}
            position="popper"
            style={{ maxHeight: maxHeight }}
          >
            {/* Barre de recherche */}
            <div className="flex items-center border-b px-3 py-2">
              <Search className="mr-2 h-4 w-4 shrink-0 opacity-50" />
              <input
                placeholder={searchPlaceholder}
                value={searchValue}
                onChange={(e) => setSearchValue(e.target.value)}
                className="flex h-9 w-full rounded-md bg-transparent py-2 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                onClick={(e) => e.stopPropagation()}
              />
            </div>

            {/* Bouton de scroll vers le haut */}
            <SelectPrimitive.ScrollUpButton className="flex cursor-default items-center justify-center py-1">
              <ChevronUp className="h-4 w-4" />
            </SelectPrimitive.ScrollUpButton>

            {/* Contenu scrollable */}
            <SelectPrimitive.Viewport className="p-1">
              {filteredOptions.length === 0 ? (
                <div className="py-6 text-center text-sm text-muted-foreground">
                  {emptyMessage}
                </div>
              ) : (
                filteredOptions.map((option) => (
                  <div
                    key={option.value}
                    className={cn(
                      "relative flex w-full cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
                      value.includes(option.value) && "bg-accent text-accent-foreground"
                    )}
                    onClick={() => handleSelect(option.value)}
                  >
                    <span className="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                      {value.includes(option.value) && (
                        <Check className="h-4 w-4" />
                      )}
                    </span>

                    <div className="flex flex-col w-full min-w-0">
                      <div className="flex items-center justify-between">
                        <span className="font-medium truncate">{option.label}</span>
                        {option.badge && (
                          <Badge variant="outline" className="text-xs shrink-0 ml-2">
                            {option.badge}
                          </Badge>
                        )}
                      </div>
                      {option.description && (
                        <span className="text-xs text-muted-foreground mt-1 truncate">
                          {option.description}
                        </span>
                      )}
                    </div>
                  </div>
                ))
              )}
            </SelectPrimitive.Viewport>

            {/* Bouton de scroll vers le bas */}
            <SelectPrimitive.ScrollDownButton className="flex cursor-default items-center justify-center py-1">
              <ChevronDown className="h-4 w-4" />
            </SelectPrimitive.ScrollDownButton>
          </SelectPrimitive.Content>
        </SelectPrimitive.Portal>
      </SelectPrimitive.Root>
    </div>
  );
} 