---
inclusion: fileMatch
fileMatchPattern: "resources/js/**/*.vue,resources/js/**/*.ts"
---

# Frontend (Inertia + Vue + Pinia)

## Componentes Vue

### Estrutura de Blocos

```vue
<script setup lang="ts">
<!-- lógica -->
</script>

<template>
<!-- markup -->
</template>
```

### Ordem no `<script setup>`

1. Imports (bibliotecas externas → componentes locais → tipos)
2. Props e Emits (`defineProps<T>()`, `defineEmits<T>()`)
3. Estado reativo (`ref`, `reactive`)
4. Computed properties
5. Watchers
6. Funções/handlers
7. Lifecycle hooks (`onMounted`, etc.)

## Inertia.js v3

- Páginas em `resources/js/pages/`
- Usar `router` do Inertia para navegação (não `window.location`)
- Usar `useForm` para formulários
- Usar Wayfinder para URLs tipadas (`@/actions/`, `@/routes/`)
- Deferred props: adicionar skeleton/loading state
- `Inertia::optional()` em vez de `Inertia::lazy()`

## Stores (Pinia)

### Padrão de Store

```ts
export const useAccountsStore = defineStore('accounts', () => {
    const items = ref<Account[]>([])
    const loading = ref(false)

    async function fetchItems() {
        loading.value = true
        try {
            // usar Inertia router ou Wayfinder
        } finally {
            loading.value = false
        }
    }

    return { items, loading, fetchItems }
})
```

### Regras de Store

- MUST usar setup syntax (`defineStore('name', () => { ... })`)
- MUST usar try/catch/finally em toda operação async
- MUST retornar explicitamente todas as propriedades e métodos públicos

## Validação de Formulários

- Schemas em `resources/js/` usando Zod
- Integrar via `toTypedSchema()` do `@vee-validate/zod`

## UI Components

- reka-ui para componentes headless
- lucide-vue-next para ícones
- vue-sonner para toasts/notificações
- @tanstack/vue-table para tabelas
- class-variance-authority + tailwind-merge para variantes de estilo

## Regras de Template

- Componentes: PascalCase (`<AccountForm />`)
- Props: camelCase (`:accountId`)
- Eventos: kebab-case (`@account-created`)
- `v-model` ao invés de prop + emit manual quando possível
