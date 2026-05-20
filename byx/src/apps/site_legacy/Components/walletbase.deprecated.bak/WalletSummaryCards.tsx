type WalletSummaryCardsProps = {
  mainLabel: string;
  mainValue: string;
  denom: string;
  loading?: boolean;
  assetsCount: number;
};

function SkeletonLine({ width = "w-24" }: { width?: string }) {
  return <div className={`wallet-skeleton h-4 rounded ${width}`} />;
}

export function WalletSummaryCards({
  mainLabel,
  mainValue,
  denom,
  loading,
  assetsCount,
}: WalletSummaryCardsProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div className="wallet-card wallet-card-hover p-4 space-y-2">
        <div className="text-sm wallet-muted">{mainLabel}</div>
        <div className="text-3xl font-semibold text-white">
          {loading ? <SkeletonLine width="w-28" /> : mainValue || "—"}
        </div>
        <div className="text-xs wallet-muted">Denom: {denom}</div>
      </div>
      <div className="wallet-card wallet-card-hover p-4 space-y-2">
        <div className="text-sm wallet-muted">Ativos encontrados</div>
        <div className="text-3xl font-semibold text-white">
          {loading ? <SkeletonLine width="w-10" /> : assetsCount.toString()}
        </div>
        <div className="text-xs wallet-muted">Inclui o denom principal e outros saldos</div>
      </div>
      <div className="wallet-card wallet-card-hover p-4 space-y-2">
        <div className="text-sm wallet-muted">Status</div>
        <div className="text-lg font-semibold text-emerald-300">
          {loading ? <SkeletonLine width="w-20" /> : "Consulta read-only"}
        </div>
        <div className="text-xs wallet-muted">Sem riscos: só leitura da chain</div>
      </div>
    </div>
  );
}
