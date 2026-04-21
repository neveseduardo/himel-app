# Documento de Requisitos — E2E CreditCardCharge (Incremento)

## Introdução

Este documento descreve dois novos requisitos para o módulo CreditCardCharge: (1) correção de sincronização do diálogo modal ao fechar via X/ESC, e (2) adição do campo `purchase_date` em toda a stack (backend, frontend, E2E).

## Glossário

- **Sistema**: Aplicação Himel App (Laravel + Inertia + Vue)
- **CreditCardCharge_Index**: Página Vue `resources/js/pages/finance/credit-card-charges/Index.vue`
- **ModalDialog**: Componente `resources/js/domain/Shared/components/ui/modal/ModalDialog.vue`
- **CreditCardChargeStore**: Store Pinia `useCreditCardChargeStore`
- **CreditCardChargeService**: Serviço backend `CreditCardChargeService.php`
- **CreditCardChargeForm**: Componente Vue do formulário de compra no cartão
- **DataTable**: Componente de tabela de dados
- **PageObject**: Classe Playwright `CreditCardChargePage.ts`
- **E2E_Spec**: Arquivo de testes Playwright `credit-card-charge.spec.ts`

## Requisitos

### Requisito 4: Sincronização do Estado do Modal ao Fechar via X/ESC

**User Story:** Como usuário, eu quero que o modal de compra no cartão reabra corretamente após ser fechado via botão X ou tecla ESC, para que eu não precise recarregar a página para criar uma nova compra.

#### Critérios de Aceitação

1. WHEN o ModalDialog emite `update:open` com valor `false`, THE CreditCardCharge_Index SHALL chamar `store.closeModal()` para sincronizar o estado do CreditCardChargeStore
2. WHEN o usuário fecha o modal via tecla ESC e clica em "Criar" novamente, THE CreditCardCharge_Index SHALL exibir o modal com título "Nova Compra"
3. WHEN o usuário fecha o modal via clique no overlay e clica em "Criar" novamente, THE CreditCardCharge_Index SHALL exibir o modal com título "Nova Compra"
4. THE PageObject SHALL fornecer métodos `closeDialogByEsc()` e `closeDialogByOverlay()` para testes de reabertura do diálogo
5. THE E2E_Spec SHALL conter um bloco "CreditCardCharge Dialog Reopen" com testes para reabertura via ESC e via overlay, seguindo o padrão do módulo CreditCard

### Requisito 5: Campo purchase_date na Compra do Cartão

**User Story:** Como usuário, eu quero registrar a data da compra ao criar uma compra no cartão, para que as parcelas sejam calculadas com base na data real da compra e não na data atual.

#### Critérios de Aceitação

1. THE Sistema SHALL armazenar o campo `purchase_date` (tipo date, nullable) na tabela `financial_credit_card_charges`
2. THE CreditCardCharge (Model) SHALL incluir `purchase_date` em `$fillable` e `$casts` (cast para `date`)
3. THE CreditCardChargeResource SHALL incluir `purchase_date` formatado como string `Y-m-d` na resposta da API
4. WHEN uma nova compra é criada, THE StoreCreditCardChargeRequest SHALL validar `purchase_date` como campo obrigatório do tipo date
5. WHEN o CreditCardChargeService cria parcelas, THE CreditCardChargeService SHALL calcular as datas de vencimento a partir de `purchase_date` em vez de `now()`
6. THE CreditCardCharge (tipo TypeScript) SHALL incluir `purchase_date: string` na interface
7. THE creditCardChargeSchema (Zod) SHALL validar `purchase_date` como string não vazia
8. THE CreditCardChargeForm SHALL exibir um campo de input tipo date para `purchase_date` com label "Data da Compra"
9. THE DataTable SHALL exibir a coluna `purchase_date` formatada no padrão brasileiro (dd/mm/aaaa)
10. THE FinancialCreditCardChargeFactory SHALL gerar `purchase_date` com data aleatória recente
11. THE E2eTestSeeder SHALL incluir `purchase_date` nos dados nomeados de CreditCardCharge
12. THE PageObject SHALL incluir `purchase_date` na interface `CreditCardChargeFormData` e no método `fillForm`
13. THE E2E_Spec SHALL incluir `purchase_date` em todos os testes de criação e verificar a coluna na DataTable
