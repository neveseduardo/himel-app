---
inclusion: always
---

# Regras Globais

## Sobre o Projeto

Aplicação de gestão financeira pessoal construída com Laravel 13 + Inertia.js v3 + Vue 3. Arquitetura Domain-Driven Design (DDD) com domínios: Account, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Period, Settings, Transaction, Transfer, User. Backend PHP 8.4 com API REST, frontend SPA via Inertia/Vue com Pinia, Tailwind CSS 4, Vee-Validate + Zod.

## Contexto do Projeto

- Histórico de implementações: `.kiro/specs/summary.md`
- Skills do Laravel Boost: `AGENTS.md`

## Workflow Git

- Branch principal: `develop` — toda branch nasce dela e volta pra ela
- Padrão de commit: Conventional Commits (`tipo: Mensagem descritiva`)
- Tipos válidos: `feat`, `fix`, `refactor`, `chore`, `docs`, `style`, `test`
- Commits atômicos: uma mudança lógica por commit
- Push e PR sempre para `develop`

## Proteção contra Sobrescrita

- MUST ler o arquivo COMPLETO antes de alterar qualquer componente
- MUST identificar TODAS as funcionalidades existentes
- MUST usar `strReplace` para alterações pontuais — evitar `fsWrite` em arquivos grandes
- MUST NOT remover ou substituir funcionalidades existentes sem autorização
- MUST preservar imports, props, emits e lógica de negócio ao refatorar

## Tratamento de Bugs

### Bug no escopo da tarefa atual
- Corrigir na mesma branch como `fix: Descrição`

### Bug fora do escopo
- Parar, informar o usuário, perguntar como proceder (corrigir aqui, nova task, ou documentar)

### Bug pré-existente
- Não misturar com a tarefa atual, sugerir issue separada

## Checklist Pré-Push

- `vendor/bin/pint --dirty --format agent` sem erros (PHP)
- `npm run lint` sem erros (JS/Vue)
- `npm run types:check` sem erros (TypeScript)
- `php artisan test --compact` sem falhas
- Commits seguem Conventional Commits
- Sem `console.log`, `any` injustificado, imports desnecessários
- Branch atualizada com develop
