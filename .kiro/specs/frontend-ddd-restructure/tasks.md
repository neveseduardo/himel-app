# Plano de Implementação: Reestruturação Frontend DDD

## Visão Geral

Migração incremental do frontend de `resources/js/modules/` para `resources/js/domain/`, seguindo as 12 fases definidas no design. Cada fase é executada em sequência para minimizar quebras intermediárias. A linguagem de implementação é TypeScript/Vue 3.

## Tasks

- [x] 1. Criar estrutura de diretórios `domain/`
  - Criar `resources/js/domain/` como raiz da nova estrutura DDD
  - Criar os 13 domínios: Account, Auth, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Period, Settings, Shared, Transaction, Transfer, User
  - Criar as 6 subpastas em cada domínio: `stores/`, `components/`, `services/`, `composables/`, `types/`, `validations/`
  - Criar a subpasta `services/adapters/` em cada domínio
  - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 2. Separar types de `finance.ts` por domínio
  - [~] 2.1 Criar tipos compartilhados em `domain/Shared/types/`
    - Criar `domain/Shared/types/pagination.ts` com a interface `PaginationMeta`
    - Criar `domain/Shared/types/common.ts` com o tipo `Direction`
    - _Requisitos: 8.1, 4.1_

  - [~] 2.2 Criar tipos por domínio financeiro
    - Criar `domain/Account/types/account.ts` com `Account` e `AccountType`
    - Criar `domain/Category/types/category.ts` com `Category` (importando `Direction` de Shared)
    - Criar `domain/Transaction/types/transaction.ts` com `Transaction`, `TransactionStatus`, `TransactionSource` (usando tipos inline para referências cruzadas conforme design)
    - Criar `domain/Transfer/types/transfer.ts` com `Transfer` (usando tipos inline para referências cruzadas)
    - Criar `domain/FixedExpense/types/fixed-expense.ts` com `FixedExpense` (usando tipos inline para referências cruzadas)
    - Criar `domain/CreditCard/types/credit-card.ts` com `CreditCard` e `CardType`
    - Criar `domain/CreditCardCharge/types/credit-card-charge.ts` com `CreditCardCharge` (usando tipos inline)
    - Criar `domain/CreditCardInstallment/types/credit-card-installment.ts` com `CreditCardInstallment` (usando tipos inline)
    - Criar `domain/Period/types/period.ts` com `Period`, `PeriodSummary`, `InitializationResult`
    - _Requisitos: 8.1, 16.1, 16.2, 16.4_

  - [~] 2.3 Atualizar imports de types nos arquivos consumidores
    - Atualizar todos os imports de `@/modules/finance/types/finance` para os novos caminhos em `@/domain/<Domínio>/types/`
    - Atualizar imports em stores, validations, composables, components e pages
    - _Requisitos: 9.1, 9.2, 9.3_

- [ ] 3. Mover validations (schemas Zod) por domínio
  - [~] 3.1 Mover schemas para domínios correspondentes
    - Mover `account-schema.ts` para `domain/Account/validations/`
    - Mover `category-schema.ts` para `domain/Category/validations/`
    - Mover `credit-card-schema.ts` para `domain/CreditCard/validations/`
    - Mover `credit-card-charge-schema.ts` para `domain/CreditCardCharge/validations/`
    - Mover `fixed-expense-schema.ts` para `domain/FixedExpense/validations/`
    - Mover `transaction-schema.ts` para `domain/Transaction/validations/`
    - Mover `transfer-schema.ts` para `domain/Transfer/validations/`
    - Atualizar imports internos dos schemas para referenciar os novos tipos do domínio
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

  - [~] 3.2 Atualizar imports de validations nos arquivos consumidores
    - Atualizar todos os imports de `@/modules/finance/validations/` para `@/domain/<Domínio>/validations/`
    - _Requisitos: 9.1, 9.2, 9.3_

- [~] 4. Checkpoint — Validar types e validations
  - Executar `npm run types:check` para garantir que a separação de types e validations não introduziu erros
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Mover stores por domínio
  - [~] 5.1 Mover stores para domínios correspondentes
    - Mover `useAccountStore.ts` para `domain/Account/stores/`
    - Mover `useCategoryStore.ts` para `domain/Category/stores/`
    - Mover `useCreditCardStore.ts` para `domain/CreditCard/stores/`
    - Mover `useCreditCardChargeStore.ts` para `domain/CreditCardCharge/stores/`
    - Mover `useFixedExpenseStore.ts` para `domain/FixedExpense/stores/`
    - Mover `useTransactionStore.ts` para `domain/Transaction/stores/`
    - Mover `useTransferStore.ts` para `domain/Transfer/stores/`
    - Atualizar imports internos das stores para referenciar os novos tipos do domínio (caminhos relativos `../types/`)
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 16.4_

  - [~] 5.2 Atualizar imports de stores nos arquivos consumidores
    - Atualizar todos os imports de `@/modules/finance/stores/` para `@/domain/<Domínio>/stores/`
    - _Requisitos: 9.1, 9.2, 9.3_

