# CLAUDE.md -- Chat Web Backend

Projeto: Chat em tempo real com salas protegidas por senha.

---

## Stack

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend | Laravel | 11 |
| WebSocket | Laravel Reverb | 1.x |
| Banco | PostgreSQL | 16+ |
| PHP Extensão | pdo_pgsql, pgsql | - |
| Testes | PHPUnit | 11 |

---

## Arquitetura do Projeto

Organização por domínio (não por tipo):

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          ← REST endpoints (RoomController, MessageController, UploadController)
│   │   └── Ws/           ← Handlers WebSocket (se necessário)
│   └── Requests/         ← Form Requests (CreateRoomRequest, JoinRoomRequest, etc.)
├── Models/               ← Room, Message, UserSession
├── Services/             ← RoomService, MessageService, UploadService
├── Broadcasting/         ← Channels Reverb
└── Events/               ← MessageSent, UserJoined, UserLeft

config/
├── reverb.php            ← Configuração do servidor WebSocket
└── broadcasting.php      ← Conexão reverb como padrão

routes/
├── api.php               ← REST: rooms, messages, uploads
├── channels.php          ← Autorização canais WebSocket
└── web.php               ← Health check

database/
├── migrations/           ← rooms, messages, user_sessions
└── seeders/              ← -
```

---

## Domínios

### Room (Salas)
- Criar sala (nome + senha hasheada)
- Listar salas (sem expor senha)
- Validar entrada (conferir senha)
- Gerenciar usuários online (nick único por sala)

### Chat (Mensagens)
- Enviar mensagem (texto, imagem, áudio)
- Histórico paginado (50 por vez)
- Broadcast via WebSocket
- Persistência completa

### Upload (Imagem e Áudio)
- Módulo independente
- Validar tipo, tamanho, duração
- Salvar em filesystem
- Retornar URL de referência

### Identity (Identidade)
- Nick único por sala
- UserSession vinculada a sala
- Entrada/saída com notificação

---

## Regras Primárias (Invioláveis)

### Jamais adivinhar, especular ou assumir sobre código não lido.

```
ANTES de propor qualquer alteração:
1. LER o arquivo/função/classe em questão
2. VERIFICAR tipos, assinaturas, dependências
3. CONFIRMAR que entendeu o contexto real
Se não leu → Não opina | Se não verificou → Não implementa | Se em dúvida → PERGUNTA
```

### Código Limpo (SEMPRE)

```
LIMPO        → Legível, autoexplicativo, sem ruído
ROBUSTO      → Trata edge cases, validações corretas
DRY          → Sem redundâncias (reutilizar antes de criar)
SIMPLES      → Sem over-engineering ou abstrações prematuras
MANUTENÍVEL  → Fácil de entender, fácil de modificar
ACENTUADO    → Português correto em strings/comentários/docs/UI
BEST-PRACTICE→ Segue padrões Laravel 11
```

Funções < 50 linhas, complexidade ciclomática < 10, early returns, imutabilidade. Organizar por domínio (room/, chat/, upload/, identity/), nunca por tipo genérico (controllers/, models/).

---

## Princípios Estruturais (SEMPRE)

- **SSOT:** uma única fonte de verdade para cada informação. Nunca duplicar dado, estado ou lógica.
- **SRP:** cada função, classe ou módulo faz UMA coisa.
- **DRY:** reutilizar antes de criar. Pesquisar equivalente existente é obrigatório antes de implementar novo.
- **SoC:** separar por domínio coeso. Lógica de negócio separada de HTTP/WebSocket.
- **Desacoplamento:** módulos se comunicam por interfaces claras. Serviço de mensagens NÃO sabe que WebSocket existe.
- **Design Proporcional:** complexidade proporcional ao problema.

---

## Convenções Laravel

| Elemento | Convenção | Exemplo |
|----------|-----------|---------|
| Model | singular, PascalCase | `Room`, `Message` |
| Migration | plural, snake_case | `create_rooms_table` |
| Controller | singular + Controller | `RoomController` |
| Service | singular + Service | `RoomService` |
| Route | plural, kebab-case | `/api/rooms`, `/api/rooms/{room}/messages` |
| Form Request | ação + Request | `CreateRoomRequest` |
| Event | passado, PascalCase | `MessageSent`, `UserJoined` |
| Broadcast Channel | modelo + ID | `room.{room}` |
| Validação | em Form Request, nunca no Controller | - |
| Hash de senha | `Hash::make()` / `Hash::check()` | - |
| Upload | `Storage::put()` + validação em Service | - |

---

## Padrões Específicos

### WebSocket (Reverb)
- `php artisan reverb:start` para iniciar servidor
- Canais autorizados em `routes/channels.php`
- Events implementam `ShouldBroadcast`
- Lógica de broadcast separada da lógica de serviço
- Reconnect automático no cliente

### Upload
- Serviço independente (`UploadService`)
- Valida: tipo MIME, tamanho máximo, duração (áudio)
- Retorna referência (URL ou path), nunca expõe filesystem
- Formatos imagem: jpg, png, gif, webp | Limite: 5MB
- Formatos áudio: webm | Limite: 2 minutos

### Persistência
- Mensagens: tipo (text/image/audio), conteúdo/referência, nick, room_id, timestamp
- Paginação: cursor-based ou offset, 50 mensagens por vez
- Room persiste sem usuários online
- UserSession: nick, room_id, connected_at, disconnected_at

---

## Idioma e Comunicação

Português do Brasil com acentuação correta. SEMPRE. Aplica-se a: respostas, comentários, docstrings, mensagens, labels, interfaces. Técnica, concisa, direta. Emojis proibidos. Sem elogios ou verbosidade.

---

## Sequência Pré-Execução

```
1. EXAMINAR → Ler arquivos, identificar padrões/utilitários, mapear dependências, verificar tipos
2. REUSO    → Já existe? Reutilizar. Posso estender? Resolver em 1 arquivo? Em dúvida → PERGUNTA
3. SINCRONIA → Backend endpoints definidos? Todos os arquivos afetados identificados?
```

---

## Boundaries

**Always**: reutilizar código existente, end-to-end completo (sem TODOs), remover código substituído, continuar até 100%.
**Ask**: mudar arquitetura/padrão, adicionar dependência externa, decisões de UX.
**Never**: assumir sem verificar, abstrações não solicitadas, modificar fora do escopo, código morto/comentado, hardcoded.

---

## Workflow

Single sprint: features completas, todas camadas integradas, production-ready em cada commit. Pesquisar/ler antes de implementar. Autonomia total até resolver.

### Fases: Explore > Plan > Gate > Implement > Review > Commit

- **Explore:** ler código, mapear dependências, identificar padrões.
- **Plan:** objetivo, abordagem, arquivos, riscos, cenários de teste.
- **Gate:** spec + arquivos + testes alinhados antes de implementar.
- **Implement:** seguir o plano. Se errado, parar e replanejar.
- **Review:** checklist de verificação + diff completo + regressões.

### Subagentes

Agente principal executa (contexto amplo). Subagentes investigam. Delegar execução apenas para itens genuinamente independentes, com: arquivos a ler, escopo fechado, regras de qualidade. Após retorno, auditar conflitos e código real.

### Rastreamento de Impacto

Rastrear quem consome o trecho modificado: quem chama, importa, depende do contrato. Expandir busca por side-effects antes de considerar a mudança segura.

### Planejamento

Lançar agentes em paralelo para investigar. Perguntar ao usuário sobre comportamentos não especificados. Sintetizar em `.md` autocontido seguindo o formato de `plano-execucao-template.md`. Implementação parte do `.md`, não de memória conversacional.

---

## Verificação (Crítico)

```
[ ] Mínimo de arquivos alterados?        [ ] Sem código parcial/morto?
[ ] Código existente reutilizado?         [ ] Apenas mudanças solicitadas?
[ ] End-to-end funcional?                 [ ] Acentuação correta em todo texto pt-BR?
[ ] API REST + WebSocket sincronizados?
```

---

## Commits

- Só commitar quando o usuário autorizar explicitamente.
- Conventional Commits (feat, fix, refactor, docs, test, chore). Linha única, 2-3 frases curtas.
- Trailer: `Assisted-by: Claude Code`. NUNCA `Co-authored-by`.
- UM commit = UMA mudança lógica.
- NUNCA `git push --force` em main. NUNCA `git add .`.
- Branches: `main`, `feat/descricao`, `fix/descricao`.

---

## Debugging

1. Classificar: problema de intenção, de spec ou de implementação? Só investigar código se for implementação.
2. Identificar causa raiz APENAS. Corrigir APENAS o que está quebrado. Solução mais simples possível.
3. NÃO refatorar código funcionando.
4. Se 3 tentativas falharam, escalar ao humano (problema é estrutural).

---

## Documentação e Rastreabilidade

- Ao criar/modificar endpoints: anotações OpenAPI/Swagger (grupo, título, descrição, auth, params, responses).
- Cada endpoint documentado com:
  - Método e URL
  - Descrição em pt-BR
  - Parâmetros (query, path, body)
  - Respostas por código (200, 201, 422, 403, 404, 409, 413, 500)
- Mudança técnica sem atualização da fonte da verdade (docs, tipos, schemas) é mudança incompleta.
- Fato externo instável: registrar URL, data de acesso e versão consultada.

---

## Condições de Falha

```
Suposição feita                    → FALHA
Especulação sem verificação        → FALHA
Implementação parcial              → FALHA
+3 arquivos quando 1 bastava       → FALHA
Duplicou código existente          → FALHA
Não pesquisou antes de criar       → FALHA
Afirmou completude sem evidência   → FALHA
Service acoplado ao WebSocket      → FALHA
```

---

## Variáveis de Ambiente

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chat_web
DB_USERNAME=sara

BROADCAST_CONNECTION=reverb
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

FILESYSTEM_DISK=local
```

