import type { ReactNode } from "react";
import "@/apps/marketplace/styles/marketplace.css";

export type MarketplaceShellProps = {
  children: ReactNode;
};

export function MarketplaceShell({ children }: MarketplaceShellProps) {
  return (
    <div className="iaos-marketplace marketplace-shell relative min-h-screen bg-[#070b0f] text-white overflow-hidden">
      <div className="marketplace-shell__overlay pointer-events-none" />
      <div className="marketplace-shell__glow pointer-events-none" />
      <div className="relative z-10">{children}</div>
    </div>
  );
}
