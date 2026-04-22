# Plano de Implementação: Separação de Entradas e Saídas em Transações

## Visão Geral

Implementação incremental da diferenciação entre transações INFLOW e OUTFLOW. Backend primeiro (exception, validação, lógica de saldo), depois frontend (schemas, formulários, store, páginas), e por fim testes.

## Tarefas

- [x] 1. Criar InsufficientBalanceException e modificar TransactionService
  - [x] 1.1 Criar classe InsufficientBalanceException
    - Criar `app/Domain/Transaction/Exceptions/InsufficientBalanceException.php`
    - Recebe `Account` e `float $requiredAmount` no construtor
    - Mensagem formatada com nome da conta, valor necessário e disponível, sugestão de transferência
    - _Requisitos: 7.2, 7.3_

  - [x] 1.2 Modificar TransactionService.create() com lógica de saldo por direção
    - Validar ownership da conta (`account_uid` pertence ao `userUid`) com `firstOrFail()`
    - Tornar `category_uid` nullable no create (usar `$data['category_uid'] ?? null`)
    - Tornar `status` e `source` com defaults (`$data['status'] ?? 'PAID'`, `$data['source'] ?? 'MANUAL'`)
    - INFLOW: creditar saldo imediatamente, independente do status
    - OUTFLOW + PAID: verificar saldo suficiente via `InsufficientBalanceException`, depois debitar
    - OUTFLOW + PENDING/OVERDUE: saldo inalterado
    - Validar compatibilidade de direção da categoria (quando informada)
    - _Requisitos: 4.1, 4.4, 4.5, 5.1, 7.1, 7.4_

  - [x] 1.3 Modificar TransactionService.update() com lógica de saldo por direção
    - INFLOW: ajustar saldo pela diferença de valor (novo - antigo)
    - OUTFLOW: tratar transições PENDING→PAID (debitar com check de saldo) e PAID→PENDING (creditar de volta)
    - OUTFLOW: ajustar saldo pela diferença de valor quando status permanece PAID
    - _Requisitos: 4.3, 5.2, 5.3, 7.1_

  - [x] 1.4 Modificar TransactionService.delete() com lógica de saldo por direção
    - INFLOW: sempre reverter saldo (debitar o valor de volta)
    - OUTFLOW + PAID: reverter saldo (creditar o valor de volta)
    - OUTFLOW + PENDING: saldo inalterado
    - _Requisitos: 4.2, 5.4, 5.5_

- [ ] 2. Modificar Form Requests com validação condicional por direção
  - [x] 2.1 Modificar StoreTransactionRequest
    - Adicionar `prepareForValidation()`: se `direction=INFLOW`, aplicar `mergeIfMissing` com `status=PAID` e `source=MANUAL`
    - Alterar regras: `category_uid`, `status`, `source` usam `required_if:direction,OUTFLOW` + `nullable`
    - Manter `account_uid`, `amount`, `direction`, `occurred_at` como `required`
    - Atualizar mensagens de validação para refletir condicionalidade
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

  - [x] 2.2 Modificar UpdateTransactionRequest
    - Adicionar `prepareForValidation()`: detectar direção do payload ou da transação existente via `Transaction::where('uid', $this->route('uid'))`
    - Se `direction=INFLOW`, aplicar `mergeIfMissing` com `status=PAID` e `source=MANUAL`
    - Alterar regras: `category_uid`, `status`, `source` usam `required_if:direction,OUTFLOW` + `nullable`
    - Manter campos com `sometimes` para update parcial
    - _Requisitos: 6.1, 6.2, 6.3, 6.4_

- [x] 3. Modificar TransactionPageController para tratar InsufficientBalanceException
  - Capturar `InsufficientBalanceException` nos métodos `store` e `update`
  - Retornar `back()->withErrors(['amount' => $e->getMessage()])` para que o erro apareça no formulário
  - _Requisitos: 7.2, 7.3_