---

## Schema do Banco

### rooms
| Coluna | Tipo | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| name | string(100) | unique |
| password | string | hashed |
| created_at | timestamp | |
| updated_at | timestamp | |

### messages
| Coluna | Tipo | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| room_id | unsignedBigInteger | FK → rooms, cascade delete |
| nick | string(50) | |
| type | enum | text, image, audio |
| content | text | texto da mensagem ou NULL |
| file_path | string(500) | NULL, caminho do arquivo (imagem/áudio) |
| created_at | timestamp | index composto (room_id, created_at) |

### user_sessions
| Coluna | Tipo | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| room_id | unsignedBigInteger | FK → rooms, cascade delete |
| nick | string(50) | unique index composto (room_id, nick) |
| connected_at | timestamp | |
| disconnected_at | timestamp | nullable |

### Relacionamentos
- Room hasMany Messages
- Room hasMany UserSessions
- Message belongsTo Room
- UserSession belongsTo Room

### Índices
- `messages`: (room_id, created_at DESC) para paginação
- `user_sessions`: (room_id, nick) UNIQUE para unicidade de nick
- `user_sessions`: (room_id, disconnected_at NULL) para contar online

---

## Limites e Regras de Negócio

| Regra | Valor |
|-------|-------|
| Usuários por sala | ilimitado |
| Limite imagem | 5MB |
| Formatos imagem | jpg, jpeg, png, gif, webp |
| Limite áudio | 2 minutos (120s) |
| Formatos áudio | webm, mp3, ogg |
| Paginação mensagens | 50 por vez, ordenado por created_at DESC |
| Retenção mensagens | permanente (nunca deletar) |
| Retenção sessões | manter histórico de conexões |
| Nick tamanho | 3-50 caracteres |
| Nome sala tamanho | 3-100 caracteres |

