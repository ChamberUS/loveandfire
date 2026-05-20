export default function LandingFooter() {
  return (
    <footer className="border-t border-white/10 py-10">
      <div className="mx-auto max-w-7xl px-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
          <div className="text-white font-semibold">IAOS</div>
          <div className="text-white/50 text-sm">Branding UX • BYX on-chain</div>
        </div>
        <div className="text-white/40 text-sm">
          © {new Date().getFullYear()} Buynnex • BYX
        </div>
      </div>
    </footer>
  );
}
