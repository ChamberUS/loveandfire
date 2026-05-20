import type { ReactNode } from "react";
import { Shield, Lock, Zap, Eye, Server, Key } from "lucide-react";

type TrustBubbleProps = {
  icon: ReactNode;
  title: string;
  description: string;
  position: string;
  delay: string;
};

export const TrustBubble = ({ icon, title, description, position, delay }: TrustBubbleProps) => {
  return (
    <div
      className={`trust-bubble absolute ${position} glass-card p-4 max-w-[220px] opacity-0`}
      style={{
        animationDelay: delay,
        animationFillMode: "forwards",
        animation: `bubble-drop 0.9s ease-out forwards, float 6s ease-in-out ${delay} infinite`,
      }}
    >
      <div className="flex items-start gap-3">
        <div className="p-2 rounded-xl bg-aios-gold/10 shrink-0">{icon}</div>
        <div>
          <h4 className="text-sm font-semibold text-foreground mb-1">{title}</h4>
          <p className="text-xs text-muted-foreground leading-relaxed">{description}</p>
        </div>
      </div>
    </div>
  );
};

export const trustFeatures = [
  {
    icon: <Shield className="w-4 h-4 text-aios-gold" />,
    title: "Proteção Máxima",
    description: "Modelo de custódia dupla: uma violação exigiria comprometer você e a AIOS simultaneamente.",
    position: "top-[16%] left-[6%] lg:left-[10%]",
    delay: "0.15s",
  },
  {
    icon: <Lock className="w-4 h-4 text-aios-gold" />,
    title: "Criptografia Avançada",
    description: "Seus dados nunca são armazenados em texto simples. Tudo é criptografado em microsegundos.",
    position: "top-[18%] right-[8%] lg:right-[12%]",
    delay: "0.3s",
  },
  {
    icon: <Zap className="w-4 h-4 text-aios-gold" />,
    title: "Performance Ultra",
    description: "Transações processadas em milissegundos sem afetar throughput ou latência.",
    position: "top-[42%] left-[10%] lg:left-[14%]",
    delay: "0.45s",
  },
  {
    icon: <Eye className="w-4 h-4 text-aios-gold" />,
    title: "Privacidade Total",
    description: "Controle total sobre seus dados. Oculte saldos e endereços com um clique.",
    position: "top-[46%] right-[10%] lg:right-[16%]",
    delay: "0.6s",
  },
  {
    icon: <Server className="w-4 h-4 text-aios-gold" />,
    title: "Infraestrutura Resiliente",
    description: "Auto-escala para milhões de transações por dia. 99.99% de uptime garantido.",
    position: "bottom-[28%] left-[16%] lg:left-[20%]",
    delay: "0.75s",
  },
  {
    icon: <Key className="w-4 h-4 text-aios-gold" />,
    title: "Você é o Dono",
    description: "Nós protegemos, você controla. Sem lock-in, máxima flexibilidade.",
    position: "bottom-[32%] right-[14%] lg:right-[20%]",
    delay: "0.9s",
  },
];
