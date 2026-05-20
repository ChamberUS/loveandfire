import { Shield, Lock, EyeOff, Zap, Server, Crown } from "lucide-react";
import aiosLogo from "@/assets/brand/aios-logo.png";
import { CryptoMatrix } from "./CryptoMatrix";
import "./walletHero.css";

const trustBubbles = [
  {
    title: "Proteção Máxima",
    description: "Modelo de custódia dupla: uma violação exigiria comprometer você e a AIOS simultaneamente.",
    icon: Shield,
    top: "12%",
    left: "14%",
  },
  {
    title: "Criptografia Avançada",
    description: "Seus dados nunca são armazenados em texto simples. Tudo é criptografado em microssegundos.",
    icon: Lock,
    top: "14%",
    right: "12%",
  },
  {
    title: "Privacidade Total",
    description: "Controle total sobre seus dados. Oculte saldos e endereços com um clique.",
    icon: EyeOff,
    top: "40%",
    right: "10%",
  },
  {
    title: "Performance Ultra",
    description: "Transações em milissegundos sem afetar throughput ou latência.",
    icon: Zap,
    top: "38%",
    left: "10%",
  },
  {
    title: "Infraestrutura Resiliente",
    description: "Auto-escala para milhões de transações por dia. 99,99% de uptime garantido.",
    icon: Server,
    top: "64%",
    left: "16%",
  },
  {
    title: "Você é o Dono",
    description: "Sem lock-in. Protegemos e você controla, com flexibilidade total.",
    icon: Crown,
    top: "66%",
    right: "18%",
  },
];

type WalletHeroProps = {
  onConnect: () => void;
};

export function WalletHero({ onConnect }: WalletHeroProps) {
  return (
    <div className="wallet-hero-scope relative min-h-[82vh] flex items-center justify-center">
      <CryptoMatrix />
      <div className="hero-grid" />
      <div className="hero-glow" />

      <div className="hero-center relative z-10 max-w-3xl mx-auto">
        <div className="hero-logo mb-4">
          <div className="hero-ring" />
          <img src={aiosLogo} alt="AIOS" />
        </div>
        <h1 className="text-4xl md:text-5xl font-bold hub-title mb-2 text-white drop-shadow-lg">AIOS Wallet</h1>
        <p className="text-lg hub-muted mb-3">Carteira Digital B2B</p>
        <p className="text-sm md:text-base hub-muted max-w-2xl mx-auto mb-4">
          Segurança de nível empresarial para seus ativos digitais. Protegemos seus dados, você mantém o controle total.
        </p>
        <div className="flex flex-col md:flex-row items-center justify-center gap-3 mb-4">
          <div className="hero-chip">
            <span className="w-2 h-2 rounded-full bg-emerald-300" />
            Criptografia Ativa
          </div>
          <div className="hero-chip">
            <span className="w-2 h-2 rounded-full bg-amber-300" />
            Ambiente Seguro
          </div>
        </div>
        <button
          type="button"
          onClick={onConnect}
          className="px-8 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-[#1a1c26] font-semibold shadow-lg shadow-amber-400/25 hover:scale-[1.01] transition-transform"
        >
          Conectar Carteira
        </button>
        <div className="flex items-center justify-center gap-3 mt-4 text-xs hub-muted">
          <span>• 256-bit SSL</span>
          <span>• SOC 2 Type II</span>
          <span>• PCI DSS</span>
        </div>
      </div>

      {trustBubbles.map((bubble, idx) => {
        const Icon = bubble.icon;
        return (
          <div
            key={bubble.title}
            className="float-card"
            style={{ top: bubble.top, left: bubble.left, right: bubble.right }}
          >
            <div className="icon">
              <Icon className="w-4 h-4" />
            </div>
            <p className="text-sm font-semibold">{bubble.title}</p>
            <p className="description">{bubble.description}</p>
          </div>
        );
      })}
    </div>
  );
}
