import React, { useEffect, useMemo, useRef, useState } from "react";
import { toast } from "sonner";
import { DENOM, formatDenomAmount } from "@/config/chain";
import { useAuth } from "@/auth/AuthContext";
import "@/apps/site_legacy/styles/wallet_base_v1.css";
import { LandingPage } from "@/apps/site_legacy/components/wallet_base_v1/landing/LandingPage";
import { WalletHeader } from "@/apps/site_legacy/components/wallet_base_v1/wallet/WalletHeader";
import { IdentityCard } from "@/apps/site_legacy/components/wallet_base_v1/wallet/IdentityCard";
import { QuickActions } from "@/apps/site_legacy/components/wallet_base_v1/wallet/QuickActions";
import { PerformanceCard } from "@/apps/site_legacy/components/wallet_base_v1/wallet/PerformanceCard";
import { AssetDonutChart } from "@/apps/site_legacy/components/wallet_base_v1/wallet/AssetDonutChart";
import { TransactionHistory } from "@/apps/site_legacy/components/wallet_base_v1/wallet/TransactionHistory";
import {
  buildMetrics,
  connectWallet as connectWalletAdapter,
  fetchWalletBalances,
  fetchWalletTransactions,
} from "@/apps/site_legacy/components/wallet_base_v1/walletApiAdapter";
import { Wallet as WalletIcon, Coins, CreditCard, Activity } from "lucide-react";

const LAST_ADDRESS_KEY = "aios_last_address";
const USER_PROFILE_KEY = "aios_user_profile";

function loadLastAddress() {
  try {
    return window.localStorage.getItem(LAST_ADDRESS_KEY) || "";
  } catch {
    return "";
  }
}

function saveLastAddress(value) {
  try {
    window.localStorage.setItem(LAST_ADDRESS_KEY, value);
  } catch {
    // ignore
  }
}

