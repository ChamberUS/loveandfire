# Security Notes

## Credenciais sensiveis no repositorio
Foi identificado que `config/config.php` contem credenciais de banco em texto puro (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

## Recomendacoes urgentes
1. Mover credenciais para arquivo local ignorado por Git (ex.: `config/config.local.php`) e carregar esse arquivo no bootstrap.
2. Rotacionar a senha atual do banco (`DB_PASS`) no provedor.
3. Nunca commitar segredos:
   - `DB_PASS`
   - tokens de API
   - chaves privadas
   - `.env` com segredos
4. Manter `config/byx.php` fora do versionamento (ja coberto no `.gitignore`).

## Escopo atual
Estas notas nao alteram automaticamente a configuracao de banco para evitar quebra do app em runtime.
