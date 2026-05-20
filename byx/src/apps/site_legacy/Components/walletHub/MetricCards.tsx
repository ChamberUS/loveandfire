import { Activity, CreditCard, Flame, ShieldCheck, TrendingUp, Zap } from "lucide-react";

type MetricCard = {
  title: string;
  value: string;
  change: string;
  icon: React.ComponentType<{ className?: string }>;
  tone?: "gold" | "purple" | "emerald" | "blue";
};

const toneMap: Record<NonNullable<MetricCard["tone"]>, string> = {
  gold: "text-amber-300",
  purple: "text-aios-purple text-[#9f80ff]",
  emerald: "text-emerald-300",
  blue: "text-cyan-300",
};

export function MetricCards({ metrics }: { metrics: MetricCard[] }) {
  return (
    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
      {metrics.map((metric, idx) => {
        const Icon = metric.icon;
        const tone = metric.tone ?? "gold";
        return (
          <div key={metric.title + idx} className="hub-card hub-card-hover p-4">
            <div className="flex items-center gap-2 mb-2">
              <Icon className={`w-4 h-4 ${toneMap[tone] || "text-white/70"}`} />
              <p className="text-sm hub-muted">{metric.title}</p>
            </div>
            <p className="text-2xl font-semibold text-white">{metric.value}</p>
            <p className="text-xs hub-muted">{metric.change}</p>
          </div>
        );
      })}
    </div>
  );
}

export const defaultMetrics: MetricCard[] = [
  { title: "24h Volume", value: "R$ 12,5K", change: "vs ontem", icon: Activity, tone: "emerald" },
  { title: "Transações", value: "47", change: "este mês", icon: CreditCard, tone: "gold" },
  { title: "Economia Gas", value: "R$ 1,2K", change: "30d", icon: Flame, tone: "orange" as any },
  { title: "Segurança", value: "Protegido", change: "Auditorias contínuas", icon: ShieldCheck, tone: "blue" },
];
