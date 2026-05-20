import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export default function FeaturesSection() {
  const navigate = useNavigate();

  const features = [
    { t: "Marketplace", d: "Área central com navegação e vitrine." },
    { t: "Carteira", d: "Conectar, visualizar saldo e operar." },
    { t: "Transações", d: "Histórico e rastreio do que aconteceu." },
    { t: "Rede", d: "Status e diagnóstico para pilotos." },
    { t: "Pagamentos", d: "Fluxo com confirmação e integração." },
    { t: "Escalável", d: "Pronto para evoluir para dados reais e integrações." },
  ];

  return (
    <section id="features" className="py-16 border-t border-white/10">
      <div className="mx-auto max-w-7xl px-6">
        <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
          <div>
            <h2 className="text-3xl font-bold text-white">Recursos</h2>
            <p className="text-white/60 mt-2 max-w-2xl">
              A junção dos dois mundos: landing moderna + rotas do produto que vocês já construíram.
            </p>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              className="border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
              onClick={() => navigate("/transactions")}
            >
              Transações
            </Button>
            <Button
              className="bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
              onClick={() => navigate("/dashboard")}
            >
              Dashboard
            </Button>
          </div>
        </div>

        <div className="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {features.map((f) => (
            <div
              key={f.t}
              className="bg-white/5 border border-white/10 rounded-2xl p-5"
            >
              <div className="text-white font-semibold">{f.t}</div>
              <div className="text-white/60 text-sm mt-2">{f.d}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
