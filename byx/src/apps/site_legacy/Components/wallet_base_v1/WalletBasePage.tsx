import { useState } from "react";
import { LandingPage } from "./landing/LandingPage";
import { WalletDashboard } from "./wallet/WalletDashboard";

export default function WalletBasePage() {
  const [isConnected, setIsConnected] = useState(false);

  const handleConnect = () => {
    // TODO: integrar Keplr e salvar endereço (perfil/localStorage)
    // TODO: carregar saldos reais e transações após conexão
    setIsConnected(true);
  };

  const handleDisconnect = () => {
    // TODO: limpar sessão/carteira conectada
    setIsConnected(false);
  };

  return (
    <div className="wallet-base-scope">
      {isConnected ? <WalletDashboard onDisconnect={handleDisconnect} /> : <LandingPage onConnect={handleConnect} />}
    </div>
  );
}
