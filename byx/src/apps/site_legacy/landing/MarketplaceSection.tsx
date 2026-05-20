import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ShoppingCart, Store, Wallet, BadgePercent } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export default function MarketplaceSection() {
  const navigate = useNavigate();

  const items = [
    {
      title: "Lojistas",
      desc: "Crie e gerencie lojas, perfis públicos e presença no marketplace.",
      icon: Store,
    },
    {
      title: "Catálogo",
      desc: "Produtos, preços e disponibilidade — pronto para evoluir para dados reais.",
      icon: ShoppingCart,
    },
    {
      title: "Pagamentos BYX",
      desc: "Fluxo de pagamentos/pedidos com confirmação e rastreio.",
      icon: Wallet,
    },
    {
      title: "Cashback",
      desc: "Incentivos e recompensas para aumentar retenção e recorrência.",
      icon: BadgePercent,
    },
  ];

  return (
    <section id="marketplace" className="py-16">
      <div className="mx-auto max-w-7xl px-6">
        <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
          <div>
            <h2 className="text-3xl font-bold text-white">Essência de marketplace</h2>
            <p className="text-white/60 mt-2 max-w-2xl">
              Mantivemos o que importa: vitrine, lojas, pedidos e pagamentos — com a base da BYX por trás.
            </p>
          </div>
          <Button
            className="bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
            onClick={() => navigate("/marketplace")}
          >
            Ir para o Marketplace
          </Button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {items.map((it) => {
            const Icon = it.icon;
            return (
              <Card key={it.title} className="bg-white/5 border-white/10 text-white">
                <CardHeader>
                  <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500/30 to-cyan-500/30 flex items-center justify-center mb-3">
                    <Icon className="text-white" size={18} />
                  </div>
                  <CardTitle className="text-white">{it.title}</CardTitle>
                </CardHeader>
                <CardContent className="text-white/60 text-sm">
                  {it.desc}
                </CardContent>
              </Card>
            );
          })}
        </div>
      </div>
    </section>
  );
}
