import { useNavigate } from 'react-router-dom';
import { Heart, Star, Package, AlertTriangle, XCircle, Eye, ShoppingCart, TrendingUp } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Product } from '@/apps/marketplace/types/product';

interface ProductCardProps {
  product: Product;
  viewMode?: 'grid' | 'list';
}

export const ProductCard = ({ product, viewMode = 'grid' }: ProductCardProps) => {
  const navigate = useNavigate();

  const discount = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  const getStockBadge = () => {
    switch (product.stockStatus) {
      case 'available':
        return (
          <Badge className="badge-stock-available text-xs">
            <Package className="h-3 w-3 mr-1" />
            {product.stock} disponíveis
          </Badge>
        );
      case 'low':
        return (
          <Badge className="badge-stock-low text-xs">
            <AlertTriangle className="h-3 w-3 mr-1" />
            Últimas {product.stock} unid.
          </Badge>
        );
      case 'out':
        return (
          <Badge className="badge-stock-out text-xs">
            <XCircle className="h-3 w-3 mr-1" />
            Esgotado
          </Badge>
        );
    }
  };

  return (
    <Card
      className={`group glass-surface glass-card-hover rounded-xl overflow-hidden cursor-pointer shadow-card border border-white/10 product-card ${
        viewMode === 'list' ? 'list-mode flex-row' : 'flex-col'
      }`}
      onClick={() => navigate(`/marketplace/product/${product.id}`)}
    >
      {/* Image Section */}
      <div className="relative aspect-[4/3] bg-black/40 overflow-hidden product-media">
        <img
          src={product.image}
          alt={product.name}
          className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        />
        
        {/* Badges */}
        <div className="absolute top-3 left-3 flex flex-col gap-2">
          {discount > 0 && (
            <Badge className="pill-badge text-amber-200 font-semibold">
              -{discount}%
            </Badge>
          )}
          {product.inCarts > 5 && (
            <Badge className="pill-badge text-emerald-200">
              <TrendingUp className="h-3 w-3 mr-1" />
              Em {product.inCarts} carrinhos
            </Badge>
          )}
        </div>

        {/* Wishlist Button */}
        <Button
          variant="ghost"
          size="icon"
          className="absolute top-3 right-3 bg-card/80 hover:bg-card shadow-md"
          onClick={(e) => {
            e.stopPropagation();
            // Handle wishlist
          }}
        >
          <Heart className="h-4 w-4" />
        </Button>

        {/* Quick view count */}
        <div className="absolute bottom-3 right-3 bg-black/60 backdrop-blur-sm rounded-full px-2 py-1 flex items-center gap-1 text-xs text-white/70">
          <Eye className="h-3 w-3" />
          {product.viewCount}
        </div>
      </div>

      {/* Content Section */}
      <div className="p-4 space-y-3 product-body">
        {/* Partner & Rating */}
        <div className="flex items-center justify-between">
          <span className="text-xs text-primary font-medium hover:underline mp-clamp-1 mp-break-words">
            {product.partner}
          </span>
          <div className="flex items-center gap-1">
            <Star className="h-3 w-3 fill-warning text-warning" />
            <span className="text-xs text-white/70">{product.partnerRating}</span>
          </div>
        </div>

        {/* Title */}
        <h3 className="font-medium text-foreground mp-line-clamp-2 text-sm leading-snug group-hover:text-primary transition-colors mp-break-words">
          {product.name}
        </h3>

        {/* Stock */}
        <div>{getStockBadge()}</div>

        {/* Price */}
        <div className="space-y-1">
          {product.originalPrice && (
            <span className="text-sm text-muted-foreground line-through">
              R$ {product.originalPrice.toLocaleString('pt-BR')}
            </span>
          )}
          <div className="flex items-baseline gap-1">
            <span className="text-xl font-bold text-white">
              R$ {product.price.toLocaleString('pt-BR')}
            </span>
          </div>
          {product.minOrder > 1 && (
            <span className="text-xs text-muted-foreground">
              Pedido mínimo: {product.minOrder} unid.
            </span>
          )}
        </div>

        {/* CTA */}
        <Button
          className="product-cta glass-button py-2 text-sm"
        onClick={(e) => {
          e.stopPropagation();
            navigate(`/marketplace/product/${product.id}`);
        }}
      >
          <ShoppingCart className="h-4 w-4 mr-2" />
          Ver Detalhes
        </Button>
      </div>
    </Card>
  );
};
