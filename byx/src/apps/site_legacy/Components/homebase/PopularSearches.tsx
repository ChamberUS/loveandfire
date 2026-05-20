type PopularSearchesProps = {
  items: string[];
  onSelect: (term: string) => void;
};

export function PopularSearches({ items, onSelect }: PopularSearchesProps) {
  if (items.length === 0) return null;
  return (
    <section className="py-6">
      <div className="flex items-center justify-between mb-3">
        <h3 className="text-lg font-semibold text-foreground">Principais buscas</h3>
        <p className="text-sm text-muted-foreground">Aproveite o que outros estão pesquisando</p>
      </div>
      <div className="flex flex-wrap gap-2">
        {items.map((term) => (
          <button
            key={term}
            type="button"
            onClick={() => onSelect(term)}
            className="px-4 py-2 rounded-full bg-secondary/40 border border-border/20 text-sm text-muted-foreground hover:text-foreground hover:border-primary/30 transition-all"
          >
            {term}
          </button>
        ))}
      </div>
    </section>
  );
}