- [x] 4. Checkpoint — Verificar backend
  - Garantir que todos os testes existentes passam, perguntar ao usuário se houver dúvidas.

- [x] 5. Criar schema e formulário INFLOW no frontend
  - [x] 5.1 Criar inflow-transaction-schema.ts
    - Criar `resources/js/domain/Transaction/validations/inflow-transaction-schema.ts`
    - Schema Zod com campos: `account_uid` (uuid, obrigatório), `amount` (number, positivo), `description` (string, nullable, opcional), `occurred_at` (string, obrigatório), `direction` (literal `'INFLOW'`, default `'INFLOW'`)
    - Exportar tipo `InflowTransactionFormData`
    - _Requisitos: 2.2, 2.3_

  - [x] 5.2 Atualizar transaction-schema.ts para foco em OUTFLOW
    - Adicionar default `'OUTFLOW'` ao campo `direction`
    - Manter todos os campos obrigatórios existentes (`category_uid`, `status`, etc.)
    - _Requisito: 2.1_

  - [x] 5.3 Criar InflowTransactionForm.vue
    - Criar `resources/js/domain/Transaction/components/InflowTransactionForm.vue`
    - Campos visíveis: conta (Select), valor (Input number), descrição (Input text, opcional), data (Input date)
    - Campo hidden: `direction = 'INFLOW'`
    - Usar `ValidatedInertiaForm` com `inflowTransactionSchema`
    - Suportar criação e edição (prop `item` opcional)
    - Suportar `periodUid` e `periodDate` para criação via período
    - Props: `item?`, `accounts`, `periodUid?`, `periodDate?`
    - Emits: `success`, `cancel`
    - _Requisitos: 3.4, 3.5, 3.7_

  - [x] 5.4 Atualizar TransactionForm.vue para foco em OUTFLOW
    - Remover o Select de direção do template
    - Hardcodar `direction: 'OUTFLOW'` nos `initialValues`
    - Manter todos os campos existentes (conta, categoria, valor, status, descrição, datas)
    - _Requisito: 3.6_

- [x] 6. Atualizar store e páginas com modais separados por direção
  - [x] 6.1 Atualizar useTransactionStore com modais separados
    - Adicionar refs: `inflowModalOpen`, `outflowModalOpen`
    - Adicionar funções: `openCreateInflowModal()`, `openCreateOutflowModal()`, `closeInflowModal()`, `closeOutflowModal()`
    - Modificar `openEditModal(item)`: detectar `item.direction` e abrir modal correto
    - Remover ou deprecar `isModalOpen` e `openCreateModal()` genéricos
    - _Requisitos: 3.2, 3.3, 3.8, 3.9_

  - [x] 6.2 Atualizar transactions/Index.vue com dropdown e dois dialogs
    - Substituir botão "Criar" por `DropdownMenu` com opções "Entrada" e "Saída"
    - Renderizar dois `ModalDialog`: um para `InflowTransactionForm`, outro para `TransactionForm`
    - Conectar watchers aos novos refs do store (`inflowModalOpen`, `outflowModalOpen`)
    - Atualizar `modalTitle` para refletir direção
    - Manter funcionalidade de view/edit/delete existente, roteando edição pelo formulário correto
    - _Requisitos: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [x] 6.3 Atualizar periods/Show.vue com dropdown e dois dialogs
    - Substituir botão "Nova Transação" por `DropdownMenu` com opções "Entrada" e "Saída"
    - Renderizar dois `ModalDialog`: um para `InflowTransactionForm` (com `periodUid` e `periodDate`), outro para `TransactionForm`
    - Gerenciar estado dos modais localmente (refs `inflowModalOpen`, `outflowModalOpen`)
    - _Requisito: 8.6_

- [ ] 7. Checkpoint — Verificar frontend
  - Garantir que todos os testes existentes passam, perguntar ao usuário se houver dúvidas.

