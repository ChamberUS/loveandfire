export default function HowItWorksSection() {
  const steps = [
    { n: "01", t: "Crie uma conta", d: "Cadastre-se e acesse o painel." },
    { n: "02", t: "Entre no marketplace", d: "Navegue por lojas e produtos." },
    { n: "03", t: "Pague com BYX", d: "Pedidos e pagamentos com rastreio." },
    { n: "04", t: "Evolua com o ecossistema", d: "Cashback, POS, webhooks e integrações." },
  ];

  return (
    <section id="how" className="py-16 border-t border-white/10">
      <div className="mx-auto max-w-7xl px-6">
        <h2 className="text-3xl font-bold text-white">Como funciona</h2>
        <p className="text-white/60 mt-2 max-w-2xl">
          Um fluxo simples para o usuário, com estrutura pronta para virar produto real.
        </p>

        <div className="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {steps.map((s) => (
            <div
              key={s.n}
              className="bg-white/5 border border-white/10 rounded-2xl p-5"
            >
              <div className="text-white/40 text-sm tracking-widest">{s.n}</div>
              <div className="text-white font-semibold mt-2">{s.t}</div>
              <div className="text-white/60 text-sm mt-2">{s.d}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
