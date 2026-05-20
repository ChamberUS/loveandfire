import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from "recharts";

type Asset = {
  name: string;
  value: number;
  color: string;
  percentage: number;
};

type AssetDonutChartProps = {
  assets: Asset[];
};

const CustomTooltip = ({ active, payload }: any) => {
  if (active && payload && payload.length) {
    const data = payload[0].payload;
    return (
      <div className="glass-card px-3 py-2">
        <p className="text-sm font-medium text-foreground">{data.name}</p>
        <p className="text-xs text-muted-foreground">{data.percentage}%</p>
      </div>
    );
  }
  return null;
};

export const AssetDonutChart = ({ assets }: AssetDonutChartProps) => {
  const total = assets.reduce((sum, asset) => sum + asset.value, 0);

  return (
    <div className="glass-card p-6 animate-fade-in animation-delay-200">
      <h3 className="text-lg font-semibold text-foreground mb-4">Distribuição de Ativos</h3>

      <div className="relative">
        <ResponsiveContainer width="100%" height={200}>
          <PieChart>
            <Pie
              data={assets}
              cx="50%"
              cy="50%"
              innerRadius={55}
              outerRadius={80}
              paddingAngle={4}
              dataKey="value"
              strokeWidth={0}
            >
              {assets.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={entry.color} className="transition-all duration-300 hover:opacity-80" />
              ))}
            </Pie>
            <Tooltip content={<CustomTooltip />} />
          </PieChart>
        </ResponsiveContainer>

        <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div className="text-center">
            <p className="text-2xl font-bold text-foreground">{assets.length}</p>
            <p className="text-xs text-muted-foreground">Ativos</p>
          </div>
        </div>
      </div>

      <div className="mt-4 space-y-2">
        {assets.map((asset, index) => (
          <div key={index} className="flex items-center justify-between p-2 rounded-lg hover:bg-muted/30 transition-colors">
            <div className="flex items-center gap-3">
              <div className="w-3 h-3 rounded-full" style={{ backgroundColor: asset.color }} />
              <span className="text-sm text-foreground">{asset.name}</span>
            </div>
            <span className="text-sm font-medium text-muted-foreground">
              {total ? ((asset.value / total) * 100).toFixed(1) : asset.percentage}%
            </span>
          </div>
        ))}
      </div>
    </div>
  );
};
