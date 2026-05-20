import { useState } from "react";
import { Eye, EyeOff, Copy, Shield, Check } from "lucide-react";
import { CryptoMatrix } from "./CryptoMatrix";
import aiosLogo from "@/assets/brand/aios-logo.png";

type IdentityCardProps = {
  balance: string;
  walletAddress: string;
  userName: string;
};

export const IdentityCard = ({ balance, walletAddress, userName }: IdentityCardProps) => {
  const [showBalance, setShowBalance] = useState(true);
  const [showAddress, setShowAddress] = useState(true);
  const [copied, setCopied] = useState(false);

  const truncatedAddress = `${walletAddress.slice(0, 6)}...${walletAddress.slice(-4)}`;

  const copyAddress = async () => {
    // TODO: persistir endereço/copiar usando integração real de perfil
    await navigator.clipboard.writeText(walletAddress);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="identity-card relative min-h-[280px] animate-fade-in">
      <div className="scan-line" />

      <div className="particle w-1 h-1 top-1/4 left-1/4" style={{ animationDelay: "0s" }} />
      <div className="particle w-1.5 h-1.5 top-1/2 left-1/3" style={{ animationDelay: "0.5s" }} />
      <div className="particle w-1 h-1 top-3/4 left-2/3" style={{ animationDelay: "1s" }} />
      <div className="particle w-2 h-2 top-1/3 right-1/4" style={{ animationDelay: "1.5s" }} />

      <div className="absolute top-0 right-0 w-2/3 h-full overflow-hidden opacity-60">
        <CryptoMatrix />
      </div>

      <div className="absolute -top-20 -left-20 w-40 h-40 rounded-full bg-aios-gold/10 blur-3xl" />
      <div className="absolute -bottom-10 -right-10 w-32 h-32 rounded-full bg-aios-purple/10 blur-2xl" />

      <div className="relative z-10">
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-full overflow-hidden border-2 border-aios-gold/30 bg-background/50 p-1">
              <img src={aiosLogo} alt="AIOS" className="w-full h-full object-contain" />
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Carteira AIOS</p>
              <p className="font-semibold text-foreground">{userName}</p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Shield className="w-5 h-5 text-aios-gold" />
            <span className="text-xs text-aios-gold font-medium">Protegido</span>
          </div>
        </div>

        <div className="mb-6">
          <div className="flex items-center gap-2 mb-1">
            <p className="text-sm text-muted-foreground">Saldo Total</p>
            <button
              onClick={() => setShowBalance(!showBalance)}
              className="privacy-toggle"
              aria-label={showBalance ? "Ocultar saldo" : "Mostrar saldo"}
            >
              {showBalance ? <Eye className="w-4 h-4 text-muted-foreground" /> : <EyeOff className="w-4 h-4 text-aios-gold" />}
            </button>
          </div>
          <p className="text-4xl font-bold text-foreground tracking-tight">
            {showBalance ? <span className="text-glow-gold">{balance}</span> : <span className="text-muted-foreground">••••••</span>}
          </p>
        </div>

        <div className="flex items-center justify-between p-3 rounded-xl bg-background/30 border border-border/50">
          <div className="flex items-center gap-2">
            <p className="text-sm text-muted-foreground">Endereço:</p>
            <button
              onClick={() => setShowAddress(!showAddress)}
              className="privacy-toggle p-1"
              aria-label={showAddress ? "Ocultar endereço" : "Mostrar endereço"}
            >
              {showAddress ? <Eye className="w-3.5 h-3.5 text-muted-foreground" /> : <EyeOff className="w-3.5 h-3.5 text-aios-gold" />}
            </button>
          </div>
          <div className="flex items-center gap-2">
            <code className="font-mono text-sm text-foreground">{showAddress ? truncatedAddress : "0x••••...••••"}</code>
            <button onClick={copyAddress} className="p-1.5 rounded-lg hover:bg-muted/50 transition-colors" aria-label="Copiar endereço">
              {copied ? <Check className="w-4 h-4 text-aios-gold" /> : <Copy className="w-4 h-4 text-muted-foreground hover:text-foreground" />}
            </button>
          </div>
        </div>
      </div>

      <div className="absolute top-4 right-4 w-8 h-8 border-t border-r border-aios-gold/20 rounded-tr-lg" />
      <div className="absolute bottom-4 left-4 w-8 h-8 border-b border-l border-aios-gold/20 rounded-bl-lg" />
    </div>
  );
};
