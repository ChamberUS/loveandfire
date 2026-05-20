import aiosLogo from "@/assets/brand/aios-logo.png";

export const WalletHeader = () => {
  return (
    <div className="flex items-center gap-3">
      <div className="w-10 h-10 rounded-full overflow-hidden border border-aios-gold/30 bg-background p-1.5">
        <img src={aiosLogo} alt="AIOS" className="w-full h-full object-contain" />
      </div>
      <div>
        <h1 className="text-xl font-bold text-foreground">AIOS</h1>
        <p className="text-xs text-muted-foreground">Carteira Digital</p>
      </div>
    </div>
  );
};
