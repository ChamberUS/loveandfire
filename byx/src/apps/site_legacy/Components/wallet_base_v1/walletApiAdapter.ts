import { getBalances, getTxsBySender } from "@/api/chainRestClient";
import { DENOM, DENOM_DECIMALS, formatDenomAmount } from "@/config/chain";
import { connectKeplr, isKeplrInstalled } from "@/wallet/keplr";

export type WalletAsset = { name: string; value: number; color: string; percentage: number };
export type WalletTx = {
  id: string;
  type: "send" | "receive" | "swap";
  asset: string;
  amount: string;
  numericAmount?: number;
  value: string;
  address: string;
  timestamp: string;
  status: "completed" | "pending";
};
export type WalletMetrics = { totalDisplay: string; volume24h?: string | null; txCount?: number };

function toDisplayAmount(amount: string, denom: string): { numeric: number; display: string } {
  const raw = Number(amount);
  if (!Number.isFinite(raw)) return { numeric: 0, display: `${amount} ${denom}` };
  if (denom !== DENOM) return { numeric: raw, display: `${raw.toLocaleString("pt-BR")} ${denom.toUpperCase()}` };

  const value = raw / 10 ** DENOM_DECIMALS;
  const displayDenom = DENOM.startsWith("u") ? DENOM.slice(1).toUpperCase() : DENOM.toUpperCase();
  return { numeric: value, display: `${value.toLocaleString("pt-BR", { maximumFractionDigits: DENOM_DECIMALS })} ${displayDenom}` };
}

export async function connectWallet(): Promise<{ ok: true; address: string } | { ok: false; error: string }> {
  if (!isKeplrInstalled()) return { ok: false, error: "Keplr não está instalado. Instale a extensão e tente novamente." };
  try {
    const { address } = await connectKeplr();
    return { ok: true, address };
  } catch (err) {
    const message = err instanceof Error ? err.message : "Não foi possível conectar ao Keplr.";
    return { ok: false, error: message };
  }
}

export async function fetchWalletBalances(
  address: string,
): Promise<
  | {
      ok: true;
      balances: Array<{ denom: string; amount: string }>;
      mainDisplay: string;
      mainNumeric: number;
      assets: WalletAsset[];
    }
  | { ok: false; error: string }
> {
  const trimmed = address.trim();
  if (!trimmed) return { ok: false, error: "Endereço vazio." };

  const result = await getBalances(trimmed);
  if (!result.ok) return { ok: false, error: result.error || "Falha ao carregar saldos." };

  const balances = Array.isArray(result.data?.balances) ? result.data.balances : [];
  const mainCoin = balances.find((b) => b.denom === DENOM) ?? balances[0];
  const mainAmount = mainCoin?.amount ?? "0";
  const { display: mainDisplay, numeric: mainNumeric } = toDisplayAmount(mainAmount, mainCoin?.denom ?? DENOM);

  const assets: WalletAsset[] = balances.map((b, idx) => {
    const { numeric } = toDisplayAmount(b.amount ?? "0", b.denom ?? "");
    const colors = ["hsl(48, 100%, 55%)", "hsl(160, 80%, 45%)", "hsl(270, 80%, 60%)", "hsl(220, 80%, 55%)"];
    return {
      name: (b.denom ?? "").toUpperCase(),
      value: numeric,
      color: colors[idx % colors.length],
      percentage: 0,
    };
  });

  return { ok: true, balances, mainDisplay, mainNumeric, assets };
}

function mapTxType(messageType: string | undefined): WalletTx["type"] {
  if (!messageType) return "send";
  if (messageType.includes("MsgSend")) return "send";
  if (messageType.includes("MsgTransfer")) return "send";
  return "swap";
}

export async function fetchWalletTransactions(address: string): Promise<{
  ok: true;
  txs: WalletTx[];
} | { ok: false; error: string; supported?: boolean }> {
  const trimmed = address.trim();
  if (!trimmed) return { ok: false, error: "Endereço vazio." };

  const result = await getTxsBySender(trimmed);
  if (!result.ok) return { ok: false, error: result.error || "Falha ao buscar transações.", supported: result.supported };

  const rawTxs: any[] = Array.isArray(result.data?.txs) ? result.data.txs : [];
  const txs: WalletTx[] = rawTxs.map((tx, idx) => {
    const msg = tx?.body?.messages?.[0] || {};
    const type = mapTxType(msg?.["@type"] as string | undefined);
    const amountField = msg.amount?.[0]?.amount ?? msg.amount?.amount ?? "0";
    const denomField = msg.amount?.[0]?.denom ?? msg.amount?.denom ?? DENOM;
    const { display, numeric } = toDisplayAmount(amountField, denomField);
    const timestamp = tx?.timestamp || tx?.auth_info?.fee?.gas || "";

    return {
      id: tx.txhash || `tx-${idx}`,
      type,
      asset: (denomField ?? "").toUpperCase(),
      amount: display,
      numericAmount: numeric,
      value: "—",
      address: tx?.body?.messages?.[0]?.to_address || tx?.body?.messages?.[0]?.from_address || trimmed,
      timestamp: timestamp || "—",
      status: "completed",
    };
  });

  return { ok: true, txs };
}

export function buildMetrics(mainNumeric: number, txs: WalletTx[]): WalletMetrics {
  const txCount = txs.length;
  const now = Date.now();
  const in24h = txs.filter((tx) => {
    const ts = Date.parse(tx.timestamp);
    if (!Number.isFinite(ts)) return false;
    return now - ts <= 24 * 60 * 60 * 1000;
  });
  const volume24h = in24h.reduce((sum, tx) => sum + (Number.isFinite(tx.numericAmount) ? Number(tx.numericAmount) : 0), 0);

  return {
    totalDisplay: formatDenomAmount(String(mainNumeric * 10 ** DENOM_DECIMALS), DENOM),
    volume24h: volume24h > 0 ? volume24h.toLocaleString("pt-BR") : null,
    txCount,
  };
}
