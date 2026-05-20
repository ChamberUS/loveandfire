import { useMemo } from "react";
import { useQuery } from "@tanstack/react-query";
import { base44 } from "@/api/base44Client";
import { useAuth } from "@/auth/AuthContext";
import { getMerchantByEmail } from "@/merchants/merchantStore";

function normalizeEmail(email: string | null | undefined): string | null {
  if (!email) return null;
  return email.trim().toLowerCase();
}

export function useStoreAccess() {
  const { adminSession, userSession } = useAuth();
  const ownerEmail = useMemo(() => normalizeEmail(userSession?.email), [userSession?.email]);

  const {
    data: stores = [],
    isLoading: isLoadingStores,
    isFetching: isFetchingStores,
  } = useQuery({
    queryKey: ["store-access", ownerEmail],
    queryFn: async () => {
      if (!ownerEmail) return [];
      try {
        const result = await base44.entities.Store.filter({ owner_email: ownerEmail });
        return Array.isArray(result) ? result : [];
      } catch (err) {
        console.warn("[store-access] Falha ao carregar lojas", err);
        return [];
      }
    },
    enabled: Boolean(ownerEmail),
  });

  const localMerchant = useMemo(() => (ownerEmail ? getMerchantByEmail(ownerEmail) : null), [ownerEmail]);
  const storeId = useMemo(
    () => (Array.isArray(stores) && stores[0]?.id ? stores[0].id : localMerchant?.id ?? null),
    [stores, localMerchant?.id],
  );

  const isLogged = Boolean(userSession);
  const isAdmin = Boolean(adminSession);
  const hasStore = Boolean((Array.isArray(stores) && stores.length > 0) || localMerchant);
  const canSeeAnalytics = isAdmin || (isLogged && hasStore);

  return {
    isLogged,
    isAdmin,
    hasStore,
    canSeeAnalytics,
    canSeeAdminMenu: isAdmin,
    canAccessMyStore: isLogged,
    storeId,
    user: userSession,
    loading: isLoadingStores || isFetchingStores,
    // Compat aliases for existing callers
    isLojista: hasStore,
    canAccessAnalytics: canSeeAnalytics,
    isLoadingStores,
  };
}
