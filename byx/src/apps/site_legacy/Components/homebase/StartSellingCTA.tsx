import { Store, ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";

type StartSellingCTAProps = {
  title: string;
  description: string;
  primaryLabel: string;
  onPrimary: () => void;
  secondaryLabel?: string;
  onSecondary?: () => void;
};

export function StartSellingCTA({
  title,
  description,
  primaryLabel,
  onPrimary,
  secondaryLabel,
  onSecondary,
}: StartSellingCTAProps) {
  return (
    <section className="py-6">
      <div className="glass-card rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border border-primary/20">
        <div className="flex items-start gap-3">
          <div className="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
            <Store className="w-5 h-5 text-primary" />
          </div>
          <div>
            <h3 className="text-lg font-semibold text-foreground">{title}</h3>
            <p className="text-sm text-muted-foreground">{description}</p>
          </div>
        </div>
        <div className="flex flex-wrap gap-3">
          <Button
            onClick={onPrimary}
            className="bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/30"
          >
            {primaryLabel}
            <ArrowRight className="w-4 h-4 ml-1" />
          </Button>
          {secondaryLabel && onSecondary && (
            <Button
              variant="outline"
              onClick={onSecondary}
              className="border-border/50 bg-secondary/50 text-foreground hover:bg-secondary/70 hover:border-primary/30"
            >
              {secondaryLabel}
            </Button>
          )}
        </div>
      </div>
    </section>
  );
}
