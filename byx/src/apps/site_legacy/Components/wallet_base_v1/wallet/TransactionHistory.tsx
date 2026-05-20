import { ArrowUpRight, ArrowDownLeft, RefreshCw, Clock } from "lucide-react";
import { cn } from "@/lib/utils";

type Transaction = {
  id: string;
  type: "send" | "receive" | "swap";
  asset: string;
  amount: string;
  value: string;
  address: string;
  timestamp: string;
  status: "completed" | "pending";
};

type TransactionHistoryProps = {
  transactions: Transaction[];
};

const getTransactionIcon = (type: Transaction["type"]) => {
  switch (type) {
    case "send":
      return <ArrowUpRight className="w-4 h-4 text-red-400" />;
    case "receive":
      return <ArrowDownLeft className="w-4 h-4 text-emerald-400" />;
    case "swap":
      return <RefreshCw className="w-4 h-4 text-aios-gold" />;
  }
};

const getTransactionLabel = (type: Transaction["type"]) => {
  switch (type) {
    case "send":
      return "Enviado";
    case "receive":
      return "Recebido";
    case "swap":
      return "Troca";
  }
};

export const TransactionHistory = ({ transactions }: TransactionHistoryProps) => {
  return (
    <div className="glass-card p-6 animate-fade-in animation-delay-300">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-foreground">Histórico de Operações</h3>
        <button className="text-sm text-aios-gold hover:text-aios-gold-glow transition-colors">Ver tudo</button>
      </div>

      <div className="space-y-3">
        {transactions.map((tx, index) => (
          <div key={tx.id} className="transaction-item" style={{ animationDelay: `${0.1 * index}s` }}>
            <div
              className={cn(
                "w-10 h-10 rounded-xl flex items-center justify-center",
                tx.type === "send" && "bg-red-500/10",
                tx.type === "receive" && "bg-emerald-500/10",
                tx.type === "swap" && "bg-aios-gold/10",
              )}
            >
              {getTransactionIcon(tx.type)}
            </div>

            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2">
                <p className="text-sm font-medium text-foreground">
                  {getTransactionLabel(tx.type)} {tx.asset}
                </p>
                {tx.status === "pending" && <Clock className="w-3 h-3 text-muted-foreground animate-pulse" />}
              </div>
              <p className="text-xs text-muted-foreground truncate">{tx.address}</p>
            </div>

            <div className="text-right">
              <p
                className={cn(
                  "text-sm font-medium",
                  tx.type === "send" && "text-red-400",
                  tx.type === "receive" && "text-emerald-400",
                  tx.type === "swap" && "text-foreground",
                )}
              >
                {tx.type === "send" ? "-" : tx.type === "receive" ? "+" : ""}
                {tx.amount}
              </p>
              <p className="text-xs text-muted-foreground">{tx.value}</p>
            </div>

            <div className="text-right hidden sm:block">
              <p className="text-xs text-muted-foreground">{tx.timestamp}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
