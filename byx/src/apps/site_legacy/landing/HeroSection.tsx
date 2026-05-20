import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export default function HeroSection() {
  const navigate = useNavigate();

  return (
    <section className="relative pt-28 md:pt-32 pb-16 overflow-hidden">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute -top-24 left-1/4 w-[520px] h-[520px] bg-emerald-500/10 rounded-full blur-[120px]" />
        <div className="absolute -bottom-24 right-1/4 w-[520px] h-[520px] bg-cyan-500/10 rounded-full blur-[120px]" />
      </div>

      <div className="relative mx-auto max-w-7xl px-6">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <p className="text-white/60 text-sm tracking-wider uppercase mb-3">
              IAOS • Branding UX | BYX on-chain
            </p>
            <h1 className="text-4xl md:text-5xl font-bold text-white leading-tight">
              O marketplace do novo comércio, com pagamentos em{" "}
              <span className="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                BYX
              </span>
              .
            </h1>
            <p className="mt-4 text-white/60 text-lg">
              Lojistas, catálogo, pedidos e pagamentos rápidos — com base na infraestrutura da BYX.
            </p>

            <div className="mt-8 flex flex-col sm:flex-row gap-3">
              <Button
                className="h-12 px-6 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
                onClick={() => navigate("/marketplace")}
              >
                Acessar Marketplace
              </Button>
              <Button
                variant="outline"
                className="h-12 px-6 border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
                onClick={() => navigate("/wallet")}
              >
                Carteira
              </Button>
              <Button
                variant="outline"
                className="h-12 px-6 border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
                onClick={() => navigate("/network")}
              >
                Status da Rede
              </Button>
            </div>

            <div className="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/50">
              <span>• Pagamentos</span>
              <span>• Cashback</span>
              <span>• Lojistas</span>
              <span>• Pedidos (QR)</span>
              <span>• Webhooks</span>
            </div>
          </div>

          <div className="bg-white/5 border border-white/10 rounded-2xl p-6">
            <div className="text-white font-semibold text-lg mb-2">
              Comece em 30 segundos
            </div>
            <p className="text-white/60 mb-4">
              Entre na sua conta ou crie uma nova e vá direto para o marketplace.
            </p>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Button
                className="h-12 bg-white/10 text-white hover:bg-white/15"
                onClick={() => navigate("/auth/login")}
              >
                Entrar
              </Button>
              <Button
                className="h-12 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
                onClick={() => navigate("/auth/register")}
              >
                Criar conta
              </Button>
            </div>

            <div className="mt-5 text-xs text-white/40">
              Dica: se você já está logado, pode ir para <span className="text-white/60">Dashboard</span> / <span className="text-white/60">Transações</span>.
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
