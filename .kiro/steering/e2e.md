---
inclusion: fileMatch
fileMatchPattern: "e2e/**/*.ts,playwright.config.ts,database/seeders/E2eTestSeeder.php"
---

# Testes E2E (Playwright)

## Stack

- Playwright com Chromium headless
- Page Object Pattern em `e2e/pages/`
- Testes em `e2e/tests/`
- Global setup com auth via Fortify + DB seeding

## Estrutura de Diretórios

```
e2e/
├── .auth/                 # Sessão autenticada (gitignored)
├── pages/                 # Page Objects por módulo
├── results/               # Traces e resultados (gitignored)
├── setup/
│   └── global-setup.ts    # Auth + seeding antes da suite
├── tests/                 # Specs por módulo
└── start-server.sh        # Script para CI/CD (build + serve)
```

## Execução

- `npm run test:e2e` — roda todos os testes (sobe servidor automaticamente)
- `npm run test:e2e:ui` — modo interativo para debug
- `npx playwright test credit-card.spec.ts` — roda um arquivo específico
- `npx playwright show-trace <trace.zip>` — visualiza trace de falha

## Convenções

### Page Objects
- Um Page Object por módulo em `e2e/pages/<Modulo>Page.ts`
- Exportar a classe e interfaces de dados do formulário
- Usar `locator('[name="field"]')` para inputs de formulário (labels sem `id` no input não funcionam com `getByLabel`)
- Usar `getByRole()` para botões, headings, dialogs
- Usar `getByText()` para conteúdo textual
- Usar `getByPlaceholder()` para inputs de busca
- NUNCA usar `waitForLoadState('networkidle')` — trava com Vite HMR websocket. Usar `waitForTimeout()` ou auto-waiting do Playwright

### Testes
- Organizar por `test.describe` blocks: Listing, Search, Pagination, Creation, Editing, Viewing, Deletion
- Testes read-only (listagem, busca, paginação) antes de testes de mutação (CRUD)
- Usar busca (`search()`) para encontrar registros específicos em vez de navegar por páginas
- Não depender de dados que outros testes modificam/deletam
- Timeout de teste: 15s. Timeout de ação: 5s. Falhar rápido.

### Seeders
- Seeder E2E DEVE limpar dados anteriores antes de re-seed (garantir estado limpo)
- Dados nomeados com valores previsíveis para assertions
- Dados factory para volume (paginação)

### Formulários
- Esperar o dialog ficar visível antes de interagir: `dialog.waitFor({ state: 'visible' })`
- Inputs usam atributo `name` (não `id`) — usar `dialog.locator('[name="field"]')`
- Combobox (select): `dialog.getByRole('combobox').click()` → `page.getByRole('option', { name: 'label' }).click()`
- Toast: `page.getByText('mensagem').waitFor({ state: 'visible', timeout: 5000 })`

### CI/CD
- `playwright.config.ts` tem `webServer` que sobe o servidor automaticamente
- `e2e/start-server.sh` faz: `rm -f public/hot` → `npm run build` → `php artisan serve`
- `reuseExistingServer: !process.env.CI` — no CI sempre sobe novo, local reutiliza se já estiver rodando
- O arquivo `public/hot` DEVE ser removido antes de rodar em build mode

## Armadilhas Conhecidas

- `getByLabel()` não funciona se o input não tem `id` correspondente ao `for` do label
- `InputPassword` é um wrapper `<div>` — o `<input>` real está dentro dele
- `waitForLoadState('networkidle')` nunca resolve com Vite HMR ativo
- Arquivo `public/hot` faz Laravel ignorar o build e tentar carregar do Vite dev server
- `app.blade.php` NÃO deve referenciar páginas individuais no `@vite()` — elas já estão no bundle via `import.meta.glob`

## Novo Módulo E2E — Checklist

1. Criar Page Object em `e2e/pages/<Modulo>Page.ts`
2. Criar spec em `e2e/tests/<modulo>.spec.ts`
3. Atualizar `E2eTestSeeder.php` com dados do novo módulo (com reset)
4. Rodar `npm run test:e2e` e iterar até todos passarem
