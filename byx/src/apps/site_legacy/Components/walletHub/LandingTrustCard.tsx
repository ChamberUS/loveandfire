import { Shield, Lock, EyeOff, KeyRound } from "lucide-react";

const icons = [Shield, Lock, EyeOff, KeyRound];

export function LandingTrustCard({ title, description, index }: { title: string; description: string; index: number }) {
  const Icon = icons[index % icons.length];
  return (
    <div className="hub-card p-4 hub-card-hover flex items-start gap-3 max-w-xs">
      <div className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
        <Icon className="w-5 h-5 text-emerald-300" />
      </div>
      <div>
        <p className="text-sm font-semibold text-white">{title}</p>
        <p className="text-xs hub-muted">{description}</p>
      </div>
    </div>
  );
}
