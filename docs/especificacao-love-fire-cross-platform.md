# Love & Fire - Especificacao Tecnica e de Produto Cross-Platform

## 1. Visao do Produto

Love & Fire e uma plataforma de relacionamentos e comunidade focada em conexoes 100% humanas. O produto combina tres mecanismos principais:

- Salas publicas de bate-papo: porta de entrada, inclusive para visitantes sem cadastro.
- Swipe e match: descoberta privada e direta para usuarios registrados.
- Feed aberto: camada comunitaria para interacao livre, descoberta social e inicio de conversas.

O principio central do produto e simples: nenhuma IA ou bot pode se passar por pessoa. Automacoes podem existir apenas para seguranca, moderacao, suporte, ranqueamento e protecao, sempre sem simular identidade humana em conversas.

## 2. Objetivos de Produto

- Converter visitantes anonimos em usuarios registrados a partir das salas publicas.
- Reduzir friccao inicial: convidado entra rapido, escolhe nickname temporario e conversa.
- Liberar recursos de maior valor apenas apos cadastro: DM, salvar contatos, match, feed completo e perfil persistente.
- Criar confianca com verificacao humana, bloqueio rapido, denuncia simples e anti-spam nas DMs.
- Entregar uma experiencia premium, limpa e emocionalmente acolhedora no desktop, web mobile e app nativo.

## 3. Stack Recomendada

### Backend

- PHP 8.3+ com Laravel.
- API RESTful como contrato principal para Web e Mobile.
- GraphQL opcional para telas agregadas com muita composicao, como home, perfil publico e feed personalizado.
- Laravel Sanctum ou Passport para autenticacao API.
- Laravel Reverb para WebSocket nativo no ecossistema Laravel.
- Jobs com Laravel Queues.
- Redis para sessoes, rate limits, filas leves, presenca online e cache.
- PostgreSQL como banco preferencial pela robustez em queries, JSONB, indices parciais e escalabilidade. MySQL 8 tambem e aceitavel.
- Object storage para midia: S3, Cloudflare R2 ou equivalente.

### Frontend Web

- React.js com TypeScript, Vite ou Next.js em modo SPA/hibrido.
- Alternativa: Vue 3 + TypeScript.
- State/data: TanStack Query para cache da API, Zustand/Pinia para estado local.
- UI: design system proprio com tokens compartilhados.
- Animacoes: Framer Motion, Motion One ou VueUse Motion.

### Mobile

- React Native com Expo ou Flutter.
- Recomendacao pratica: React + React Native para reaproveitar logica, contratos TypeScript, tokens e parte da cultura tecnica.
- Push notifications: Firebase Cloud Messaging e APNs.

### Tempo Real

- WebSockets para salas, DMs, digitando, presenca online e notificacoes.
- Eventos persistidos no banco para mensagens; Redis/Reverb apenas distribui eventos.
- Fallback com polling curto somente para ambientes sem WebSocket.

## 4. Arquitetura de Alto Nivel

```txt
Web SPA / Desktop
        |
Mobile iOS / Android
        |
        v
API Gateway / Laravel API
        |
        +-- Auth & Identity Service
        +-- Guest Chat Service
        +-- Match Service
        +-- Feed Service
        +-- Messaging Service
        +-- Moderation & Safety Service
        +-- Notification Service
        +-- Media Service
        |
        +-- PostgreSQL/MySQL
        +-- Redis
        +-- Object Storage
        +-- WebSocket Server
```

### Principios Arquiteturais

- API unica para todos os clientes.
- Separacao clara entre visitante convidado e usuario registrado.
- Mensagens sempre persistidas antes de emitir eventos em tempo real.
- Ids publicos em ULID/UUID para evitar enumeracao simples.
- Rate limiting por usuario, guest session, IP hash e device hash.
- Todos os fluxos sensiveis geram auditoria: login, verificacao, bloqueio, denuncia, envio massivo, criacao de DM.

## 5. Modelo de Dados

Tipos sugeridos:

- `uuid` ou `ulid` para ids publicos.
- `bigint` auto increment pode existir internamente, mas a API deve expor `public_id`.
- Campos `created_at`, `updated_at`, `deleted_at` onde fizer sentido.

