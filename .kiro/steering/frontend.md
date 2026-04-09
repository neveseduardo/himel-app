# Regras de Frontend — Himel App

> **Glob:** `resources/js/**/*.{ts,vue}`
>
> Regras obrigatórias para todo código Vue/TypeScript do projeto.

## Componentes Vue

- Todo componente DEVE usar `<script setup lang="ts">`.
- É PROIBIDO usar `any` em TypeScript. Todo código DEVE ser tipado.
- Props DEVEM usar `defineProps<T>()` com interface tipada.
- Emits DEVEM usar `defineEmits<T>()` com interface tipada.
- Componentes expostos DEVEM usar `defineExpose()`.

## Shadcn/Vue

- USAR EXCLUSIVAMENTE componentes Shadcn/Vue existentes no projeto.
- NÃO criar componentes de UI básicos se houver equivalente no Shadcn.
- Caso não exista, adicionar via: `npx shadcn-vue@latest add <componente>`.
- Componentes customizados de UI ficam em `resources/js/components/ui/`.

## Arquitetura Modular

Toda lógica de frontend DEVE seguir a estrutura em `resources/js/modules/finance/`:

| Pasta | Conteúdo |
|-------|----------|
| `stores/` | Pinia stores por entidade (ex: `useAccountStore`) |
| `components/` | Componentes específicos do módulo (Forms, DataTable, FilterBar) |
| `services/` | Serviços utilitários (`finance.services.ts`) |
| `composables/` | Hooks reutilizáveis (`useFinanceFilters`, `usePagination`, `useCrudToast`) |
| `types/` | TypeScript types (`finance.ts`) |
| `validations/` | Zod schemas por entidade |

## Pinia Stores

Cada módulo DEVE ter um Pinia store dedicado com o padrão:
- `isModalOpen`, `modalMode` ('create' | 'edit' | 'view'), `currentItem`, `deletingUid`
- Ações: `openCreateModal()`, `openEditModal(item)`, `openViewModal(item)`, `closeModal()`
- `closeModal()` DEVE ter delay de 200ms antes de resetar `currentItem` (animação do Dialog)

## Roteamento

- USAR EXCLUSIVAMENTE Wayfinder para chamadas de rotas. URLs em string pura são PROIBIDAS.
- Importar de `@/actions/App/Domain/{Entity}/Controllers/{Controller}`.
- Usar `.url()` para gerar URLs dinâmicas com parâmetros.
- Navegação entre páginas via componente `<Link>` do Inertia.

## Formulários

- Formulários DEVEM usar `ValidatedInertiaForm` + `ValidatedField` para integração vee-validate + zod + Inertia.
- Cada módulo DEVE ter um Zod schema em `modules/finance/validations/`.
- Submissão via Inertia router (`router.post`, `router.put`, `router.delete`).
- Formulários de módulo DEVEM ser reutilizáveis para create, edit e view (via props `item?` e `readonly?`).

## Notificações

- Toasts DEVEM usar `vue-sonner` via composable `useCrudToast`.
- Mensagens padronizadas: "{entidade} criado(a)/atualizado(a)/excluído(a) com sucesso!"
- Erros do backend DEVEM ser exibidos via toast com mensagem retornada ou fallback genérico.

## Padrão CRUD (Modal-Based)

- Todas as operações CRUD são centralizadas na página Index via modais.
- Criação/edição: `ModalDialog` com formulário reutilizável do módulo.
- Exclusão: `DeleteConfirmPopover` inline na tabela com confirmação.
- NÃO existem páginas Create/Edit separadas.

## Páginas Inertia

- Páginas ficam em `resources/js/pages/finance/{entity}/`.
- Cada página Index DEVE ter: `PageHeader`, `DataTable`, `FilterBar`, `ModalDialog`, formulário do módulo.
- Props DEVEM ser tipadas com `defineProps<T>()`.
- Breadcrumbs DEVEM ser definidos em cada página.
