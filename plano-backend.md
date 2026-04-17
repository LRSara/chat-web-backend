# Plano de Execução -- Chat Web Backend

> Autocontido. Sessão nova, sem histórico, executa do início ao fim.

## Contexto

Backend Laravel 13 consome PostgreSQL e broadcast via Laravel Reverb (WebSocket). Projeto já tem migrations, models, services, controllers e events configurados. API REST com 8 endpoints cobre: salas (CRUD + join), mensagens (envio + histórico paginado), uploads (imagem + áudio) e usuários online.

Frontend roda em `localhost:5173` (Vite) e consome a API em `localhost:8000`. WebSocket conecta em `localhost:8080` (Reverb).

## Escopo

**Dentro:** Backend completo -- criar/listar/entrar salas, enviar/receber mensagens (texto/imagem/áudio), histórico paginado, upload de arquivos, broadcast de eventos em tempo real.
**Fora:** Autenticação com contas de usuário, deploy, monitoramento.

## Decisões

| Decisão | Justificativa | Alternativa descartada | Por que não |
|---------|---------------|----------------------|-------------|
| Laravel Reverb | WebSocket nativo Laravel, protocolo Pusher compatível com Echo | Socket.IO | Incompatível com ecossistema Laravel |
| Services separados | SRP: RoomService, MessageService, UploadService independentes | Lógica no Controller | Controllers ficariam inchados |
| Nick temporário (sem auth) | Chat anônimo por sala, sem necessidade de cadastro | Auth com Sanctum/JWT | Complexidade desnecessária para chat casual |
| Hash de senha da sala | Segurança básica, senha nunca exposta | Texto puro | Vulnerabilidade |
| Upload local (storage/public) | Simplicidade para dev, symlink público | S3/cloud | Overhead para ambiente de desenvolvimento |
| ShouldBroadcastNow (não fila) | Mensagens em tempo real, sem latência de fila | ShouldBroadcast com queue | Latência indesejada para chat |

## Arquivos Para Ler

- `CLAUDE.md` -- schema do banco, endpoints, limites, regras de negócio
- `routes/api.php` -- endpoints REST definidos
- `routes/channels.php` -- canais WebSocket autorizados
- `app/Services/RoomService.php` -- lógica de salas e sessões
- `app/Services/MessageService.php` -- lógica de mensagens
- `app/Services/UploadService.php` -- validação e armazenamento de arquivos
- `app/Models/Room.php` -- modelo com relacionamentos
- `app/Models/Message.php` -- modelo de mensagem
- `app/Models/UserSession.php` -- modelo de sessão de usuário
- `app/Events/MessageSent.php` -- broadcast de mensagem
- `app/Events/UserJoined.php` -- broadcast de entrada
- `app/Events/UserLeft.php` -- broadcast de saída

## Cobertura

- API: RoomController, MessageController, UploadController
- Serviço: RoomService, MessageService, UploadService
- Modelo: Room, Message, UserSession
- Banco: 3 migrations (rooms, messages, user_sessions)
- WebSocket: 3 events (MessageSent, UserJoined, UserLeft) + canal room.{id}
- Testes: 38 testes (unitários + integração)

## Implementação

### Componente 1: RoomService

**Arquivo:** `app/Services/RoomService.php`
**Responsabilidades:**
- `createRoom`: cria sala com senha hasheada
- `listRooms`: lista com `withCount` de sessões online
- `joinRoom`: valida senha, verifica nick duplicado (409), cria sessão
- `leaveRoom`: marca `disconnected_at` na sessão

### Componente 2: MessageService

**Arquivo:** `app/Services/MessageService.php`
**Responsabilidades:**
- `store`: persiste mensagem, broadcast `MessageSent`
- `getHistory`: paginação (50 por vez, DESC por created_at)

### Componente 3: UploadService

**Arquivo:** `app/Services/UploadService.php`
**Responsabilidades:**
- `storeImage`: valida tipo MIME + tamanho (5MB), salva em uploads/images
- `storeAudio`: valida tipo MIME + tamanho (10MB), salva em uploads/audio
- `isValidAudioType`: validação tripla (MIME detectado, MIME cliente, extensão)

### Componente 4: Events WebSocket

**Arquivos:** `app/Events/MessageSent.php`, `UserJoined.php`, `UserLeft.php`
**Responsabilidades:**
- Broadcast no canal `room.{room_id}`
- Payload com dados mínimos necessários para o frontend

## Verificação Final

```bash
# Testes
php artisan test --compact
# Esperado: 38 passed (92 assertions)

# Pint (lint)
./vendor/bin/pint --test
# Esperado: {"result":"pass"}

# Verificação funcional
php artisan tinker --execute="echo App\Models\Room::count() . ' salas';"
```

## Critérios de Pronto

- [ ] Cada artefato: existe, conteúdo real, importado e usado
- [ ] Testes passando (38 testes)
- [ ] Lint limpo (Pint)
- [ ] Zero TODOs, placeholders ou stubs
- [ ] Migrations idempotentes (IF NOT EXISTS)
- [ ] Cascade deletes configurados (messages, user_sessions)
- [ ] Senhas hasheadas, nunca expostas

## Desvios

- Bug pontual ou dependência faltante → auto-fix (max 3 tentativas)
- Conflito com decisão já tomada → PARAR e reportar
- Tarefa maior que o esperado → completar o possível, documentar o resto