- [ ] 6. Criar services — Ports e Adapters
  - [~] 6.1 Criar Ports e Adapters para domínio Account
    - Criar `domain/Account/services/AccountServicePort.ts` com interface pura (sem imports de infraestrutura)
    - Criar `domain/Account/services/adapters/AccountWayfinderAdapter.ts` implementando `AccountServicePort`
    - _Requisitos: 14.1, 14.2, 14.3, 14.4, 14.5, 16.5_

  - [~] 6.2 Criar Ports e Adapters para demais domínios financeiros
    - Criar Ports e Adapters para Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Transaction, Transfer seguindo o mesmo padrão de Account
    - Cada Port define métodos com tipos explícitos sem referências a bibliotecas de infraestrutura
    - Cada Adapter importa e implementa (`implements`) o Port correspondente
    - _Requisitos: 14.1, 14.2, 14.3, 14.4, 14.5, 16.5_

  - [~] 6.3 Criar Port e Adapter para Auth (Two Factor)
    - Criar `domain/Auth/services/TwoFactorServicePort.ts` com interface para QR code, setup key e recovery codes
    - Criar `domain/Auth/services/adapters/TwoFactorFetchAdapter.ts` implementando o Port com fetch + CSRF
    - _Requisitos: 14.1, 14.2, 14.3, 14.5_

  - [~] 6.4 Criar Ports e Adapters compartilhados em Shared
    - Criar `domain/Shared/services/FormatServicePort.ts` com interface de formatação (currency, date, dateTime)
    - Criar `domain/Shared/services/adapters/FormatIntlAdapter.ts` implementando o Port com `Intl`
    - Criar `domain/Shared/services/adapters/InertiaNavigationAdapter.ts` para navegação via Inertia router
    - Remover `modules/finance/services/finance.services.ts` após migração da lógica para os adapters
    - _Requisitos: 7.1, 7.2, 14.7_

- [~] 7. Checkpoint — Validar services
  - Executar `npm run types:check` para garantir que Ports e Adapters estão tipados corretamente
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Mover composables para domínios e Shared
  - [~] 8.1 Mover composables compartilhados para Shared
    - Mover `useFinanceFilters.ts` para `domain/Shared/composables/` e refatorar para injeção de dependência (receber `NavigationPort` como parâmetro)
    - Mover `useCrudToast.ts` para `domain/Shared/composables/`
    - Mover `useFlashMessages.ts` para `domain/Shared/composables/`
    - Mover `usePagination.ts` para `domain/Shared/composables/`
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 16.3, 16.6_

  - [~] 8.2 Mover composable de Auth
    - Mover `useTwoFactorAuth.ts` de `resources/js/composables/` para `domain/Auth/composables/`
    - Refatorar para consumir `TwoFactorServicePort` via injeção de dependência
    - _Requisitos: 6.5, 14.6, 16.6_

  - [~] 8.3 Atualizar imports de composables nos arquivos consumidores
    - Atualizar todos os imports de `@/modules/finance/composables/` para `@/domain/Shared/composables/`
    - Atualizar imports de `@/composables/useTwoFactorAuth` para `@/domain/Auth/composables/useTwoFactorAuth`
    - Manter `useAppearance.ts`, `useCurrentUrl.ts` e `useInitials.ts` em `resources/js/composables/`
    - _Requisitos: 6.6, 9.1, 9.2, 9.3_

- [ ] 9. Mover components para domínios e Shared
  - [~] 9.1 Mover componentes de formulário para domínios
    - Mover `AccountForm.vue` para `domain/Account/components/`
    - Mover `CategoryForm.vue` para `domain/Category/components/`
    - Mover `CreditCardForm.vue` para `domain/CreditCard/components/`
    - Mover `CreditCardChargeForm.vue` para `domain/CreditCardCharge/components/`
    - Mover `FixedExpenseForm.vue` para `domain/FixedExpense/components/`
    - Mover `TransactionForm.vue` para `domain/Transaction/components/`
    - Mover `TransferForm.vue` para `domain/Transfer/components/`
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [~] 9.2 Mover componentes de Auth e Settings
    - Mover `DeleteUser.vue`, `PasswordInput.vue`, `TwoFactorRecoveryCodes.vue`, `TwoFactorSetupModal.vue` para `domain/Auth/components/`
    - Mover `AlertError.vue`, `AppearanceTabs.vue`, `Heading.vue`, `InputError.vue`, `TextLink.vue`, `UserInfo.vue`, `UserMenuContent.vue` para `domain/Settings/components/`
    - _Requisitos: 3.8, 3.9_

  - [~] 9.3 Mover componentes compartilhados para Shared
    - Mover `DataTable.vue`, `DirectionBadge.vue`, `FilterBar.vue`, `StatusBadge.vue` para `domain/Shared/components/`
    - _Requisitos: 4.1_

  - [~] 9.4 Atualizar imports de components nos arquivos consumidores
    - Atualizar todos os imports de `@/modules/finance/components/` para `@/domain/<Domínio>/components/` ou `@/domain/Shared/components/`
    - Atualizar imports de `@/modules/auth/components/` para `@/domain/Auth/components/`
    - Atualizar imports de `@/modules/settings/components/` para `@/domain/Settings/components/`
    - Manter `resources/js/components/` (layout e UI base) inalterado
    - _Requisitos: 4.2, 9.1, 9.2, 9.3, 10.5_

