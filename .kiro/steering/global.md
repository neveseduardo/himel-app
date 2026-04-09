# Regras Globais — Himel App

> Este arquivo define as regras globais do projeto. O agente DEVE seguir estas regras em toda interação.

## Visão do Produto

SaaS de controle financeiro pessoal focado em clareza de saldo, previsibilidade (projeções) e simplicidade. O conceito central baseia-se em movimentações vinculadas a contas e organizadas por períodos mensais.

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 13 (PHP 8.4+) |
| Frontend | Vue 3 (`<script setup lang="ts">`) |
| Comunicação | Inertia.js v3 (bridge única BE ↔ FE) |
| Database | MySQL 8.0 |
| Estilização | Tailwind CSS 4 + Shadcn/Vue |
| Qualidade | ESLint 9 + Laravel Boost + Pint |
| Rotas FE | Wayfinder (gerador de rotas tipadas) |
| Testes | PHPUnit 12 |
| Estado FE | Pinia |
| Validação FE | Vee-Validate + Zod |
| Notificações | vue-sonner |
| Ícones | lucide-vue-next |

## Idioma

- Mensagens de erro, validação e UI DEVEM ser em Português (pt-BR).
- Código (variáveis, classes, métodos) DEVE ser em Inglês.
- Documentação e comentários podem ser em Português.

## Identificadores (UUID v4)

- É PROIBIDO usar IDs incrementais (`int`). Todos os models DEVEM usar UUID v4.
- Foreign keys DEVEM seguir o padrão `{model}_uid` (ex: `user_uid`, `period_uid`).
- Toda Model DEVE definir:
  ```php
  protected $primaryKey = 'uid';
  public $incrementing = false;
  protected $keyType = 'string';
  ```
- DEVE usar a trait `HasUids` para geração automática de UUID.

## Checklist de Validação (Definition of Done)

Antes de entregar qualquer alteração, o agente DEVE executar e validar:

1. `vendor/bin/pint --dirty --format agent` — formatação PHP
2. `npm run lint` — sem erros ESLint
3. `npx vue-tsc --noEmit` — sem erros de tipo
4. `npm run build` — build bem-sucedido
5. `php artisan test --compact` — testes passando (filtrar pelo escopo alterado)

## Diretrizes Anti-Alucinação

- VERIFICAR compatibilidade com Tailwind CSS 4 (NÃO usar `tailwind.config.js` — v4 usa CSS).
- NUNCA ignorar um erro no backend. SEMPRE retornar resposta via Inertia com mensagem de erro tratada.
- VERIFICAR se componentes Shadcn/Vue referenciados existem no projeto antes de usá-los.
- VERIFICAR se rotas Wayfinder existem antes de importá-las. Rodar `php artisan wayfinder:generate` se necessário.
