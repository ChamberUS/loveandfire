import { localDataClient } from "@/apps/site_legacy/api/localDataClient";
import { listPaymentRequestsByMerchant } from "@/api/paymentsClient";

function normalizeEmail(email?: string | null): string | null {
  if (!email) return null;
  return email.trim().toLowerCase();
}

async function safeCount<T>(fn: () => Promise<T[]>): Promise<number | null> {
  try {
    const data = await fn();
    if (!Array.isArray(data)) return null;
    return data.length;
  } catch (err) {
    console.warn("[analytics] erro ao contar registros", err);
    return null;
  }
}

export async function getActiveProductsCount(ownerEmail?: string | null): Promise<number | null> {
  const email = normalizeEmail(ownerEmail);
  return safeCount(() =>
    email
      ? localDataClient.entities.Product.filter({ seller_email: email, status: "available" })
      : localDataClient.entities.Product.filter({ status: "available" }),
  );
}

export async function getConfirmedPaymentsCount(
  merchantId?: string | null,
  ownerEmail?: string | null,
): Promise<number | null> {
  if (!merchantId) return null;
  const email = normalizeEmail(ownerEmail);

  try {
    const requests = await listPaymentRequestsByMerchant(merchantId);
    const confirmed = requests.filter((req) => {
      const ownerMatch = email ? normalizeEmail(req.ownerEmail) === email : true;
      return req.status === "paid" && ownerMatch;
    });
    return confirmed.length;
  } catch (err) {
    console.warn("[analytics] erro ao buscar pagamentos confirmados", err);
    return null;
  }
}

export async function getOrdersCount(ownerEmail?: string | null): Promise<number | null> {
  const email = normalizeEmail(ownerEmail);
  try {
    // Vendas registradas (Sales) representam pedidos concluídos no fluxo atual.
    const sales = await localDataClient.entities.Sale.filter(email ? { seller_email: email } : {});
    if (Array.isArray(sales)) return sales.length;
  } catch (err) {
    console.warn("[analytics] erro ao contar vendas", err);
  }

  try {
    const orders = await localDataClient.entities.Order.filter(email ? { seller_email: email } : {});
    if (Array.isArray(orders)) return orders.length;
  } catch (err) {
    console.warn("[analytics] entidade Order indisponível", err);
  }

  return null;
}

export async function getOpenTicketsCount(ownerEmail?: string | null): Promise<number | null> {
  const email = normalizeEmail(ownerEmail);
  try {
    const filter = email
      ? { $or: [{ buyer_email: email }, { seller_email: email }], status: "active" }
      : { status: "active" };
    const conversations = await localDataClient.entities.ChatConversation.filter(filter);
    if (Array.isArray(conversations)) return conversations.length;
  } catch (err) {
    console.warn("[analytics] tickets/conversas indisponíveis", err);
  }
  return null;
}
