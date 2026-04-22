# Requirements Document

## Introduction

Padronização do componente `PageHeader.vue` para servir como cabeçalho unificado em todas as páginas da aplicação. O componente atual possui uma API rígida com botão hard-coded, sem suporte a breadcrumbs e sem slot para botão de voltar. A nova versão deve oferecer uma API flexível baseada em props e slots, permitindo que cada página controle seu conteúdo de forma independente. A mudança afeta 8 páginas Index e 1 página Show (periods/Show.vue).

## Glossary

- **PageHeader**: Componente Vue compartilhado em `resources/js/domain/Shared/components/PageHeader.vue` que renderiza o cabeçalho de conteúdo das páginas (título, breadcrumbs, ações)
- **BreadcrumbItem**: Tipo TypeScript definido em `resources/js/domain/Shared/types/navigation.ts` com propriedades `title` (string) e `href` (InertiaLinkProps['href'])
- **Index_Page**: Página de listagem de um domínio (ex: accounts/Index.vue, categories/Index.vue) que exibe tabela de dados com ações CRUD
- **Show_Page**: Página de detalhe de um recurso (ex: periods/Show.vue) que exibe informações detalhadas com múltiplas ações
- **Back_Slot**: Slot nomeado `#back` do PageHeader destinado a conteúdo de navegação de retorno (lado esquerdo, antes do título)
- **Actions_Slot**: Slot nomeado `#actions` do PageHeader destinado a botões de ação (lado direito, alinhado à direita)
- **Breadcrumb_Components**: Suite de componentes UI em `resources/js/domain/Shared/components/ui/breadcrumb/` (Breadcrumb, BreadcrumbList, BreadcrumbItem, BreadcrumbLink, BreadcrumbPage, BreadcrumbSeparator)

## Requirements

### Requirement 1: Title Prop

**User Story:** As a developer, I want the PageHeader to accept a `title` prop, so that each page can display its own heading text consistently.

#### Acceptance Criteria

1. THE PageHeader SHALL accept a required `title` prop of type `string`
2. THE PageHeader SHALL render the `title` prop value inside an `h1` element with `text-2xl font-semibold` styling
3. WHEN the `title` prop value changes, THE PageHeader SHALL update the rendered heading text reactively

### Requirement 2: Breadcrumbs Prop

**User Story:** As a developer, I want the PageHeader to accept a `breadcrumbs` prop, so that pages can display navigation breadcrumbs above the title area.

#### Acceptance Criteria

1. THE PageHeader SHALL accept an optional `breadcrumbs` prop of type `BreadcrumbItem[]`
2. WHEN the `breadcrumbs` prop is provided with one or more items, THE PageHeader SHALL render a breadcrumb navigation using the Breadcrumb_Components suite
3. WHEN the `breadcrumbs` prop contains multiple items, THE PageHeader SHALL render intermediate items as clickable Inertia Links and the last item as plain text (BreadcrumbPage)
4. WHEN the `breadcrumbs` prop contains multiple items, THE PageHeader SHALL render a BreadcrumbSeparator between each pair of items
5. WHEN the `breadcrumbs` prop is not provided or is an empty array, THE PageHeader SHALL not render any breadcrumb navigation

### Requirement 3: Back Slot

**User Story:** As a developer, I want the PageHeader to provide a `#back` slot, so that pages like periods/Show can render a back button to the left of the title.

#### Acceptance Criteria

1. THE PageHeader SHALL provide a named slot called `back`
2. WHEN the `#back` slot has content, THE PageHeader SHALL render the slot content to the left of the title element
3. WHEN the `#back` slot is empty, THE PageHeader SHALL render the title without extra left-side spacing or empty containers

### Requirement 4: Actions Slot

**User Story:** As a developer, I want the PageHeader to provide an `#actions` slot aligned to the right, so that each page can render its own action buttons flexibly.

#### Acceptance Criteria

1. THE PageHeader SHALL provide a named slot called `actions`
2. WHEN the `#actions` slot has content, THE PageHeader SHALL render the slot content aligned to the right side of the header row
3. WHEN the `#actions` slot is empty, THE PageHeader SHALL not render any right-side container or placeholder
4. THE PageHeader SHALL apply a flex layout with `items-center` and a gap between action items within the actions area

### Requirement 5: Remove Hard-Coded Button API

**User Story:** As a developer, I want the old `buttonLabel`, `buttonIcon` props and `@action` emit removed from PageHeader, so that the component API is clean and slot-based.

#### Acceptance Criteria

1. THE PageHeader SHALL not accept `buttonLabel`, `buttonIcon` props or emit an `action` event
2. THE PageHeader SHALL not render any hard-coded Button element in its template

### Requirement 6: Migrate Index Pages

**User Story:** As a developer, I want all 8 Index pages updated to use the new PageHeader API, so that the application uses a consistent header pattern.

#### Acceptance Criteria

1. WHEN an Index_Page renders PageHeader, THE Index_Page SHALL pass the locally defined `breadcrumbs` array as the `breadcrumbs` prop
2. WHEN an Index_Page renders PageHeader, THE Index_Page SHALL move its action button into the `#actions` slot of PageHeader
3. THE accounts/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
4. THE categories/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
5. THE credit-cards/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
6. THE credit-card-charges/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
7. THE fixed-expenses/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
8. THE periods/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar Período" button
9. THE transactions/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
10. THE transfers/Index.vue SHALL use the new PageHeader with `title`, `breadcrumbs`, and `#actions` slot containing the "Criar" button
11. WHEN an Index_Page is migrated, THE Index_Page SHALL remove the `eslint-disable` comment above the `breadcrumbs` variable since the variable is now used

### Requirement 7: Migrate periods/Show Page

**User Story:** As a developer, I want periods/Show.vue to use the standardized PageHeader, so that the detail page follows the same header pattern with back button and multiple actions.

#### Acceptance Criteria

1. THE periods/Show.vue SHALL replace its custom header markup with the PageHeader component
2. THE periods/Show.vue SHALL pass the locally defined `breadcrumbs` array as the `breadcrumbs` prop to PageHeader
3. THE periods/Show.vue SHALL render the ArrowLeft back button inside the `#back` slot of PageHeader
4. THE periods/Show.vue SHALL render the "Remover Todas as Transações", "Nova Transação", and "Inicializar Período" buttons inside the `#actions` slot of PageHeader
5. WHEN the periods/Show.vue is migrated, THE periods/Show.vue SHALL preserve all existing button behavior (click handlers, disabled states, AlertDialog for destructive action)
6. WHEN the periods/Show.vue is migrated, THE periods/Show.vue SHALL remove the `eslint-disable` comment above the `breadcrumbs` variable since the variable is now used

### Requirement 8: Visual Consistency

**User Story:** As a developer, I want the new PageHeader to maintain the same visual layout as the current implementation, so that the migration does not introduce visual regressions.

#### Acceptance Criteria

1. THE PageHeader SHALL use a flex layout with `items-center justify-between` for the title row (back + title on the left, actions on the right)
2. WHEN breadcrumbs are present, THE PageHeader SHALL render breadcrumbs above the title row
3. THE PageHeader SHALL maintain the existing `text-2xl font-semibold` styling for the title heading
