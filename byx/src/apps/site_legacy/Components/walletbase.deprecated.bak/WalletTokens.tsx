import { DENOM, formatDenomAmount } from "@/config/chain";
import { Coins, Sparkles } from "lucide-react";

type Balance = {
  denom: string;
  amount: string;
};

type WalletTokensProps = {
  balances: Balance[];
  loading?: boolean;
};

function SkeletonRow() {
  return (
    <div className="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
      <div className="wallet-skeleton h-4 rounded w-24" />
      <div className="wallet-skeleton h-4 rounded w-16" />
    </div>
  );
}

export function WalletTokens({ balances, loading }: WalletTokensProps) {
  return (
    <div className="wallet-card p-5 wallet-card-hover">
      <div className="flex items-center justify-between mb-2">
        <div>
          <p className="text-sm wallet-muted">Ativos</p>
          <h3 className="text-lg font-semibold text-white">Saldos encontrados</h3>
        </div>
        <div className="wallet-badge flex items-center gap-2">
          <Coins className="w-4 h-4 text-amber-300" />
          BYX e outros
        </div>
      </div>

      <div className="space-y-1">
        {loading && (
          <>
            <SkeletonRow />
            <SkeletonRow />
            <SkeletonRow />
          </>
        )}

        {!loading && balances.length === 0 && (
          <div className="flex items-center gap-2 text-sm wallet-muted py-2">
            <Sparkles className="w-4 h-4 text-white/40" />
            Nenhum saldo carregado ainda. Consulte um endereço.
          </div>
        )}

        {!loading &&
          balances.map((c) => (
            <div
              key={`${c.denom}:${c.amount}`}
              className="flex items-center justify-between py-2 border-b border-white/5 last:border-0"
            >
              <div className="flex items-center gap-2">
                <span className="text-white font-medium">{c.denom.toUpperCase()}</span>
                {c.denom === DENOM && <span className="wallet-badge text-xs text-emerald-300">Principal</span>}
              </div>
              <div className="text-white">
                {c.denom === DENOM ? formatDenomAmount(c.amount, c.denom) : `${c.amount} ${c.denom}`}
              </div>
            </div>
          ))}
      </div>
    </div>
  );
}
