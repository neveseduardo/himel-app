# Design Document

## Overview

Refatoração do componente `PageHeader.vue` para substituir a API rígida atual (props `title`, `buttonLabel`, `buttonIcon` + emit `action`) por uma API flexível baseada em props e slots. O novo componente suporta breadcrumbs via prop, slot `#back` para navegação de retorno, e slot `#actions` para botões de ação arbitrários.

A migração afeta 9 páginas:
- 8 Index pages que já usam `<PageHeader>` com a API antiga
- 1 Show page (`periods/Show.vue`) que usa markup customizado de header

Todas as páginas já declaram um array `breadcrumbs` local (atualmente não utilizado, com `eslint-disable` comment). A migração conecta esses arrays ao novo componente.

## Architecture

A mudança é isolada na camada de componentes Vue — não há alterações em backend, rotas, stores, ou lógica de negócio.

```mermaid
graph TD
    A[PageHeader.vue] -->|props| B[title: string]
    A -->|props| C[breadcrumbs?: BreadcrumbItem[]]
    A -->|slot| D["#back"]
    A -->|slot| E["#actions"]

    F[Index Pages x8] -->|usa| A
    G[periods/Show.vue] -->|usa| A

    A -->|renderiza| H[Breadcrumb Components]
    A -->|renderiza| I[h1 title]
```

O componente `PageHeader` passa a ser o único ponto de renderização de cabeçalhos de página, eliminando markup duplicado no `periods/Show.vue`.

## Components and Interfaces

### PageHeader.vue — Nova API

```typescript
// Props
defineProps<{
  title: string;
  breadcrumbs?: BreadcrumbItem[];
}>();

// Slots
defineSlots<{
  back?: () => VNode[];
  actions?: () => VNode[];
}>();
```

**Removido da API anterior:**
- Prop `buttonLabel: string`
- Prop `buttonIcon?: Component`
- Emit `action: []`

### Template Structure

```vue
<template>
  <div>
    <!-- Breadcrumbs row (conditional) -->
    <Breadcrumb v-if="breadcrumbs?.length">
      <BreadcrumbList>
        <template v-for="(item, index) in breadcrumbs" :key="index">
          <BreadcrumbItem>
            <BreadcrumbLink v-if="index < breadcrumbs.length - 1" as-child>
              <Link :href="item.href">{{ item.title }}</Link>
            </BreadcrumbLink>
            <BreadcrumbPage v-else>{{ item.title }}</BreadcrumbPage>
          </BreadcrumbItem>
          <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
        </template>
      </BreadcrumbList>
    </Breadcrumb>

    <!-- Title row -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <slot name="back" />
        <h1 class="text-2xl font-semibold">{{ title }}</h1>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
```

**Design decisions:**
- Breadcrumbs renderizam acima da title row, seguindo o mesmo padrão do `AppSidebarHeader.vue`
- O slot `#back` fica dentro do flex container à esquerda, antes do `h1`, com `gap-3` (mesmo gap usado no `periods/Show.vue` atual)
- O slot `#actions` é condicional via `v-if="$slots.actions"` para não renderizar container vazio
- O container de actions usa `gap-2` (mesmo gap usado no `periods/Show.vue` atual)
- Breadcrumb rendering usa `Link` do Inertia (auto-imported) para navegação SPA

### Imports necessários no PageHeader

```typescript
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/domain/Shared/types/navigation';
```

Os componentes `Breadcrumb`, `BreadcrumbList`, `BreadcrumbItem`, `BreadcrumbLink`, `BreadcrumbPage`, `BreadcrumbSeparator` são auto-imported via `unplugin-vue-components`.

## Data Models

Nenhuma alteração em data models. O tipo `BreadcrumbItem` já existe:

```typescript
// resources/js/domain/Shared/types/navigation.ts
export type BreadcrumbItem = {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
};
```

## Migration Patterns

### Index Pages (8 páginas)