- [ ] 8. Testes unitários PHPUnit para validação condicional
  - [ ] 8.1 Testes para StoreTransactionRequest
    - Testar que OUTFLOW rejeita payload sem `category_uid`, `status`, `source`
    - Testar que INFLOW aceita payload com apenas `account_uid`, `amount`, `direction`, `occurred_at`
    - Testar que `prepareForValidation()` aplica defaults `status=PAID` e `source=MANUAL` para INFLOW
    - Testar que INFLOW aceita campos opcionais quando presentes
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

  - [ ] 8.2 Testes para UpdateTransactionRequest
    - Testar validação condicional na atualização para INFLOW e OUTFLOW
    - Testar detecção de direção da transação existente quando não enviada no payload
    - Testar `prepareForValidation()` aplica defaults para INFLOW
    - _Requisitos: 6.1, 6.2, 6.3, 6.4_

- [ ] 9. Testes unitários PHPUnit para lógica de saldo
  - [ ] 9.1 Testes para TransactionService.create()
    - Testar INFLOW credita saldo imediatamente
    - Testar OUTFLOW PAID debita saldo
    - Testar OUTFLOW PENDING não altera saldo
    - Testar InsufficientBalanceException quando saldo insuficiente para OUTFLOW PAID
    - Testar rejeição quando conta não pertence ao usuário
    - _Requisitos: 4.1, 4.5, 5.1, 7.1, 7.4_

  - [ ] 9.2 Testes para TransactionService.update()
    - Testar INFLOW ajusta saldo pela diferença de valor
    - Testar OUTFLOW transição PENDING→PAID debita saldo
    - Testar OUTFLOW transição PAID→PENDING credita saldo de volta
    - Testar InsufficientBalanceException na transição PENDING→PAID com saldo insuficiente
    - _Requisitos: 4.3, 5.2, 5.3, 7.1_

  - [ ] 9.3 Testes para TransactionService.delete()
    - Testar INFLOW reverte saldo (debita de volta)
    - Testar OUTFLOW PAID reverte saldo (credita de volta)
    - Testar OUTFLOW PENDING não altera saldo
    - _Requisitos: 4.2, 5.4, 5.5_

