# [Nome descritivo da mudança]

> Autocontido. Sessão nova, sem histórico, executa do início ao fim.

## Contexto

[Estado atual do sistema na área afetada. Por que essa mudança é necessária.
Incluir evidência concreta: dados, fluxo real, código que comprova o problema.
Não descrever -- demonstrar.]

## Escopo

**Dentro:** [o que este plano faz]
**Fora:** [o que este plano NÃO faz -- previne expansão]

## Decisões

[Escolhas já tomadas e alternativas descartadas. Cada decisão com justificativa
e razão de descarte da alternativa. Decisões negativas são tão importantes
quanto positivas.]

| Decisão | Justificativa | Alternativa descartada | Por que não |
|---------|---------------|----------------------|-------------|
| [usar X] | [razão concreta] | [usar Y] | [razão concreta] |

## Arquivos Para Ler

[Lista explícita. Ler TODOS antes de qualquer edição.]

- `caminho/arquivo` -- [por que ler: o que contém de relevante]

## Cobertura

[Identificar todas as camadas afetadas pela mudança. Backend, banco e WebSocket
devem estar integrados -- feature parcial não funciona.]

- API: [quais endpoints/controllers são afetados]
- Serviço: [quais Services são afetados]
- Modelo: [quais Models são afetados]
- Banco: [migrations, se houver]
- WebSocket: [events/canais, se houver]
- Testes: [quais cenários precisam ser cobertos]

## Implementação

[Organizar por componente, módulo ou item -- conforme a natureza do trabalho.
Cada bloco é independente quando possível. Usar APENAS APIs, métodos e
sintaxe da versão atual da stack -- zero deprecações, zero métodos velhos.]

### [Componente/Item 1]: [Nome]

**Arquivo:** `caminho/arquivo:linhas`
**Problema:** [o que está errado, com evidência]
**Risco:** [nenhum | baixo | médio -- com justificativa]

**Antes:**
```[linguagem]
// código atual real, com linhas do arquivo
```

**Depois:**
```[linguagem]
// código modificado, completo
```

**Verificação:**
```bash
[comando exato]
# Esperado: [output concreto]
```

---

### [Componente/Item 2]: [Nome]

[Mesmo padrão. Repetir quantas vezes necessário.]

---

## Verificação Final

```bash
# Lint/type-check
[comando exato do projeto]

# Testes
[comando exato do projeto]

# Verificação funcional
[comando ou query que prova que o objetivo foi atingido]
```

## Critérios de Pronto

- [ ] Cada artefato: existe, conteúdo real, importado e usado
- [ ] Testes passando
- [ ] Lint/type-check limpo
- [ ] Zero TODOs, placeholders ou stubs
- [ ] [critério específico desta mudança]

## Desvios

- Bug pontual ou dependência faltante → auto-fix (max 3 tentativas)
- Conflito com decisão já tomada → PARAR e reportar
- Tarefa maior que o esperado → completar o possível, documentar o resto
