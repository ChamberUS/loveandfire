import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  ArrowLeft,
  Heart,
  Star,
  Shield,
  Truck,
  Package,
  AlertTriangle,
  XCircle,
  ShoppingCart,
  Tag,
  Minus,
  BarChart3,
  Plus
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { CatalogHeader } from '@/apps/marketplace/components/catalog/CatalogHeader';
import { ProductGallery } from '@/apps/marketplace/components/product/ProductGallery';
import { VolumePricingTable } from '@/apps/marketplace/components/product/VolumePricingTable';
import { NegotiationForm } from '@/apps/marketplace/components/product/NegotiationForm';
import { SellerDescription } from '@/apps/marketplace/components/product/SellerDescription';
import { SellerContact } from '@/apps/marketplace/components/product/SellerContact';
import { SimilarPartners } from '@/apps/marketplace/components/product/SimilarPartners';
import { mockProducts } from '@/apps/marketplace/data/mockData';
import { useToast } from '@/hooks/use-toast';
import { useCart } from '@/apps/marketplace/hooks/useCart';
import { useCompare } from '@/apps/marketplace/hooks/useCompare';
import { MarketplaceShell } from '@/apps/marketplace/components/MarketplaceShell';

const ProductDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { toast } = useToast();
  const { addItem } = useCart();
  const { addProduct, isComparing, removeProduct } = useCompare();
  const [searchQuery, setSearchQuery] = useState('');
  const [quantity, setQuantity] = useState(1);
  const [isFavorite, setIsFavorite] = useState(false);
  const [showNegotiation, setShowNegotiation] = useState(false);

  const product = mockProducts.find((p) => p.id === id);

  if (!product) {
    return (
      <MarketplaceShell>
        <div className="min-h-screen flex items-center justify-center">
          <div className="text-center glass-surface rounded-2xl px-6 py-8 border border-white/10">
            <h1 className="text-2xl font-bold mb-4 text-white">Produto não encontrado</h1>
            <Button onClick={() => navigate('/marketplace')} className="glass-button text-black">Voltar ao Catálogo</Button>
          </div>
        </div>
      </MarketplaceShell>
    );
  }

  const discount = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;
  const inStock = product.stockStatus !== 'out';

  const getCurrentPrice = () => {
    for (let i = product.volumePricing.length - 1; i >= 0; i--) {
      if (quantity >= product.volumePricing[i].minQty) {
        return product.volumePricing[i];
      }
    }
    return product.volumePricing[0];
  };

  const currentPricing = getCurrentPrice();
  const totalPrice = currentPricing.price * quantity;

  const getStockBadge = () => {
    switch (product.stockStatus) {
      case 'available':
        return (
          <Badge className="badge-stock-available">
            <Package className="h-3 w-3 mr-1" />
            {product.stock} unidades disponíveis
          </Badge>
        );
      case 'low':
        return (
          <Badge className="badge-stock-low animate-pulse-subtle">
            <AlertTriangle className="h-3 w-3 mr-1" />
            Últimas {product.stock} unidades!
          </Badge>
        );
      case 'out':
        return (
          <Badge className="badge-stock-out">
            <XCircle className="h-3 w-3 mr-1" />
            Esgotado
          </Badge>
        );
    }
  };

  const handleAddToCart = () => {
    addItem(product, quantity, currentPricing.price);
    toast({
      title: 'Adicionado ao carrinho!',
      description: `${quantity}x ${product.name}`,
    });
  };

  const handleCompare = () => {
    if (isComparing(product.id)) {
      removeProduct(product.id);
    } else {
      addProduct(product);
    }
  };

  const handleToggleFavorite = () => {
    setIsFavorite(!isFavorite);
    toast({
      title: isFavorite ? 'Removido dos favoritos' : 'Adicionado aos favoritos',
      description: product.name,
    });
  };

  return (
    <MarketplaceShell>
      <CatalogHeader
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSearch={() => navigate('/marketplace')}
      />

      {/* Breadcrumb */}
      <div className="glass-surface border border-white/10">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-white/60 flex items-center gap-2">
            <button onClick={() => navigate('/marketplace')} className="flex items-center gap-1 hover:text-white transition-colors">
              <ArrowLeft className="h-4 w-4" />
              Voltar
            </button>
            <span className="mx-2">/</span>
            <span className="hover:text-white cursor-pointer">Catálogo</span>
            <span className="mx-2">/</span>
            <span className="text-white capitalize">{product.category}</span>
          </nav>
        </div>
      </div>

      <main className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Left Column - Gallery */}
          <div className="lg:col-span-7 space-y-8">
            <ProductGallery
              images={product.images}
              productName={product.name}
              discount={discount}
              inCarts={product.inCarts}
            />

            {/* Product Details Tabs */}
            <div className="glass-surface rounded-2xl border border-white/10 p-4 md:p-6 shadow-xl">
              <Tabs defaultValue="specs" className="w-full">
                <TabsList className="w-full justify-start bg-white/5 p-1 rounded-lg border border-white/10">
                  <TabsTrigger value="specs" className="flex-1 text-white data-[state=active]:bg-white/10">Especificações</TabsTrigger>
                  <TabsTrigger value="description" className="flex-1 text-white data-[state=active]:bg-white/10">Descrição</TabsTrigger>
                  <TabsTrigger value="shipping" className="flex-1 text-white data-[state=active]:bg-white/10">Envio</TabsTrigger>
                </TabsList>
                <TabsContent value="specs" className="mt-4">
                  <div className="glass-surface rounded-xl p-6 border border-white/10">
                    <h3 className="font-semibold text-white mb-4">Especificações</h3>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      {product.specs.map((spec, index) => (
                        <div key={index} className="flex justify-between py-2 border-b border-white/10 last:border-0">
                          <span className="text-white/70">{spec.label}</span>
                          <span className="font-medium text-white">{spec.value}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </TabsContent>
                <TabsContent value="description" className="mt-4">
                  <div className="glass-surface rounded-xl p-6 border border-white/10">
                    <h3 className="font-semibold text-white mb-4">Descrição</h3>
                    <p className="text-white/70 leading-relaxed">{product.description}</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                      {product.tags.map((tag) => (
                        <Badge key={tag} variant="secondary" className="pill-badge text-white/80 border-white/10">{tag}</Badge>
                      ))}
                    </div>
                  </div>
                </TabsContent>
                <TabsContent value="shipping" className="mt-4">
                  <div className="glass-surface rounded-xl p-6 border border-white/10 space-y-4">
                    <div className="flex items-start gap-3">
                      <Truck className="h-5 w-5 text-emerald-400 mt-0.5" />
                      <div>
                        <h4 className="font-semibold text-white">Envio e Logística</h4>
                        <p className="text-white/70 text-sm">Prazo médio de entrega: {product.leadTime}</p>
                      </div>
                    </div>
                    <div className="flex items-start gap-3">
                      <Shield className="h-5 w-5 text-emerald-400 mt-0.5" />
                      <div>
                        <h4 className="font-semibold text-white">Garantia e Proteção</h4>
                        <p className="text-white/70 text-sm">
                          Garantia do fabricante e proteção contra defeitos de transporte.
                        </p>
                      </div>
                    </div>
                  </div>
                </TabsContent>
              </Tabs>
            </div>
          </div>

          {/* Right Column - Purchase Info */}
          <div className="lg:col-span-5">
            <div className="glass-surface border border-white/10 rounded-2xl p-6 space-y-6 shadow-2xl sticky top-24">
              <div className="flex justify-between items-start gap-4">
                <div>
                  <div className="flex items-center gap-2 mb-2">
                    <Badge variant="secondary" className="pill-badge text-emerald-200">
                      <Tag className="h-3 w-3 mr-1" />
                      Lojas verificadas
                    </Badge>
                    {discount > 0 && (
                      <Badge className="pill-badge text-amber-200">
                        -{discount}% off
                      </Badge>
                    )}
                  </div>
                  <h1 className="text-2xl font-bold text-white mb-1">{product.name}</h1>
                  <p className="text-white/70">Parceiro: {product.partner}</p>
                </div>
                <button onClick={handleToggleFavorite} className="text-white/60 hover:text-white">
                  <Heart className={`h-5 w-5 ${isFavorite ? 'fill-emerald-400 text-emerald-400' : ''}`} />
                </button>
              </div>

              {/* Pricing */}
              <div className="glass-surface rounded-lg p-4 border border-white/10">
                <div className="flex items-center gap-3">
                  <p className="text-3xl font-bold text-white">
                    R$ {totalPrice.toLocaleString('pt-BR')}
                  </p>
                  {product.originalPrice && (
                    <p className="text-white/60 line-through">
                      R$ {product.originalPrice.toLocaleString('pt-BR')}
                    </p>
                  )}
                </div>
                <p className="text-sm text-white/70 mt-1">
                  Preço unitário atual: R$ {currentPricing.price.toLocaleString('pt-BR')}
                </p>
              </div>

              {/* Quantity Selector */}
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-white/70">Quantidade</span>
                  <span className="text-sm font-medium text-white">Mínimo: {product.minOrder} unid.</span>
                </div>
                <div className="flex items-center gap-2">
                  <Button variant="outline" size="icon" onClick={() => setQuantity(Math.max(product.minOrder, quantity - 1))} className="glass-outline text-white hover:border-white/40">
                    <Minus className="h-4 w-4" />
                  </Button>
                  <span className="text-lg font-semibold w-10 text-center">{quantity}</span>
                  <Button variant="outline" size="icon" onClick={() => setQuantity(quantity + 1)} className="glass-outline text-white hover:border-white/40">
                    <Plus className="h-4 w-4" />
                  </Button>
                </div>
              </div>

              {/* Actions */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <Button className="w-full glass-button text-black" size="lg" onClick={handleAddToCart}>
                  <ShoppingCart className="h-4 w-4 mr-2" />
                  Adicionar ao carrinho
                </Button>
                <Button variant="outline" size="lg" onClick={handleCompare} className="glass-outline text-white hover:border-white/40">
                  <BarChart3 className="h-4 w-4 mr-2" />
                  {isComparing(product.id) ? 'Remover da comparação' : 'Comparar'}
                </Button>
              </div>

              {/* Solicitar negociação */}
              {inStock && (
                <div className="space-y-3">
                  <Button
                    variant="outline"
                    className="w-full glass-outline text-white hover:border-emerald-300/50"
                    onClick={() => setShowNegotiation((prev) => !prev)}
                  >
                    Solicitar negociação
                  </Button>
                  {showNegotiation && (
                    <div className="glass-surface border border-white/10 rounded-xl p-4">
                      <NegotiationForm product={product} selectedQty={quantity} />
                    </div>
                  )}
                </div>
              )}

              {/* Guarantees */}
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div className="glass-surface border border-white/10 rounded-lg p-3 flex items-start gap-2">
                  <Shield className="h-4 w-4 text-emerald-400 mt-0.5" />
                  <div>
                    <p className="font-medium text-white">Compra protegida</p>
                    <p className="text-white/60 text-xs">Suporte direto com a loja</p>
                  </div>
                </div>
                <div className="glass-surface border border-white/10 rounded-lg p-3 flex items-start gap-2">
                  <Truck className="h-4 w-4 text-emerald-400 mt-0.5" />
                  <div>
                    <p className="font-medium text-white">Envio rastreado</p>
                    <p className="text-white/60 text-xs">Logística sob demanda</p>
                  </div>
                </div>
              </div>

              {/* Seller Info */}
              <div className="space-y-4">
                <div className="flex items-center gap-3">
                  <img
                    src={product.partnerLogo}
                    alt={product.partner}
                    className="w-12 h-12 rounded-lg object-cover border border-white/10"
                  />
                  <div>
                    <p className="text-sm text-white/60">Lojas verificadas</p>
                    <p className="font-semibold text-white">{product.partner}</p>
                    <div className="flex items-center gap-1 text-sm text-white/70">
                      <Star className="h-4 w-4 fill-warning text-warning" />
                      <span>{product.partnerRating} • Avaliações da loja</span>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div className="glass-surface border border-white/10 rounded-lg p-3">
                    <p className="text-xs text-white/60">Garantia</p>
                    <p className="font-medium text-white">{product.warranty}</p>
                  </div>
                  <div className="glass-surface border border-white/10 rounded-lg p-3">
                    <p className="text-xs text-white/60">Prazo de entrega</p>
                    <p className="font-medium text-white">{product.leadTime}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Additional Sections */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-10">
          <div className="lg:col-span-2 space-y-6">
            <VolumePricingTable pricing={product.volumePricing} />
            <SellerDescription product={product} />
          </div>
          <div className="space-y-6">
            <SellerContact product={product} />
            <SimilarPartners products={mockProducts.filter((p) => p.category === product.category && p.id !== product.id)} />
          </div>
        </div>
      </main>
    </MarketplaceShell>
  );
};

export default ProductDetails;
