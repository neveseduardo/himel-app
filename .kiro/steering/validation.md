---
inclusion: fileMatch
fileMatchPattern: "app/Domain/**/Requests/*.php,resources/js/modules/finance/validations/*.ts"
priority: 45
---

# Validação de Dados — Himel App

> Regras de validação para backend (FormRequest) e frontend (Vee-Validate + Zod).

## Princípio: Validação Dupla Obrigatória

Toda operação de escrita DEVE ter validação em duas camadas:
1. **Frontend (UX):** Vee-Validate + Zod — feedback imediato ao usuário
2. **Backend (Segurança):** FormRequest do Laravel — fonte de verdade

Ambas DEVEM existir. A validação frontend NUNCA substitui a backend.

## Backend — FormRequests

- Arquivo: `app/Domain/{Entity}/Requests/Store{Entity}Request.php` e `Update{Entity}Request.php`
- Mensagens de erro DEVEM ser em Português (pt-BR)
- Método `authorize()` DEVE verificar permissão do usuário (ou delegar para Policy)
- Regras DEVEM ser explícitas e completas
- DEVE validar ownership de recursos referenciados

### Exemplo

```php
class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'financial_account_uid' => ['required', 'uuid', 'exists:financial_accounts,uid'],
            'financial_category_uid' => ['required', 'uuid', 'exists:financial_categories,uid'],
            'direction' => ['required', Rule::in(['INFLOW', 'OUTFLOW'])],
            'occurred_at' => ['required', 'date'],
            'period_uid' => ['nullable', 'uuid', 'exists:financial_periods,uid'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
        ];
    }
}
```

## Frontend — Zod Schemas

- Arquivo: `resources/js/modules/finance/validations/{entity}-schema.ts`
- Schema DEVE espelhar as regras do FormRequest
- Mensagens de erro DEVEM ser em Português (pt-BR)
- Tipos DEVEM ser inferidos do schema via `z.infer<typeof schema>`

### Exemplo

```typescript
import { z } from 'zod'

export const transactionSchema = z.object({
  amount: z.number({ required_error: 'O valor é obrigatório.' }).min(0.01, 'O valor deve ser maior que zero.'),
  financial_account_uid: z.string({ required_error: 'A conta é obrigatória.' }).uuid(),
  financial_category_uid: z.string({ required_error: 'A categoria é obrigatória.' }).uuid(),
  direction: z.enum(['INFLOW', 'OUTFLOW'], { required_error: 'A direção é obrigatória.' }),
  occurred_at: z.string({ required_error: 'A data é obrigatória.' }),
  period_uid: z.string().uuid().nullable().optional(),
})

export type TransactionFormData = z.infer<typeof transactionSchema>
```

## Fluxo de Validação

1. Usuário digita → `ValidatedField` valida campo individual via Zod
2. Usuário submete → `ValidatedInertiaForm` valida schema completo
3. Se Zod falha → erros inline nos campos, SEM request HTTP
4. Se Zod passa → Inertia envia request ao backend
5. Se backend retorna 422 → erros mapeados para campos via `setErrors`
