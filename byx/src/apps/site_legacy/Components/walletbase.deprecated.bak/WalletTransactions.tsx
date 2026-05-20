import { ArrowDownLeft, ArrowUpRight, Clock3, Receipt } from "lucide-react";

type Transaction = {
  id: string;
  type: "send" | "receive";
  amount: string;
  denom: string;
  status?: string;
  time?: string;
};

type WalletTransactionsProps = {
  transactions: Transaction[];
  loading?: boolean;
};

function SkeletonRow() {
  return (
    <div className="flex items-center justify-between py-3 border-b border-white/5">
      <div className="wallet-skeleton h-4 w-28 rounded" />
      <div className="wallet-skeleton h-4 w-16 rounded" />
    </div>
  );
}

export function WalletTransactions({ transactions, loading }: WalletTransactionsProps) {
  return (
    <div className="wallet-card wallet-card-hover p-5">
      <div className="flex items-center justify-between mb-2">
        <div>
          <p className="text-sm wallet-muted">Transações</p>
          <h3 className="text-lg font-semibold text-white">Histórico recente</h3>
        </div>
        <div className="wallet-badge flex items-center gap-2">
          <Clock3 className="w-4 h-4 text-white/60" />
          Últimas movimentações
        </div>
      </div>

      <div className="space-y-1">
        {loading && (
          <>
            <SkeletonRow />
            <SkeletonRow />
          </>
        )}

        {!loading && transactions.length === 0 && (
          <div className="flex items-center gap-2 text-sm wallet-muted py-3">
            <Receipt className="w-4 h-4 text-white/40" />
            Nenhuma transação ainda.
          </div>
        )}

        {!loading &&
          transactions.map((tx) => (
            <div
              key={tx.id}
              className="flex items-center justify-between py-3 border-b border-white/5 last:border-0"
            >
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/5">
                  {tx.type === "receive" ? (
                    <ArrowDownLeft className="w-5 h-5 text-emerald-300" />
                  ) : (
                    <ArrowUpRight className="w-5 h-5 text-rose-300" />
                  )}
                </div>
                <div>
                  <p className="text-white font-medium">
                    {tx.type === "receive" ? "Recebido" : "Enviado"} · {tx.amount} {tx.denom}
                  </p>
                  <p className="text-xs wallet-muted">{tx.time || "—"}</p>
                </div>
              </div>
              <div className="wallet-badge text-xs">
                {tx.status ? tx.status : "—"}
              </div>
            </div>
          ))}
      </div>
    </div>
  );
}
