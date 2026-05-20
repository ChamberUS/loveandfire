import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { createPageUrl } from '@/utils/siteLegacy';
import { motion } from 'framer-motion';
import { 
  Store, 
  Menu,
  X,
  ChevronDown,
  Activity
} from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import SearchBar from '@/apps/site_legacy/Components/layout/SearchBar';
import UserMenu from '@/components/UserMenu';
import ProfileMenu from '@/components/ProfileMenu';
import { useStoreAccess } from '@/hooks/useStoreAccess';
import aiosLogo from '@/assets/brand/aios-logo.png';
import { CryptoBackground } from '@/apps/site_legacy/components/homebase/CryptoBackground';

const baseNavigationCategories = [];

const storeOperations = [
  { name: 'Vendas', page: 'SalesCashback' },
  { name: 'Transações', page: 'Transactions' },
];

export default function Layout({ children, currentPageName }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [openDropdown, setOpenDropdown] = useState(null);
  const closeTimerRef = useRef(null);
  const {
    hasStore,
    canSeeAnalytics,
    canSeeAdminMenu,
    isLogged,
    isAdmin,
  } = useStoreAccess();
  const canShowStoreOps = isLogged || isAdmin;

  const navigationCategories = useMemo(
    () => baseNavigationCategories.filter((category) => {
      if (category.requiresAuth) return isLogged || isAdmin;
      return true;
    }),
    [isAdmin, isLogged],
  );

  const adminCategories = useMemo(
    () => (canSeeAdminMenu
      ? [
          {
            name: 'Admin',
            items: [
              { name: 'Status da Rede', page: 'Network' },
              { name: 'Trade', page: 'Trade' },
              { name: 'Earn', page: 'Earn' },
            ],
          },
        ]
      : []),
    [canSeeAdminMenu],
  );

  const storePath = hasStore ? "/mystore" : isLogged ? "/merchant/setup" : "/auth/login";

  const clearDropdownTimer = () => {
    if (closeTimerRef.current) {
      clearTimeout(closeTimerRef.current);
      closeTimerRef.current = null;
    }
  };

  const handleOpenDropdown = (name) => {
    clearDropdownTimer();
    setOpenDropdown(name);
  };

  const handleCloseDropdown = () => {
    clearDropdownTimer();
    closeTimerRef.current = window.setTimeout(() => setOpenDropdown(null), 200);
  };

  useEffect(() => () => clearDropdownTimer(), []);

  return (
    <div className="min-h-screen bg-[#000000] relative overflow-hidden">
      <div className="fixed inset-0 pointer-events-none -z-10">
        <CryptoBackground />
      </div>
      {/* Top Navigation Bar */}
      <nav className="fixed top-0 left-0 right-0 h-16 bg-[#0a0a0a]/95 backdrop-blur-xl border-b border-[#1a4d2e]/30 z-50">
        <div className="max-w-[1920px] mx-auto h-full px-8 flex items-center justify-between">
          {/* Logo */}
          <Link 
            to="/"
            className="flex items-center gap-3 hover:opacity-90 transition-opacity flex-shrink-0 mr-4"
            aria-label="Voltar para a Home"
          >
            <img src={aiosLogo} alt="AIOS" className="h-9 w-9 rounded-md object-contain border border-white/10" />
            <div className="text-xl font-semibold text-white tracking-tight">AIOS</div>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden lg:flex items-center gap-1">
            {currentPageName !== 'Home' && (
              <Button
                asChild
                variant="ghost"
                className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4"
              >
                <Link to="/">Home</Link>
              </Button>
            )}
            <Button
              asChild
              variant="ghost"
              className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4"
            >
              <Link to="/marketplace">Marketplace</Link>
            </Button>
            <div
              className="relative"
              onMouseEnter={() => handleOpenDropdown('Minha Loja')}
              onMouseLeave={handleCloseDropdown}
              onMouseMove={clearDropdownTimer}
            >
              <Button 
                variant="ghost" 
                className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4 inline-flex items-center gap-2"
              >
                <Store className="w-4 h-4" />
                Minha Loja
                <ChevronDown className="w-3 h-3 ml-1" />
              </Button>
              {openDropdown === 'Minha Loja' && (
                <div 
                  data-dropdown
                  className="absolute top-full left-0 pt-3 min-w-[220px] z-50"
                  onMouseEnter={() => handleOpenDropdown('Minha Loja')}
                  onMouseLeave={handleCloseDropdown}
                  onMouseMove={clearDropdownTimer}
                >
                  <div
                    className="absolute left-0 right-0 top-0 h-3"
                    aria-hidden
                    onMouseEnter={() => handleOpenDropdown('Minha Loja')}
                    onMouseMove={clearDropdownTimer}
                  />
                  <div className="relative bg-[#0a0a0a] border border-[#1a4d2e]/50 rounded-xl shadow-xl py-2">
                    <Link
                      to={storePath}
                      className="block px-4 py-2.5 text-white/80 hover:text-white hover:bg-[#1a4d2e]/30 transition-colors first:rounded-t-xl last:rounded-b-xl"
                    >
                      Minha Loja
                    </Link>
                    {canShowStoreOps &&
                      storeOperations.map((item, idx) => (
                        <Link
                          key={item.page}
                          to={createPageUrl(item.page)}
                          className={`block px-4 py-2.5 text-white/80 hover:text-white hover:bg-[#1a4d2e]/30 transition-colors ${
                            idx === storeOperations.length - 1 ? 'last:rounded-b-xl' : ''
                          }`}
                        >
                          {item.name}
                        </Link>
                      ))}
                  </div>
                </div>
              )}
            </div>
            {[...navigationCategories, ...adminCategories].map((category) => (
              <div 
                key={category.name}
                className="relative"
                onMouseEnter={() => handleOpenDropdown(category.name)}
                onMouseLeave={handleCloseDropdown}
                onMouseMove={clearDropdownTimer}
              >
                <Button 
                  variant="ghost" 
                  className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4"
                >
                  {category.name}
                  <ChevronDown className="w-3 h-3 ml-1" />
                </Button>
                {openDropdown === category.name && (
                  <div 
                    data-dropdown
                    className="absolute top-full left-0 pt-3 min-w-[220px] z-50"
                    onMouseEnter={() => handleOpenDropdown(category.name)}
                    onMouseLeave={handleCloseDropdown}
                    onMouseMove={clearDropdownTimer}
                  >
                    <div
                      className="absolute left-0 right-0 top-0 h-3"
                      aria-hidden
                      onMouseEnter={() => handleOpenDropdown(category.name)}
                      onMouseMove={clearDropdownTimer}
                    />
                    <div className="relative bg-[#0a0a0a] border border-[#1a4d2e]/50 rounded-xl shadow-xl py-2">
                      {category.items.map((item) => (
                        <Link
                          key={item.page}
                          to={createPageUrl(item.page)}
                          className="block px-4 py-2.5 text-white/80 hover:text-white hover:bg-[#1a4d2e]/30 transition-colors first:rounded-t-xl last:rounded-b-xl"
                        >
                          {item.name}
                        </Link>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            ))}
            <TooltipProvider>
              {canSeeAnalytics ? (
                <Button
                  asChild
                  variant="ghost"
                  className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4"
                >
                  <Link to="/analytics" className="inline-flex items-center gap-2">
                    <Activity className="w-4 h-4" />
                    Análises
                  </Link>
                </Button>
              ) : isLogged ? (
                <Tooltip delayDuration={150}>
                  <TooltipTrigger asChild>
                    <Button
                      variant="ghost"
                      disabled
                      className="text-white/40 cursor-not-allowed h-10 px-4 inline-flex items-center gap-2"
                    >
                      <Activity className="w-4 h-4" />
                      Análises
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent sideOffset={6}>Disponível para lojistas</TooltipContent>
                </Tooltip>
              ) : (
                <Button
                  asChild
                  variant="ghost"
                  className="text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 transition-all h-10 px-4"
                >
                  <Link to="/auth/login" className="inline-flex items-center gap-2">
                    <Activity className="w-4 h-4" />
                    Análises
                  </Link>
                </Button>
              )}
            </TooltipProvider>
          </div>

          {/* Search Bar */}
          <div className="hidden lg:flex flex-1 max-w-xl mx-8">
            <SearchBar />
          </div>

          {/* User Menu */}
          <div className="flex items-center gap-4">
            {!isAdmin && isLogged && (
              <Button
                asChild
                className="bg-white/5 text-white/80 hover:bg-white/10 h-10 px-4 hidden md:inline-flex"
              >
                <Link to="/merchant">Lojista</Link>
              </Button>
            )}
            {isAdmin && (
              <UserMenu
                label="Admin"
                variant="legacy"
                contentClassName="bg-[#0a0a0a] border-[#1a4d2e]/50"
              />
            )}
            {!isAdmin && isLogged && (
              <ProfileMenu
                label="Perfil"
                variant="legacy"
              />
            )}
            {!isAdmin && !isLogged && (
              <Button
                asChild
                className="bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white font-medium px-6 h-10"
              >
                <Link to="/auth/login">Entrar</Link>
              </Button>
            )}

            {/* Mobile Menu Button */}
            <Button 
              variant="ghost" 
              size="icon" 
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="lg:hidden text-white"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </Button>
          </div>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="lg:hidden bg-[#0a0a0a] border-b border-[#1a4d2e]/30"
          >
            <div className="px-4 py-4 space-y-2">
              <div className="space-y-1">
                <p className="text-white/50 text-xs font-semibold px-3 py-2">Acesso rápido</p>
                {currentPageName !== 'Home' && (
                  <Link
                    to="/"
                    className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Home
                  </Link>
                )}
                <Link
                  to="/marketplace"
                  className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Marketplace
                </Link>
                <Link
                  to={storePath}
                  className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Minha Loja
                </Link>
                {canShowStoreOps &&
                  storeOperations.map((item) => (
                    <Link
                      key={item.page}
                      to={createPageUrl(item.page)}
                      className="block pl-5 pr-3 py-2 text-white/60 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors text-sm"
                      onClick={() => setMobileMenuOpen(false)}
                    >
                      {item.name}
                    </Link>
                  ))}
                {canSeeAnalytics ? (
                  <Link
                    to="/analytics"
                    className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Análises
                  </Link>
                ) : isLogged ? (
                  <div className="px-3 py-2 text-white/60 text-sm rounded-lg border border-dashed border-white/10">
                    Análises (Disponível para lojistas)
                  </div>
                ) : (
                  <Link
                    to="/auth/login"
                    className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Análises
                  </Link>
                )}
              </div>
              {[...navigationCategories, ...adminCategories].map((category) => (
                <div key={category.name} className="space-y-1">
                  <p className="text-white/50 text-xs font-semibold px-3 py-2">{category.name}</p>
                  {category.items.map((item) => (
                    <Link
                      key={item.page}
                      to={createPageUrl(item.page)}
                      className="block px-3 py-2 text-white/70 hover:text-white hover:bg-[#1a4d2e]/30 rounded-lg transition-colors"
                      onClick={() => setMobileMenuOpen(false)}
                    >
                      {item.name}
                    </Link>
                  ))}
                </div>
              ))}
            </div>
          </motion.div>
        )}
      </nav>

      {/* Main Content */}
      <main className="min-h-screen pt-16 relative z-0">
        <div className="relative z-10">
          {children}
        </div>
      </main>
    </div>
  );
}
