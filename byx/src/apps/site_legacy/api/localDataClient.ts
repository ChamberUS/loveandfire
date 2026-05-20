import { getUserSession } from "@/auth/userAuth";

type StoreRecord = {
  id: string;
  name: string;
  description?: string;
  logo_url?: string;
  category?: string;
  owner_email: string;
  wallet_address?: string;
  website?: string;
  instagram?: string;
  youtube?: string;
  created_date: string;
};

type ProductRecord = {
  id: string;
  name: string;
  description?: string;
  price_byx?: number;
  price_brl?: number;
  category?: string;
  condition?: string;
  seller_email: string;
  store_id?: string;
  status: string;
  views: number;
  created_date: string;
};

type SaleRecord = {
  id: string;
  store_id?: string;
  store_name?: string;
  seller_email: string;
  amount_brl: number;
  cashback_aios?: number;
  customer_email?: string;
  description?: string;
  payment_method?: string;
  created_date: string;
};

type QRPaymentRecord = {
  id: string;
  amount_brl: number;
  description?: string;
  seller_email: string;
  created_date: string;
};

type ChatConversationRecord = {
  id: string;
  seller_email?: string;
  updated_date: string;
};

type UserProfile = {
  banner_url?: string;
  logo_url?: string;
};

const STORAGE_KEYS = {
  stores: "iaos_local_stores",
  products: "iaos_local_products",
  sales: "iaos_local_sales",
  qrs: "iaos_local_qr_payments",
  conversations: "iaos_local_conversations",
  profiles: "iaos_local_profiles",
};

function loadArray<T>(key: string): T[] {
  if (typeof window === "undefined") return [];
  const raw = window.localStorage.getItem(key);
  if (!raw) return [];
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function saveArray<T>(key: string, value: T[]) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(key, JSON.stringify(value));
}

function loadProfiles(): Record<string, UserProfile> {
  if (typeof window === "undefined") return {};
  const raw = window.localStorage.getItem(STORAGE_KEYS.profiles);
  if (!raw) return {};
  try {
    const parsed = JSON.parse(raw);
    return typeof parsed === "object" && parsed ? parsed : {};
  } catch {
    return {};
  }
}

function saveProfiles(profiles: Record<string, UserProfile>) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(STORAGE_KEYS.profiles, JSON.stringify(profiles));
}

function ensureSession() {
  const session = getUserSession();
  if (!session) throw new Error("Faça login para continuar");
  return session;
}

function generateId(prefix: string) {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
}

async function uploadFile(file: File): Promise<{ file_url: string }> {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = () => resolve({ file_url: typeof reader.result === "string" ? reader.result : "" });
    reader.onerror = () => resolve({ file_url: URL.createObjectURL(file) });
    reader.readAsDataURL(file);
  });
}

