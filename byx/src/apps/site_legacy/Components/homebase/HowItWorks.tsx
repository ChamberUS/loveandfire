import { BarChart, Package, Store } from "lucide-react";

const steps = [
  {
    icon: Store,
    step: "Passo 1",
    title: "Crie sua loja",
    description: "Use o fluxo existente para configurar sua marca em minutos.",
  },
  {
    icon: Package,
    step: "Passo 2",
    title: "Publique produtos",
    description: "Cadastre itens e receba pagamentos em AIOS ou QR.",
  },
  {
    icon: BarChart,
    step: "Passo 3",
    title: "Acompanhe tudo",
    description: "Use chat e análises para operar com clareza.",
  },
];

export function HowItWorks() {
  return (
    <section className="py-8">
      <div className="flex items-center gap-2 mb-6">
        <div className="w-6 h-6 rounded bg-primary/20 flex items-center justify-center">
          <span className="text-primary text-xs font-bold">?</span>
        </div>
        <h2 className="text-xl font-semibold text-foreground">Como funciona</h2>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {steps.map((item, index) => (
          <div
            key={item.step}
            className="glass-card rounded-xl p-6 relative overflow-hidden group"
            style={{ animationDelay: `${index * 0.1}s` }}
          >
            <div className="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-primary/10 transition-colors duration-500" />
            <div className="relative">
              <div className="flex items-center gap-2 mb-3">
                <item.icon className="w-5 h-5 text-primary" />
                <span className="text-xs font-medium text-primary">{item.step}</span>
              </div>
              <h3 className="font-semibold text-foreground mb-2">{item.title}</h3>
              <p className="text-sm text-muted-foreground">{item.description}</p>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}