### Usuarios e Identidade

#### users

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid | Id principal |
| email | varchar unique nullable | Visitantes nao possuem email |
| phone | varchar unique nullable | Opcional |
| password_hash | varchar nullable | Obrigatorio para login classico |
| status | enum | active, suspended, banned, deleted |
| role | enum | user, moderator, admin |
| email_verified_at | timestamp nullable | Verificacao de email |
| last_seen_at | timestamp nullable | Presenca |
| created_at | timestamp |  |
| updated_at | timestamp |  |

#### user_profiles

| Campo | Tipo | Observacao |
| --- | --- | --- |
| user_id | uuid pk/fk |  |
| display_name | varchar | Nome exibido |
| birth_date | date | Usado para idade e maioridade |
| gender | varchar | Identidade de genero |
| orientation | varchar | Orientacao declarada |
| looking_for | json/jsonb | Preferencias |
| city | varchar |  |
| region | varchar | Estado |
| country | varchar |  |
| bio | text |  |
| avatar_media_id | uuid nullable | Foto principal |
| interests | json/jsonb | Tags |
| visibility | enum | public, registered_only, hidden |

#### identity_verifications

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| user_id | uuid fk |  |
| status | enum | pending, approved, rejected, expired |
| selfie_media_id | uuid | Selfie ou video curto |
| document_media_id | uuid nullable | Opcional, conforme politica |
| reviewed_by | uuid nullable | Moderador |
| rejection_reason | varchar nullable |  |
| submitted_at | timestamp |  |
| reviewed_at | timestamp nullable |  |

#### guest_sessions

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| nickname | varchar | Nome temporario |
| device_hash | varchar | Hash nao reversivel |
| ip_hash | varchar | Hash com salt rotativo |
| user_agent_hash | varchar | Auxiliar anti-abuso |
| converted_user_id | uuid nullable | Ligacao apos cadastro |
| expires_at | timestamp | Expiracao da sessao |
| last_seen_at | timestamp | Presenca |
| created_at | timestamp |  |

### Salas Publicas

#### room_categories

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| slug | varchar unique | hetero, gays, lesbicas, bi, amizade, etc |
| name | varchar | Nome publico |
| description | text |  |
| visual_theme | json/jsonb | Cores, icones, gradientes, microelementos |
| min_age | int | Normalmente 18 |
| is_active | boolean |  |

#### chat_rooms

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| category_id | uuid fk |  |
| slug | varchar unique |  |
| name | varchar |  |
| audience | enum | public, registered_only, premium |
| max_participants | int |  |
| status | enum | open, locked, archived |
| created_at | timestamp |  |

#### room_participants

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| room_id | uuid fk |  |
| user_id | uuid nullable | Preenchido para registrado |
| guest_session_id | uuid nullable | Preenchido para convidado |
| display_name_snapshot | varchar | Nome exibido no momento |
| role | enum | guest, member, moderator |
| joined_at | timestamp |  |
| left_at | timestamp nullable |  |
| muted_until | timestamp nullable | Anti-spam/moderacao |

#### room_messages

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| room_id | uuid fk |  |
| sender_user_id | uuid nullable |  |
| sender_guest_session_id | uuid nullable |  |
| body | text |  |
| media_id | uuid nullable | Futuro |
| status | enum | visible, hidden, deleted, flagged |
| moderation_score | decimal nullable | Risco calculado |
| created_at | timestamp |  |

### Match e Swipe

#### swipes

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| swiper_user_id | uuid fk | Quem avaliou |
| target_user_id | uuid fk | Quem foi avaliado |
| action | enum | like, pass, super_like |
| source | enum | swipe, profile, room, feed |
| created_at | timestamp |  |

Indice unico: `(swiper_user_id, target_user_id)`.

#### matches

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| user_one_id | uuid fk | Menor/ordenado para unicidade |
| user_two_id | uuid fk |  |
| status | enum | active, unmatched, blocked |
| matched_at | timestamp |  |
| closed_at | timestamp nullable |  |

