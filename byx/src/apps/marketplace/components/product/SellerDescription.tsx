import { Badge } from '@/components/ui/badge';
import { Product } from '@/apps/marketplace/types/product';

interface SellerDescriptionProps {
  product: Product;
}

export const SellerDescription = ({ product }: SellerDescriptionProps) => {
  return (
    <div className="mp-glass-card p-6 space-y-6">
      <h2 className="text-xl font-bold mp-text-primary mp-break-words">
        Descrição do item dada pelo vendedor
      </h2>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {/* Product Image and Title */}
        <div className="space-y-4">
          <div className="aspect-square bg-black/40 rounded-lg overflow-hidden border border-white/10">
            <img
              src={product.images[0]}
              alt={product.name}
              className="w-full h-full object-contain"
            />
          </div>
        </div>

        {/* Description Content */}
        <div className="space-y-4">
          <h3 className="text-lg font-semibold text-white/70 uppercase tracking-wide mp-line-clamp-2 mp-break-words">
            {product.name}
          </h3>
          
          <p className="mp-text-muted leading-relaxed mp-line-clamp-3 mp-break-words">
            {product.description}
          </p>

          {/* Included Items */}
          <div className="pt-4 border-t border-white/10">
            <h4 className="font-semibold mp-text-primary mb-2">Itens Inclusos</h4>
            <p className="mp-text-muted text-sm">
              {product.name} - {product.specs.map(s => s.value).slice(0, 3).join(' - ')}
            </p>
          </div>

          {/* Features */}
          <div className="pt-4 border-t border-white/10">
            <h4 className="font-semibold mp-text-primary mb-3">Características</h4>
            <div className="space-y-3">
              {product.tags.map((tag, index) => (
                <div key={index}>
                  <span className="font-medium mp-text-primary">{tag}</span>
                  <p className="mp-text-muted text-sm">
                    Característica destacada do produto para uso profissional e empresarial.
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Specifications Table */}
      <div className="pt-6 border-t border-white/10">
        <h4 className="font-semibold mp-text-primary mb-4">Especificações</h4>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
          {product.specs.map((spec, index) => (
            <div key={index} className="flex flex-col">
              <span className="text-sm mp-text-muted">{spec.label}:</span>
              <span className="font-medium mp-text-primary">{spec.value}</span>
            </div>
          ))}
          <div className="flex flex-col">
            <span className="text-sm mp-text-muted">Condição:</span>
            <span className="font-medium mp-text-primary capitalize">
              {product.condition === 'new' ? 'Novo' : product.condition}
            </span>
          </div>
          <div className="flex flex-col">
            <span className="text-sm mp-text-muted">País de Origem:</span>
            <span className="font-medium mp-text-primary">Brasil</span>
          </div>
        </div>
      </div>

      {/* Category Breadcrumb */}
      <div className="flex items-center gap-2 text-sm">
        <span className="mp-text-muted">Categoria:</span>
        <a href="#" className="text-emerald-300 hover:underline capitalize">{product.category}</a>
        <span className="mp-text-muted">›</span>
        <a href="#" className="text-emerald-300 hover:underline">Equipamentos Empresariais</a>
      </div>

      {/* Tags */}
      <div className="flex flex-wrap gap-2">
        {product.tags.map((tag) => (
          <Badge key={tag} variant="secondary" className="text-xs pill-badge text-white/80 border-white/10">
            {tag}
          </Badge>
        ))}
      </div>
    </div>
  );
};
