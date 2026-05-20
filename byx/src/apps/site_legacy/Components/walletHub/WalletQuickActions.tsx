import { ArrowRightLeft, Download, Send, ShoppingBag, Upload } from "lucide-react";

type Action = {
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  onClick?: () => void;
  disabled?: boolean;
  note?: string;
};

export function WalletQuickActions({ actions }: { actions: Action[] }) {
  return (
    <div className="hub-card hub-card-hover p-4">
      <div className="flex items-center justify-between mb-3">
        <p className="text-sm hub-muted">Ações rápidas</p>
        <div className="hub-badge">
          <ArrowRightLeft className="w-4 h-4 text-white/70" />
          Operações
        </div>
      </div>
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {actions.map((action) => {
          const Icon = action.icon;
          return (
            <button
              key={action.label}
              type="button"
              onClick={action.disabled ? undefined : action.onClick}
              className="hub-card p-3 text-left rounded-xl hub-card-hover border border-white/10"
            >
              <div className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-2">
                <Icon className="w-5 h-5 text-white/80" />
              </div>
              <p className="text-sm font-semibold text-white">{action.label}</p>
              <p className="text-xs hub-muted">{action.note || (action.disabled ? "Em breve" : "Disponível")}</p>
            </button>
          );
        })}
      </div>
    </div>
  );
}

export const defaultQuickActions: Action[] = [
  { label: "Enviar", icon: Upload, disabled: true, note: "Em breve" },
  { label: "Receber", icon: Download, disabled: true, note: "Em breve" },
  { label: "Trocar", icon: ArrowRightLeft, disabled: true, note: "Em breve" },
  { label: "Comprar", icon: ShoppingBag, disabled: true, note: "Em breve" },
];
