import { useState, useMemo } from 'react';
import { CatalogHeader } from '@/apps/marketplace/components/catalog/CatalogHeader';
import { FilterSidebar } from '@/apps/marketplace/components/catalog/FilterSidebar';
import { ProductList } from '@/apps/marketplace/components/catalog/ProductList';
import { mockProducts } from '@/apps/marketplace/data/mockData';
import { MarketplaceShell } from '@/apps/marketplace/components/MarketplaceShell';

const Index = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string | null>('notebooks');
  const [selectedPartners, setSelectedPartners] = useState<string[]>([]);
  const [stockStatus, setStockStatus] = useState<string[]>([]);
  const [priceRange, setPriceRange] = useState<[number, number]>([0, 50000]);
  const [dynamicFilters, setDynamicFilters] = useState<Record<string, string[]>>({});
  const [currentPage, setCurrentPage] = useState(1);

  const handleSearch = () => {
    setCurrentPage(1);
    console.log('Searching for:', searchQuery);
  };

  const handleDynamicFilterChange = (filterId: string, values: string[]) => {
    setDynamicFilters((prev) => ({
      ...prev,
      [filterId]: values,
    }));
  };

  const handleClearAll = () => {
    setSelectedCategory(null);
    setSelectedPartners([]);
    setStockStatus([]);
    setPriceRange([0, 50000]);
    setDynamicFilters({});
  };

  const activeFiltersCount = useMemo(() => {
    let count = 0;
    if (selectedCategory) count++;
    count += selectedPartners.length;
    count += stockStatus.length;
    if (priceRange[0] > 0 || priceRange[1] < 50000) count++;
    Object.values(dynamicFilters).forEach((values) => {
      count += values.length;
    });
    return count;
  }, [selectedCategory, selectedPartners, stockStatus, priceRange, dynamicFilters]);

  const filteredProducts = useMemo(() => {
    return mockProducts.filter((product) => {
      // Search filter
      if (searchQuery) {
        const query = searchQuery.toLowerCase();
        if (
          !product.name.toLowerCase().includes(query) &&
          !product.description.toLowerCase().includes(query) &&
          !product.partner.toLowerCase().includes(query)
        ) {
          return false;
        }
      }

      // Category filter
      if (selectedCategory && product.category !== selectedCategory) {
        return false;
      }

      // Partners filter
      if (selectedPartners.length > 0) {
        const partnerMatch = selectedPartners.some((p) =>
          product.partner.toLowerCase().includes(p.toLowerCase())
        );
        if (!partnerMatch) return false;
      }

      // Stock status filter
      if (stockStatus.length > 0 && !stockStatus.includes(product.stockStatus)) {
        return false;
      }

      // Price range filter
      if (product.price < priceRange[0] || product.price > priceRange[1]) {
        return false;
      }

      return true;
    });
  }, [searchQuery, selectedCategory, selectedPartners, stockStatus, priceRange]);

  return (
    <MarketplaceShell>
      <CatalogHeader
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSearch={handleSearch}
      />

      {/* Hero Banner */}
      <div className="container mx-auto px-4 pt-8">
        <div className="glass-surface rounded-2xl border border-white/10 p-6 md:p-8 shadow-xl">
          <div className="max-w-2xl space-y-3">
            <h1 className="text-3xl lg:text-4xl font-bold text-white mb-1">
              Catálogo de Produtos
            </h1>
            <p className="text-white/70 text-lg">
              Encontre os melhores produtos com preços e condições especiais. Negocie direto com lojas verificadas.
            </p>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          <FilterSidebar
            selectedCategory={selectedCategory}
            selectedPartners={selectedPartners}
            stockStatus={stockStatus}
            priceRange={priceRange}
            dynamicFilters={dynamicFilters}
            onCategoryChange={setSelectedCategory}
            onPartnersChange={setSelectedPartners}
            onStockStatusChange={setStockStatus}
            onPriceRangeChange={setPriceRange}
            onDynamicFilterChange={handleDynamicFilterChange}
            onClearAll={handleClearAll}
            activeFiltersCount={activeFiltersCount}
          />

          <ProductList
            products={filteredProducts}
            currentPage={currentPage}
            onPageChange={setCurrentPage}
          />
        </div>
      </div>
    </MarketplaceShell>
  );
};

export default Index;