- [ ] 10. Testes de propriedade (Property-Based Testing) — Backend
  - [ ]* 10.1 Propriedade 1: Validação OUTFLOW exige todos os campos obrigatórios
    - **Propriedade 1: Validação OUTFLOW exige todos os campos obrigatórios**
    - Gerar payloads OUTFLOW aleatórios removendo campos obrigatórios; validação MUST rejeitar
    - Gerar payloads OUTFLOW completos com valores válidos; validação MUST aceitar
    - Mínimo 100 iterações com factories do Laravel
    - **Valida: Requisitos 1.1, 1.6, 1.7**

  - [ ]* 10.2 Propriedade 2: Validação INFLOW exige apenas campos mínimos
    - **Propriedade 2: Validação INFLOW exige apenas campos mínimos**
    - Gerar payloads INFLOW com apenas `account_uid`, `amount`, `direction`, `occurred_at`; validação MUST aceitar
    - Gerar payloads INFLOW sem algum dos campos mínimos; validação MUST rejeitar
    - Mínimo 100 iterações
    - **Valida: Requisitos 1.2, 1.3**

  - [ ]* 10.3 Propriedade 5: Defaults de INFLOW são aplicados automaticamente
    - **Propriedade 5: Defaults de INFLOW são aplicados automaticamente**
    - Gerar payloads INFLOW sem `status` e `source`; após `prepareForValidation()`, MUST conter `status=PAID` e `source=MANUAL`
    - Mínimo 100 iterações
    - **Valida: Requisitos 1.4, 1.5**

  - [ ]* 10.4 Propriedade 6: Round-trip de saldo para INFLOW (criar e excluir)
    - **Propriedade 6: Round-trip de saldo para INFLOW (criar e excluir)**
    - Para saldo inicial `B` e valor aleatório, após criar INFLOW saldo = `B + amount`, após excluir saldo = `B`
    - Mínimo 100 iterações
    - **Valida: Requisitos 4.1, 4.2**

  - [ ]* 10.5 Propriedade 7: Atualização de INFLOW ajusta saldo pela diferença
    - **Propriedade 7: Atualização de INFLOW ajusta saldo pela diferença**
    - Para INFLOW existente com valor `V1`, atualizar para `V2`; saldo final = saldo antes + (V2 - V1)
    - Mínimo 100 iterações
    - **Valida: Requisito 4.3**

  - [ ]* 10.6 Propriedade 8: Validação de ownership da conta
    - **Propriedade 8: Validação de ownership da conta**
    - Gerar `account_uid` de outro usuário; criação MUST ser rejeitada
    - Mínimo 100 iterações
    - **Valida: Requisito 4.5**

  - [ ]* 10.7 Propriedade 9: OUTFLOW PENDING não afeta saldo (criar e excluir)
    - **Propriedade 9: OUTFLOW PENDING não afeta saldo (criar e excluir)**
    - Para OUTFLOW PENDING, saldo MUST permanecer inalterado na criação e exclusão
    - Mínimo 100 iterações
    - **Valida: Requisitos 5.1, 5.5**

  - [ ]* 10.8 Propriedade 10: Round-trip de status OUTFLOW (PENDING↔PAID)
    - **Propriedade 10: Round-trip de status OUTFLOW (PENDING↔PAID)**
    - Criar OUTFLOW PENDING (saldo = B), alterar para PAID (saldo = B - amount), retornar para PENDING (saldo = B)
    - Mínimo 100 iterações
    - **Valida: Requisitos 5.2, 5.3, 5.4**

  - [ ]* 10.9 Propriedade 11: Validação de atualização segue regras da direção
    - **Propriedade 11: Validação de atualização segue regras da direção**
    - Para transações existentes, regras de validação na atualização MUST corresponder à direção
    - Mínimo 100 iterações
    - **Valida: Requisitos 6.1, 6.2, 6.3, 6.4**

  - [ ]* 10.10 Propriedade 12: Verificação de saldo suficiente para OUTFLOW PAID
    - **Propriedade 12: Verificação de saldo suficiente para OUTFLOW PAID**
    - Para OUTFLOW sendo marcado como PAID, se saldo < amount MUST rejeitar; se saldo >= amount MUST aceitar e debitar
    - Mínimo 100 iterações
    - **Valida: Requisitos 7.1, 7.2, 7.4**

- [ ] 11. Testes de propriedade (Property-Based Testing) — Frontend (Zod schemas)
  - [ ]* 11.1 Propriedade 3: Validação frontend OUTFLOW exige campos obrigatórios
    - **Propriedade 3: Validação frontend OUTFLOW exige campos obrigatórios**
    - Gerar objetos de formulário OUTFLOW; schema Zod MUST rejeitar quando falta `account_uid`, `category_uid`, `amount`, `status` ou `occurred_at`
    - Mínimo 100 iterações com fast-check
    - **Valida: Requisito 2.1**

  - [ ]* 11.2 Propriedade 4: Validação frontend INFLOW exige apenas campos mínimos
    - **Propriedade 4: Validação frontend INFLOW exige apenas campos mínimos**
    - Gerar objetos de formulário INFLOW; schema Zod MUST aceitar com apenas `account_uid`, `amount`, `occurred_at` e MUST rejeitar quando qualquer um falta
    - Mínimo 100 iterações com fast-check
    - **Valida: Requisitos 2.2, 2.3**

- [ ] 12. Checkpoint final
  - Garantir que todos os testes passam, perguntar ao usuário se houver dúvidas.

## Notas

- Tarefas marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada tarefa referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental
- Testes de propriedade validam propriedades universais de corretude definidas no design
- Testes unitários validam exemplos específicos e edge cases
