import { Send, QrCode, ArrowLeftRight, Plus } from "lucide-react";
import { toast } from "sonner";

const actions = [
  { icon: Send, label: "Enviar", color: "text-aios-gold" },
  { icon: QrCode, label: "Receber", color: "text-emerald-400" },
  { icon: ArrowLeftRight, label: "Trocar", color: "text-aios-purple" },
  { icon: Plus, label: "Comprar", color: "text-blue-400" },
];

export const QuickActions = () => {
  const handleAction = (label: string) => {
    // TODO: acionar ações reais de transferência/recebimento
    toast.info(`Ação "${label}" será implementada em breve!`);
  };

  return (
    <div className="glass-card p-4 animate-fade-in animation-delay-100">
      <div className="grid grid-cols-4 gap-2">
        {actions.map((action, index) => (
          <button
            key={action.label}
            onClick={() => handleAction(action.label)}
            className="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-muted/50 transition-all duration-300 group"
            style={{ animationDelay: `${0.1 * index}s` }}
          >
            <div className="w-12 h-12 rounded-xl bg-muted/50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <action.icon className={`w-5 h-5 ${action.color}`} />
            </div>
            <span className="text-xs text-muted-foreground group-hover:text-foreground transition-colors">{action.label}</span>
          </button>
        ))}
      </div>
    </div>
  );
};
