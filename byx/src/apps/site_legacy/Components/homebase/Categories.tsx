import type { LucideIcon } from "lucide-react";

export type CategoryCard = {
  icon: LucideIcon;
  label: string;
  onClick: () => void;
};

export function Categories({ categories }: { categories: CategoryCard[] }) {
  return (
    <section className="py-8">
      <h2 className="text-xl font-semibold text-foreground mb-6">Buscar por categorias</h2>
      <div className="grid grid-cols-3 md:grid-cols-6 gap-4">
        {categories.map((category) => (
          <button
            key={category.label}
            className="glass-card-hover rounded-xl p-6 text-center group cursor-pointer"
            type="button"
            onClick={category.onClick}
          >
            <div className="w-14 h-14 mx-auto mb-3 rounded-xl bg-secondary/40 flex items-center justify-center group-hover:bg-primary/20 transition-all duration-300">
              <category.icon className="w-7 h-7 text-muted-foreground group-hover:text-primary transition-colors duration-300" />
            </div>
            <p className="text-sm font-medium text-foreground">{category.label}</p>
          </button>
        ))}
      </div>
    </section>
  );
}
