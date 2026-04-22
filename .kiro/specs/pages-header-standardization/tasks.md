# Implementation Plan: Pages Header Standardization

## Overview

Refatoração do componente `PageHeader.vue` para uma API baseada em slots e props (breadcrumbs, #back, #actions), seguida da migração de 8 Index pages e 1 Show page para o novo padrão. Abordagem incremental: primeiro o componente, depois as páginas uma a uma, finalizando com validação de lint e tipos.

## Tasks

- [x] 1. Refactor PageHeader.vue component
  - Replace the current props (`buttonLabel`, `buttonIcon`) and emit (`action`) with the new API: `title` (required), `breadcrumbs?` (optional `BreadcrumbItem[]`), slot `#back`, slot `#actions`
  - Add imports: `Link` from `@inertiajs/vue3`, `BreadcrumbItem` type from `@/domain/Shared/types/navigation`
  - Render breadcrumbs conditionally above the title row using `Breadcrumb`, `BreadcrumbList`, `BreadcrumbItem`, `BreadcrumbLink`, `BreadcrumbPage`, `BreadcrumbSeparator` (auto-imported)
  - Intermediate breadcrumb items render as Inertia `Link`, last item as `BreadcrumbPage`
  - Title row: flex container with `#back` slot + `h1` on the left, conditional `#actions` container on the right
  - Remove the hard-coded `<Button>` element entirely
  - _Requirements: 1.1, 1.2, 2.1–2.5, 3.1–3.3, 4.1–4.4, 5.1–5.2, 8.1–8.3_

- [~] 2. Migrate accounts/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace `<PageHeader title="Contas" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />` with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.3, 6.11_

- [~] 3. Migrate categories/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.4, 6.11_

- [~] 4. Migrate credit-cards/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.5, 6.11_

- [~] 5. Migrate credit-card-charges/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.6, 6.11_

- [~] 6. Migrate fixed-expenses/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.7, 6.11_

- [~] 7. Migrate periods/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar Período" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.8, 6.11_

- [~] 8. Migrate transactions/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.9, 6.11_

- [~] 9. Migrate transfers/Index.vue
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace old PageHeader usage with new API: pass `:breadcrumbs="breadcrumbs"`, move "Criar" button into `#actions` slot
  - _Requirements: 6.1, 6.2, 6.10, 6.11_

- [~] 10. Checkpoint - Ensure all Index pages are migrated
  - Ensure all tests pass, ask the user if questions arise.

- [~] 11. Migrate periods/Show.vue (special case)
  - Remove `eslint-disable-next-line` comment above `breadcrumbs`
  - Replace the custom header `<div class="flex items-center justify-between">` block with `<PageHeader>` component
  - Pass dynamic title `` :title="`${monthNames[period.month]} ${period.year}`" `` and `:breadcrumbs="breadcrumbs"`
  - Move ArrowLeft back button into `#back` slot
  - Move all 3 action buttons (AlertDialog "Remover Todas as Transações", "Nova Transação", "Inicializar Período") into `#actions` slot
  - Preserve all existing click handlers, disabled states, and AlertDialog markup exactly as-is
  - _Requirements: 7.1–7.6_

- [~] 12. Final checkpoint - Run lint and type checks
  - Run `npm run lint` to confirm no ESLint errors (especially that `eslint-disable` comments are removed and `breadcrumbs` is no longer unused)
  - Run `npm run types:check` to confirm TypeScript compilation passes with the new PageHeader API
  - Ensure all tests pass, ask the user if questions arise.
  - _Requirements: 8.1–8.3_

## Notes

- No property-based tests apply — this is pure UI component refactoring
- All Breadcrumb sub-components are auto-imported via `unplugin-vue-components`
- Each Index page follows the exact same migration pattern (remove eslint-disable, add `:breadcrumbs`, move button to `#actions` slot)
- periods/Show.vue is the only page using `#back` slot
- Validation relies on `npm run lint` + `npm run types:check` as defined in the pre-push checklist
