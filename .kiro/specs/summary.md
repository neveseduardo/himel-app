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
- `playwright.config.ts` — configuração Playwright
- `e2e/setup/global-setup.ts` — auth + DB seeding
- `e2e/pages/CreditCardPage.ts` — Page Object
- `e2e/tests/credit-card.spec.ts` — 26 testes E2E
- `database/seeders/E2eTestSeeder.php` — seeder dedicado
- `.gitignore` atualizado com `e2e/results/` e `e2e/.auth/`
- `package.json` atualizado com Playwright e scripts E2E
