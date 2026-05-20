import { Check } from 'lucide-react';
import { VolumePricing } from '@/apps/marketplace/types/product';

interface VolumePricingTableProps {
  pricing: VolumePricing[];
  selectedQty: number;
  onSelectQty: (minQty: number) => void;
  currency: string;
}

export const VolumePricingTable = ({ pricing, selectedQty, onSelectQty, currency }: VolumePricingTableProps) => {
  const getCurrentTier = () => {
    for (let i = pricing.length - 1; i >= 0; i--) {
      if (selectedQty >= pricing[i].minQty) {
        return pricing[i];
      }
    }
    return pricing[0];
  };

  const currentTier = getCurrentTier();

  return (
    <div className="mp-glass-card overflow-hidden">
      <div className="p-4 bg-white/5 border-b border-white/10">
        <h3 className="font-semibold mp-text-primary">Preços por Volume</h3>
        <p className="text-sm mp-text-muted mt-1">
          Quanto mais você compra, mais economiza
        </p>
      </div>
      <div className="divide-y divide-white/10">
        {pricing.map((tier, index) => {
          const isActive = selectedQty >= tier.minQty && 
            (tier.maxQty === null || selectedQty <= tier.maxQty);
          
          return (
            <button
              key={index}
              onClick={() => onSelectQty(tier.minQty)}
              className={`volume-pricing-row w-full text-left ${isActive ? 'active' : ''}`}
            >
              <div className="flex items-center gap-3">
                <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors ${
                  isActive ? 'border-primary bg-primary' : 'border-white/20'
                }`}>
                  {isActive && <Check className="h-3 w-3 text-primary-foreground" />}
                </div>
                <div>
                  <span className="font-medium mp-text-primary">
                    {tier.maxQty 
                      ? `${tier.minQty} - ${tier.maxQty} unid.`
                      : `${tier.minQty}+ unid.`
                    }
                  </span>
                </div>
              </div>
              <div className="text-right">
                <span className="font-bold mp-text-primary">
                  R$ {tier.price.toLocaleString('pt-BR')}
                </span>
                {tier.discount > 0 && (
                  <span className="text-sm text-emerald-300 font-medium ml-2">
                    -{tier.discount}%
                  </span>
                )}
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
};
