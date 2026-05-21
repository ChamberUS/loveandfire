# Love & Fire PHP 8+

MVP inicial de app de relacionamentos hibrido com identidade Love & Fire: **Descoberta + Curtidas + Matches + Chat humano**.

Este pacote roda com **PHP 8+** e MySQL usando `mysqli`, sem Composer, sem Laravel e sem Node.js.

## Modulos incluidos

- Cadastro e login
- Perfil com bio, cidade, genero, preferencia e privacidade de DM
- Feed social aberto
- Comentarios e curtidas em posts
- DM livre a partir do feed ou explorar
- Limite diario de DMs livres sem match
- Swipe/Match
- Conversa de match criada automaticamente
- Chat por AJAX polling
- Bloqueio de usuario
- Denuncia rapida
- Estrutura de selo humano verificado
- Design visual moderno inspirado em app social/mobile

## Requisitos

- PHP 8.0 ou superior (recomendado: PHP 8.3+)
- Extensao `mysqli`
- Extensao `mbstring` (recomendada)
- MySQL 5.7+ ou MariaDB equivalente
- HTTPS em producao

## Instalacao

1. Envie a pasta para a hospedagem.
2. Crie um banco MySQL.
3. Importe o arquivo:

```sql
database/humanconnect_schema.sql
```

4. Edite o arquivo:

```txt
config/config.php
```

Altere:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'humanconnect_php53');
```

5. Abra no navegador:

```txt
/index.php
```

6. Crie pelo menos duas contas para testar match e chat.

## Como testar o Match

1. Crie usuario A.
2. Crie usuario B.
3. Entre com usuario A e curta usuario B em `Match`.
4. Saia e entre com usuario B.
5. Curta usuario A.
6. O sistema cria o match e abre conversa automaticamente.

## Como testar DM livre

1. Entre com um usuario.
2. Va em `Explorar` ou `Feed`.
3. Clique em `Mensagem` ou `Enviar DM`.
4. O sistema cria conversa `DM livre` se o usuario destino permitir.
5. O limite padrao e de 5 DMs livres por dia, configurado em `config/config.php`.

## Estrutura de pastas

```txt
api/                  Endpoints AJAX e acoes simples
assets/css/           Visual do app
assets/js/            Chat polling e interacoes
config/               Configuracao do banco e constantes
core/                 Banco, auth, regras e helpers
database/             SQL do banco
uploads/              Futuro upload de fotos/feed
index.php             Landing page
register.php          Cadastro
login.php             Login
dashboard.php         Explorar pessoas
swipe.php             Match engine
feed.php              Feed social
chat.php              Chat
profile_edit.php      Perfil e privacidade
```

## Proximos passos recomendados

1. Ativar upload seguro de fotos de perfil e feed.
2. Criar painel administrativo para denuncias e verificacao humana.
3. Criar pagina publica de perfil.
4. Melhorar busca por cidade, idade e interesses.
5. Implementar notificacoes internas.
6. Futuramente migrar para PHP 8.x + Laravel + WebSocket.

## Observacao de seguranca

Este MVP foi criado para atender o requisito de PHP 5.3. Antes de usar em producao real, revise seguranca, hospedagem, HTTPS, politica de privacidade, LGPD, moderacao e armazenamento de dados sensiveis.
