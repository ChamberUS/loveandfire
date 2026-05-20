import { useEffect, useState } from "react";
import { Menu, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export default function LandingHeader() {
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const links = [
    { label: "Marketplace", href: "#marketplace" },
    { label: "Como funciona", href: "#how" },
    { label: "Recursos", href: "#features" },
    { label: "Contato", href: "#contact" },
  ];

  return (
    <header
      className={[
        "fixed top-0 left-0 right-0 z-50 transition-all duration-300",
        scrolled ? "bg-[#070B0F]/85 backdrop-blur border-b border-white/10" : "bg-transparent",
      ].join(" ")}
    >
      <div className="mx-auto max-w-7xl px-6">
        <div className="flex items-center justify-between h-16">
          <div className="flex items-center gap-3">
            <button
              className="p-2 text-white/80 hover:text-white transition md:hidden"
              onClick={() => setOpen((v) => !v)}
              aria-label="Abrir menu"
            >
              {open ? <X size={20} /> : <Menu size={20} />}
            </button>

            <a href="#" className="flex items-center gap-2">
              <div className="h-8 w-8 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400" />
              <span className="font-semibold tracking-wide text-white">IAOS</span>
            </a>
          </div>

          <nav className="hidden md:flex items-center gap-6">
            {links.map((l) => (
              <a
                key={l.label}
                href={l.href}
                className="text-sm text-white/70 hover:text-white transition"
              >
                {l.label}
              </a>
            ))}
          </nav>

          <div className="hidden md:flex items-center gap-2">
            <Button
              variant="outline"
              className="border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
              onClick={() => navigate("/auth/login")}
            >
              Entrar
            </Button>
            <Button
              className="bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
              onClick={() => navigate("/auth/register")}
            >
              Criar conta
            </Button>
          </div>

          <div className="md:hidden">
            <Button
              size="sm"
              className="bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
              onClick={() => navigate("/marketplace")}
            >
              Marketplace
            </Button>
          </div>
        </div>
      </div>

      {open && (
        <div className="md:hidden bg-[#070B0F] border-t border-white/10">
          <div className="px-6 py-4 flex flex-col gap-3">
            {links.map((l) => (
              <a
                key={l.label}
                href={l.href}
                className="text-white/80 hover:text-white transition"
                onClick={() => setOpen(false)}
              >
                {l.label}
              </a>
            ))}
            <div className="pt-2 flex gap-2">
              <Button
                variant="outline"
                className="flex-1 border-white/15 bg-white/0 text-white/80 hover:bg-white/10"
                onClick={() => {
                  setOpen(false);
                  navigate("/auth/login");
                }}
              >
                Entrar
              </Button>
              <Button
                className="flex-1 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600"
                onClick={() => {
                  setOpen(false);
                  navigate("/auth/register");
                }}
              >
                Criar conta
              </Button>
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
