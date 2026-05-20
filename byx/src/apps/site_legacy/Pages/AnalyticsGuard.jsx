import React from "react";
import { Link } from "react-router-dom";
import { useStoreAccess } from "@/hooks/useStoreAccess";

export default function AnalyticsGuard({ children }) {
  const { canSeeAnalytics, hasStore, loading } = useStoreAccess();
  const storeCtaPath = hasStore ? "/mystore" : "/merchant/setup";

  if (canSeeAnalytics) return children;

  if (loading) {
    return (
      <div className="min-h-screen bg-[#0a0a0a] flex items-center justify-center">
        <div className="text-white/70 text-sm">Checando acesso...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#0a0a0a] flex items-center justify-center px-6">
      <div className="text-center max-w-xl space-y-4">
        <div className="text-white text-2xl font-semibold">Área de análises para lojistas</div>
        <div className="text-white/60">
          Crie sua loja para acessar métricas reais do seu negócio. Em poucos minutos você libera os painéis.
        </div>
        <div className="flex flex-wrap gap-3 justify-center">
          <Link
            className="px-4 h-11 rounded-xl bg-emerald-500 text-black font-medium flex items-center hover:bg-emerald-400 transition"
            to={storeCtaPath}
          >
            {hasStore ? "Ir para Minha Loja" : "Criar minha loja"}
          </Link>
          <Link
            className="px-4 h-11 rounded-xl bg-white/10 text-white flex items-center hover:bg-white/15 transition"
            to="/"
          >
            Voltar para Home
          </Link>
        </div>
      </div>
    </div>
  );
}
