import { Copy } from "lucide-react";

type WalletHeaderProps = {
  logoSrc: string;
  title?: string;
  subtitle?: string;
  address?: string;
  onCopy?: () => void;
  copying?: boolean;
};

export function WalletHeader({
  logoSrc,
  title = "AIOS",
  subtitle = "Carteira Digital",
  address,
  onCopy,
  copying,
}: WalletHeaderProps) {
  return (
    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div className="flex items-center gap-3">
        <div className="w-12 h-12 rounded-full overflow-hidden border border-white/10 bg-black/50 p-2">
          <img src={logoSrc} alt="AIOS" className="w-full h-full object-contain" />
        </div>
        <div>
          <h1 className="text-2xl font-bold wallet-title">{title}</h1>
          <p className="text-sm wallet-muted">{subtitle}</p>
        </div>
      </div>
      {address && (
        <button
          type="button"
          onClick={onCopy}
          className="inline-flex items-center gap-2 px-3 py-2 rounded-xl wallet-card wallet-card-hover text-sm"
        >
          <Copy className="w-4 h-4 text-white/70" />
          <span className="text-white/80 truncate max-w-xs">{address}</span>
          <span className="text-xs text-emerald-300">{copying ? "Copiado" : "Copiar"}</span>
        </button>
      )}
    </div>
  );
}
