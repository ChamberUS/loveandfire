import React, { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";
import { getBalances } from "@/api/chainRestClient";
import { DENOM, formatDenomAmount } from "@/config/chain";
import { useAuth } from "@/auth/AuthContext";
import { connectKeplr, isKeplrInstalled } from "@/wallet/keplr";
import aiosLogo from "@/assets/brand/aios-logo.png";
import "@/apps/site_legacy/components/walletHub/walletHub.css";
import "@/apps/site_legacy/components/walletHub/walletHero.css";
import { CryptoMatrix } from "@/apps/site_legacy/components/walletHub/CryptoMatrix";
import { LandingTrustCard } from "@/apps/site_legacy/components/walletHub/LandingTrustCard";
import { WalletHubHeader } from "@/apps/site_legacy/components/walletHub/WalletHubHeader";
import { WalletQuickActions, defaultQuickActions } from "@/apps/site_legacy/components/walletHub/WalletQuickActions";
import { MetricCards, defaultMetrics } from "@/apps/site_legacy/components/walletHub/MetricCards";
import { AssetDonutChart, buildSlicesFromBalances } from "@/apps/site_legacy/components/walletHub/AssetDonutChart";
import { TransactionHistory } from "@/apps/site_legacy/components/walletHub/TransactionHistory";
import { WalletHero } from "@/apps/site_legacy/components/walletHub/WalletHero";

const LAST_ADDRESS_KEY = 'aios_last_address';
const USER_PROFILE_KEY = 'aios_user_profile';

function loadLastAddress() {
  try {
    return window.localStorage.getItem(LAST_ADDRESS_KEY) || '';
  } catch {
    return '';
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
    if (parsed && typeof parsed === 'object' && parsed.email === email && typeof parsed.byxAddress === 'string') {
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
  const [copied, setCopied] = useState(false);
  const [lastError, setLastError] = useState(false);

  useEffect(() => {
    setSavingToProfile(Boolean(userEmail));
  }, [userEmail]);

  async function onQuery() {
    const trimmed = address.trim();
    if (!trimmed) {
      toast.error('Informe um endereço.');
      return;
    }
    if (loading) return;

    setLoading(true);
    setLastError(false);
    try {
      const result = await getBalances(trimmed);
      if (!result.ok) {
        toast.error(result.error || 'Não foi possível consultar.');
        setLastError(true);
        return;
      }
      const list = result.data?.balances || [];
      setBalances(Array.isArray(list) ? list : []);
      saveLastAddress(trimmed);
      if (userEmail && savingToProfile) saveProfileAddress(userEmail, trimmed);
    } finally {
      setLoading(false);
    }
  }

  async function onConnectKeplr() {
    if (connecting) return;
    if (!isKeplrInstalled()) {
      toast.error('Keplr não está instalado. Instale a extensão e tente novamente.');
      return;
    }

    setConnecting(true);
    try {
      const { address: addr } = await connectKeplr();
      setAddress(addr);
      saveLastAddress(addr);
      if (userEmail && savingToProfile) saveProfileAddress(userEmail, addr);
      toast.success('Carteira conectada!');
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Não foi possível conectar ao Keplr.';
      toast.error(message);
    } finally {
      setConnecting(false);
    }
  }

  const denomBalance = balances.find((c) => c.denom === DENOM)?.amount ?? '0';
  const totalAssets = balances.length || 0;

  const onCopyAddress = async () => {
    const trimmed = address.trim();
    if (!trimmed) return;
    try {
      await navigator.clipboard.writeText(trimmed);
      setCopied(true);
      toast.success('Endereço copiado');
      window.setTimeout(() => setCopied(false), 1200);
    } catch {
      toast.error('Não foi possível copiar.');
    }
  };

  const isConnected = Boolean(address.trim());

  const demoSlices = [
    { label: "BYX", value: 45, color: "#f5c249", note: "Demonstração" },
    { label: "USDT", value: 30, color: "#7b5cff", note: "Demonstração" },
    { label: "Créditos AIOS", value: 15, color: "#38ef7d", note: "Demonstração" },
    { label: "ETH", value: 10, color: "#00c6ff", note: "Demonstração" },
  ];

  const chartSlices = buildSlicesFromBalances(balances, demoSlices);

  const demoTransactions = [
    { id: "1", type: "receive", title: "Recebido de parceiro", subtitle: "BYX", amount: "+500 BYX", status: "Demonstração" },
    { id: "2", type: "send", title: "Envio para fornecedor", subtitle: "USDT", amount: "-1.000 USDT", status: "Demonstração" },
    { id: "3", type: "receive", title: "Cashback AIOS", subtitle: "Créditos", amount: "+5.000 Créditos", status: "Demonstração" },
    { id: "4", type: "send", title: "Pagamento gas", subtitle: "BYX", amount: "-20 BYX", status: "Demonstração" },
  ];

  return (
    <div className="wallet-hub-scope min-h-screen relative">
      <CryptoMatrix />
      <div className="relative z-10 max-w-6xl mx-auto px-6 py-10 space-y-8">
        {!isConnected ? (
          <>
            <WalletHero onConnect={onConnectKeplr} />
          <div className="hub-card hub-card-hover p-5 rounded-2xl space-y-3 max-w-3xl mx-auto">
            <p className="text-sm hub-muted text-center">Ou consulte um endereço BYX</p>
            <div className="flex gap-2 flex-col sm:flex-row">
              <input
                value={address}
                onChange={(e) => setAddress(e.target.value)}
                placeholder="byx1..."
                  className="flex-1 bg-black/40 border border-white/10 rounded-lg px-3 py-3 text-sm text-white placeholder:text-white/40"
                />
                <button
                  type="button"
                  onClick={onQuery}
                  disabled={loading}
                  className="px-4 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-cyan-500 text-white font-semibold"
                >
                  {loading ? "Consultando..." : "Consultar"}
                </button>
              </div>
            </div>
          </>
        ) : (
          <>
            <WalletHubHeader
              logoSrc={aiosLogo}
              address={address.trim()}
              onCopy={address.trim() ? onCopyAddress : undefined}
              copying={copied}
            />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <div className="hub-card hub-card-hover p-4 lg:col-span-2">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm hub-muted">Saldo total</p>
                    <h2 className="text-3xl font-semibold text-white">
                      {formatDenomAmount(denomBalance, DENOM)}
                    </h2>
                    <p className="text-xs hub-muted">Denom: {DENOM}</p>
                  </div>
                  <div className="hub-badge">Consulta read-only</div>
                </div>
              </div>
              <div className="hub-card hub-card-hover p-4 flex flex-col gap-2">
                <p className="text-sm hub-muted">Atualizar</p>
                <button
                  type="button"
                  onClick={onQuery}
                  disabled={loading}
                  className="w-full py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-cyan-500 text-white font-semibold"
                >
                  {loading ? "Consultando..." : "Atualizar saldos"}
                </button>
                {userEmail && (
                  <label className="flex items-center gap-3 text-sm hub-muted">
                    <input
                      type="checkbox"
                      checked={savingToProfile}
                      onChange={(e) => setSavingToProfile(e.target.checked)}
                    />
                    Associar endereço ao perfil ({userEmail})
                  </label>
                )}
                {lastError && <p className="text-xs text-amber-300">Não foi possível carregar. Tente novamente.</p>}
              </div>
            </div>

            <WalletQuickActions actions={defaultQuickActions} />

            <MetricCards metrics={defaultMetrics} />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <AssetDonutChart slices={chartSlices} title="Carteira AIOS" />
              <div className="lg:col-span-2">
                <TransactionHistory
                  transactions={demoTransactions}
                  loading={loading}
                  isDemo={!balances.length}
                  onRetry={onQuery}
                  error={lastError}
                />
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