- [~] 10. Checkpoint — Validar migração completa
  - Executar `npm run types:check` para garantir zero erros TypeScript
  - Executar `npm run lint` para garantir zero erros ESLint
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Atualizar configurações e documentação
  - [~] 11.1 Atualizar `vite.config.ts`
    - Substituir `./resources/js/modules/auth/components` por `./resources/js/domain/Auth/components` no array `dirs` do `unplugin-vue-components`
    - Substituir `./resources/js/modules/settings/components` por `./resources/js/domain/Settings/components` no array `dirs` do `unplugin-vue-components`
    - _Requisitos: 9.4_

  - [~] 11.2 Atualizar `conventions.md`
    - Substituir referências a `resources/js/modules/` por `resources/js/domain/` na seção "Estrutura de Diretórios"
    - Documentar a nova organização DDD do frontend com as subpastas padronizadas por domínio
    - _Requisitos: 12.1, 12.2_

- [ ] 12. Validação pós-migração e limpeza
  - [~] 12.1 Executar validação completa
    - Executar `npm run types:check` — zero erros TypeScript
    - Executar `npm run lint` — zero erros ESLint
    - Executar `npm run build` — build sem erros de compilação
    - Executar `php artisan test --compact` — testes existentes passando
    - _Requisitos: 13.1, 13.2, 13.3, 13.4_

  - [~] 12.2 Remover estrutura antiga `modules/`
    - Verificar que nenhum import referencia `@/modules/`
    - Remover `resources/js/modules/` e todos os subdiretórios
    - _Requisitos: 11.1, 11.2_

  - [~] 12.3 Validação final
    - Executar `npm run types:check`, `npm run lint`, `npm run build` e `php artisan test --compact` novamente após remoção
    - Confirmar que a aplicação compila e todos os testes passam sem a estrutura antiga
    - _Requisitos: 13.1, 13.2, 13.3, 13.4_

- [ ]* 13. Testes de propriedade arquitetural (opcional)
  - [ ]* 13.1 Property 1 — Estrutura de subpastas por domínio
    - **Property 1: Estrutura de subpastas por domínio**
    - Verificar que cada domínio em `domain/` contém exatamente as 6 subpastas: `stores/`, `components/`, `services/`, `composables/`, `types/`, `validations/`
    - **Valida: Requisitos 1.5, 15.6**

  - [ ]* 13.2 Property 2 — Regra de Dependência entre camadas
    - **Property 2: Regra de Dependência entre camadas**
    - Verificar via análise de imports que `types/` não importa de outras pastas do domínio; `stores/` importa apenas de `types/`; `composables/` importa de `types/`, `stores/` e `services/`; `components/` importa de `composables/`, `stores/` e `types/`; `adapters/` são os únicos que importam bibliotecas de infraestrutura
    - **Valida: Requisitos 14.4, 14.6, 16.1, 16.3, 16.4, 16.5, 16.6**

  - [ ]* 13.3 Property 3 — Isolamento entre domínios via Shared
    - **Property 3: Isolamento entre domínios via Shared**
    - Verificar que nenhum domínio importa diretamente de outro domínio que não seja Shared
    - **Valida: Requisitos 16.7, 16.8**

  - [ ]* 13.4 Property 4 — Adapters implementam Ports
    - **Property 4: Adapters implementam Ports**
    - Verificar que cada arquivo em `services/adapters/` importa e implementa (`implements`) a interface Port correspondente
    - **Valida: Requisitos 14.2, 14.5**

  - [ ]* 13.5 Property 5 — Ordem de imports consistente
    - **Property 5: Ordem de imports consistente**
    - Verificar que imports seguem a ordem: (1) dependências externas, (2) imports da aplicação, (3) imports do Shared, (4) imports do próprio domínio
    - **Valida: Requisitos 15.9**

  - [ ]* 13.6 Property 6 — Limites de tamanho de código
    - **Property 6: Limites de tamanho de código**
    - Verificar que funções têm no máximo 30 linhas executáveis e blocos `<script setup>` têm no máximo 200 linhas
    - **Valida: Requisitos 15.3, 15.7**

## Notas

- Tasks marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada task referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental a cada fase crítica
- A migração preserva `pages/`, `actions/`, `routes/`, `wayfinder/`, `components/` (layout) e `lib/` inalterados
- Durante a migração, `modules/` e `domain/` coexistem temporariamente — a remoção de `modules/` só ocorre na fase final
- Usar `smartRelocate` para mover arquivos e atualizar imports automaticamente quando possível