export const localDataClient = {
  auth: {
    async me() {
      const session = ensureSession();
      const profiles = loadProfiles();
      const profile = profiles[session.email] || {};
      return { email: session.email, role: session.type, ...profile };
    },
    async updateMe(data: Partial<UserProfile>) {
      const session = ensureSession();
      const profiles = loadProfiles();
      profiles[session.email] = { ...(profiles[session.email] || {}), ...data };
      saveProfiles(profiles);
      return { email: session.email, ...profiles[session.email] };
    },
  },
  integrations: {
    Core: {
      UploadFile: uploadFile,
    },
  },
  entities: {
    Store: {
      async filter(filter: Partial<StoreRecord>) {
        const stores = loadArray<StoreRecord>(STORAGE_KEYS.stores);
        if (!filter || Object.keys(filter).length === 0) return stores;
        return stores.filter((store) =>
          Object.entries(filter).every(([key, value]) => (store as any)[key] === value),
        );
      },
      async create(data: Partial<StoreRecord>) {
        const session = ensureSession();
        const stores = loadArray<StoreRecord>(STORAGE_KEYS.stores);
        const record: StoreRecord = {
          id: generateId("store"),
          name: data.name || data.description || "Minha Loja",
          description: data.description || "",
          logo_url: data.logo_url || "",
          category: data.category || "outros",
          owner_email: session.email,
          wallet_address: data.wallet_address || "",
          website: (data as any).website || "",
          instagram: (data as any).instagram || "",
          youtube: (data as any).youtube || "",
          created_date: new Date().toISOString(),
        };
        stores.push(record);
        saveArray(STORAGE_KEYS.stores, stores);
        return record;
      },
    },
    Product: {
      async filter(filter: Partial<ProductRecord>) {
        const products = loadArray<ProductRecord>(STORAGE_KEYS.products);
        if (!filter || Object.keys(filter).length === 0) return products;
        return products.filter((product) =>
          Object.entries(filter).every(([key, value]) => (product as any)[key] === value),
        );
      },
      async create(data: Partial<ProductRecord>) {
        const session = ensureSession();
        const products = loadArray<ProductRecord>(STORAGE_KEYS.products);
        const record: ProductRecord = {
          id: generateId("product"),
          name: data.name || "Novo Produto",
          description: data.description || "",
          price_byx: data.price_byx || 0,
          price_brl: data.price_brl || 0,
          category: data.category || "outros",
          condition: data.condition || "novo",
          seller_email: data.seller_email || session.email,
          store_id: data.store_id,
          status: data.status || "available",
          views: data.views ?? 0,
          created_date: new Date().toISOString(),
        };
        products.push(record);
        saveArray(STORAGE_KEYS.products, products);
        return record;
      },
      async update(id: string, data: Partial<ProductRecord>) {
        const products = loadArray<ProductRecord>(STORAGE_KEYS.products);
        const idx = products.findIndex((p) => p.id === id);
        if (idx >= 0) {
          products[idx] = { ...products[idx], ...data };
          saveArray(STORAGE_KEYS.products, products);
          return products[idx];
        }
        throw new Error("Produto não encontrado");
      },
      async delete(id: string) {
        const products = loadArray<ProductRecord>(STORAGE_KEYS.products).filter((p) => p.id !== id);
        saveArray(STORAGE_KEYS.products, products);
        return true;
      },
    },
    Sale: {
      async filter(filter: Partial<SaleRecord>) {
        const sales = loadArray<SaleRecord>(STORAGE_KEYS.sales);
        if (!filter || Object.keys(filter).length === 0) return sales;
        return sales.filter((sale) =>
          Object.entries(filter).every(([key, value]) => (sale as any)[key] === value),
        );
      },
      async create(data: Partial<SaleRecord>) {
        const session = ensureSession();
        const sales = loadArray<SaleRecord>(STORAGE_KEYS.sales);
        const record: SaleRecord = {
          id: generateId("sale"),
          seller_email: data.seller_email || session.email,
          store_id: data.store_id,
          store_name: data.store_name,
          amount_brl: data.amount_brl || 0,
          cashback_aios: data.cashback_aios || 0,
          customer_email: data.customer_email || "",
          description: data.description || "",
          payment_method: data.payment_method || "manual",
          created_date: new Date().toISOString(),
        };
        sales.push(record);
        saveArray(STORAGE_KEYS.sales, sales);
        return record;
      },
    },
    QRPayment: {
      async create(data: Partial<QRPaymentRecord>) {
        const session = ensureSession();
        const qrs = loadArray<QRPaymentRecord>(STORAGE_KEYS.qrs);
        const record: QRPaymentRecord = {
          id: generateId("qr"),
          amount_brl: data.amount_brl || 0,
          description: data.description || "",
          seller_email: data.seller_email || session.email,
          created_date: new Date().toISOString(),
        };
        qrs.push(record);
        saveArray(STORAGE_KEYS.qrs, qrs);
        return record;
      },
    },
    Order: {
      async filter() {
        return [];
      },
    },
    ChatConversation: {
      async filter() {
        return loadArray<ChatConversationRecord>(STORAGE_KEYS.conversations);
      },
    },
  },
};
