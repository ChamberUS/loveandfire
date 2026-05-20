import { ReactNode } from "react";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Upload, Image, Tags, Wallet } from "lucide-react";

type CreateAdPanelProps = {
  productForm: {
    name: string;
    description: string;
    price_byx: string;
    category: string;
    condition: string;
  };
  onChange: (values: Partial<CreateAdPanelProps["productForm"]>) => void;
  onSubmit: (e: React.FormEvent) => void;
  onCancel: () => void;
  categories: { value: string; label: string }[];
  conditions: { value: string; label: string }[];
  isSubmitting: boolean;
  extraActions?: ReactNode;
};

export function CreateAdPanel({
  productForm,
  onChange,
  onSubmit,
  onCancel,
  categories,
  conditions,
  isSubmitting,
  extraActions,
}: CreateAdPanelProps) {
  return (
    <div className="iaos-glass-card p-6 lg:p-7 space-y-5">
      <div className="flex items-start gap-3 justify-between">
        <div>
          <p className="text-sm iaos-text-muted">Criar Anúncio</p>
          <h2 className="text-2xl font-semibold iaos-text-primary">Publique seu produto</h2>
          <p className="text-sm iaos-text-muted iaos-clamp-2">
            Complete os campos abaixo para disponibilizar seu produto no marketplace AIOS.
          </p>
        </div>
        <div className="hidden md:flex items-center gap-2 iaos-chip text-xs">
          <Upload className="w-4 h-4" />
          Upload seguro
        </div>
      </div>

      <form onSubmit={onSubmit} className="space-y-4">
        <div className="grid md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label className="iaos-text-muted text-sm">Nome do Produto</Label>
            <Input
              value={productForm.name}
              onChange={(e) => onChange({ name: e.target.value })}
              className="iaos-input"
              placeholder="Ex: Notebook ultrafino"
            />
          </div>
          <div className="space-y-2">
            <Label className="iaos-text-muted text-sm flex items-center gap-2">
              <Wallet className="w-4 h-4" />
              Preço (AIOS)
            </Label>
            <Input
              type="number"
              value={productForm.price_byx}
              onChange={(e) => onChange({ price_byx: e.target.value })}
              className="iaos-input"
              placeholder="0.00"
            />
          </div>
        </div>

        <div className="space-y-2">
          <Label className="iaos-text-muted text-sm">Descrição</Label>
          <Textarea
            value={productForm.description}
            onChange={(e) => onChange({ description: e.target.value })}
            className="iaos-input"
            placeholder="Destaque specs, estado e garantia."
            rows={3}
          />
        </div>

        <div className="grid md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label className="iaos-text-muted text-sm flex items-center gap-2">
              <Tags className="w-4 h-4" />
              Categoria
            </Label>
            <Select value={productForm.category} onValueChange={(v) => onChange({ category: v })}>
              <SelectTrigger className="iaos-input">
                <SelectValue />
              </SelectTrigger>
              <SelectContent className="bg-[#0c111a] border border-white/10 text-white">
                {categories.map((cat) => (
                  <SelectItem key={cat.value} value={cat.value} className="text-white hover:bg-white/10">
                    {cat.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label className="iaos-text-muted text-sm flex items-center gap-2">
              <Image className="w-4 h-4" />
              Condição
            </Label>
            <Select value={productForm.condition} onValueChange={(v) => onChange({ condition: v })}>
              <SelectTrigger className="iaos-input">
                <SelectValue />
              </SelectTrigger>
              <SelectContent className="bg-[#0c111a] border border-white/10 text-white">
                {conditions.map((c) => (
                  <SelectItem key={c.value} value={c.value} className="text-white hover:bg-white/10">
                    {c.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>

        {extraActions}

        <div className="flex flex-col sm:flex-row gap-3 pt-2">
          <Button
            type="button"
            variant="outline"
            onClick={onCancel}
            className="flex-1 border-white/15 iaos-text-primary"
          >
            Cancelar
          </Button>
          <Button
            type="submit"
            className="flex-1 iaos-button-primary"
            disabled={isSubmitting}
          >
            {isSubmitting ? 'Salvando...' : 'Publicar anúncio'}
          </Button>
        </div>
      </form>
    </div>
  );
}
