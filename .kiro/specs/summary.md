# Summary de Specs e Histórico

Este documento consolida o histórico de todas as specs implementadas neste projeto.

---

## Spec: Frontend DDD Restructure

Migração do frontend de `resources/js/modules/` para `resources/js/domain/`, adotando Domain-Driven Design espelhando o backend.

### Requisitos
- Criar estrutura DDD com 13 domínios (Account, Auth, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Period, Settings, Shared, Transaction, Transfer, User)
- Cada domínio com 6 subpastas: `stores/`, `components/`, `services/`, `composables/`, `types/`, `validations/`
- Arquitetura Hexagonal (Ports & Adapters) para isolar lógica de domínio da infraestrutura
- Clean Architecture com Regra de Dependência entre camadas
- Preservar `pages/`, `actions/`, `routes/`, `components/` (layout) e `lib/` inalterados

### Decisões de Design
- Referências cruzadas entre domínios usam tipos inline simplificados (ex: `account?: { uid: string; name: string }`) em vez de importar tipos de outros domínios
- Tipos compartilhados (`Direction`, `PaginationMeta`) centralizados em `domain/Shared/types/`
- Ports são interfaces TypeScript puras sem dependências de infraestrutura
- Adapters implementam Ports e encapsulam Inertia/Wayfinder/fetch
- Componentes de formulário importam Wayfinder actions diretamente para URLs (funções puras, não I/O)
- Composables recebem adapters via parâmetro (Inversão de Controle)

### Tasks Concluídas (12/12 obrigatórias)
1. Criar estrutura de diretórios `domain/`
2. Separar types de `finance.ts` por domínio
3. Mover validations (schemas Zod) por domínio
4. Checkpoint — Validar types e validations
5. Mover stores por domínio
6. Criar services — Ports e Adapters
7. Checkpoint — Validar services
8. Mover composables para domínios e Shared
9. Mover components para domínios e Shared
10. Checkpoint — Validar migração completa
11. Atualizar configurações e documentação
12. Validação pós-migração e limpeza (remoção de `modules/`)

### Tasks Opcionais (não executadas)
- Testes de propriedade arquitetural (6 properties definidas no design)

### Artefatos Criados/Alterados
- **Domínios criados**: 13 domínios em `resources/js/domain/` com subpastas padronizadas
- **Types**: Separados de `finance.ts` monolítico para arquivos por domínio
- **Stores**: 7 stores Pinia migradas para domínios correspondentes
- **Validations**: 7 schemas Zod migrados para domínios correspondentes
- **Services**: Ports e Adapters criados para Account, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Transaction, Transfer, Auth (TwoFactor), Shared (Format, Navigation)
- **Composables**: 5 composables migrados (4 para Shared, 1 para Auth)
- **Components**: Formulários, componentes de Auth/Settings e componentes compartilhados migrados
- **Configuração**: `vite.config.ts` atualizado com novos caminhos de auto-import
- **Documentação**: `conventions.md` atualizado com nova estrutura DDD
- **Removido**: `resources/js/modules/` (estrutura antiga)

---

## Spec: E2E Testing CreditCard

Infraestrutura de testes E2E com Playwright para o módulo CreditCard, primeiro módulo de uma estratégia módulo-a-módulo.

### Requisitos
- Playwright configurado com headless Chromium, npm scripts (`test:e2e`, `test:e2e:ui`)
- Autenticação automática via Fortify login com reuso de sessão
- Isolamento de banco com seeder dedicado (`E2eTestSeeder`)
- Testes CRUD completos: listagem, busca/filtro, paginação, criação, edição, visualização, exclusão
- Page Object Pattern para reusabilidade entre módulos futuros
- Reporting com traces para diagnóstico de falhas

### Decisões de Design
- Playwright escolhido sobre Cypress: melhor suporte a SPA/Inertia, TypeScript nativo, auto-waiting superior
- Global setup autentica uma vez e salva `storageState` para reuso
- Page Object centraliza selectors baseados em roles/text/placeholder
- Seeder idempotente com 3 cartões nomeados + 20 via factory (paginação)
- Sem PBT — todos testes são example-based E2E

### Tasks Concluídas (14/14)
1. Instalar Playwright e configurar (`playwright.config.ts`, npm scripts, `.gitignore`)
2. Criar `E2eTestSeeder` (usuário teste + cartões de crédito)
3. Criar global setup (auth via Fortify + seeding)
4. Checkpoint — infraestrutura verificada
5. Criar `CreditCardPage` Page Object
6-8. Testes de listagem, busca/filtro, paginação
9. Checkpoint — testes read-only
10-13. Testes de criação, edição, visualização, exclusão
14. Checkpoint final

