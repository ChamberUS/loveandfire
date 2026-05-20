import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { cn } from "@/lib/utils";
import type { LucideIcon } from "lucide-react";

export type QuickAction = {
  icon: LucideIcon;
  label: string;
  description: string;
  onClick: () => void;
  disabled?: boolean;
  tooltip?: string;
};

export function QuickActions({ actions }: { actions: QuickAction[] }) {
  return (
    <section className="py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-semibold text-foreground">Ações rápidas</h2>
        <span className="text-sm text-muted-foreground">
          Carteira → Chat → Vendas → QR → Análises → Suporte
        </span>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {actions.map((action) => {
          const content = (
            <button
              key={action.label}
              onClick={action.disabled ? undefined : action.onClick}
              className={cn(
                "glass-card-hover rounded-xl p-5 text-center group cursor-pointer",
                action.disabled && "opacity-60 cursor-not-allowed border-dashed",
              )}
              type="button"
            >
              <div className="w-12 h-12 mx-auto mb-3 rounded-xl bg-secondary/50 flex items-center justify-center group-hover:bg-primary/20 transition-colors duration-300">
                <action.icon
                  className={cn(
                    "w-6 h-6 text-muted-foreground transition-colors duration-300",
                    !action.disabled && "group-hover:text-primary",
                  )}
                />
              </div>
              <p className="font-medium text-foreground text-sm">{action.label}</p>
              <p className="text-xs text-muted-foreground mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                {action.description}
              </p>
            </button>
          );

          if (action.disabled && action.tooltip) {
            return (
              <Tooltip key={action.label} delayDuration={150}>
                <TooltipTrigger asChild>{content}</TooltipTrigger>
                <TooltipContent sideOffset={8}>{action.tooltip}</TooltipContent>
              </Tooltip>
            );
          }
          return content;
        })}
      </div>
    </section>
  );
}
