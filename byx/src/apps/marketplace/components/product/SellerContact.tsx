import { Star, Calendar, Package, MessageSquare, Heart, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Product } from '@/apps/marketplace/types/product';

interface SellerContactProps {
  product: Product;
}

export const SellerContact = ({ product }: SellerContactProps) => {
  const currentYear = new Date().getFullYear();
  const memberSince = currentYear - Math.floor(product.partnerSales / 5000);

  return (
    <div className="mp-glass-card p-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {/* About Seller */}
        <div className="space-y-4">
          <h3 className="text-xl font-bold mp-text-primary">Sobre este vendedor</h3>
          
          <div className="flex items-center gap-4">
            <div className="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500/20 to-cyan-400/20 flex items-center justify-center border-2 border-emerald-400/30">
              <span className="text-2xl font-bold text-emerald-300">
                {product.partner.charAt(0)}
              </span>
            </div>
            <div>
              <h4 className="font-semibold mp-text-primary text-lg">{product.partner}</h4>
              <div className="flex items-center gap-2 text-sm mp-text-muted">
                <span className="text-emerald-300 font-medium">
                  {Math.round(product.partnerRating * 20)}% de feedback positivo
                </span>
                <span>·</span>
                <span>{product.partnerSales.toLocaleString('pt-BR')} de itens vendidos</span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2 text-sm mp-text-muted">
            <Calendar className="h-4 w-4" />
            <span>Cadastrado desde {memberSince}</span>
          </div>

          <div className="space-y-2">
            <Button className="w-full glass-button text-black hover:opacity-90">
              <Package className="h-4 w-4 mr-2" />
              Outros itens do vendedor
            </Button>
            <Button variant="outline" className="w-full glass-outline text-white hover:border-white/40">
              <MessageSquare className="h-4 w-4 mr-2" />
              Contatar
            </Button>
            <Button variant="ghost" className="w-full text-white/80 hover:text-white">
              <Heart className="h-4 w-4 mr-2" />
              Salvar vendedor
            </Button>
          </div>
        </div>

        {/* Seller Feedback */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <h3 className="text-xl font-bold mp-text-primary">Feedback sobre o vendedor</h3>
            <Badge variant="secondary" className="text-xs pill-badge text-white/80 border-white/10">
              {Math.ceil(product.soldCount / 10)}
            </Badge>
          </div>

          <div className="flex items-center gap-4 border-b border-white/10 pb-4">
            <button className="text-sm mp-text-muted hover:text-white pb-2 border-b-2 border-transparent">
              Este item ({Math.ceil(product.soldCount / 20)})
            </button>
            <button className="text-sm font-medium mp-text-primary pb-2 border-b-2 border-emerald-300">
              Todos os itens ({Math.ceil(product.soldCount / 10)})
            </button>
          </div>

          <div className="space-y-3">
            {/* Sample Feedback */}
            <div className="p-3 bg-white/5 border border-white/10 rounded-lg space-y-2">
              <div className="flex items-center gap-2">
                <div className="flex items-center text-accent">
                  <span className="text-sm font-medium">⊕</span>
                </div>
                <span className="text-xs mp-text-muted">Avaliação automática · Último mês</span>
              </div>
              <p className="text-sm mp-text-muted">
                Pedido entregue no prazo, sem problemas.
              </p>
              <a href="#" className="text-xs text-primary hover:underline">
                {product.name.substring(0, 50)}...
              </a>
            </div>

            <Button variant="outline" size="sm" className="text-emerald-300 glass-outline hover:border-white/40">
              Ver todos os feedbacks
              <ExternalLink className="h-3 w-3 ml-2" />
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};
