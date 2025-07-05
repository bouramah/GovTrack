"use client";

import * as React from "react";
import * as SelectPrimitive from "@radix-ui/react-select";
import { Check, ChevronDown, ChevronUp, Search } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

export interface SearchableSelectOption {
  value: string;
  label: string;
  description?: string;
  badge?: string;
  disabled?: boolean;
}

interface SearchableSelectProps {
  options: SearchableSelectOption[];
  value?: string;
  onValueChange: (value: string) => void;
  placeholder?: string;
  searchPlaceholder?: string;
  emptyMessage?: string;
  disabled?: boolean;
  className?: string;
  maxHeight?: string;
}

export function SearchableSelect({
  options,
  value,
  onValueChange,
  placeholder = "Sélectionner...",
  searchPlaceholder = "Rechercher...",
  emptyMessage = "Aucun résultat trouvé.",
  disabled = false,
  className,
  maxHeight = "384px"
}: SearchableSelectProps) {
  const [open, setOpen] = React.useState(false);
  const [searchValue, setSearchValue] = React.useState("");

  const selectedOption = options.find(option => option.value === value);

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

  return (
    <SelectPrimitive.Root
      open={open}
      onOpenChange={setOpen}
      value={value}
      onValueChange={(newValue) => {
        onValueChange(newValue);
        setOpen(false);
        setSearchValue("");
      }}
      disabled={disabled}
    >
      <SelectPrimitive.Trigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          className={cn(
            "w-full justify-between",
            !selectedOption && "text-muted-foreground",
            disabled && "opacity-50 cursor-not-allowed",
            className
          )}
          disabled={disabled}
        >
          {selectedOption ? (
            <div className="flex items-center gap-2 truncate">
              <span className="truncate">{selectedOption.label}</span>
              {selectedOption.badge && (
                <Badge variant="outline" className="text-xs shrink-0">
                  {selectedOption.badge}
                </Badge>
              )}
            </div>
          ) : (
            placeholder
          )}
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
                <SelectPrimitive.Item
                  key={option.value}
                  value={option.value}
                  disabled={option.disabled}
                  className={cn(
                    "relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                  )}
                >
                  <span className="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                    <SelectPrimitive.ItemIndicator>
                      <Check className="h-4 w-4" />
                    </SelectPrimitive.ItemIndicator>
                  </span>

                  <SelectPrimitive.ItemText asChild>
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
                  </SelectPrimitive.ItemText>
                </SelectPrimitive.Item>
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
  );
} 