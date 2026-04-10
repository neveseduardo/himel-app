---
inclusion: auto
priority: 90
---

# Regras Globais — Himel App

> Regras globais obrigatórias em toda interação. Prioridade inferior apenas a `financial-rules.md`.

## Visão do Produto

SaaS de controle financeiro pessoal focado em clareza de saldo, previsibilidade (projeções) e simplicidade. Conceito central: movimentações vinculadas a contas e organizadas por períodos mensais.

## Stack Tecnológica

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend | Laravel | 13 (PHP 8.4+) |
| Frontend | Vue 3 | `<script setup lang="ts">` |
| Comunicação | Inertia.js | v3 |
| Database | MySQL | 8.0 |
| Estilização | Tailwind CSS + Shadcn/Vue | v4 |
| Qualidade | ESLint 9 + Pint + Laravel Boost | — |
| Rotas FE | Wayfinder | tipadas |
| Testes | PHPUnit | 12 |
| Estado FE | Pinia | — |
| Validação FE | Vee-Validate + Zod | — |
| Notificações | vue-sonner | — |
| Ícones | lucide-vue-next | — |

## Idioma

- Mensagens de erro, validação e UI DEVEM ser em Português (pt-BR).
- Código (variáveis, classes, métodos) DEVE ser em Inglês.
- Documentação e comentários podem ser em Português.

## Identificadores (UUID v4)

- É PROIBIDO usar IDs incrementais (`int`) como primary key de models financeiros.
- Todos os models DEVEM usar UUID v4 como primary key.
- Foreign keys DEVEM seguir o padrão `{model}_uid` (ex: `user_uid`, `period_uid`).
- Toda Model DEVE definir:
  ```php
  use HasUids;
  protected $primaryKey = 'uid';
  public $incrementing = false;
  protected $keyType = 'string';
  ```

## Checklist de Validação (Definition of Done)

Antes de entregar qualquer alteração, o agente DEVE executar e validar:

1. `vendor/bin/pint --dirty --format agent` — formatação PHP
2. `npx vue-tsc --noEmit 2>&1` — sem erros de tipo TypeScript
3. `npm run lint` — sem erros ESLint
4. `npm run build` — build bem-sucedido
5. `php artisan test --compact` — testes passando (filtrar pelo escopo alterado)

Os passos 2 e 3 DEVEM ser executados juntos como validação frontend obrigatória.

## Diretrizes Anti-Alucinação

- VERIFICAR compatibilidade com Tailwind CSS 4 (NÃO usar `tailwind.config.js` — v4 usa CSS).
- NUNCA ignorar um erro no backend. SEMPRE retornar resposta via Inertia com mensagem de erro tratada.
- VERIFICAR se componentes Shadcn/Vue referenciados existem no projeto antes de usá-los.
- VERIFICAR se rotas Wayfinder existem antes de importá-las. Rodar `php artisan wayfinder:generate` se necessário.
- NUNCA inventar nomes de tabelas, colunas ou rotas. Consultar `data-model.md` e `api.md`.

## Hierarquia de Prioridade dos Steering Files

1. `financial-rules.md` — Regras de negócio (prioridade máxima)
2. `global.md` — Regras globais (este arquivo)
3. `security.md` — Segurança e isolamento
4. `backend.md` — Padrões backend
5. `frontend.md` — Padrões frontend
6. `data-model.md` — Schema do banco
7. `api.md` — Padrões de endpoints
8. `validation.md` — Validação dupla
9. `testing.md` — Padrões de teste
10. `architecture.md` — Estrutura do projeto

Em caso de conflito, o arquivo de maior prioridade PREVALECE.