function loadProfileAddress(email) {
  try {
    const raw = window.localStorage.getItem(USER_PROFILE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (parsed && typeof parsed === "object" && parsed.email === email && typeof parsed.byxAddress === "string") {
      return parsed.byxAddress;
    }
    return null;
  } catch {
    return null;
  }
}

function saveProfileAddress(email, address) {
  try {
    window.localStorage.setItem(USER_PROFILE_KEY, JSON.stringify({ email, byxAddress: address }));
  } catch {
    // ignore
  }
}

const DEMO_ASSETS = [
  { name: "BYX Token", value: 45000, color: "hsl(48, 100%, 55%)", percentage: 45 },
  { name: "USDT", value: 30000, color: "hsl(160, 80%, 45%)", percentage: 30 },
  { name: "Créditos AIOS", value: 15000, color: "hsl(270, 80%, 60%)", percentage: 15 },
  { name: "ETH", value: 10000, color: "hsl(220, 80%, 55%)", percentage: 10 },
];

function buildDemoTransactions(addr) {
  const displayAddress = addr || "byx1demo...";
  return [
    {
      id: "1",
      type: "receive",
      asset: "BYX",
      amount: "500 BYX",
      value: "≈ R$ 2.500",
      address: displayAddress,
      timestamp: "Há 2 horas",
      status: "completed",
    },
    {
      id: "2",
      type: "send",
      asset: "USDT",
      amount: "1.000 USDT",
      value: "≈ R$ 5.000",
      address: "byx1destino...",
      timestamp: "Há 5 horas",
      status: "completed",
    },
    {
      id: "3",
      type: "swap",
      asset: "BYX → USDT",
      amount: "200 BYX",
      value: "≈ 1.000 USDT",
      address: "Troca interna",
      timestamp: "Ontem",
      status: "completed",
    },
    {
      id: "4",
      type: "receive",
      asset: "Créditos AIOS",
      amount: "5.000 Créditos",
      value: "≈ R$ 500",
      address: "Sistema AIOS",
      timestamp: "Há 2 dias",
      status: "completed",
    },
  ];
}

const DEFAULT_METRICS = { totalDisplay: formatDenomAmount("0", DENOM), volume24h: null, txCount: null };
const REQUEST_TIMEOUT = 5000;

function withTimeout(promise, timeoutMs = REQUEST_TIMEOUT) {
  return Promise.race([
    promise,
    new Promise((_, reject) => {
      const timer = setTimeout(() => reject(new Error("timeout")), timeoutMs);
      // ensure timer cleared when resolved/rejected
      promise.finally(() => clearTimeout(timer));
    }),
  ]);
}

export default function Wallet() {
  const { userSession } = useAuth();
  const userEmail = userSession?.email ?? null;

  const initialAddress = useMemo(() => {
    const last = loadLastAddress();
    if (userEmail) return loadProfileAddress(userEmail) || last;
    return last;
  }, [userEmail]);

  const [address, setAddress] = useState(initialAddress);
  const [savingToProfile, setSavingToProfile] = useState(Boolean(userEmail));
  const [loading, setLoading] = useState(false);
  const [connecting, setConnecting] = useState(false);
  const [balances, setBalances] = useState([]);
  const [assets, setAssets] = useState(DEMO_ASSETS);
  const [transactions, setTransactions] = useState(() => buildDemoTransactions(initialAddress));
  const [metrics, setMetrics] = useState(DEFAULT_METRICS);
  const [mainDisplay, setMainDisplay] = useState(formatDenomAmount("0", DENOM));
  const [lastError, setLastError] = useState(false);
  const [networkOffline, setNetworkOffline] = useState(false);
  const networkToastShown = useRef(false);

  useEffect(() => {
    setSavingToProfile(Boolean(userEmail));
  }, [userEmail]);

  useEffect(() => {
    if (initialAddress.trim()) {
      void loadDataForAddress(initialAddress.trim(), { persist: false });
    }
  }, [initialAddress]);

  function persistAddressIfNeeded(trimmed) {
    if (userEmail && savingToProfile) saveProfileAddress(userEmail, trimmed);
    saveLastAddress(trimmed);
  }

  function markNetworkOffline(message = "Rede indisponível") {
    setNetworkOffline(true);
    if (!networkToastShown.current) {
      toast.info(message);
      networkToastShown.current = true;
    }
  }

  function resetNetworkFlag() {
    setNetworkOffline(false);
    networkToastShown.current = false;
  }

  function normalizeAssets(list) {
    if (!Array.isArray(list) || !list.length) return [];
    const total = list.reduce((sum, item) => sum + (Number(item.value) || 0), 0);
    if (!total) return list;
    return list.map((item) => ({ ...item, percentage: Number(((item.value / total) * 100).toFixed(1)) }));
  }

  async function loadDataForAddress(targetAddress, { persist = false, forceRetry = false } = {}) {
    const trimmed = targetAddress.trim();
    if (!trimmed) {
      toast.error("Informe um endereço.");
      return;
    }
    if (loading) return;
    if (networkOffline && !forceRetry) {
      setAssets(DEMO_ASSETS);
      setBalances([]);
      setTransactions(buildDemoTransactions(trimmed));
      setMetrics(DEFAULT_METRICS);
      setMainDisplay(formatDenomAmount("0", DENOM));
      return;
    }

    setLoading(true);
    setLastError(false);

    let balancesResult = null;
    let txResult = null;

    try {
      if (persist) {
        setAddress(trimmed);
        persistAddressIfNeeded(trimmed);
      }

      balancesResult = await withTimeout(fetchWalletBalances(trimmed));
      if (balancesResult.ok) {
        setBalances(balancesResult.balances);
        setMainDisplay(balancesResult.mainDisplay || formatDenomAmount("0", DENOM));
        const nextAssets = normalizeAssets(balancesResult.assets);
        setAssets(nextAssets.length ? nextAssets : DEMO_ASSETS);
        resetNetworkFlag();
      } else {
        setBalances([]);
        setMainDisplay(formatDenomAmount("0", DENOM));
        setAssets(DEMO_ASSETS);
        setLastError(true);
        markNetworkOffline(balancesResult.error || "Rede indisponível");
      }

      txResult = await withTimeout(fetchWalletTransactions(trimmed));
      if (txResult.ok) {
        setTransactions(txResult.txs.length ? txResult.txs : buildDemoTransactions(trimmed));
        resetNetworkFlag();
      } else {
        setTransactions(buildDemoTransactions(trimmed));
        setLastError(true);
        markNetworkOffline(txResult.error || "Rede indisponível");
      }

      const txsForMetrics = txResult && txResult.ok ? txResult.txs : [];
      const mainNumeric = balancesResult && balancesResult.ok ? balancesResult.mainNumeric : 0;
      setMetrics(balancesResult && balancesResult.ok ? buildMetrics(mainNumeric, txsForMetrics) : DEFAULT_METRICS);
    } catch (err) {
      setAssets(DEMO_ASSETS);
      setBalances([]);
      setTransactions(buildDemoTransactions(trimmed));
      setMetrics(DEFAULT_METRICS);
      setLastError(true);
      markNetworkOffline("Rede indisponível");
    } finally {
      setLoading(false);
    }
  }

  async function onQuery() {
    const trimmed = address.trim();
    if (!trimmed) {
      toast.error("Informe um endereço.");
      return;
    }
    await loadDataForAddress(trimmed, { persist: true, forceRetry: true });
  }

  async function onConnectKeplr() {
    if (connecting) return;

    setConnecting(true);
    try {
      const result = await connectWalletAdapter();
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      await loadDataForAddress(result.address, { persist: true, forceRetry: true });
      toast.success("Carteira conectada!");
    } finally {
      setConnecting(false);
    }
  }

  const denomBalance = balances.find((c) => c.denom === DENOM)?.amount ?? "0";
  const isConnected = Boolean(address.trim());
  const assetsForChart = assets?.length ? assets : DEMO_ASSETS;
  const transactionsForHistory = transactions?.length ? transactions : buildDemoTransactions(address);
  const totalBalanceDisplay = metrics?.totalDisplay || mainDisplay || formatDenomAmount(denomBalance, DENOM);
  const volumeDisplay = metrics?.volume24h ?? "—";
  const txCountDisplay = typeof metrics?.txCount === "number" ? String(metrics.txCount) : "—";
  const primaryAsset = assetsForChart[0];
  const primaryAssetDisplay = primaryAsset ? `${primaryAsset.value.toLocaleString("pt-BR")} ${primaryAsset.name}` : "—";
  const creditAsset = assetsForChart.find((asset) => asset.name.toLowerCase().includes("crédito")) || null;
  const creditDisplay = creditAsset ? `${creditAsset.value.toLocaleString("pt-BR")} ${creditAsset.name}` : "—";

  return (
    <div className="wallet-base-scope min-h-screen bg-background">
      {!isConnected ? (
        <div className="relative">
          <LandingPage onConnect={onConnectKeplr} connecting={connecting} />
          <div className="max-w-2xl mx-auto px-4 pb-10">
            <div className="glass-card p-4 mt-6">
              <p className="text-sm text-muted-foreground text-center mb-3">Ou consulte um endereço BYX</p>
              <div className="flex gap-2 flex-col sm:flex-row">
                <input
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  placeholder="byx1..."
                  className="flex-1 bg-background/80 border border-border rounded-lg px-3 py-3 text-sm text-foreground placeholder:text-muted-foreground"
                />
                <button
                  type="button"
                  onClick={onQuery}
                  disabled={loading}
                  className="px-4 py-3 rounded-lg font-semibold text-primary-foreground"
                  style={{ background: "linear-gradient(135deg, hsl(var(--aios-gold)), hsl(40, 100%, 45%))" }}
                >
                  {loading ? "Consultando..." : "Consultar"}
                </button>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="relative overflow-hidden min-h-screen">
          <div className="fixed inset-0 pointer-events-none">
            <div className="absolute top-0 left-1/4 w-[500px] h-[500px] rounded-full bg-aios-gold/5 blur-[120px]" />
            <div className="absolute bottom-0 right-1/4 w-[400px] h-[400px] rounded-full bg-aios-purple/5 blur-[100px]" />
          </div>

          <div className="relative z-10 max-w-7xl mx-auto p-4 lg:p-6 space-y-6">
            <header className="flex items-center justify-between py-4 px-2 lg:px-0 border-b border-border/50">
              <WalletHeader />
              <button
                onClick={() => {
                  setAddress("");
                  setBalances([]);
                  setAssets(DEMO_ASSETS);
                  setTransactions(buildDemoTransactions(""));
                  setMetrics(DEFAULT_METRICS);
                  setMainDisplay(formatDenomAmount("0", DENOM));
                  setLastError(false);
                  resetNetworkFlag();
                }}
                className="flex items-center gap-2 px-4 py-2 rounded-xl text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
              >
                Limpar endereço
              </button>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div className="lg:col-span-2">
                <IdentityCard
                  balance={totalBalanceDisplay}
                  walletAddress={address.trim() || "byx1demo"}
                  userName={userEmail || "Conta AIOS"}
                />
              </div>

              <div className="space-y-4">
                <QuickActions />

                <div className="grid grid-cols-2 gap-3">
                  <PerformanceCard
                    title="24h Volume"
                    value={volumeDisplay}
                    change={0}
                    changeLabel="vs ontem"
                    icon={<Activity className="w-4 h-4 text-aios-gold" />}
                    delay="0.2s"
                  />
                  <PerformanceCard
                    title="Transações"
                    value={txCountDisplay}
                    change={0}
                    changeLabel="últimas 24h"
                    icon={<CreditCard className="w-4 h-4 text-aios-purple" />}
                    delay="0.3s"
                  />
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
              <PerformanceCard
                title="Valorização Total"
                value={totalBalanceDisplay}
                change={0}
                changeLabel="saldo atual"
                icon={<WalletIcon className="w-4 h-4 text-emerald-400" />}
                delay="0.1s"
              />
              <PerformanceCard
                title="BYX Token"
                value={primaryAssetDisplay}
                change={0}
                changeLabel="principal"
                icon={<Coins className="w-4 h-4 text-aios-gold" />}
                delay="0.2s"
              />
              <PerformanceCard
                title="Créditos AIOS"
                value={creditDisplay}
                change={0}
                changeLabel="estável"
                icon={<CreditCard className="w-4 h-4 text-aios-purple" />}
                delay="0.3s"
              />
              <PerformanceCard
                title="Economia Gas"
                value="—"
                change={0}
                changeLabel="em breve"
                icon={<Activity className="w-4 h-4 text-blue-400" />}
                delay="0.4s"
              />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <AssetDonutChart assets={assetsForChart} />
              <div className="lg:col-span-2">
                <TransactionHistory transactions={transactionsForHistory} />
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
