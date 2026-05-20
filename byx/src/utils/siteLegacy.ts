export function createPageUrl(pageName: string) {
  const map: Record<string, string> = {
    Home: "/",
    Dashboard: "/",
    Analytics: "/analytics",
    Stores: "/stores",
    Marketplace: "/marketplace",
    SalesCashback: "/sales-cashback",
    Transactions: "/transactions",
    Wallet: "/wallet",
    Chat: "/chat",
  };

  if (map[pageName]) return map[pageName];
  return "/" + pageName.toLowerCase();
}
