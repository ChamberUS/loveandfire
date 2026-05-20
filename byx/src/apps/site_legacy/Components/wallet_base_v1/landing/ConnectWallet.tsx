import { Wallet, ArrowRight, Sparkles } from "lucide-react";
import aiosLogo from "@/assets/brand/aios-logo.png";

type ConnectWalletProps = {
  onConnect: () => Promise<void> | void;
  connecting?: boolean;
};

export const ConnectWallet = ({ onConnect, connecting = false }: ConnectWalletProps) => {
  const handleConnect = async () => {
    await onConnect();
  };

  return (
    <div className="relative z-20 flex flex-col items-center">
      <div className="relative mb-8">
        <div className="pulse-ring w-32 h-32 -inset-4" style={{ animationDelay: "0s" }} />
        <div className="pulse-ring w-32 h-32 -inset-4" style={{ animationDelay: "0.5s" }} />
        <div className="pulse-ring w-32 h-32 -inset-4" style={{ animationDelay: "1s" }} />

        <div className="relative w-28 h-28 rounded-full overflow-hidden border-2 border-aios-gold/40 bg-background/80 backdrop-blur-sm p-4 glow-gold">
          <img src={aiosLogo} alt="AIOS" className="w-full h-full object-contain animate-float" />
        </div>

        <Sparkles className="absolute -top-2 -right-2 w-5 h-5 text-aios-gold animate-glow-pulse" />
        <Sparkles className="absolute -bottom-1 -left-3 w-4 h-4 text-aios-gold animate-glow-pulse animation-delay-500" />
      </div>

      <h1 className="text-4xl lg:text-5xl font-bold text-foreground mb-3 text-center">
        <span className="text-glow-gold">AIOS</span> Wallet
      </h1>
      <p className="text-lg text-muted-foreground mb-2 text-center max-w-md">Carteira Digital B2B</p>
      <p className="text-sm text-muted-foreground/70 mb-8 text-center max-w-lg">
        Segurança de nível empresarial para seus ativos digitais. Protegemos seus dados, você mantém o controle total.
      </p>

      <div className="flex items-center gap-2 mb-8 px-4 py-2 rounded-full bg-aios-gold/5 border border-aios-gold/20">
        <div className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
        <span className="text-xs text-muted-foreground">Criptografia Ativa • Ambiente Seguro</span>
      </div>

      <button
        onClick={handleConnect}
        disabled={connecting}
        className="group relative px-8 py-4 rounded-2xl font-semibold text-primary-foreground overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-2xl disabled:opacity-80 disabled:cursor-not-allowed"
        style={{
          background: "linear-gradient(135deg, hsl(var(--aios-gold)), hsl(40, 100%, 45%))",
          boxShadow: "0 10px 40px -10px hsl(var(--aios-gold) / 0.5)",
        }}
      >
        <div className="absolute inset-0 shimmer" />

        <div className="relative flex items-center gap-3">
          {connecting ? (
            <>
              <div className="w-5 h-5 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
              <span>Conectando...</span>
            </>
          ) : (
            <>
              <Wallet className="w-5 h-5" />
              <span>Conectar Carteira</span>
              <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </>
          )}
        </div>
      </button>

      <div className="flex items-center gap-6 mt-8 text-xs text-muted-foreground">
        <div className="flex items-center gap-1.5">
          <div className="w-1.5 h-1.5 rounded-full bg-aios-gold" />
          <span>256-bit SSL</span>
        </div>
        <div className="flex items-center gap-1.5">
          <div className="w-1.5 h-1.5 rounded-full bg-aios-gold" />
          <span>SOC 2 Type II</span>
        </div>
        <div className="flex items-center gap-1.5">
          <div className="w-1.5 h-1.5 rounded-full bg-aios-gold" />
          <span>PCI DSS</span>
        </div>
      </div>
    </div>
  );
};
