import { ArrowDownLeft, ArrowUpRight, Clock3, Sparkles } from "lucide-react";

type Tx = {
  id: string;
  type: "send" | "receive";
  title: string;
  subtitle?: string;
  amount: string;
  status?: string;
  time?: string;
};

type TransactionHistoryProps = {
  transactions: Tx[];
  loading?: boolean;
  isDemo?: boolean;
  onRetry?: () => void;
  error?: boolean;
};

function SkeletonRow() {
  return (
    <div className="flex items-center justify-between py-3 border-b border-white/5">
      <div className="hub-skeleton h-4 w-28 rounded" />
      <div className="hub-skeleton h-4 w-16 rounded" />
    </div>
  );
}

export function TransactionHistory({ transactions, loading, isDemo, onRetry, error }: TransactionHistoryProps) {
  return (
    <div className="hub-card hub-card-hover p-4 h-full flex flex-col">
      <div className="flex items-center justify-between mb-3">
        <div>
          <p className="text-sm hub-muted">Histórico de operações</p>
          <h3 className="text-lg font-semibold text-white">Movimentações recentes</h3>
          {isDemo && <p className="text-xs text-amber-300 mt-1">Demonstração</p>}
        </div>
        {onRetry && (
          <button
            type="button"
            onClick={onRetry}
            className="text-xs hub-badge hover:border-white/40 transition-colors"
          >
            Recarregar
          </button>
        )}
      </div>

      <div className="space-y-1 flex-1">
        {loading && (
          <>
            <SkeletonRow />
            <SkeletonRow />
          </>
        )}

        {!loading && error && (
          <div className="flex items-center gap-2 text-sm text-amber-300 py-3">
            <Clock3 className="w-4 h-4" />
            Não foi possível carregar agora. Tente novamente.
          </div>
        )}

        {!loading && !error && transactions.length === 0 && (
          <div className="flex items-center gap-2 text-sm hub-muted py-3">
            <Sparkles className="w-4 h-4 text-white/40" />
            Nenhuma transação ainda.
          </div>
        )}

        {!loading &&
          !error &&
          transactions.map((tx) => (
            <div
              key={tx.id}
              className="flex items-center justify-between py-3 border-b border-white/5 last:border-0"
            >
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                  {tx.type === "receive" ? (
                    <ArrowDownLeft className="w-5 h-5 text-emerald-300" />
                  ) : (
                    <ArrowUpRight className="w-5 h-5 text-rose-300" />
                  )}
                </div>
                <div>
                  <p className="text-white font-medium">{tx.title}</p>
                  <p className="text-xs hub-muted">{tx.subtitle || "—"}</p>
                </div>
              </div>
              <div className="text-right">
                <p className="text-sm font-semibold text-white">{tx.amount}</p>
                <p className="text-xs hub-muted">{tx.status || tx.time || "—"}</p>
              </div>
            </div>
          ))}
      </div>
    </div>
  );
}