### Artefatos Criados
- `playwright.config.ts` — configuração Playwright com `webServer` auto-start (build + serve)
- `e2e/setup/global-setup.ts` — auth + DB seeding
- `e2e/pages/CreditCardPage.ts` — Page Object
- `e2e/tests/credit-card.spec.ts` — 26 testes E2E
- `e2e/start-server.sh` — script para CI/CD (build + serve)
- `database/seeders/E2eTestSeeder.php` — seeder dedicado com reset de dados
- `database/migrations/..._add_closing_day_and_last_four_digits_to_financial_credit_cards_table.php` — migration para campos faltantes
- `.gitignore` atualizado com `e2e/results/` e `e2e/.auth/`
- `package.json` atualizado com Playwright e scripts E2E

### Bugs Encontrados e Corrigidos

#### Frontend
- `AppLayout.vue` e `AuthLayout.vue` referenciavam a si mesmos no template — recursão infinita que impedia qualquer página de renderizar. Corrigido para usar `AppSidebarLayout` e `AuthSimpleLayout` respectivamente
- `<Sonner />` (toasts) não estava renderizado em nenhum layout — chamadas `toast()` nunca mostravam nada. Adicionado ao `AppSidebarLayout.vue`
- `Input.vue` não sincronizava o `value` prop do vee-validate — formulários de edição abriam com campos vazios. Adicionado suporte ao prop `value` com watcher
- `app.blade.php` referenciava cada página Vue individualmente no `@vite()` — funcionava com Vite dev server mas quebrava com `npm run build`. Removida referência redundante (páginas já estão bundled no `app.ts` via `import.meta.glob`)

#### Backend
- Colunas `closing_day` e `last_four_digits` não existiam no banco — o formulário tinha os campos mas o backend não persistia. Adicionada migration, atualizado Model (fillable/casts), Resource, Store/Update FormRequests e Service
- `FinancialCreditCardFactory` não tinha `protected $model` definido — Laravel tentava resolver `App\FinancialCreditCard` que não existe

#### Infraestrutura E2E
- Labels do login em português ("Endereço de e-mail", "Senha", "Entrar") — testes iniciais usavam labels em inglês
- Campo de senha (`InputPassword`) é um wrapper `<div>` com `<input>` dentro — `#password` resolvia pro div, não pro input
- Inputs do formulário de cartão não tinham `id` (labels tinham `for="name"` mas inputs tinham `id=""`) — `getByLabel()` não funcionava, corrigido para usar `locator('[name="..."]')`
- `waitForLoadState('networkidle')` travava por causa do websocket do Vite HMR — substituído por waits explícitos
- Arquivo `public/hot` do Vite fazia Laravel achar que dev server estava rodando em build mode — removido no script de start
- Seeder original não limpava dados anteriores — cada execução acumulava cartões, mudando paginação. Corrigido com reset antes de seed


---

## Spec: E2E Testing FixedExpense

Testes E2E com Playwright para o módulo FixedExpense (Despesas Fixas), terceiro módulo da estratégia módulo-a-módulo.

### Requisitos
- Correção da factory `FinancialFixedExpenseFactory` (adicionar `$model`)
- Seed de dados FixedExpense no `E2eTestSeeder` (3 nomeados + 20 factory)
- Dialog sync fix no `FixedExpense Index.vue` (`@update:open`)
- Page Object `FixedExpensePage.ts` com suporte a combobox Select e checkbox
- 28 testes E2E cobrindo CRUD completo: Listing, Search, Pagination, Dialog Reopen, Creation, Editing, Viewing, Deletion

### Decisões de Design
- Mesmo padrão de CreditCard/CreditCardCharge: Page Object + `waitForResponse` (sem `waitForTimeout`)
- Campo `active` tratado como checkbox com lógica condicional no `fillForm`
- Campo `category_uid` tratado como reka-ui Select combobox (mesmo padrão de CreditCardCharge)
- Categoria selecionada dinamicamente nos testes de criação (primeira opção OUTFLOW)

### Tasks Concluídas (5/5)
1. Fix da factory `FinancialFixedExpenseFactory`
2. Atualização do `E2eTestSeeder` com dados FixedExpense
3. Dialog sync fix no `Index.vue`
4. Page Object `FixedExpensePage.ts`
5. Spec de testes `fixed-expense.spec.ts` (28 testes)

### Artefatos Criados/Alterados
- `e2e/pages/FixedExpensePage.ts` — Page Object
- `e2e/tests/fixed-expense.spec.ts` — 28 testes E2E
- `database/factories/FinancialFixedExpenseFactory.php` — adicionado `$model`
- `database/seeders/E2eTestSeeder.php` — adicionados métodos FixedExpense
- `resources/js/pages/finance/fixed-expenses/Index.vue` — dialog sync fix

### Bugs Encontrados e Corrigidos
- `FinancialFixedExpenseFactory` sem `protected $model` (mesmo bug do CreditCard)
- `FixedExpense Index.vue` sem `@update:open` no ModalDialog (modal não reabria após ESC/overlay)
