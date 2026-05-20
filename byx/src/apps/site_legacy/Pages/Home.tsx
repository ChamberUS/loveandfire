import React, { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { ShoppingBag, MessageCircle, Wallet, TrendingUp, QrCode, BarChart3, ArrowLeftRight, Monitor, Smartphone, Cpu, Zap, Home as HomeIcon, RotateCcw } from "lucide-react";
import { base44 } from "@/api/base44Client";
import { useStoreAccess } from "@/hooks/useStoreAccess";
import { HeroSection } from "@/apps/site_legacy/components/homebase/HeroSection";
import { QuickActions, type QuickAction } from "@/apps/site_legacy/components/homebase/QuickActions";
import { LatestProducts, type LatestProductCard } from "@/apps/site_legacy/components/homebase/LatestProducts";
import { Categories as CategoriesSection, type CategoryCard } from "@/apps/site_legacy/components/homebase/Categories";
import { AiosStats } from "@/apps/site_legacy/components/homebase/AiosStats";
import { HowItWorks } from "@/apps/site_legacy/components/homebase/HowItWorks";
import { Footer } from "@/apps/site_legacy/components/homebase/Footer";
import { StartSellingCTA } from "@/apps/site_legacy/components/homebase/StartSellingCTA";
import { PopularSearches } from "@/apps/site_legacy/components/homebase/PopularSearches";
import aiosLogo from "@/assets/brand/aios-logo.png";
import "@/apps/site_legacy/components/homebase/homebase.css";

type Product = {
  id: string;
  name?: string;
  price_byx?: number;
  seller_email?: string;
  store_name?: string;
};

const popularSearches = ["notebook", "teclado mecânico", "monitor 144hz", "smartphone", "servidor", "componentes"];

export default function Home() {
  const navigate = useNavigate();
  const { hasStore, canSeeAnalytics, isLogged } = useStoreAccess();
  const storePath = hasStore ? "/mystore" : isLogged ? "/merchant/setup" : "/auth/login";

  const { data: products = [] } = useQuery<Product[]>({
    queryKey: ["homebase-latest-products"],
    queryFn: async () => {
      const result = await base44.entities.Product.filter({ status: "available" }, "-created_date", 8);
      return Array.isArray(result) ? result : [];
    },
    staleTime: 60_000,
  });

  const latestProducts: LatestProductCard[] = useMemo(() => {
    if (products.length > 0) {
      return products.slice(0, 8).map((p) => ({
        id: p.id,
        name: p.name || "Produto",
        store: p.store_name || "Loja AIOS",
        price: typeof p.price_byx === "number" ? `${p.price_byx.toLocaleString("pt-BR")} AIOS` : "—",
      }));
    }
    return [
      { id: "p1", name: "Notebook ultrafino", store: "Loja Prime", price: "3.200 AIOS" },
      { id: "p2", name: "Servidor rack 1U", store: "Tech Supply", price: "8.900 AIOS" },
      { id: "p3", name: "Smartphone 5G", store: "Mobile Hub", price: "2.100 AIOS" },
      { id: "p4", name: "Headset com cancelamento", store: "Audio Pro", price: "650 AIOS" },
    ];
  }, [products]);

  const handleSearch = (term: string) => {
    const query = term.trim();
    if (!query) return;
    navigate(`/marketplace?query=${encodeURIComponent(query)}`);
  };

  const quickActions: QuickAction[] = [
    {
      icon: ShoppingBag,
      label: "Marketplace",
      description: "Explorar produtos",
      onClick: () => navigate("/marketplace"),
    },
    {
      icon: MessageCircle,
      label: "Chat",
      description: "Suporte e negociações",
      onClick: () => navigate("/chat"),
    },
    {
      icon: Wallet,
      label: "Carteira",
      description: "Saldo e envios",
      onClick: () => navigate("/wallet"),
    },
    {
      icon: TrendingUp,
      label: "Vendas",
      description: "Painel de vendas",
      onClick: () => navigate(hasStore ? "/sales-cashback" : storePath),
      disabled: !hasStore,
      tooltip: "Disponível para lojistas",
    },
    {
      icon: QrCode,
      label: "QR Payments",
      description: "Cobranças rápidas",
      onClick: () => navigate(hasStore ? "/merchant/requests" : storePath),
      disabled: !hasStore,
      tooltip: "Disponível para lojistas",
    },
    {
      icon: ArrowLeftRight,
      label: "Transações",
      description: "Histórico da loja",
      onClick: () => navigate(hasStore ? "/transactions" : storePath),
      disabled: !hasStore,
      tooltip: "Disponível para lojistas",
    },
    {
      icon: BarChart3,
      label: "Análises",
      description: "Dados da loja",
      onClick: () => navigate("/analytics"),
      disabled: !canSeeAnalytics,
      tooltip: isLogged ? "Disponível para lojistas" : "Faça login",
    },
  ];

  const categories: CategoryCard[] = [
    { icon: Monitor, label: "Notebooks", onClick: () => navigate("/marketplace?category=notebooks") },
    { icon: Smartphone, label: "Smartphones", onClick: () => navigate("/marketplace?category=smartphones") },
    { icon: Cpu, label: "Componentes", onClick: () => navigate("/marketplace?category=componentes") },
    { icon: Zap, label: "Eletrônicos", onClick: () => navigate("/marketplace?category=eletronicos") },
    { icon: HomeIcon, label: "Casa e Jardim", onClick: () => navigate("/marketplace?category=casa-e-jardim") },
    { icon: RotateCcw, label: "Recondicionados", onClick: () => navigate("/marketplace?category=recondicionados") },
  ];

  const primaryHeroLabel = hasStore ? "Ir para Minha Loja" : "Criar minha loja";

  return (
    <div className="homebase-scope bg-background text-foreground min-h-screen relative">
      <div className="relative z-10">
        <main className="max-w-6xl mx-auto px-6 py-10 space-y-10">
          <HeroSection
            logoSrc={aiosLogo}
            primaryLabel={primaryHeroLabel}
            secondaryLabel="Explorar marketplace"
            onPrimary={() => navigate(storePath)}
            onSecondary={() => navigate("/marketplace")}
            onSearch={handleSearch}
            popularSearches={popularSearches}
            subtitle="Crie sua loja e publique produtos em minutos. Centralize pagamentos, chat e operações em um só lugar."
          />

          <StartSellingCTA
            title="Comece a vender com a AIOS"
            description="Configure sua loja, publique produtos e acompanhe pagamentos em um só lugar."
            primaryLabel={primaryHeroLabel}
            onPrimary={() => navigate(storePath)}
            secondaryLabel="Falar com suporte"
            onSecondary={() => navigate("/chat")}
          />

          <QuickActions actions={quickActions} />

          <LatestProducts
            products={latestProducts}
            onNavigateMarketplace={() => navigate("/marketplace")}
            onViewProduct={(id) => navigate(`/productdetail?id=${encodeURIComponent(id)}`)}
          />

          <AiosStats />

          <PopularSearches items={popularSearches} onSelect={handleSearch} />

          <CategoriesSection categories={categories} />

          <HowItWorks />
        </main>

        <Footer
          onSupport={() => navigate("/support")}
          onIntegrations={() => navigate("/docs/api")}
          onCompliance={() => navigate("/compliance")}
        />
      </div>
    </div>
  );
}
