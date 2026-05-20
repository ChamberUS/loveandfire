import React, { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import {
  Activity,
  ArrowRight,
  BarChart3,
  MessageCircle,
  ShoppingBag,
  Store,
  Wallet,
  ShieldCheck,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { useStoreAccess } from "@/hooks/useStoreAccess";
import {
  getActiveProductsCount,
  getConfirmedPaymentsCount,
  getOpenTicketsCount,
  getOrdersCount,
} from "@/apps/site_legacy/api/analyticsClient";

type KpiCard = {
  title: string;
  icon: typeof ShoppingBag;
  value: number | null | undefined;
  loading: boolean;
  detail: string;
};

function formatValue(value: number | null | undefined) {
  if (value === null || value === undefined) return "—";
  return value.toLocaleString("pt-BR");
}

function subtitleFor(value: number | null | undefined) {
  if (value === null || value === undefined) return "Em breve";
  if (value === 0) return "0";
  return "Atualizado agora";
}

function LoadingBar() {
  return <span className="inline-flex h-8 w-16 animate-pulse rounded bg-white/10" />;
}

export default function Analytics() {
  const navigate = useNavigate();
  const { storeId, user, hasStore } = useStoreAccess();
  const ownerEmail = user?.email ?? null;

  // Produtos ativos: status === "available" filtrando pelo seller_email do usuário.
  const productsQuery = useQuery({
    queryKey: ["analytics", "products-count", ownerEmail],
    queryFn: () => getActiveProductsCount(ownerEmail),
  });

  // Pagamentos confirmados: PaymentRequest com status "paid" para o merchant/store atual.
  const paymentsQuery = useQuery({
    queryKey: ["analytics", "payments-count", storeId, ownerEmail],
    queryFn: () => getConfirmedPaymentsCount(storeId, ownerEmail),
  });

  // Pedidos/Vendas: entidade Sale (ou Order) associada ao seller_email do usuário.
  const ordersQuery = useQuery({
    queryKey: ["analytics", "orders-count", ownerEmail],
    queryFn: () => getOrdersCount(ownerEmail),
  });

  // Tickets/Chats: conversas ativas no ChatConversation.
  const ticketsQuery = useQuery({
    queryKey: ["analytics", "tickets-count", ownerEmail],
    queryFn: () => getOpenTicketsCount(ownerEmail),
  });

  const kpis: KpiCard[] = useMemo(
    () => [
      {
        title: "Produtos ativos",
        icon: ShoppingBag,
        value: productsQuery.data,
        loading: productsQuery.isLoading || productsQuery.isFetching,
        detail: "Produtos publicados com status ativo.",
      },
      {
        title: "Pagamentos confirmados",
        icon: Wallet,
        value: paymentsQuery.data,
        loading: paymentsQuery.isLoading || paymentsQuery.isFetching,
        detail: "PaymentRequests pagos (status paid).",
      },
      {
        title: "Pedidos/Vendas",
        icon: BarChart3,
        value: ordersQuery.data,
        loading: ordersQuery.isLoading || ordersQuery.isFetching,
        detail: "Pedidos registrados na loja.",
      },
      {
        title: "Tickets/Chats abertos",
        icon: MessageCircle,
        value: ticketsQuery.data,
        loading: ticketsQuery.isLoading || ticketsQuery.isFetching,
        detail: "Conversas ativas com clientes.",
      },
    ],
    [
      ordersQuery.data,
      ordersQuery.isFetching,
      ordersQuery.isLoading,
      paymentsQuery.data,
      paymentsQuery.isFetching,
      paymentsQuery.isLoading,
      productsQuery.data,
      productsQuery.isFetching,
      productsQuery.isLoading,
      ticketsQuery.data,
      ticketsQuery.isFetching,
      ticketsQuery.isLoading,
    ],
  );

  const quickActions = useMemo(
    () => [
      {
        title: hasStore ? "Registrar venda" : "Criar minha loja",
        description: hasStore
          ? "Registre uma venda manual para atualizar os KPIs."
          : "Complete o fluxo de criação e libere as análises.",
        to: hasStore ? "/sales-cashback" : "/merchant/setup",
      },
      {
        title: "Gerar cobrança",
        description: "Crie um pedido com QR para confirmar pagamentos.",
        to: hasStore ? "/merchant/requests" : "/merchant/setup",
      },
      {
        title: "Publicar produto",
        description: "Cadastre itens para aumentar seu catálogo ativo.",
        to: hasStore ? "/mystore" : "/merchant/setup",
      },
      {
        title: "Falar com clientes",
        description: "Abra o chat para responder pedidos e tickets.",
        to: "/chat",
      },
    ],
    [hasStore],
  );

  return (
    <div className="min-h-screen bg-[#0a0a0a] text-white">
      <div className="max-w-6xl mx-auto px-6 py-10 space-y-10">
        <header className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-1">
            <p className="text-white/60 text-sm flex items-center gap-2">
              <Activity className="w-4 h-4 text-emerald-400" />
              Painel conectado aos dados da loja
            </p>
            <h1 className="text-3xl md:text-4xl font-bold">Análises</h1>
            <p className="text-white/60 text-sm">
              KPIs com base em produtos ativos, cobranças confirmadas e conversas em andamento.
            </p>
          </div>
          <div className="flex flex-wrap gap-3">
            <Button
              variant="outline"
              className="border-white/15 text-white/80 hover:bg-white/10"
              onClick={() => navigate("/marketplace")}
            >
              Marketplace
            </Button>
            <Button
              className="bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white"
              onClick={() => navigate(hasStore ? "/mystore" : "/merchant/setup")}
            >
              <Store className="w-4 h-4 mr-2" />
              {hasStore ? "Minha loja" : "Criar minha loja"}
            </Button>
          </div>
        </header>

        <section className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          {kpis.map((kpi) => {
            const Icon = kpi.icon;
            return (
              <div
                key={kpi.title}
                className="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-2 shadow-lg"
              >
                <div className="flex items-center justify-between text-sm text-white/60">
                  <span>{kpi.title}</span>
                  <Icon className="w-4 h-4 text-white/40" />
                </div>
                <div className="text-3xl font-semibold">
                  {kpi.loading ? <LoadingBar /> : formatValue(kpi.value)}
                </div>
                <div className="text-white/50 text-sm">{subtitleFor(kpi.value)}</div>
                <div className="text-white/30 text-xs">{kpi.detail}</div>
              </div>
            );
          })}
        </section>

        <section className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div className="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-5 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-lg font-semibold">Próximas ações</h3>
                <p className="text-white/60 text-sm">
                  Complete estas etapas para movimentar os indicadores.
                </p>
              </div>
              <ShieldCheck className="w-5 h-5 text-emerald-400" />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              {quickActions.map((action) => (
                <button
                  key={action.title}
                  onClick={() => navigate(action.to)}
                  className="group rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-left hover:border-emerald-400/40 hover:bg-white/10 transition"
                >
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <p className="text-white font-semibold">{action.title}</p>
                      <p className="text-white/60 text-sm">{action.description}</p>
                    </div>
                    <ArrowRight className="w-4 h-4 text-white/40 group-hover:text-emerald-300" />
                  </div>
                </button>
              ))}
            </div>
          </div>

          <div className="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-semibold">Fontes de dados</h3>
              <Activity className="w-4 h-4 text-white/40" />
            </div>
            <ul className="space-y-2 text-sm text-white/70">
              <li>Produtos ativos: status "available" com seller_email do usuário.</li>
              <li>Pagamentos: PaymentRequests com status "paid" do merchant atual.</li>
              <li>Pedidos/Vendas: contagem de Sales (ou Order) associada ao seller.</li>
              <li>Tickets: conversas ativas em ChatConversation.</li>
            </ul>
          </div>
        </section>
      </div>
    </div>
  );
}
