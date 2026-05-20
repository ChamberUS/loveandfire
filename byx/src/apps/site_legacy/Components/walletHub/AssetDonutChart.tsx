type AssetSlice = {
  label: string;
  value: number;
  color: string;
  note?: string;
};

function buildConicGradient(slices: AssetSlice[]) {
  const total = slices.reduce((sum, s) => sum + (Number.isFinite(s.value) ? s.value : 0), 0) || 1;
  let current = 0;
  const parts: string[] = [];
  slices.forEach((slice) => {
    const start = (current / total) * 360;
    const angle = ((slice.value || 0) / total) * 360;
    const end = start + angle;
    parts.push(`${slice.color} ${start}deg ${end}deg`);
    current += slice.value || 0;
  });
  return `conic-gradient(${parts.join(", ")})`;
}

export function AssetDonutChart({ slices, title }: { slices: AssetSlice[]; title: string }) {
  const gradient = buildConicGradient(slices);
  return (
    <div className="hub-card hub-card-hover p-4 h-full flex flex-col">
      <div className="flex items-center justify-between mb-3">
        <div>
          <p className="text-sm hub-muted">Distribuição de ativos</p>
          <h3 className="text-lg font-semibold text-white">{title}</h3>
        </div>
      </div>
      <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
        <div className="flex items-center justify-center">
          <div className="relative">
            <div
              className="w-44 h-44 rounded-full"
              style={{ background: gradient }}
            />
            <div className="absolute inset-6 bg-black/70 rounded-full border border-white/5 flex items-center justify-center">
              <div className="text-center">
                <p className="text-sm hub-muted">Carteira</p>
                <p className="text-lg font-semibold text-white">AIOS</p>
              </div>
            </div>
          </div>
        </div>
        <div className="space-y-2">
          {slices.map((slice) => (
            <div key={slice.label} className="flex items-center gap-3">
              <span className="w-3 h-3 rounded-full" style={{ background: slice.color }} />
              <div className="flex-1">
                <p className="text-sm text-white">{slice.label}</p>
                {slice.note && <p className="text-xs hub-muted">{slice.note}</p>}
              </div>
              <span className="text-sm font-semibold text-white">
                {slice.value.toLocaleString("pt-BR")}%
              </span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export function buildSlicesFromBalances(
  balances: Array<{ denom: string; amount: string }>,
  fallback: AssetSlice[],
): AssetSlice[] {
  const parsed = balances
    .map((b) => ({
      label: b.denom.toUpperCase(),
      value: Number(b.amount) || 0,
      denom: b.denom,
    }))
    .filter((b) => b.value > 0);

  const total = parsed.reduce((sum, b) => sum + b.value, 0);
  if (!parsed.length || total === 0) return fallback;

  return parsed.map((p, idx) => {
    const colors = ["#f5c249", "#7b5cff", "#38ef7d", "#00c6ff", "#ff7f50", "#9b8cff"];
    return {
      label: p.label,
      value: Math.round((p.value / total) * 100),
      color: colors[idx % colors.length],
    };
  });
}
