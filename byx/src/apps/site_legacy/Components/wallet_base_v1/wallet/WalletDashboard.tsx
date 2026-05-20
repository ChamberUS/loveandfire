import { Wallet, Coins, CreditCard, Activity, LogOut } from "lucide-react";
import { WalletHeader } from "./WalletHeader";
import { IdentityCard } from "./IdentityCard";
import { AssetDonutChart } from "./AssetDonutChart";
import { PerformanceCard } from "./PerformanceCard";
import { TransactionHistory } from "./TransactionHistory";
import { QuickActions } from "./QuickActions";

// TODO: substituir mocks por dados reais de saldo/ativos/transações
const mockAssets = [
  { name: "BYX Token", value: 45000, color: "hsl(48, 100%, 55%)", percentage: 45 },
  { name: "USDT", value: 30000, color: "hsl(160, 80%, 45%)", percentage: 30 },
  { name: "Créditos AIOS", value: 15000, color: "hsl(270, 80%, 60%)", percentage: 15 },
  { name: "ETH", value: 10000, color: "hsl(220, 80%, 55%)", percentage: 10 },
];

const mockTransactions = [
  {
    id: "1",
    type: "receive" as const,
    asset: "BYX",
    amount: "500 BYX",
    value: "≈ R$ 2.500",
    address: "0x742d35Cc6634C0532925a3b844Bc9e7595f4E3",
    timestamp: "Há 2 horas",
    status: "completed" as const,
  },
  {
    id: "2",
    type: "send" as const,
    asset: "USDT",
    amount: "1.000 USDT",
    value: "≈ R$ 5.000",
    address: "0x8f7d35Cc6634C0532925a3b844Bc9e7595f4E3",
    timestamp: "Há 5 horas",
    status: "completed" as const,
  },
  {
    id: "3",
    type: "swap" as const,
    asset: "BYX → USDT",
    amount: "200 BYX",
    value: "≈ 1.000 USDT",
    address: "Troca interna",
    timestamp: "Ontem",
    status: "completed" as const,
  },
  {
    id: "4",
    type: "receive" as const,
    asset: "Créditos AIOS",
    amount: "5.000 Créditos",
    value: "≈ R$ 500",
    address: "Sistema AIOS",
    timestamp: "Há 2 dias",
    status: "completed" as const,
  },
  {
    id: "5",
    type: "send" as const,
    asset: "ETH",
    amount: "0.5 ETH",
    value: "≈ R$ 4.500",
    address: "0x3d7f35Cc6634C0532925a3b844Bc9e7595f4E3",
    timestamp: "Há 3 dias",
    status: "pending" as const,
  },
];

type WalletDashboardProps = {
  onDisconnect: () => void;
};

export const WalletDashboard = ({ onDisconnect }: WalletDashboardProps) => {
  return (
    <div className="min-h-screen bg-background relative overflow-hidden">
      <div className="fixed inset-0 pointer-events-none">
        <div className="absolute top-0 left-1/4 w-[500px] h-[500px] rounded-full bg-aios-gold/5 blur-[120px]" />
        <div className="absolute bottom-0 right-1/4 w-[400px] h-[400px] rounded-full bg-aios-purple/5 blur-[100px]" />
      </div>

      <div className="relative z-10 max-w-7xl mx-auto">
        <header className="flex items-center justify-between py-4 px-6 border-b border-border/50">
          <WalletHeader />
          <button
            onClick={onDisconnect}
            className="flex items-center gap-2 px-4 py-2 rounded-xl text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
          >
            <LogOut className="w-4 h-4" />
            <span className="hidden sm:inline">Desconectar</span>
          </button>
        </header>

        <main className="p-4 lg:p-6 space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2">
              <IdentityCard balance="R$ 125.430,00" walletAddress="byx1demoaddressxyz1234e3Af" userName="Empresa AIOS Ltda" />
            </div>

            <div className="space-y-4">
              <QuickActions />

              <div className="grid grid-cols-2 gap-3">
                <PerformanceCard
                  title="24h Volume"
                  value="R$ 12.5K"
                  change={8.5}
                  changeLabel="vs ontem"
                  icon={<Activity className="w-4 h-4 text-aios-gold" />}
                  delay="0.2s"
                />
                <PerformanceCard
                  title="Transações"
                  value="47"
                  change={12}
                  changeLabel="este mês"
                  icon={<CreditCard className="w-4 h-4 text-aios-purple" />}
                  delay="0.3s"
                />
              </div>
            </div>
          </div>

          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <PerformanceCard
              title="Valorização Total"
              value="+R$ 15.230"
              change={12.5}
              changeLabel="últimos 30 dias"
              icon={<Wallet className="w-4 h-4 text-emerald-400" />}
              delay="0.1s"
            />
            <PerformanceCard
              title="BYX Token"
              value="R$ 56.250"
              change={5.2}
              changeLabel="últimas 24h"
              icon={<Coins className="w-4 h-4 text-aios-gold" />}
              delay="0.2s"
            />
            <PerformanceCard
              title="Créditos AIOS"
              value="18.750"
              change={0}
              changeLabel="estável"
              icon={<CreditCard className="w-4 h-4 text-aios-purple" />}
              delay="0.3s"
            />
            <PerformanceCard
              title="Economia Gas"
              value="R$ 1.230"
              change={-3.2}
              changeLabel="este mês"
              icon={<Activity className="w-4 h-4 text-blue-400" />}
              delay="0.4s"
            />
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <AssetDonutChart assets={mockAssets} />
            <div className="lg:col-span-2">
              <TransactionHistory transactions={mockTransactions} />
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};
