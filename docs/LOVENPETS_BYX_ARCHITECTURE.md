# LOVENPETS + BYX ARCHITECTURE (MVP V1)

## Visao Geral: Dois Mundos

O ecossistema foi separado em dois contextos de jogo para preservar a identidade real do usuario:

1. **LovenPets (social/romantico)**
- Linguagem: adotar, guardiao atual, charme, coracoes, presentes, disputa romantica.
- Foco: interacao social, paquera e dinamica simbolica de posse.
- Economia social sem misturar pontos afetivos com saldo financeiro real.

2. **LovenFire Arena (competitivo/economico)**
- Linguagem: BYX Coin, cards sociais, posse atual, ranking, historico, compra de blocos/cards.
- Foco: disputa de posse de cards e historico economico.
- A posse do card muda, a pessoa real nao muda.

## Regra dos IDs Imutaveis

- `users.id` e permanente e imutavel.
- `lf_pet_blocks.id` (card/bloco social) e permanente e imutavel.
- `lf_pet_blocks.usuario_id` (dono real do perfil) e permanente.
- Apenas `lf_pet_ownership.dono_atual_id` muda quando ha compra.

**Regra central:** no LovenFire nao se altera a pessoa, altera-se apenas a posse do bloco/card social dentro do jogo.

## Regra de Posse Atual

- O jogador comprador vira `dono_atual_id` do pet/card.
- A posse permanece com ele ate novo jogador comprar o mesmo bloco/card.
- Cada compra registra trilha auditavel em `lf_pet_transacoes`.

## Regra de BYX Coin

- Moeda oficial: **BYX Coin (BYX)**.
- No MVP, BYX e saldo interno em `lf_wallets`.
- Nao ha saque real, exchange real ou blockchain real nessa fase.
- Arquitetura preparada para integracao futura com blockchain.

## Cuidado Anti-inflacao (MVP)

Aumento progressivo por faixa no momento da compra:
- <= 100 BYX: +3%
- <= 1.000 BYX: +2%
- <= 10.000 BYX: +1.5%
- <= 100.000 BYX: +1%
- <= 1.000.000 BYX: +0.5%
- > 1.000.000 BYX: +0.25%

Distribuicao do valor de compra:
- 90% para vendedor (quando houver dono anterior).
- 3% para dono real do perfil (`usuario_original_id`).
- 5% taxa plataforma (registrada).
- 1% taxa reserva/queima simbolica (registrada).

## Tabelas Criadas

- `lf_wallets`
- `lf_wallet_transacoes`
- `lf_pet_blocks`
- `lf_pet_ownership`
- `lf_pet_transacoes`
- `lf_pet_config`
- `lf_pet_notificacoes`

## Fluxo de Compra (pet_buy.php)

1. Usuario autenticado envia POST com CSRF valido.
2. Sistema valida regras:
- nao comprar proprio perfil;
- nao comprar pet que ja possui.
3. Sistema calcula novo valor por faixa.
4. Sistema abre transacao SQL (`START TRANSACTION`).
5. Debita BYX do comprador.
6. Credita vendedor (se houver).
7. Credita bonus ao dono real do perfil.
8. Atualiza ownership e valor do bloco.
9. Registra `lf_pet_transacoes` + trilhas em `lf_wallet_transacoes`.
10. Gera notificacoes.
11. Finaliza com `COMMIT` (ou `ROLLBACK` em erro).

## Proximos Passos

1. Criar painel `wallet.php` para deposito/credito administrativo e extrato BYX.
2. Criar `arena.php` com regras economicas proprias e cards por bloco/temporada.
3. Adicionar protecao anti-sniping (cooldown e taxa dinamica por alta frequencia).
4. Criar jobs de consolidacao de ranking e metricas de inflacao.
5. Preparar adapter para futura ponte com blockchain real (sem quebrar modelo interno).
