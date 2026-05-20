import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Copy, RefreshCw, Link as LinkIcon, Shield } from "lucide-react";
import { Switch } from "@/components/ui/switch";
import { cn } from "@/lib/utils";

type WalletActionsProps = {
  address: string;
  onAddressChange: (value: string) => void;
  onQuery: () => void;
  onConnect: () => void;
  onCopy: () => void;
  loading?: boolean;
  connecting?: boolean;
  savingToProfile?: boolean;
  onToggleSaveProfile?: (next: boolean) => void;
  userEmail?: string | null;
};

export function WalletActions({
  address,
  onAddressChange,
  onQuery,
  onConnect,
  onCopy,
  loading,
  connecting,
  savingToProfile,
  onToggleSaveProfile,
  userEmail,
}: WalletActionsProps) {
  return (
    <div className="wallet-card p-5 space-y-4 wallet-card-hover">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm wallet-muted">Endereço BYX</p>
          <p className="text-lg font-semibold text-white">Consulta segura</p>
        </div>
        <div className="wallet-badge flex items-center gap-2">
          <Shield className="w-4 h-4 text-emerald-300" />
          Read-only
        </div>
      </div>

      <div className="space-y-2">
        <Label className="text-white/80">Endereço</Label>
        <div className="flex gap-2 flex-col sm:flex-row">
          <Input
            value={address}
            onChange={(e) => onAddressChange(e.target.value)}
            placeholder="byx1..."
            className="bg-white/5 border-white/10 text-white placeholder:text-white/30 flex-1"
          />
          <Button
            type="button"
            variant="outline"
            className="bg-white/5 border-white/10 text-white hover:bg-white/10"
            onClick={onCopy}
            disabled={!address.trim()}
          >
            <Copy className="w-4 h-4 mr-2" />
            Copiar
          </Button>
        </div>
      </div>

      {userEmail && onToggleSaveProfile && (
        <label className="flex items-center gap-3 text-sm wallet-muted">
          <Switch
            checked={Boolean(savingToProfile)}
            onCheckedChange={onToggleSaveProfile}
            className="data-[state=checked]:bg-emerald-500"
          />
          Associar este endereço ao meu perfil ({userEmail})
        </label>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <Button
          type="button"
          disabled={connecting}
          onClick={onConnect}
          className="h-12 bg-white/5 text-white/90 hover:bg-white/10 border border-white/10"
        >
          {connecting ? (
            <div className="flex items-center gap-2">
              <RefreshCw className="w-4 h-4 animate-spin" />
              Conectando...
            </div>
          ) : (
            <div className="flex items-center gap-2">
              <LinkIcon className="w-4 h-4" />
              Conectar Keplr
            </div>
          )}
        </Button>
        <Button
          type="button"
          disabled={loading}
          onClick={onQuery}
          className="h-12 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white font-semibold hover:from-emerald-600 hover:to-cyan-600"
        >
          {loading ? "Consultando..." : "Consultar saldos"}
        </Button>
        <Button
          type="button"
          variant="outline"
          disabled
          className={cn(
            "h-12 border-white/10 bg-white/5 text-white/70",
            "cursor-not-allowed opacity-70",
          )}
        >
          Em breve: Enviar/Receber
        </Button>
      </div>
    </div>
  );
}
