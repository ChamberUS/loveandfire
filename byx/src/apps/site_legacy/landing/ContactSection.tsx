import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export default function ContactSection() {
  const navigate = useNavigate();

  return (
    <section id="contact" className="py-16 border-t border-white/10">
      <div className="mx-auto max-w-7xl px-6">
        <div className="bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 border border-white/10 rounded-2xl p-8">
          <h2 className="text-3xl font-bold text-white">Pronto para testar?</h2>
          <p className="text-white/60 mt-2 max-w-2xl">
            Entre agora e explore o marketplace. Se algo falhar, a rota de “Status da Rede”
            ajuda a diagnosticar rápido.
          </p>

          <div className="mt-6 flex flex-col sm:flex-row gap-3">
            <Button
              className="h-12 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
              onClick={() => navigate("/auth/register")}
            >
              Criar conta
            </Button>
            <Button
              variant="outline"
              className="h-12 border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
              onClick={() => navigate("/network")}
            >
              Status da Rede
            </Button>
            <Button
              variant="outline"
              className="h-12 border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
              onClick={() => navigate("/marketplace")}
            >
              Marketplace
            </Button>
          </div>

          <div className="mt-6 text-xs text-white/40">
            Contato (placeholder): suporte@buynnex • docs • comunidade
          </div>
        </div>
      </div>
    </section>
  );
}