---

## API REST

Padrão de resposta JSON:

### Sucesso
```json
{
  "data": { ... },
  "message": "Mensagem descritiva"
}
```

### Lista (paginação)
```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "has_more": true
  }
}
```

### Erro
```json
{
  "message": "Descrição do erro",
  "errors": { ... }
}
```

### Endpoints

#### Salas
```
POST   /api/rooms              Criar sala
GET    /api/rooms              Listar salas
POST   /api/rooms/{id}/join    Entrar na sala
```

#### Mensagens
```
GET    /api/rooms/{id}/messages   Histórico paginado (?page=1&per_page=50)
```

#### Uploads
```
POST   /api/upload/image       Upload de imagem (multipart)
POST   /api/upload/audio       Upload de áudio (multipart)
```

### Códigos de Resposta

| Código | Situação |
|--------|----------|
| 200 | Sucesso |
| 201 | Recurso criado |
| 422 | Validação falhou (erros em `errors`) |
| 403 | Senha incorreta |
| 404 | Sala não encontrada |
| 409 | Nick já em uso na sala |
| 413 | Arquivo excede limite |
| 500 | Erro interno |

---

## Eventos WebSocket

Canal: `room.{room_id}`

### MessageSent
```json
{
  "type": "text|image|audio",
  "nick": "usuario",
  "content": "texto ou null",
  "file_url": "/uploads/... ou null",
  "created_at": "2026-01-01T12:00:00Z"
}
```

### UserJoined
```json
{
  "nick": "usuario",
  "users_online": ["nick1", "nick2"]
}
```

### UserLeft
```json
{
  "nick": "usuario",
  "users_online": ["nick1"]
}
```

---

## Estratégia de Testes

### Unitários (Services)
- `RoomService::createRoom` — senha hasheada, nome único
- `RoomService::joinRoom` — senha correta entra, incorreta rejeita (403)
- `RoomService::joinRoom` — nick duplicado na mesma sala rejeita (409)
- `RoomService::joinRoom` — mesmo nick em sala diferente aceita
- `MessageService::store` — persiste mensagem com tipo correto
- `MessageService::getHistory` — retorna paginado, ordem correta
- `UploadService::storeImage` — valida tipo, tamanho, salva arquivo
- `UploadService::storeAudio` — valida duração, formato

### Integração (HTTP)
- `POST /api/rooms` — cria sala, retorna 201
- `GET /api/rooms` — lista sem expor senha
- `POST /api/rooms/{id}/join` — 200 com senha correta, 403 com errada, 409 com nick duplicado
- `GET /api/rooms/{id}/messages` — retorna histórico paginado
- `POST /api/upload/image` — 201 com arquivo válido, 422 com inválido
- `POST /api/upload/audio` — 201 com arquivo válido, 422 com inválido

### Regressão (E2E)
```
Criar sala → Entrar → Enviar texto → Enviar imagem → Enviar áudio →
Sair → Entrar novamente → Histórico íntegro (3 mensagens, tipos corretos, ordem)
```

---

## Comandos Úteis

```bash
php artisan serve                    # API HTTP
php artisan reverb:start             # Servidor WebSocket
php artisan migrate                  # Rodar migrations
php artisan test                     # Rodar testes
php artisan tinker                   # REPL
```
