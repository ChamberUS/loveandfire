import { TrustBubble, trustFeatures } from "./TrustBubble";
import { ConnectWallet } from "./ConnectWallet";
import { CryptoMatrix } from "../wallet/CryptoMatrix";

type LandingPageProps = {
  onConnect: () => Promise<void> | void;
  connecting?: boolean;
};

export const LandingPage = ({ onConnect, connecting = false }: LandingPageProps) => {
  return (
    <div className="min-h-screen bg-background relative overflow-hidden flex items-center justify-center">
      <div className="fixed inset-0 pointer-events-none">
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-aios-gold/5 blur-[150px]" />
        <div className="absolute top-0 right-0 w-[400px] h-[400px] rounded-full bg-aios-purple/5 blur-[120px]" />
        <div className="absolute bottom-0 left-0 w-[300px] h-[300px] rounded-full bg-aios-gold/3 blur-[100px]" />

        <div
          className="absolute inset-0 opacity-[0.02]"
          style={{
            backgroundImage: `linear-gradient(hsl(var(--foreground)) 1px, transparent 1px),
                             linear-gradient(90deg, hsl(var(--foreground)) 1px, transparent 1px)`,
            backgroundSize: "50px 50px",
          }}
        />
      </div>

      <div className="fixed top-0 left-0 w-[300px] h-[200px] overflow-hidden opacity-30 pointer-events-none">
        <CryptoMatrix />
      </div>
      <div className="fixed top-0 right-0 w-[300px] h-[200px] overflow-hidden opacity-30 pointer-events-none">
        <CryptoMatrix />
      </div>
      <div className="fixed bottom-0 left-0 w-[300px] h-[200px] overflow-hidden opacity-30 pointer-events-none">
        <CryptoMatrix />
      </div>
      <div className="fixed bottom-0 right-0 w-[300px] h-[200px] overflow-hidden opacity-30 pointer-events-none">
        <CryptoMatrix />
      </div>

      <div className="hidden md:block">
        {trustFeatures.map((feature, index) => (
          <TrustBubble
            key={index}
            icon={feature.icon}
            title={feature.title}
            description={feature.description}
            position={feature.position}
            delay={feature.delay}
          />
        ))}
      </div>

      <div className="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-background via-background to-transparent">
        <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
          {trustFeatures.slice(0, 4).map((feature, index) => (
            <div key={index} className="glass-card p-3 min-w-[200px] flex-shrink-0">
              <div className="flex items-start gap-2">
                <div className="p-1.5 rounded-lg bg-aios-gold/10">{feature.icon}</div>
                <div>
                  <h4 className="text-xs font-semibold text-foreground">{feature.title}</h4>
                  <p className="text-[10px] text-muted-foreground line-clamp-2">{feature.description}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      <ConnectWallet onConnect={onConnect} connecting={connecting} />

      <div className="absolute top-1/4 left-1/4 w-px h-20 bg-gradient-to-b from-transparent via-aios-gold/20 to-transparent" />
      <div className="absolute top-1/3 right-1/4 w-px h-16 bg-gradient-to-b from-transparent via-aios-purple/20 to-transparent" />
      <div className="absolute bottom-1/4 left-1/3 w-20 h-px bg-gradient-to-r from-transparent via-aios-gold/20 to-transparent" />
    </div>
  );
};
