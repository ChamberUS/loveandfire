import { useEffect, useMemo, useState } from "react";

const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%&*";

type MatrixColumn = {
  chars: string[];
  speed: number;
  delay: number;
};

export const CryptoMatrix = () => {
  const [columns, setColumns] = useState<MatrixColumn[]>([]);

  const columnCount = 30;
  const rowCount = 12;

  useEffect(() => {
    const generateColumns = () => {
      const newColumns: MatrixColumn[] = [];
      for (let i = 0; i < columnCount; i++) {
        const chars: string[] = [];
        for (let j = 0; j < rowCount; j++) {
          chars.push(characters[Math.floor(Math.random() * characters.length)]);
        }
        newColumns.push({
          chars,
          speed: 1 + Math.random() * 2,
          delay: Math.random() * 2,
        });
      }
      setColumns(newColumns);
    };

    generateColumns();

    const interval = setInterval(() => {
      setColumns((prev) =>
        prev.map((col) => ({
          ...col,
          chars: col.chars.map(() => characters[Math.floor(Math.random() * characters.length)]),
        })),
      );
    }, 150);

    return () => clearInterval(interval);
  }, []);

  const highlightIndices = useMemo(() => {
    const indices: Set<string> = new Set();
    for (let i = 0; i < 15; i++) {
      indices.add(`${Math.floor(Math.random() * columnCount)}-${Math.floor(Math.random() * rowCount)}`);
    }
    return indices;
  }, []);

  return (
    <div className="crypto-matrix pointer-events-none select-none">
      <div
        className="grid gap-0"
        style={{
          gridTemplateColumns: `repeat(${columnCount}, 1fr)`,
          opacity: 0.4,
        }}
      >
        {columns.map((column, colIndex) => (
          <div key={colIndex} className="flex flex-col">
            {column.chars.map((char, rowIndex) => {
              const isHighlighted = highlightIndices.has(`${colIndex}-${rowIndex}`);
              return (
                <span
                  key={rowIndex}
                  className="font-mono text-[10px] leading-tight transition-colors duration-300 matrix-char"
                  style={{
                    color: isHighlighted ? "hsl(var(--aios-gold))" : "hsl(var(--muted-foreground))",
                    opacity: isHighlighted ? 1 : 0.3 + Math.random() * 0.4,
                    animationDelay: `${column.delay}s`,
                  }}
                >
                  {char}
                </span>
              );
            })}
          </div>
        ))}
      </div>
    </div>
  );
};