Indice unico: `(user_one_id, user_two_id)`.

### Feed e Comunidade

#### feed_posts

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| user_id | uuid fk | Apenas registrados |
| body | text |  |
| visibility | enum | public, registered, matches |
| status | enum | published, hidden, deleted, flagged |
| created_at | timestamp |  |

#### post_comments

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| post_id | uuid fk |  |
| user_id | uuid fk |  |
| body | text |  |
| status | enum | visible, hidden, deleted |
| created_at | timestamp |  |

#### post_reactions

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| post_id | uuid fk |  |
| user_id | uuid fk |  |
| reaction | varchar | like, fire, heart |
| created_at | timestamp |  |

Indice unico: `(post_id, user_id, reaction)`.

### Conversas Privadas e DMs

#### dm_requests

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| requester_user_id | uuid fk | Quem pediu DM |
| target_user_id | uuid fk | Quem recebe |
| source_type | enum | room, feed, profile, match |
| source_id | uuid nullable | Id da sala/post/perfil |
| status | enum | pending, accepted, declined, expired, blocked |
| message_preview | varchar | Primeira mensagem curta |
| created_at | timestamp |  |
| responded_at | timestamp nullable |  |

#### conversations

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| type | enum | match, accepted_dm, support |
| match_id | uuid nullable |  |
| dm_request_id | uuid nullable |  |
| status | enum | active, blocked, closed |
| created_at | timestamp |  |
| updated_at | timestamp | Usado para ordenacao |

#### conversation_participants

| Campo | Tipo | Observacao |
| --- | --- | --- |
| conversation_id | uuid fk |  |
| user_id | uuid fk |  |
| last_read_message_id | uuid nullable |  |
| muted_until | timestamp nullable |  |
| archived_at | timestamp nullable |  |

#### messages

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| conversation_id | uuid fk |  |
| sender_user_id | uuid fk |  |
| body | text |  |
| media_id | uuid nullable |  |
| status | enum | sent, delivered, read, deleted, flagged |
| created_at | timestamp |  |

### Seguranca, Moderacao e Anti-Abuso

#### blocks

| Campo | Tipo | Observacao |
| --- | --- | --- |
| blocker_user_id | uuid fk |  |
| blocked_user_id | uuid fk |  |
| reason | varchar nullable |  |
| created_at | timestamp |  |

#### reports

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| reporter_user_id | uuid nullable |  |
| reporter_guest_session_id | uuid nullable |  |
| reported_user_id | uuid nullable |  |
| reported_guest_session_id | uuid nullable |  |
| target_type | enum | user, guest, room_message, message, post, comment |
| target_id | uuid |  |
| reason | varchar | spam, harassment, fake, sexual_content, scam |
| details | text nullable |  |
| status | enum | open, reviewing, resolved, dismissed |
| created_at | timestamp |  |

#### moderation_actions

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| moderator_id | uuid fk |  |
| target_type | varchar |  |
| target_id | uuid |  |
| action | enum | warn, mute, hide, ban, approve_verification, reject_verification |
| reason | text |  |
| created_at | timestamp |  |

#### rate_limit_events

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| actor_type | enum | guest, user, ip, device |
| actor_hash | varchar | Nao guardar IP puro |
| action | varchar | send_room_message, request_dm, login_attempt |
| count | int | Janela agregada |
| window_start | timestamp |  |
| window_end | timestamp |  |

### Midia e Dispositivos

#### media

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| owner_user_id | uuid nullable |  |
| disk | varchar | s3, r2, local |
| path | varchar |  |
| mime_type | varchar |  |
| size_bytes | bigint |  |
| purpose | enum | avatar, post, verification, chat |
| status | enum | uploaded, processing, approved, rejected |
| created_at | timestamp |  |

#### user_devices

| Campo | Tipo | Observacao |
| --- | --- | --- |
| id | uuid |  |
| user_id | uuid fk |  |
| platform | enum | web, ios, android, desktop |
| push_token | varchar nullable |  |
| device_hash | varchar |  |
| last_seen_at | timestamp |  |

## 6. Arquitetura da API

### Padroes Gerais

