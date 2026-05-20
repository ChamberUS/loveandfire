# Regras de UI — IAOS

1. **CryptoBackground global**  
   - Renderizar uma única vez via `src/apps/site_legacy/Layout.jsx`.  
   - Não repetir `CryptoBackground` em páginas ou shells individuais (wallet, marketplace, mystore, home etc.).

2. **Dark mode + glass**  
   - Usar tokens/utis escopados (ex.: `iaos-mystore`, `mp-glass-card`) para superfícies escuras com `backdrop-blur`, borda sutil e texto claro.  
   - Proibido `bg-white`/`text-gray-*` hardcoded fora de escopos controlados.

3. **Tipografia resiliente**  
   - Aplicar clamp/break-words em títulos e descrições longas (`*-clamp-*`, `*-break`), evitando overflow em cards/listas.

4. **Backgrounds não duplicados**  
   - Evitar múltiplas camadas de fundo animado; se precisar de variação local, use overlays/glows, não re-renderize o canvas global.

5. **Acessibilidade de movimento**  
   - `CryptoBackground` respeita `prefers-reduced-motion` e pausa animação quando `document.hidden`. Não introduzir efeitos que ignorem essas flags.
