import { Activity, TrendingDown, TrendingUp, Users } from "lucide-react";

export function AiosStats() {
  return (
    <section className="py-8">
      <div className="glass-card rounded-2xl p-6">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
            <span className="text-primary font-bold text-lg">Φ</span>
          </div>
          <div>
            <h3 className="font-semibold text-foreground">Cotação AIOS</h3>
            <p className="text-xs text-muted-foreground">Atualizado em tempo real</p>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-4 rounded-xl bg-secondary/30 border border-border/20">
            <div className="flex items-center gap-2 text-primary mb-2">
              <TrendingUp className="w-4 h-4" />
              <span className="text-xs font-medium">Preço</span>
            </div>
            <p className="text-2xl font-bold text-foreground">R$ 2,45</p>
            <p className="text-xs text-primary mt-1">+5.2% hoje</p>
          </div>

          <div className="p-4 rounded-xl bg-secondary/30 border border-border/20">
            <div className="flex items-center gap-2 text-muted-foreground mb-2">
              <Activity className="w-4 h-4" />
              <span className="text-xs font-medium">Volume 24h</span>
            </div>
            <p className="text-2xl font-bold text-foreground">1.2M</p>
            <p className="text-xs text-muted-foreground mt-1">AIOS negociados</p>
          </div>

          <div className="p-4 rounded-xl bg-secondary/30 border border-border/20">
            <div className="flex items-center gap-2 text-muted-foreground mb-2">
              <Users className="w-4 h-4" />
              <span className="text-xs font-medium">Lojas ativas</span>
            </div>
            <p className="text-2xl font-bold text-foreground">847</p>
            <p className="text-xs text-muted-foreground mt-1">+23 esta semana</p>
          </div>

          <div className="p-4 rounded-xl bg-secondary/30 border border-border/20">
            <div className="flex items-center gap-2 text-muted-foreground mb-2">
              <TrendingDown className="w-4 h-4" />
              <span className="text-xs font-medium">Transações</span>
            </div>
            <p className="text-2xl font-bold text-foreground">12.4K</p>
            <p className="text-xs text-muted-foreground mt-1">Últimas 24h</p>
          </div>
        </div>
      </div>
    </section>
  );
}
