type FooterProps = {
  onSupport?: () => void;
  onIntegrations?: () => void;
  onCompliance?: () => void;
};

export function Footer({ onSupport, onIntegrations, onCompliance }: FooterProps) {
  return (
    <footer className="border-t border-border/20 py-8 mt-8">
      <div className="max-w-6xl mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <h4 className="font-semibold text-foreground mb-2">Suporte</h4>
            <button
              type="button"
              onClick={onSupport}
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              Suporte 24h
            </button>
          </div>
          <div>
            <h4 className="font-semibold text-foreground mb-2">Integração</h4>
            <button
              type="button"
              onClick={onIntegrations}
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              APIs de Integração
            </button>
          </div>
          <div>
            <h4 className="font-semibold text-foreground mb-2">Compliance</h4>
            <button
              type="button"
              onClick={onCompliance}
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              Termos de Compliance
            </button>
          </div>
        </div>

        <div className="mt-8 pt-6 border-t border-border/20 flex flex-col md:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <div className="w-6 h-6 rounded bg-primary/20 flex items-center justify-center">
              <span className="text-primary font-bold text-sm">Φ</span>
            </div>
            <span className="text-sm font-medium text-foreground">AIOS</span>
          </div>
          <p className="text-xs text-muted-foreground">
            © 2024 AIOS. Todos os direitos reservados.
          </p>
        </div>
      </div>
    </footer>
  );
}
