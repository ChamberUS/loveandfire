import { useMemo, useState } from "react";
import { Search, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type HeroSectionProps = {
  logoSrc: string;
  primaryLabel: string;
  secondaryLabel: string;
  onPrimary: () => void;
  onSecondary: () => void;
  onSearch: (term: string) => void;
  popularSearches?: string[];
  subtitle?: string;
};

export function HeroSection({
  logoSrc,
  primaryLabel,
  secondaryLabel,
  onPrimary,
  onSecondary,
  onSearch,
  popularSearches = [],
  subtitle = "Crie sua loja e publique produtos em minutos. Centralize pagamentos, chat e operações em um só lugar.",
}: HeroSectionProps) {
  const [searchTerm, setSearchTerm] = useState("");
  const chips = useMemo(() => popularSearches.slice(0, 8), [popularSearches]);

  const submitSearch = (term?: string) => {
    const value = (term ?? searchTerm).trim();
    if (!value) return;
    onSearch(value);
  };

  return (
    <section className="relative py-12">
      <div className="absolute inset-0 gradient-glow pointer-events-none" />

      <div className="relative glass-card rounded-3xl p-8 lg:p-10 overflow-hidden">
        <div className="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
        <div className="absolute bottom-0 left-0 w-48 h-48 bg-primary/5 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2" />

        <div className="relative grid lg:grid-cols-2 gap-8 items-center">
          <div>
            <div className="flex items-center gap-2 mb-4">
              <img src={logoSrc} alt="AIOS" className="w-10 h-10 animate-float" />
            </div>

            <div className="flex items-center gap-2 text-primary text-sm font-medium mb-3">
              <ShieldCheck className="w-4 h-4" />
              <span>Venda com segurança no AIOS</span>
            </div>

            <h1 className="text-4xl lg:text-5xl font-bold text-foreground mb-4">
              Venda no <span className="text-gradient">AIOS</span>
            </h1>

            <p className="text-muted-foreground mb-6 text-lg">
              {subtitle}
            </p>

            <div className="flex flex-wrap gap-3">
              <Button
                className={cn(
                  "bg-primary text-primary-foreground shadow-lg shadow-primary/30 hover:shadow-primary/50 hover:scale-[1.01]",
                )}
                size="lg"
                onClick={onPrimary}
              >
                {primaryLabel}
              </Button>
              <Button
                variant="outline"
                size="lg"
                className="border-border/50 bg-secondary/50 text-foreground hover:bg-secondary/70 hover:border-primary/30"
                onClick={onSecondary}
              >
                {secondaryLabel}
              </Button>
            </div>
          </div>

          <div className="glass-card rounded-2xl p-6 border border-border/20">
            <h3 className="text-sm font-medium text-foreground mb-4">Buscar no marketplace</h3>

            <div className="relative mb-4">
              <input
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && submitSearch()}
                type="text"
                placeholder="Ex: notebook, servidor, headset..."
                className="w-full h-12 pl-4 pr-12 rounded-xl bg-secondary/50 border border-border/30 text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 transition-colors"
              />
              <button
                className="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-primary flex items-center justify-center hover:bg-primary/90 transition-colors"
                type="button"
                onClick={() => submitSearch()}
                aria-label="Buscar produtos"
              >
                <Search className="w-4 h-4 text-primary-foreground" />
              </button>
            </div>

            <div className="flex flex-wrap gap-2">
              {chips.map((search) => (
                <button
                  key={search}
                  onClick={() => submitSearch(search)}
                  className="px-3 py-1.5 rounded-lg bg-secondary/40 border border-border/20 text-xs text-muted-foreground hover:text-foreground hover:border-primary/30 transition-all"
                >
                  {search}
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
