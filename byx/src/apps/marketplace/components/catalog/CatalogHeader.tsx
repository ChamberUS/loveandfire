import { Search, ShoppingCart, Heart, User, Menu, Bell, Store } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import aiosLogo from '@/assets/brand/aios-logo.png';
import { useStoreAccess } from '@/hooks/useStoreAccess';

interface CatalogHeaderProps {
  searchQuery: string;
  onSearchChange: (value: string) => void;
  onSearch: () => void;
}

export const CatalogHeader = ({ searchQuery, onSearchChange, onSearch }: CatalogHeaderProps) => {
  const { hasStore, isLogged } = useStoreAccess();
  const storePath = hasStore ? "/mystore" : isLogged ? "/merchant/setup" : "/auth/login";

  return (
    <header className="sticky top-0 z-50 backdrop-blur-md bg-[#070b0f]/85 border-b border-white/10 shadow-lg">
      {/* Main header */}
      <div className="container mx-auto px-4 py-4">
        <div className="flex items-center gap-6">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2 flex-shrink-0 hover:opacity-90 transition">
            <img src={aiosLogo} alt="AIOS" className="w-10 h-10 object-contain rounded-md border border-white/10" />
            <div className="hidden sm:block">
              <span className="font-bold text-xl text-white">IAOS</span>
              <span className="text-xs text-white/60 block -mt-1">Marketplace</span>
            </div>
          </Link>

          {/* Search */}
          <div className="flex-1 max-w-2xl">
            <div className="flex glass-surface rounded-xl border border-white/15 overflow-hidden">
              <Input
                type="text"
                placeholder="Buscar produtos, marcas, categorias..."
                value={searchQuery}
                onChange={(e) => onSearchChange(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && onSearch()}
                className="rounded-none h-11 bg-transparent border-0 text-white placeholder:text-white/50 focus-visible:ring-0 focus-visible:ring-offset-0"
              />
              <Button
                onClick={onSearch}
                className="rounded-none h-11 px-6 glass-button hover:opacity-90"
              >
                <Search className="h-5 w-5" />
              </Button>
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center gap-2">
            <Button variant="ghost" size="icon" className="relative hover:bg-white/5 text-white/70">
              <Bell className="h-5 w-5" />
              <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs bg-urgency">
                3
              </Badge>
            </Button>
            <Button variant="ghost" size="icon" className="relative hover:bg-white/5 text-white/70">
              <Heart className="h-5 w-5" />
              <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs bg-popularity">
                12
              </Badge>
            </Button>
            <Button variant="ghost" size="icon" className="relative hover:bg-white/5 text-white/70">
              <ShoppingCart className="h-5 w-5" />
              <Badge className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs bg-accent">
                5
              </Badge>
            </Button>
            <Button variant="outline" size="sm" className="hidden md:flex gap-2 ml-2 glass-outline text-white hover:border-white/40">
              <User className="h-4 w-4" />
              Minha Conta
            </Button>
            <Link
              to={storePath}
              className="hidden md:inline-flex items-center gap-2 rounded-xl px-3 py-2 glass-outline text-white hover:border-white/40 transition"
            >
              <Store className="h-4 w-4" />
              {hasStore ? "Minha Loja" : "Criar minha loja"}
            </Link>
            <Button variant="ghost" size="icon" className="lg:hidden text-white/80 hover:text-white">
              <Menu className="h-5 w-5" />
            </Button>
          </div>
        </div>
      </div>
    </header>
  );
};
