import { useRef, useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import {
  ArrowLeft,
  ArrowRight,
  Upload,
  X,
  Check,
  Plus,
  Sparkles,
  MapPin,
  Truck,
  MessageSquare,
  Edit3,
  Loader2,
} from "lucide-react";
import { toast } from "sonner";

type ImageItem = { file: File; preview: string };

type FormData = {
  categoria: string;
  titulo: string;
  imagens: ImageItem[];
  atributos: Record<string, string>;
  descricao: string;
  preco: string;
  paraDoacao: boolean;
  aceitaTrocas: boolean;
  entrega: "retirada" | "entrega" | "combinar" | "";
};

type Props = {
  onPublish: (data: FormData) => Promise<void> | void;
  onCancel?: () => void;
  isSubmitting?: boolean;
};

const categories = [
  { id: "automoveis", name: "Automóveis", icon: "🚗", description: "Carros, motos, caminhões" },
  { id: "pecas", name: "Peças e Acessórios", icon: "🔧", description: "Peças automotivas, acessórios" },
  { id: "celulares", name: "Celulares e Tablets", icon: "📱", description: "Smartphones, tablets, acessórios" },
  { id: "eletronicos", name: "Eletrônicos", icon: "💻", description: "Notebooks, TVs, áudio" },
  { id: "produtos", name: "Produtos", icon: "📦", description: "Roupas, móveis, eletrodomésticos" },
  { id: "imoveis", name: "Imóveis", icon: "🏠", description: "Apartamentos, casas, terrenos" },
  { id: "servicos", name: "Serviços", icon: "🛠️", description: "Domésticos, eventos, informática" },
  { id: "beleza", name: "Beleza e Saúde", icon: "💄", description: "Cosméticos, perfumes, saúde" },
  { id: "sexshop", name: "Sex Shop", icon: "💋", description: "Produtos adultos" },
  { id: "hardware", name: "Hardware e Games", icon: "🎮", description: "Placas, memórias, consoles" },
];

const categoryFields: Record<string, { label: string; placeholder: string; type?: string }[]> = {
  automoveis: [
    { label: "Marca", placeholder: "Ex: Toyota, Honda, Volkswagen" },
    { label: "Modelo", placeholder: "Ex: Corolla, Civic, Gol" },
    { label: "Ano", placeholder: "Ex: 2020" },
    { label: "Quilometragem", placeholder: "Ex: 50.000 km" },
    { label: "Condição", placeholder: "Novo ou Usado" },
  ],
  pecas: [
    { label: "Tipo de Peça", placeholder: "Ex: Motor, Suspensão, Freios" },
    { label: "Marca Compatível", placeholder: "Ex: Fiat, Chevrolet" },
    { label: "Condição", placeholder: "Novo ou Usado" },
    { label: "Código da Peça", placeholder: "Ex: ABC123" },
  ],
  celulares: [
    { label: "Marca", placeholder: "Ex: Apple, Samsung, Xiaomi" },
    { label: "Modelo", placeholder: "Ex: iPhone 15, Galaxy S24" },
    { label: "Armazenamento", placeholder: "Ex: 128GB, 256GB" },
    { label: "Condição", placeholder: "Novo ou Usado" },
    { label: "Cor", placeholder: "Ex: Preto, Branco, Azul" },
  ],
  eletronicos: [
    { label: "Tipo", placeholder: "Ex: Notebook, TV, Fone de ouvido" },
    { label: "Marca", placeholder: "Ex: Dell, LG, Sony" },
    { label: "Modelo", placeholder: "Ex: Inspiron 15, OLED C3" },
    { label: "Tamanho da Tela", placeholder: "Ex: 15.6 polegadas" },
    { label: "Memória RAM", placeholder: "Ex: 8GB, 16GB" },
    { label: "Armazenamento SSD", placeholder: "Ex: 256GB, 512GB" },
    { label: "Condição", placeholder: "Novo ou Usado" },
  ],
  produtos: [
    { label: "Tipo de Produto", placeholder: "Ex: Roupa, Móvel, Eletrodoméstico" },
    { label: "Marca", placeholder: "Ex: Nike, Tramontina" },
    { label: "Tamanho/Dimensões", placeholder: "Ex: M, 1.80m x 0.90m" },
    { label: "Cor", placeholder: "Ex: Preto, Branco" },
    { label: "Condição", placeholder: "Novo ou Usado" },
  ],
  imoveis: [
    { label: "Tipo", placeholder: "Ex: Apartamento, Casa, Terreno" },
    { label: "Área (m²)", placeholder: "Ex: 80m²" },
    { label: "Quartos", placeholder: "Ex: 2, 3" },
    { label: "Banheiros", placeholder: "Ex: 1, 2" },
    { label: "Vagas de Garagem", placeholder: "Ex: 1, 2" },
    { label: "Localização", placeholder: "Ex: Bairro, Cidade" },
  ],
  servicos: [
    { label: "Tipo de Serviço", placeholder: "Ex: Encanador, Eletricista" },
    { label: "Área de Atuação", placeholder: "Ex: São Paulo - SP" },
    { label: "Experiência", placeholder: "Ex: 5 anos" },
    { label: "Disponibilidade", placeholder: "Ex: Segunda a Sexta" },
  ],
  beleza: [
    { label: "Tipo de Produto", placeholder: "Ex: Perfume, Creme, Maquiagem" },
    { label: "Marca", placeholder: "Ex: Natura, O Boticário" },
    { label: "Volume/Quantidade", placeholder: "Ex: 100ml, 50g" },
    { label: "Condição", placeholder: "Novo ou Usado" },
  ],
  sexshop: [
    { label: "Tipo de Produto", placeholder: "Ex: Lingerie, Acessório" },
    { label: "Material", placeholder: "Ex: Silicone, Algodão" },
    { label: "Tamanho", placeholder: "Ex: P, M, G" },
    { label: "Condição", placeholder: "Novo" },
  ],
  hardware: [
    { label: "Tipo", placeholder: "Ex: Placa de Vídeo, Processador, Console" },
    { label: "Marca", placeholder: "Ex: NVIDIA, AMD, Sony" },
    { label: "Modelo", placeholder: "Ex: RTX 4070, Ryzen 5" },
    { label: "Memória", placeholder: "Ex: 8GB, 16GB" },
    { label: "Condição", placeholder: "Novo ou Usado" },
  ],
};

export function CreateAdFlow({ onPublish, onCancel, isSubmitting }: Props) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [currentStep, setCurrentStep] = useState(1);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [isGeneratingDescription, setIsGeneratingDescription] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState<FormData>({
    categoria: "",
    titulo: "",
    imagens: [],
    atributos: {},
    descricao: "",
    preco: "",
    paraDoacao: false,
    aceitaTrocas: false,
    entrega: "",
  });

  const totalSteps = 8;
  const progress = (currentStep / totalSteps) * 100;

  const updateFormData = (field: keyof FormData, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleCategorySelect = (categoryId: string) => {
    updateFormData("categoria", categoryId);
    setTimeout(() => setCurrentStep(2), 200);
  };

  const handleTitleSubmit = () => {
    if (formData.titulo.trim()) {
      setCurrentStep(3);
    } else {
      toast.error("Digite um título");
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    const validFiles = files.filter((file) =>
      ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp", "image/heic"].includes(
        file.type.toLowerCase(),
      ),
    );

    if (formData.imagens.length + validFiles.length > 6) {
      toast.error("Máximo de 6 fotos permitidas");
      return;
    }

    const newImages = validFiles.map((file) => ({
      file,
      preview: URL.createObjectURL(file),
    }));

    updateFormData("imagens", [...formData.imagens, ...newImages]);
  };

  const removeImage = (index: number) => {
    URL.revokeObjectURL(formData.imagens[index].preview);
    updateFormData(
      "imagens",
      formData.imagens.filter((_, i) => i !== index),
    );
  };

  const handleImagesSubmit = () => {
    if (formData.imagens.length === 0) {
      toast.error("Adicione pelo menos 1 foto");
      return;
    }
    setIsAnalyzing(true);
    setTimeout(() => {
      setIsAnalyzing(false);
      setCurrentStep(4);
    }, 1200);
  };

  const handleAttributesSubmit = () => setCurrentStep(5);

  const handleGenerateDescription = () => {
    setIsGeneratingDescription(true);
    setTimeout(() => {
      const category = categories.find((c) => c.id === formData.categoria);
      const attrs = Object.entries(formData.atributos)
        .filter(([_, v]) => v)
        .map(([k, v]) => `${k}: ${v}`)
        .join(", ");
      const generatedDesc = `${formData.titulo}\n\n${category?.name || "Produto"} em excelente estado. ${
        attrs ? `Características: ${attrs}.` : ""
      }\n\nEntre em contato para mais informações. Aceito propostas!`;
      updateFormData("descricao", generatedDesc);
      setIsGeneratingDescription(false);
    }, 1200);
  };

  const handleDescriptionSubmit = () => {
    if (formData.descricao.trim()) {
      setCurrentStep(6);
    } else {
      toast.error("Adicione uma descrição");
    }
  };

  const handlePriceSubmit = () => {
    if (formData.preco || formData.paraDoacao) {
      setCurrentStep(7);
    } else {
      toast.error("Digite um preço ou marque como doação");
    }
  };

  const handleDeliverySubmit = () => {
    if (formData.entrega) {
      setCurrentStep(8);
    } else {
      toast.error("Selecione uma opção de entrega");
    }
  };

  const handlePublish = async () => {
    try {
      await onPublish(formData);
      setIsEditing(false);
    } catch (error) {
      // toast handled upstream or here if needed
    }
  };

  const goBack = () => {
    if (currentStep > 1) setCurrentStep(currentStep - 1);
  };

  return (
    <div className="iaos-glass-card p-4 lg:p-6 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-xs iaos-text-muted uppercase tracking-wider">Criação de anúncio</p>
          <h2 className="text-xl font-semibold text-white">Novo anúncio</h2>
        </div>
        {onCancel && (
          <Button variant="ghost" size="sm" onClick={onCancel} className="text-white/70 hover:text-white">
            <X className="w-4 h-4" />
          </Button>
        )}
      </div>

      <div className="w-full h-2 bg-white/5 rounded-full overflow-hidden">
        <div
          className="h-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all duration-500"
          style={{ width: `${progress}%` }}
        />
      </div>

      <div className="space-y-6">
        {currentStep === 1 && (
          <div className="space-y-4">
            <div className="text-center space-y-1">
              <h3 className="text-2xl font-semibold text-white">O que temos hoje?</h3>
              <p className="iaos-text-muted">Escolha a categoria do anúncio</p>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
              {categories.map((cat) => (
                <button
                  key={cat.id}
                  onClick={() => handleCategorySelect(cat.id)}
                  className="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-emerald-400/50 hover:bg-white/10 transition-all text-left"
                >
                  <span className="text-3xl mb-2 block">{cat.icon}</span>
                  <h4 className="text-white font-semibold">{cat.name}</h4>
                  <p className="text-xs iaos-text-muted mt-1">{cat.description}</p>
                </button>
              ))}
            </div>
          </div>
        )}

        {currentStep === 2 && (
          <div className="space-y-4">
            <div className="space-y-1">
              <h3 className="text-2xl font-semibold text-white">Qual o título?</h3>
              <p className="iaos-text-muted">Seja claro e específico para atrair compradores.</p>
            </div>
            <Input
              autoFocus
              placeholder="Ex: iPhone 14 Pro Max 256GB Preto"
              value={formData.titulo}
              onChange={(e) => updateFormData("titulo", e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && handleTitleSubmit()}
              className="text-lg py-6 iaos-input"
            />
          </div>
        )}

        {currentStep === 3 && (
          <div className="space-y-4">
            <div className="space-y-1">
              <h3 className="text-2xl font-semibold text-white">Adicione fotos</h3>
              <p className="iaos-text-muted">Até 6 fotos (JPG, PNG, GIF, WEBP, HEIC).</p>
            </div>

            <input
              ref={fileInputRef}
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/heic"
              multiple
              onChange={handleFileSelect}
              className="hidden"
            />

            {formData.imagens.length > 0 && (
              <div className="grid grid-cols-3 gap-3">
                {formData.imagens.map((img, index) => (
                  <div key={index} className="relative aspect-square group">
                    <img src={img.preview} alt={`Foto ${index + 1}`} className="w-full h-full object-cover rounded-xl border border-white/10" />
                    <button
                      onClick={() => removeImage(index)}
                      className="absolute top-2 right-2 p-1.5 bg-black/70 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <X className="w-4 h-4 text-white" />
                    </button>
                  </div>
                ))}
              </div>
            )}

            {formData.imagens.length < 6 && (
              <div
                onClick={() => fileInputRef.current?.click()}
                className="border-2 border-dashed border-white/20 rounded-2xl p-6 text-center cursor-pointer hover:border-emerald-400/60 hover:bg-white/5 transition-all"
              >
                <Upload className="w-10 h-10 text-emerald-300 mx-auto mb-3" />
                <p className="text-white font-medium">Arraste as fotos ou clique para adicionar</p>
                <Button variant="outline" size="sm" className="mt-3 border-white/30 text-white hover:bg-white/10">
                  <Plus className="w-4 h-4 mr-2" />
                  Adicionar fotos
                </Button>
              </div>
            )}
          </div>
        )}

        {currentStep === 4 && isAnalyzing && (
          <div className="flex flex-col items-center justify-center py-12 space-y-4">
            <div className="w-16 h-16 rounded-full bg-emerald-500/10 flex items-center justify-center animate-pulse">
              <Sparkles className="w-8 h-8 text-emerald-300 animate-spin" />
            </div>
            <div className="text-center">
              <h2 className="text-xl font-semibold text-white">Analisando suas fotos...</h2>
              <p className="iaos-text-muted mt-1">Identificando informações do produto</p>
            </div>
          </div>
        )}

        {currentStep === 4 && !isAnalyzing && (
          <div className="space-y-4">
            <div className="space-y-1">
              <h3 className="text-2xl font-semibold text-white">Complete as informações</h3>
              <p className="iaos-text-muted">Preencha os detalhes para facilitar a busca.</p>
            </div>

            <div className="space-y-3">
              {(categoryFields[formData.categoria] || categoryFields.produtos).map((field, index) => (
                <div key={index} className="space-y-1">
                  <label className="text-sm font-medium text-white/80">{field.label}</label>
                  <Input
                    placeholder={field.placeholder}
                    type={field.type || "text"}
                    value={formData.atributos[field.label] || ""}
                    onChange={(e) => updateFormData("atributos", { ...formData.atributos, [field.label]: e.target.value })}
                    className="iaos-input"
                  />
                </div>
              ))}
            </div>
          </div>
        )}

        {currentStep === 5 && (
          <div className="space-y-4">
            <div className="space-y-1">
              <h3 className="text-2xl font-semibold text-white">Descrição e prévia</h3>
              <p className="iaos-text-muted">Visualize e escreva a descrição.</p>
            </div>

            <div className="iaos-surface p-4 rounded-xl space-y-3">
              {formData.imagens[0] && (
                <img src={formData.imagens[0].preview} alt="Preview" className="w-full aspect-video object-cover rounded-lg" />
              )}
              <h3 className="font-semibold text-lg text-white">{formData.titulo}</h3>
              <div className="flex flex-wrap gap-2">
                {Object.entries(formData.atributos)
                  .filter(([_, v]) => v)
                  .map(([k, v]) => (
                    <span key={k} className="text-xs px-2 py-1 bg-white/10 text-white rounded-full">
                      {k}: {v}
                    </span>
                  ))}
              </div>
            </div>

            <div className="space-y-3">
              <div className="flex gap-3">
                <Button
                  variant="secondary"
                  className="flex-1"
                  onClick={handleGenerateDescription}
                  disabled={isGeneratingDescription}
                >
                  {isGeneratingDescription ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Sparkles className="w-4 h-4 mr-2" />}
                  AIOS cria para mim
                </Button>
                <Button variant="outline" className="flex-1">
                  <Edit3 className="w-4 h-4 mr-2" />
                  Eu prefiro escrever
                </Button>
              </div>

              <Textarea
                placeholder="Descreva seu produto em detalhes..."
                value={formData.descricao}
                onChange={(e) => updateFormData("descricao", e.target.value)}
                rows={6}
                className="iaos-input resize-none"
              />
            </div>
          </div>
        )}

        {currentStep === 6 && (
          <div className="space-y-4">
            <h3 className="text-2xl font-semibold text-white">Defina o preço</h3>
            <div className="space-y-4">
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-3xl font-bold text-white/50">
                  R$
                </span>
                <Input
                  type="number"
                  placeholder="0,00"
                  value={formData.preco}
                  onChange={(e) => updateFormData("preco", e.target.value)}
                  disabled={formData.paraDoacao}
                  className="text-3xl font-bold pl-16 py-6 iaos-input"
                />
              </div>

              <div className="flex flex-wrap gap-4">
                <label className="flex items-center gap-2 cursor-pointer text-white">
                  <Checkbox
                    checked={formData.paraDoacao}
                    onCheckedChange={(checked) => {
                      updateFormData("paraDoacao", Boolean(checked));
                      if (checked) updateFormData("preco", "");
                    }}
                  />
                  <span>Para doação</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer text-white">
                  <Checkbox
                    checked={formData.aceitaTrocas}
                    onCheckedChange={(checked) => updateFormData("aceitaTrocas", Boolean(checked))}
                  />
                  <span>Aceita trocas</span>
                </label>
              </div>
            </div>
          </div>
        )}

        {currentStep === 7 && (
          <div className="space-y-4">
            <h3 className="text-2xl font-semibold text-white">Entrega</h3>
            <p className="iaos-text-muted">Escolha a melhor opção.</p>

            <div className="space-y-3">
              {[
                { key: "retirada", title: "Retirada no local", desc: "O comprador retira no seu endereço", icon: <MapPin className="w-6 h-6" /> },
                { key: "entrega", title: "Entrego pelo site", desc: "Você envia para o comprador", icon: <Truck className="w-6 h-6" /> },
                { key: "combinar", title: "Combinar com comprador", desc: "Vocês decidem juntos", icon: <MessageSquare className="w-6 h-6" /> },
              ].map((opt) => (
                <button
                  key={opt.key}
                  onClick={() => updateFormData("entrega", opt.key as FormData["entrega"])}
                  className={`w-full p-4 rounded-2xl border text-left transition-all ${
                    formData.entrega === opt.key
                      ? "bg-emerald-500/10 border-emerald-400/60"
                      : "bg-white/5 border-white/10 hover:border-emerald-400/40"
                  }`}
                >
                  <div className="flex items-center gap-4">
                    <div className={`w-12 h-12 rounded-full flex items-center justify-center ${
                      formData.entrega === opt.key ? "bg-emerald-500 text-black" : "bg-black/40 text-white"
                    }`}>
                      {opt.icon}
                    </div>
                    <div>
                      <h4 className="text-white font-semibold">{opt.title}</h4>
                      <p className="text-sm iaos-text-muted">{opt.desc}</p>
                    </div>
                  </div>
                </button>
              ))}
            </div>
          </div>
        )}

        {currentStep === 8 && !isEditing && (
          <div className="space-y-4">
            <h3 className="text-2xl font-semibold text-white">Revisão final</h3>
            <div className="iaos-surface p-4 rounded-xl space-y-4">
              {formData.imagens.length > 0 && (
                <div className="grid grid-cols-3 gap-2">
                  {formData.imagens.map((img, index) => (
                    <img key={index} src={img.preview} alt={`Foto ${index + 1}`} className="w-full aspect-square object-cover rounded-lg" />
                  ))}
                </div>
              )}
              <div>
                <span className="text-xs text-emerald-300 uppercase tracking-wider">
                  {categories.find((c) => c.id === formData.categoria)?.name}
                </span>
                <h2 className="text-xl font-bold text-white mt-1">{formData.titulo}</h2>
              </div>
              <div className="flex items-baseline gap-2">
                {formData.paraDoacao ? (
                  <span className="text-2xl font-bold text-emerald-300">Doação</span>
                ) : (
                  <span className="text-2xl font-bold text-white">
                    R$ {parseFloat(formData.preco || "0").toLocaleString("pt-BR", { minimumFractionDigits: 2 })}
                  </span>
                )}
                {formData.aceitaTrocas && <span className="text-sm text-emerald-300">• Aceita trocas</span>}
              </div>
              <div className="flex flex-wrap gap-2">
                {Object.entries(formData.atributos)
                  .filter(([_, v]) => v)
                  .map(([k, v]) => (
                    <span key={k} className="text-xs px-2 py-1 bg-white/10 text-white rounded-full">
                      {k}: {v}
                    </span>
                  ))}
              </div>
              <div className="pt-3 border-t border-white/10">
                <p className="text-sm iaos-text-muted whitespace-pre-line">{formData.descricao}</p>
              </div>
              <div className="pt-3 border-t border-white/10 flex items-center gap-2 text-sm iaos-text-muted">
                {formData.entrega === "retirada" && (
                  <>
                    <MapPin className="w-4 h-4" />
                    <span>Retirada no local</span>
                  </>
                )}
                {formData.entrega === "entrega" && (
                  <>
                    <Truck className="w-4 h-4" />
                    <span>Entrega pelo site</span>
                  </>
                )}
                {formData.entrega === "combinar" && (
                  <>
                    <MessageSquare className="w-4 h-4" />
                    <span>Combinar com comprador</span>
                  </>
                )}
              </div>
            </div>
          </div>
        )}

        {currentStep === 8 && isEditing && (
          <div className="space-y-3">
            <h3 className="text-2xl font-semibold text-white">Editar anúncio</h3>
            <div className="space-y-2">
              <label className="text-sm text-white/80">Título</label>
              <Input value={formData.titulo} onChange={(e) => updateFormData("titulo", e.target.value)} className="iaos-input" />
            </div>
            <div className="space-y-2">
              <label className="text-sm text-white/80">Preço (R$)</label>
              <Input value={formData.preco} onChange={(e) => updateFormData("preco", e.target.value)} className="iaos-input" />
            </div>
            <div className="space-y-2">
              <label className="text-sm text-white/80">Descrição</label>
              <Textarea
                value={formData.descricao}
                onChange={(e) => updateFormData("descricao", e.target.value)}
                rows={5}
                className="iaos-input"
              />
            </div>
            {(categoryFields[formData.categoria] || categoryFields.produtos).map((field, index) => (
              <div key={index} className="space-y-1">
                <label className="text-sm text-white/80">{field.label}</label>
                <Input
                  value={formData.atributos[field.label] || ""}
                  onChange={(e) => updateFormData("atributos", { ...formData.atributos, [field.label]: e.target.value })}
                  className="iaos-input"
                />
              </div>
            ))}
            <Button variant="secondary" className="w-full" onClick={() => setIsEditing(false)}>
              <Check className="w-4 h-4 mr-2" />
              Salvar alterações
            </Button>
          </div>
        )}
      </div>

      <div className="flex flex-wrap gap-3 justify-between">
        <div className="flex gap-2">
          {currentStep > 1 && (
            <Button variant="outline" onClick={goBack} className="border-white/20 text-white hover:bg-white/10">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Voltar
            </Button>
          )}
          {onCancel && currentStep === 1 && (
            <Button variant="ghost" onClick={onCancel} className="text-white/70 hover:text-white">
              Cancelar
            </Button>
          )}
        </div>

        {currentStep === 2 && (
          <Button onClick={handleTitleSubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 3 && (
          <Button onClick={handleImagesSubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 4 && !isAnalyzing && (
          <Button onClick={handleAttributesSubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 5 && (
          <Button onClick={handleDescriptionSubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 6 && (
          <Button onClick={handlePriceSubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 7 && (
          <Button onClick={handleDeliverySubmit} className="iaos-button-primary">
            Continuar
            <ArrowRight className="w-4 h-4 ml-2" />
          </Button>
        )}
        {currentStep === 8 && !isEditing && (
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => setIsEditing(true)} className="border-white/20 text-white hover:bg-white/10">
              <Edit3 className="w-4 h-4 mr-2" />
              Editar
            </Button>
            <Button onClick={handlePublish} disabled={isSubmitting} className="iaos-button-primary">
              {isSubmitting ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Check className="w-4 h-4 mr-2" />}
              Anunciar agora
            </Button>
          </div>
        )}
      </div>
    </div>
  );
}
