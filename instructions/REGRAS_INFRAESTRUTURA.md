# 📜 PROTOCOLO DE INFRAESTRUTURA E DESENVOLVIMENTO (LARAVEL 12 + INERTIA)

Este documento define as regras inegociáveis para o desenvolvimento do SaaS. O Agente de IA deve ler estas regras antes de cada tarefa e garantir 100% de conformidade.

---

## 1. STACK TECNOLÓGICA E FERRAMENTAL
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 (Sintaxe: `<script setup lang="ts">`)
- **Comunicação:** Inertia.js (Bridge única entre BE e FE)
- **Database:** MySQL 8.0
- **Estilização:** Tailwind CSS 4 + Shadcn/Vue
- **Qualidade:** ESLint 9 + Laravel Boost (Skills)
- **Rotas:** Wayfinder (Gerador de rotas para o frontend)

---

## 2. REGRAS DE BACKEND (ARQUITETURA E PERSISTÊNCIA)

### 🆔 Identificadores e Chaves (UUID v4)
- **Primary Keys:** É PROIBIDO o uso de IDs incrementais (`int`). Todos os models (incluindo `User.php`) devem usar **UUID v4**.
- **Foreign Keys:** Devem seguir o padrão `{model}_uid`. (Ex: `user_uid`).
- **Configuração de Model:** - Definir `$incrementing = false;`
    - Definir `$keyType = 'string';`

### 🏗️ Camada de Serviço (Service Layer)
- **Lógica de Negócio:** Controllers não devem conter lógica. Toda a regra de negócio deve residir em `App\Services`.
- **Abstração:** Toda `Service` deve implementar uma `Interface`.
- **Injeção de Dependência:** O Controller deve injetar a `Interface` da Service no construtor.

### 🚦 Controllers e Segurança de Dados
- **Transacionalidade:** Operações de escrita (`POST`, `PUT`, `DELETE`) DEVEM ser encapsuladas em `DB::transaction()`.
- **Tratamento de Erros:** Obrigatório o uso de `try-catch` em métodos de escrita.
- **Observabilidade:** O bloco `catch` deve registrar logs detalhados via `Log::error()`.
- **Validação:** Requests devem utilizar `FormRequests` com mensagens de erro em **Português (pt-BR)**.

---

## 3. REGRAS DE FRONTEND (VUE 3 & MODULARIZAÇÃO)

### 🧱 Componentização e Design
- **UI Framework:** Utilizar EXCLUSIVAMENTE componentes do **Shadcn/Vue** alocados no projeto. Não criar componentes de UI básicos (Botões, Inputs) se houver equivalente no Shadcn. Caso não houver, criar um `components/ui` com o componente ou adicionar o componente via cmd. Ex: `npx shadcn-vue@latest add switch`.
- **Tipagem:** Proibido o uso de `any`. Todo o código Vue deve ser em TypeScript.

### 📂 Arquitetura de Módulos (Modular Pattern)
Toda lógica de frontend deve ser alocada dentro de `resources/js/Modules/` seguindo a estrutura:
- `Modules/store/**.ts` (Pinia/Estado)
- `Modules/components/**.vue` (Componentes específicos)
- `Modules/services/**.services.ts` (Serviços de frontend)
- `Modules/composables/**.ts` (Hooks reutilizáveis)

### 🛣️ Roteamento e Formulários
- **Wayfinder:** Usar exclusivamente o arquivo `index` gerado pelo Wayfinder para chamadas de rotas. URLs em string pura são proibidas.
- **Navegação:** Utilizar o componente `<Link>` do Inertia.
- **Formulários:** Validação obrigatória com **Vee-Validate + Zod**. Submissão via `useForm` do Inertia.

---

## 4. CHECKLIST DE VALIDAÇÃO (DEFINITION OF DONE)

Antes de entregar qualquer alteração, o agente deve executar e validar com sucesso:
1.  **Linter:** `npm run lint`
2.  **Types:** `npx vue-tsc --noEmit`
3.  **Build:** `npm run build`

---

## 5. DIRETRIZES ANTI-ALUCINAÇÃO
- **Consistência:** Verifique se o código é compatível com Tailwind 4 (não use arquivos `tailwind.config.js` se a versão 4 for via CSS).
- **Tratamento de Exceções:** Nunca ignore um erro no backend. Retorne sempre uma resposta via Inertia com a mensagem de erro tratada.

## 6. Mapeamento de Models & Relacionamentos (Domínio Financeiro)

O agente deve gerar as Models seguindo rigorosamente este mapeamento. Todas as chaves estrangeiras foram convertidas para o padrão `_uid`.

### 🗄️ Lista de Models Obrigatórias:

1.  **FinancialAccount**
    - Chave: `uid` (Primary)
    - Relacionamentos: HasMany `FinancialTransaction`, HasMany `FinancialTransfer`.
    - Enums: `type` (CHECKING, SAVINGS, CASH, OTHER).

2.  **FinancialCategory**
    - Chave: `uid` (Primary)
    - Relacionamentos: HasMany `FinancialTransaction`, HasMany `FinancialFixedExpense`.
    - Enums: `direction` (INFLOW, OUTFLOW).

3.  **FinancialTransaction**
    - Chave: `uid` (Primary)
    - Chaves Estrangeiras: `financial_account_uid`, `financial_category_uid`.
    - Enums: `direction` (INFLOW, OUTFLOW), `source` (MANUAL, CREDIT_CARD, FIXED, TRANSFER), `status` (PENDING, PAID, OVERDUE).

4.  **FinancialTransfer**
    - Chave: `uid` (Primary)
    - Chaves Estrangeiras: `from_account_uid`, `to_account_uid`.

5.  **FinancialFixedExpense**
    - Chave: `uid` (Primary)
    - Chave Estrangeira: `financial_category_uid`.

6.  **FinancialCreditCard**
    - Chave: `uid` (Primary)
    - Relacionamentos: HasMany `FinancialCreditCardCharge`.
    - Enums: `card_type` (PHYSICAL, VIRTUAL).

7.  **FinancialCreditCardCharge**
    - Chave: `uid` (Primary)
    - Chave Estrangeira: `credit_card_uid`.
    - Relacionamentos: HasMany `FinancialCreditCardInstallment`.

8.  **FinancialCreditCardInstallment**
    - Chave: `uid` (Primary)
    - Chaves Estrangeiras: `credit_card_charge_uid`, `financial_transaction_uid`.

9.  **FinancialPeriod**
    - Chave: `uid` (Primary)
    - Unique: `(user_uid, month, year)`.

### ⚠️ Regra de Implementação Eloquent:
Sempre que o agente criar uma Model, ele deve incluir:
- `protected $primaryKey = 'uid';`
- `public $incrementing = false;`
- `protected $keyType = 'string';`
- Uma Trait de boot para gerar o UUID automaticamente (ex: `HasUids`).