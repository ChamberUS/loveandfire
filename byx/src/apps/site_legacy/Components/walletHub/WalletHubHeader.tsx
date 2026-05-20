import { ShieldCheck, Copy } from "lucide-react";

type WalletHubHeaderProps = {
  logoSrc: string;
  address?: string;
  onCopy?: () => void;
  copying?: boolean;
};

export function WalletHubHeader({ logoSrc, address, onCopy, copying }: WalletHubHeaderProps) {
  return (
    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div className="flex items-center gap-3">
        <div className="w-12 h-12 rounded-2xl overflow-hidden border border-white/10 bg-black/40 p-2">
          <img src={logoSrc} alt="AIOS" className="w-full h-full object-contain" />
        </div>
        <div>
          <h1 className="text-2xl font-bold hub-title">AIOS Wallet</h1>
          <div className="hub-badge gap-2">
            <ShieldCheck className="w-4 h-4 text-emerald-300" />
            Protegido
          </div>
        </div>
      </div>
      {address && (
        <button
          type="button"
          onClick={onCopy}
          className="hub-card hub-card-hover px-4 py-2 rounded-xl flex items-center gap-2 text-sm"
        >
          <Copy className="w-4 h-4 text-white/70" />
          <span className="truncate max-w-xs">{address}</span>
          <span className="text-emerald-300 text-xs">{copying ? "Copiado" : "Copiar"}</span>
        </button>
      )}
    </div>
  );
}
