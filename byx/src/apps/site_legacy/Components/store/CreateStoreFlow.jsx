import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Store,
  Upload,
  ArrowRight,
  ArrowLeft,
  Check,
  X,
  ShieldCheck,
  Globe,
  Instagram,
  Youtube,
  Clipboard,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { localDataClient } from '@/apps/site_legacy/api/localDataClient';
import { toast } from 'sonner';

const steps = [
  { id: 1, title: 'Identidade', description: 'Nome, logo e categoria' },
  { id: 2, title: 'Presença Digital', description: 'Links opcionais' },
  { id: 3, title: 'Blockchain', description: 'Endereço para pagamentos' },
  { id: 4, title: 'Ativar', description: 'Revise antes de publicar' },
];

const StepShell = ({ children }) => (
  <motion.div
    initial={{ opacity: 0, y: 12 }}
    animate={{ opacity: 1, y: 0 }}
    exit={{ opacity: 0, y: -12 }}
    className="iaos-glass-card p-6 lg:p-8 space-y-6"
  >
    {children}
  </motion.div>
);

export default function CreateStoreFlow({ onComplete, onCancel }) {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    nomeLoja: '',
    categoria: '',
    bio: '',
    logoUrl: '',
    website: '',
    instagram: '',
    youtube: '',
    carteira: '',
  });
  const [isUploading, setIsUploading] = useState(false);

  const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const result = await localDataClient.integrations.Core.UploadFile({ file });
      setFormData({ ...formData, logoUrl: result.file_url || result.url || formData.logoUrl });
      toast.success('Logo carregado!');
    } catch (error) {
      toast.error('Erro ao carregar logo');
    }
    setIsUploading(false);
  };

  const handlePasteWallet = async () => {
    try {
      const text = await navigator.clipboard.readText();
      setFormData((prev) => ({ ...prev, carteira: text }));
      toast.success('Endereço colado da área de transferência');
    } catch {
      toast.error('Não foi possível ler a área de transferência');
    }
  };

  const handleSubmit = async () => {
    try {
      const user = await localDataClient.auth.me();

      await localDataClient.entities.Store.create({
        name: formData.nomeLoja,
        description: formData.bio || '',
        logo_url: formData.logoUrl || '',
        category: formData.categoria || 'outros',
        owner_email: user.email,
        wallet_address: formData.carteira || '',
        website: formData.website || '',
        instagram: formData.instagram || '',
        youtube: formData.youtube || '',
        rating: 5,
        total_sales: 0,
        is_verified: false,
      });

      toast.success('Loja criada com sucesso!');
      onComplete();
    } catch (error) {
      toast.error('Erro ao criar loja');
    }
  };

  const nextStep = () => {
    if (step === 1 && !formData.nomeLoja) {
      toast.error('Digite o nome da loja');
      return;
    }
    if (step === 3 && !formData.carteira) {
      toast.error('Cole o endereço da carteira AIOS');
      return;
    }
    setStep((prev) => Math.min(prev + 1, steps.length));
  };

  const skipStep = () => {
    setStep((prev) => Math.min(prev + 1, steps.length));
  };

  return (
    <div className="relative z-10 max-w-5xl mx-auto space-y-6">
      <div className="iaos-glass-card p-6 lg:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-400/40 flex items-center justify-center">
            <Store className="w-6 h-6 text-emerald-300" />
          </div>
          <div>
            <p className="text-sm iaos-text-muted">Fluxo de criação</p>
            <h1 className="text-2xl font-semibold text-white leading-tight">Crie sua loja AIOS</h1>
            <p className="text-sm iaos-text-muted mt-1">Etapa {step} de {steps.length}</p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <ShieldCheck className="w-5 h-5 text-emerald-300" />
          <span className="text-sm iaos-text-muted">Dados seguros e verificáveis</span>
        </div>
      </div>

      <div className="iaos-glass-card p-4 lg:p-5 space-y-3">
        <div className="grid grid-cols-1 md:grid-cols-5 gap-3">
          {steps.map((item, index) => {
            const active = item.id === step;
            const done = item.id < step;
            return (
              <div key={item.id} className="flex items-center gap-3">
                <div
                  className={`w-11 h-11 rounded-2xl border flex items-center justify-center transition-all ${
                    active
                      ? 'bg-gradient-to-br from-emerald-500 to-cyan-500 text-black border-transparent shadow-lg shadow-emerald-500/30'
                      : done
                        ? 'bg-white/10 text-emerald-300 border-emerald-400/50'
                        : 'bg-white/5 text-white/60 border-white/10'
                  }`}
                >
                  {done ? <Check className="w-5 h-5" /> : item.id}
                </div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-white">{item.title}</p>
                  <p className="text-xs iaos-text-muted iaos-clamp-2">{item.description}</p>
                </div>
                {index < steps.length - 1 && <div className="flex-1 h-px bg-white/10 ml-2 hidden md:block" />}
              </div>
            );
          })}
        </div>
      </div>

      <AnimatePresence mode="wait">
        {step === 1 && (
          <StepShell key="step1">
            <div className="space-y-3 text-center">
              <Store className="w-12 h-12 text-emerald-300 mx-auto" />
              <h2 className="text-3xl font-semibold text-white">Qual o nome da sua loja?</h2>
              <p className="iaos-text-muted text-sm">Use um nome fácil de lembrar e que represente seu negócio.</p>
            </div>

            <div className="flex flex-col items-center gap-4">
              <label className="w-28 h-28 rounded-full border-2 border-dashed border-white/20 flex items-center justify-center cursor-pointer hover:border-emerald-400/60 transition-colors overflow-hidden bg-black/30">
                <input type="file" accept="image/*" onChange={handleFileUpload} className="hidden" />
                {formData.logoUrl ? (
                  <img src={formData.logoUrl} alt="Logo" className="w-full h-full object-cover" />
                ) : (
                  isUploading ? <span className="text-xs text-white/60">Carregando...</span> : <Upload className="w-6 h-6 text-white/60" />
                )}
              </label>
              <p className="text-xs iaos-text-muted">Clique para enviar o logo</p>
            </div>

            <div className="space-y-3">
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm">Nome da Loja</Label>
                <Input
                  value={formData.nomeLoja}
                  onChange={(e) => setFormData({ ...formData, nomeLoja: e.target.value })}
                  placeholder="Como sua empresa será conhecida?"
                  className="iaos-input h-12"
                  autoFocus
                />
              </div>
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm">Categoria</Label>
                <Select
                  value={formData.categoria}
                  onValueChange={(v) => setFormData({ ...formData, categoria: v })}
                >
                  <SelectTrigger className="iaos-input h-12">
                    <SelectValue placeholder="Foque no que vende hoje" />
                  </SelectTrigger>
                  <SelectContent className="bg-[#0b1117] border-white/10">
                    {['Revenda','Fabricação Própria','Distribuição','Serviços Logísticos','Importação/Exportação','Outros'].map((cat) => (
                      <SelectItem key={cat} value={cat}>{cat}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm">Bio / Conte-nos mais</Label>
                <Textarea
                  value={formData.bio}
                  onChange={(e) => setFormData({ ...formData, bio: e.target.value })}
                  placeholder="Conte brevemente a história ou o diferencial da sua loja..."
                  className="iaos-input min-h-[120px]"
                />
              </div>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
              <Button
                onClick={onCancel}
                variant="outline"
                className="flex-1 border-white/20 text-white hover:bg-white/10"
              >
                <X className="w-4 h-4 mr-2" />
                Cancelar
              </Button>
              <Button
                onClick={nextStep}
                className="flex-1 iaos-button-primary"
              >
                Próximo
                <ArrowRight className="w-4 h-4 ml-2" />
              </Button>
            </div>
          </StepShell>
        )}

        {step === 2 && (
          <StepShell key="step2">
            <div className="space-y-2 text-center">
              <h2 className="text-3xl font-semibold text-white">Presença digital</h2>
              <p className="iaos-text-muted text-sm">Campos opcionais para aumentar a confiança.</p>
            </div>

            <div className="space-y-3">
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm flex items-center gap-2">
                  <Globe className="w-4 h-4" /> Site
                </Label>
                <Input
                  value={formData.website}
                  onChange={(e) => setFormData({ ...formData, website: e.target.value })}
                  placeholder="www.sualoja.com.br"
                  className="iaos-input"
                />
              </div>
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm flex items-center gap-2">
                  <Instagram className="w-4 h-4" /> Instagram
                </Label>
                <Input
                  value={formData.instagram}
                  onChange={(e) => setFormData({ ...formData, instagram: e.target.value })}
                  placeholder="@sualoja"
                  className="iaos-input"
                />
              </div>
              <div className="space-y-1">
                <Label className="iaos-text-muted text-sm flex items-center gap-2">
                  <Youtube className="w-4 h-4" /> YouTube
                </Label>
                <Input
                  value={formData.youtube}
                  onChange={(e) => setFormData({ ...formData, youtube: e.target.value })}
                  placeholder="Link do canal"
                  className="iaos-input"
                />
              </div>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
              <Button
                onClick={() => setStep(1)}
                variant="outline"
                className="flex-1 border-white/20 text-white hover:bg-white/10"
              >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Voltar
              </Button>
              <Button
                onClick={nextStep}
                className="flex-1 iaos-button-primary"
              >
                Próximo
                <ArrowRight className="w-4 h-4 ml-2" />
              </Button>
            </div>
          </StepShell>
        )}

        {step === 3 && (
          <StepShell key="step3">
            <div className="space-y-2 text-center">
              <Upload className="w-12 h-12 text-purple-300 mx-auto" />
              <h2 className="text-3xl font-semibold text-white">Endereço da carteira AIOS</h2>
              <p className="iaos-text-muted text-sm">Obrigatório para receber pagamentos.</p>
            </div>

            <div className="p-4 iaos-surface border border-emerald-400/30 rounded-xl text-sm iaos-text-muted">
              Este será o endereço onde você receberá os pagamentos das vendas.
            </div>

            <div className="space-y-2">
              <Label className="iaos-text-muted text-sm">Endereço da carteira</Label>
              <div className="flex gap-2">
                <Input
                  value={formData.carteira}
                  onChange={(e) => setFormData({ ...formData, carteira: e.target.value })}
                  placeholder="0x..."
                  className="iaos-input font-mono"
                />
                <Button variant="outline" onClick={handlePasteWallet} className="border-white/20 text-white hover:bg-white/10">
                  <Clipboard className="w-4 h-4" />
                </Button>
              </div>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
              <Button
                onClick={() => setStep(2)}
                variant="outline"
                className="flex-1 border-white/20 text-white hover:bg-white/10"
              >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Voltar
              </Button>
              <Button
                onClick={nextStep}
                className="flex-1 iaos-button-primary"
              >
                Próximo
                <ArrowRight className="w-4 h-4 ml-2" />
              </Button>
            </div>
          </StepShell>
        )}

        {step === 4 && (
          <StepShell key="step4">
            <div className="space-y-2 text-center">
              <h2 className="text-3xl font-semibold text-white">Pré-visualização</h2>
              <p className="iaos-text-muted text-sm">Revise antes de publicar sua loja.</p>
            </div>

            <div className="iaos-surface p-4 rounded-xl border border-white/10 space-y-4">
              <div className="flex items-start gap-4">
                <div className="w-16 h-16 rounded-full overflow-hidden border border-white/15 bg-black/40 flex-shrink-0">
                  {formData.logoUrl ? (
                    <img src={formData.logoUrl} alt="Logo" className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center">
                      <Store className="w-6 h-6 text-white/50" />
                    </div>
                  )}
                </div>
                <div className="min-w-0">
                  <h4 className="text-xl font-semibold text-white truncate">{formData.nomeLoja || 'Nome da Loja'}</h4>
                  <span className="inline-block px-2 py-0.5 bg-white/10 text-white/80 text-xs rounded-full mt-1">
                    {formData.categoria || 'Categoria'}
                  </span>
                  <p className="text-sm iaos-text-muted mt-2 iaos-clamp-3">{formData.bio || 'Descrição da loja aparecerá aqui...'}</p>
                </div>
              </div>

              <div className="flex flex-wrap gap-3 pt-3 border-t border-white/10 text-xs iaos-text-muted">
                {formData.website && (
                  <span className="flex items-center gap-1">
                    <Globe className="w-3 h-3" /> {formData.website}
                  </span>
                )}
                {formData.instagram && (
                  <span className="flex items-center gap-1">
                    <Instagram className="w-3 h-3" /> {formData.instagram}
                  </span>
                )}
                {formData.youtube && (
                  <span className="flex items-center gap-1">
                    <Youtube className="w-3 h-3" /> Canal
                  </span>
                )}
              </div>

              {formData.carteira && (
                <div className="pt-3 border-t border-white/10">
                  <p className="text-xs iaos-text-muted mb-1">Carteira AIOS</p>
                  <p className="font-mono text-xs text-emerald-300 truncate">{formData.carteira}</p>
                </div>
              )}
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
              <Button
                onClick={() => setStep(3)}
                variant="outline"
                className="flex-1 border-white/20 text-white hover:bg-white/10"
              >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Voltar
              </Button>
              <Button
                onClick={handleSubmit}
                className="flex-1 iaos-button-primary"
              >
                <Check className="w-4 h-4 mr-2" />
                Gerar minha vitrine digital
              </Button>
            </div>
          </StepShell>
        )}
      </AnimatePresence>
    </div>
  );
}