- Base URL: `/api/v1`.
- Autenticacao:
  - Visitantes: `Guest-Session-Token`.
  - Registrados: Bearer token.
- Toda resposta deve incluir `request_id`.
- Erros padronizados:

```json
{
  "error": {
    "code": "DM_REGISTRATION_REQUIRED",
    "message": "Crie uma conta para enviar mensagem direta.",
    "action": "open_quick_signup"
  }
}
```

### Endpoints Principais

#### Guest Mode

- `POST /guest-sessions`
  - Cria sessao convidada com nickname.
- `GET /room-categories`
  - Lista categorias publicas com tema visual.
- `GET /rooms?category=hetero`
  - Lista salas.
- `POST /rooms/{roomId}/join`
  - Entra na sala como guest ou usuario.
- `GET /rooms/{roomId}/messages`
  - Historico paginado.
- `POST /rooms/{roomId}/messages`
  - Envia mensagem publica.
- `POST /rooms/{roomId}/participants/{participantId}/dm-intent`
  - Visitante tentou DM. Retorna erro/acao de cadastro rapido.

#### Autenticacao e Cadastro

- `POST /auth/register`
- `POST /auth/quick-register`
  - Converte guest em usuario preservando nickname, sala e intencao de DM.
- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/refresh`
- `POST /identity-verifications`
  - Envia selfie/verificacao.
- `GET /me`
- `PATCH /me/profile`

#### Swipe e Match

- `GET /discover/swipe-stack`
  - Retorna lote de perfis.
- `POST /swipes`
  - Body: `target_user_id`, `action`.
- `GET /matches`
- `POST /matches/{matchId}/unmatch`

#### Feed

- `GET /feed`
- `POST /feed/posts`
- `POST /feed/posts/{postId}/comments`
- `POST /feed/posts/{postId}/reactions`
- `DELETE /feed/posts/{postId}`

#### DMs e Conversas

- `POST /dm-requests`
  - Para DM livre fora de match.
- `POST /dm-requests/{id}/accept`
- `POST /dm-requests/{id}/decline`
- `GET /conversations`
- `GET /conversations/{id}/messages`
- `POST /conversations/{id}/messages`
- `POST /conversations/{id}/read`

#### Seguranca

- `POST /blocks`
- `DELETE /blocks/{userId}`
- `POST /reports`
- `GET /safety/preferences`
- `PATCH /safety/preferences`

### WebSocket Channels

```txt
presence.room.{roomId}
private.conversation.{conversationId}
private.user.{userId}.notifications
presence.feed.global
```

Eventos:

- `room.message.created`
- `room.participant.joined`
- `room.participant.left`
- `conversation.message.created`
- `conversation.typing.started`
- `dm_request.created`
- `dm_request.accepted`
- `match.created`
- `user.presence.updated`

## 7. Regras de Negocio

### Visitantes

- Podem criar nickname temporario.
- Podem entrar em salas publicas.
- Podem conversar no grupo dentro de limites de frequencia.
- Nao podem enviar DM.
- Nao podem salvar contato.
- Nao podem participar do feed.
- Nao podem usar swipe.
- Sessao expira automaticamente.
- Conversao para conta deve preservar:
  - nickname escolhido;
  - sala de origem;
  - pessoa alvo da tentativa de DM;
  - mensagem inicial, se o visitante escreveu uma.

### Gatilhos de Conversao

Abrir modal de cadastro rapido quando o visitante tentar:

- Enviar DM.
- Salvar contato.
- Curtir perfil de alguem na sala.
- Seguir pessoa.
- Enviar imagem/audio.
- Entrar em sala exclusiva para registrados.

O modal deve ser curto:

- Nome exibido.
- Email ou telefone.
- Data de nascimento.
- Senha.
- Aceite dos termos.

Apos cadastro:

- Criar `user`.
- Criar `user_profile`.
- Vincular `guest_sessions.converted_user_id`.
- Reexecutar a acao que disparou o modal, quando seguro.

### Usuarios Registrados

- Podem usar swipe.
- Podem postar no feed.
- Podem comentar e reagir.
- Podem pedir DM livre.
- Podem aceitar/recusar DMs.
- Podem bloquear e denunciar com um clique.
- Recebem destaque de confianca apos verificacao por selfie.

### DMs Livres

- DM fora de match vira `dm_request`.
- A conversa so vira `conversation` ativa apos aceite.
- Antes do aceite, apenas uma mensagem curta pode ser enviada.
- Limites:
  - limite diario por usuario;
  - limite por alvo;
  - limite por sala de origem;
  - bloqueio automatico por repeticao de mensagens iguais.

### Anti-Spam e Assedio

- Bloqueio interrompe conversas, DMs pendentes, match e visibilidade reciproca.
- Denuncia cria evidencia com snapshots de mensagens.
- Usuario denunciado muitas vezes entra em revisao.
- Convidados com abuso devem ser bloqueados por device hash + IP hash + comportamento.
- Mensagens podem ser ocultadas preventivamente quando violarem regras claras.

### Garantia 100% Humano

- Proibido bot conversacional se passando por usuario.
- Contas suspeitas podem ser limitadas ate verificacao.
- Selo humano deve exigir selfie/video curto.
- Moderacao automatizada pode classificar risco, mas nao deve criar conversas humanas falsas.
- Perfis devem sinalizar claramente quando sao oficiais, suporte ou moderacao.

## 8. User Flow Principal

### Jornada: Visitante para Usuario Completo

1. Visitante acessa landing page.
2. Escolhe uma categoria de sala: Hetero, Gays, Lesbicas, Bi, Amizades, etc.
3. Informa nickname temporario.
4. Entra na sala publica.
5. Conversa com o grupo e ve participantes online.
6. Clica em "Enviar DM" em uma pessoa interessante.
7. Sistema exibe modal de cadastro rapido.
8. Visitante cria conta.
9. Sistema converte guest em usuario.
10. Sistema cria `dm_request` para a pessoa alvo.
11. Pessoa alvo aceita ou recusa.
12. Se aceitar, conversa privada e aberta.
13. Usuario completa perfil, envia selfie de verificacao e ganha mais confianca.
14. Usuario passa a usar Swipe, Feed e DMs com limites maiores.

### Jornada: Match

1. Usuario abre Descobrir/Swipe.
2. API retorna lote de perfis compativeis.
3. Usuario aprova ou nega.
4. Se houver curtida reciproca, cria match.
5. Cria conversa privada do tipo `match`.
6. Ambos recebem notificacao em tempo real.

### Jornada: Feed

1. Usuario registrado abre feed.
2. Ve posts globais ou segmentados por interesse.
3. Reage, comenta ou abre perfil.
4. Pode solicitar DM a partir do post.
5. Se nao houver match, cria `dm_request` com aceite obrigatorio.

## 9. UX/UI - Direcao de Experiencia

### Principios

- Premium, limpo e emocional, com acabamento inspirado no ecossistema Apple.
- Nenhuma tela deve parecer sistema administrativo.
- A interface deve explicar pouco e demonstrar muito.
- Conversao deve ser contextual: pedir cadastro no momento de desejo, nao antes.
- Microinteracoes leves: entrada de mensagem, match, aceite de DM, troca de sala.

### Identidade Visual Dinamica

Cada categoria de sala pode ter `visual_theme`:

```json
{
  "primary": "#ff4f6d",
  "secondary": "#ff9b54",
  "background": "peach-pink",
  "icon": "heart-fire",
  "ambient": "soft-sparkles"
}
```

Exemplos:

- Sala Hetero: coral, dourado suave, iconografia de coracao/chama.
- Sala Gay: gradiente arco-iris sutil, nunca caricato.
- Sala Lesbica: rosa, vinho e laranja suave.
- Sala Bi: magenta, roxo e azul com baixa saturacao.
- Amizades: azul claro e verde suave.

### Layout Desktop sem App Esticado

Desktop nao deve ser um celular gigante. Recomenda-se um shell de tres colunas:

```txt
+-------------------------------------------------------------+
| Topbar: logo, busca global, perfil, notificacoes            |
+------------------+--------------------------+---------------+
| Navegacao        | Conteudo principal       | Contexto      |
| - Salas          | Chat / Feed / Swipe      | Participantes |
| - Descobrir      |                          | Perfil breve  |
| - Feed           |                          | Conversoes    |
+------------------+--------------------------+---------------+
```

#### Salas no Desktop

- Coluna esquerda: categorias e lista de salas.
- Centro: chat da sala.
- Direita: participantes online, perfil rapido e CTA de cadastro.
- Visitante ve CTA fixo discreto: "Salve contatos e envie DMs criando sua conta".

#### Swipe no Desktop

- Centro: card principal com foto grande.
- Esquerda: filtros e criterios.
- Direita: preview de perfil, interesses e seguranca.
- Atalhos de teclado: aprovar, negar, abrir perfil.

#### Feed no Desktop

- Centro: timeline.
- Esquerda: navegacao por interesses/salas.
- Direita: tendencias, matches recentes, sugestoes seguras.

### Mobile

- Navegacao inferior persistente.
- Telas principais em abas:
  - Salas
  - Descobrir
  - Curtidas/Feed
  - Mensagens
  - Perfil
- Chat ocupa tela inteira.
- Participantes ficam em bottom sheet.
- Cadastro rapido em modal/bottom sheet.

## 10. Componentes UI Sugeridos

### Componentes Globais

- `AppShell`
- `TopBar`
- `BottomTabBar`
- `SideNavigation`
- `AuthQuickModal`
- `SafetyActionSheet`
- `ProfilePreviewCard`
- `HumanVerifiedBadge`
- `ContextThemeProvider`

### Salas

- `RoomCategoryPill`
- `RoomList`
- `RoomChatPanel`
- `RoomMessageBubble`
- `ParticipantRail`
- `GuestNicknameGate`
- `GuestConversionPrompt`

### Match

- `SwipeDeck`
- `SwipeProfileCard`
- `MatchCelebration`
- `InterestTags`
- `CompatibilityHints`

### Feed

- `PostComposer`
- `FeedCard`
- `ReactionBar`
- `CommentThread`
- `DMRequestCTA`

### Mensagens

- `ConversationList`
- `DMRequestInbox`
- `ChatHeader`
- `MessageBubble`
- `MessageComposer`
- `TypingIndicator`

## 11. Conversao e Metricas

Eventos essenciais:

- `guest_session_created`
- `guest_joined_room`
- `guest_sent_room_message`
- `guest_clicked_dm`
- `quick_signup_opened`
- `quick_signup_completed`
- `dm_request_created`
- `dm_request_accepted`
- `match_created`
- `feed_post_created`
- `identity_verification_submitted`
- `block_created`
- `report_created`

Funis:

- Visitante -> sala -> mensagem enviada -> tentativa de DM -> cadastro.
- Cadastro -> perfil completo -> verificacao -> primeiro match.
- Feed view -> comentario/reacao -> DM request -> conversa aceita.

## 12. Roadmap Sugerido

### Fase 1 - MVP Moderno

- API Laravel.
- Cadastro/login.
- Guest sessions.
- Salas publicas com WebSocket.
- Modal de cadastro rapido.
- DMs com aceite.
- Bloqueio e denuncia.

### Fase 2 - Relacionamento Completo

- Swipe e match.
- Feed aberto.
- Notificacoes push.
- Verificacao por selfie.
- Moderacao operacional.

### Fase 3 - Escala e Premium

- Ranking de salas.
- Recomendacao por interesses.
- Recursos premium opcionais.
- Painel completo de moderacao.
- Observabilidade, BI e experimentos A/B.

## 13. Riscos e Decisoes Importantes

- PHP 5.3 nao e adequado para o futuro do produto. Deve permanecer apenas como MVP legado.
- A arquitetura nova deve nascer API-first.
- Moderacao e seguranca nao podem ser deixadas para depois em um produto de relacionamento.
- Visitantes aumentam conversao, mas elevam risco de abuso; rate limiting e auditoria sao obrigatorios.
- A promessa "100% humano" exige politica clara, verificacao gradual e transparencia.

