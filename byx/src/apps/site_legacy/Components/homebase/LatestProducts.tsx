import { ArrowRight, ImageOff } from "lucide-react";
import { Button } from "@/components/ui/button";

export type LatestProductCard = {
  id: string;
  name: string;
  store: string;
  price: string;
};

type LatestProductsProps = {
  products: LatestProductCard[];
  onNavigateMarketplace: () => void;
  onViewProduct?: (id: string) => void;
};

export function LatestProducts({ products, onNavigateMarketplace, onViewProduct }: LatestProductsProps) {
  return (
    <section className="py-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-semibold text-foreground">Últimos anúncios</h2>
        <Button
          variant="ghost"
          className="text-primary hover:text-primary/80 gap-1"
          onClick={onNavigateMarketplace}
        >
          Ver marketplace <ArrowRight className="w-4 h-4" />
        </Button>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {products.map((product) => (
          <div
            key={product.id}
            className="glass-card-hover rounded-xl overflow-hidden group cursor-pointer"
            onClick={() => onViewProduct?.(product.id)}
          >
            <div className="aspect-[4/3] bg-secondary/30 flex items-center justify-center border-b border-border/20">
              <div className="text-center text-muted-foreground">
                <ImageOff className="w-8 h-8 mx-auto mb-2 opacity-50" />
                <span className="text-xs opacity-50">Sem imagem</span>
              </div>
            </div>
            <div className="p-4">
              <h3 className="font-medium text-foreground mb-1 group-hover:text-primary transition-colors">
                {product.name}
              </h3>
              <p className="text-xs text-muted-foreground mb-3">🏪 {product.store}</p>
              <div className="flex items-center justify-between">
                <span className="text-primary font-semibold">{product.price}</span>
                <Button
                  variant="outline"
                  size="sm"
                  className="text-xs"
                  onClick={(e) => {
                    e.stopPropagation();
                    onViewProduct?.(product.id);
                  }}
                >
                  Ver detalhes
                </Button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}
