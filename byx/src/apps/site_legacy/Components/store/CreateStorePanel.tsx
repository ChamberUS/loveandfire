import { Button } from "@/components/ui/button";
import { Store, Shield, Sparkles, CheckCircle2 } from "lucide-react";
import { ReactNode } from "react";

type CreateStorePanelProps = {
  hasStore: boolean;
  storeName?: string;
  onCreateClick: () => void;
  onOpenPanel?: () => void;
  extra?: ReactNode;
};

export function CreateStorePanel({ hasStore, storeName, onCreateClick, onOpenPanel, extra }: CreateStorePanelProps) {
  return (
    <div className="iaos-glass-card p-6 lg:p-8 relative overflow-hidden">
      <div className="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl" />
      <div className="absolute -left-10 bottom-0 w-40 h-40 bg-cyan-500/10 rounded-full blur-3xl" />
      <div className="relative z-10 flex flex-col lg:flex-row gap-4 lg:items-center justify-between">
        <div className="flex items-start gap-3">
          <div className="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
            {hasStore ? <Shield className="w-6 h-6 text-emerald-300" /> : <Store className="w-6 h-6 text-emerald-300" />}
          </div>
          <div>
            <p className="text-sm iaos-text-muted">{hasStore ? "Loja ativa" : "Comece sua loja"}</p>
            <h2 className="text-2xl font-semibold iaos-text-primary leading-tight iaos-break">
              {hasStore ? `Sua loja ${storeName || ""} está ativa` : "Crie sua loja e publique em minutos"}
            </h2>
            <p className="text-sm iaos-text-muted mt-1 iaos-clamp-2">
              Configure identidade, banner e catálogo em um único lugar. Gerencie pagamentos e anúncios com segurança AIOS.
            </p>
            {hasStore && (
              <div className="flex flex-wrap gap-2 mt-3">
                <span className="iaos-chip text-xs flex items-center gap-1">
                  <CheckCircle2 className="w-4 h-4 text-emerald-300" /> Verificada
                </span>
                <span className="iaos-chip text-xs flex items-center gap-1">
                  <Sparkles className="w-4 h-4 text-emerald-300" /> Publicando produtos
                </span>
              </div>
            )}
          </div>
        </div>
        <div className="flex items-center gap-3">
          {!hasStore ? (
            <Button onClick={onCreateClick} className="iaos-button-primary">
              Criar Loja
            </Button>
          ) : (
            <Button onClick={onOpenPanel} className="iaos-button-primary">
              Ir para Painel
            </Button>
          )}
          {extra}
        </div>
      </div>
    </div>
  );
}
