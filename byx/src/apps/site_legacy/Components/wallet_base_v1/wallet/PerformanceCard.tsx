import { TrendingUp, TrendingDown, Minus } from "lucide-react";
import { cn } from "@/lib/utils";
import type { ReactNode } from "react";

type PerformanceCardProps = {
  title: string;
  value: string;
  change: number;
  changeLabel: string;
  icon: ReactNode;
  delay?: string;
};

export const PerformanceCard = ({ title, value, change, changeLabel, icon, delay = "0s" }: PerformanceCardProps) => {
  const isPositive = change > 0;
  const isNeutral = change === 0;

  return (
    <div className="stat-card animate-fade-in" style={{ animationDelay: delay }}>
      <div className="flex items-start justify-between mb-3">
        <div className="p-2 rounded-xl bg-muted/50">{icon}</div>
        <div
          className={cn(
            "flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium",
            isPositive && "bg-emerald-500/10 text-emerald-400",
            !isPositive && !isNeutral && "bg-red-500/10 text-red-400",
            isNeutral && "bg-muted text-muted-foreground",
          )}
        >
          {isPositive && <TrendingUp className="w-3 h-3" />}
          {!isPositive && !isNeutral && <TrendingDown className="w-3 h-3" />}
          {isNeutral && <Minus className="w-3 h-3" />}
          <span>
            {isPositive ? "+" : ""}
            {change}%
          </span>
        </div>
      </div>

      <p className="text-sm text-muted-foreground mb-1">{title}</p>
      <p className="text-xl font-bold text-foreground">{value}</p>
      <p className="text-xs text-muted-foreground mt-1">{changeLabel}</p>
    </div>
  );
};