Todas seguem o mesmo padrão. Exemplo com `accounts/Index.vue`:

**Antes:**
```vue
<script setup lang="ts">
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financeiro', href: '/' },
  { title: 'Contas', href: index.url() },
];
</script>

<template>
  <PageHeader title="Contas" button-label="Criar" :button-icon="Plus" @action="store.openCreateModal()" />
</template>
```

**Depois:**
```vue
<script setup lang="ts">
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financeiro', href: '/' },
  { title: 'Contas', href: index.url() },
];
</script>

<template>
  <PageHeader title="Contas" :breadcrumbs="breadcrumbs">
    <template #actions>
      <Button size="sm" @click="store.openCreateModal()">
        <Plus class="size-4" />
        Criar
      </Button>
    </template>
  </PageHeader>
</template>
```

**Mudanças por página:**
1. Remover `eslint-disable-next-line @typescript-eslint/no-unused-vars` acima de `breadcrumbs`
2. Adicionar `:breadcrumbs="breadcrumbs"` prop
3. Remover `button-label`, `:button-icon`, `@action` props/emit
4. Mover botão para slot `#actions`

### periods/Show.vue

**Antes (markup customizado):**
```vue
<div class="flex items-center justify-between">
  <div class="flex items-center gap-3">
    <Button variant="ghost" size="icon" @click="router.get(index.url())">
      <ArrowLeft class="size-4" />
    </Button>
    <h1 class="text-2xl font-semibold">
      {{ monthNames[period.month] }} {{ period.year }}
    </h1>
  </div>
  <div class="flex items-center gap-2">
    <!-- AlertDialog + Nova Transação + Inicializar Período buttons -->
  </div>
</div>
```

**Depois:**
```vue
<PageHeader :title="`${monthNames[period.month]} ${period.year}`" :breadcrumbs="breadcrumbs">
  <template #back>
    <Button variant="ghost" size="icon" @click="router.get(index.url())">
      <ArrowLeft class="size-4" />
    </Button>
  </template>
  <template #actions>
    <AlertDialog>
      <!-- ... existing AlertDialog markup preserved exactly ... -->
    </AlertDialog>
    <Button size="sm" @click="createModalRef?.openDialog()">
      <Plus class="mr-2 size-4" />
      Nova Transação
    </Button>
    <Button size="sm" :disabled="initializing" @click="handleInitialize">
      <Play class="mr-2 size-4" />
      {{ initializing ? 'Inicializando...' : 'Inicializar Período' }}
    </Button>
  </template>
</PageHeader>
```

**Mudanças:**
1. Remover `eslint-disable-next-line @typescript-eslint/no-unused-vars` acima de `breadcrumbs`
2. Substituir o bloco `<div class="flex items-center justify-between">` inteiro pelo `<PageHeader>`
3. Back button vai no slot `#back`
4. Os 3 botões de ação (AlertDialog, Nova Transação, Inicializar) vão no slot `#actions`
5. Toda lógica de handlers, disabled states, e AlertDialog é preservada intacta

## Error Handling

Não aplicável — esta é uma refatoração de template sem lógica de negócio ou operações assíncronas. Os handlers de erro existentes nas páginas permanecem inalterados.

## Testing Strategy

**PBT não se aplica a esta feature.** Trata-se de refatoração de componentes Vue (UI rendering) sem transformações de dados, parsers, ou lógica de negócio. Não há propriedades universais testáveis via property-based testing.

**Estratégia de validação:**

1. **TypeScript type checking** — `npm run types:check` garante que a nova API de props/slots está correta
2. **ESLint** — `npm run lint` confirma que os `eslint-disable` comments foram removidos e não há variáveis não utilizadas
3. **E2E tests existentes** — Os testes Playwright do módulo CreditCard continuam passando, validando que a página de cartões funciona com o novo PageHeader
4. **Verificação visual manual** — Navegar por cada uma das 9 páginas e confirmar que breadcrumbs, título, e botões de ação renderizam corretamente
