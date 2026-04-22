# Documento de Requisitos — Separação de Entradas e Saídas em Transações

## Introdução

O módulo de transações atualmente trata entradas (INFLOW) e saídas (OUTFLOW) de forma idêntica, exigindo os mesmos campos obrigatórios para ambas as direções. Isso gera uma experiência ruim para o usuário ao cadastrar entradas, pois campos como data de vencimento, categoria, status e data de pagamento são irrelevantes nesse contexto. Esta feature diferencia o fluxo de criação e validação de transações com base na direção, simplificando entradas e mantendo o fluxo completo para saídas. Além disso, garante que entradas atualizem corretamente o saldo da conta selecionada (reservando o valor imediatamente), e que saídas só afetem o saldo quando marcadas como pagas, com validação de saldo suficiente. A interface é separada em componentes distintos por direção para manter a clareza e evitar lógica condicional excessiva em um único componente.

## Glossário

- **Sistema_Transação**: Módulo responsável por criar, atualizar, excluir e listar transações financeiras (backend + frontend).
- **Formulário_Saída**: Componente Vue existente (`TransactionForm.vue`) que renderiza o formulário de criação/edição de transações de saída (OUTFLOW).
- **Formulário_Entrada**: Novo componente Vue (`InflowTransactionForm.vue`) que renderiza o formulário de criação/edição de transações de entrada (INFLOW).
- **Botão_Nova_Transação**: Componente dropdown que substitui o botão simples "Criar", oferecendo as opções "Entrada" e "Saída".
- **Validador_Backend**: Conjunto de Form Requests Laravel (`StoreTransactionRequest`, `UpdateTransactionRequest`) que validam os dados recebidos do frontend.
- **Validador_Frontend**: Schema Zod (`transaction-schema.ts`) que valida os dados no lado do cliente antes do envio.
- **Serviço_Transação**: Classe `TransactionService` que contém a lógica de negócio para operações de transação.
- **INFLOW**: Direção de transação que representa uma entrada de dinheiro (receita). O saldo da conta é creditado imediatamente na criação para reservar o valor.
- **OUTFLOW**: Direção de transação que representa uma saída de dinheiro (despesa). O saldo da conta só é debitado quando o status muda para PAID.
- **Conta**: Model `Account` que representa uma conta bancária/financeira do usuário, com saldo rastreável.
- **Módulo_Transferência**: Módulo existente (`Transfer`) que permite mover dinheiro entre contas do usuário.

## Requisitos


### Requisito 1: Validação condicional de campos no backend por direção

**User Story:** Como usuário, eu quero que o sistema exija apenas os campos relevantes para cada tipo de transação, para que eu possa cadastrar entradas sem preencher campos desnecessários.

#### Critérios de Aceitação

1. WHEN a direção da transação é OUTFLOW, THE Validador_Backend SHALL exigir os campos `account_uid`, `category_uid`, `amount`, `direction`, `status`, `source` e `occurred_at` como obrigatórios.
2. WHEN a direção da transação é INFLOW, THE Validador_Backend SHALL exigir apenas os campos `account_uid`, `amount`, `direction` e `occurred_at` como obrigatórios.
3. WHEN a direção da transação é INFLOW, THE Validador_Backend SHALL aceitar os campos `category_uid`, `status`, `source`, `due_date` e `paid_at` como opcionais (nullable).
4. WHEN a direção da transação é INFLOW, THE Validador_Backend SHALL definir o valor padrão de `status` como `PAID` caso o campo não seja enviado.
5. WHEN a direção da transação é INFLOW, THE Validador_Backend SHALL definir o valor padrão de `source` como `MANUAL` caso o campo não seja enviado.
6. WHEN a direção da transação é OUTFLOW e o campo `category_uid` não é enviado, THE Validador_Backend SHALL retornar um erro de validação informando que a categoria é obrigatória.
7. WHEN a direção da transação é OUTFLOW e o campo `status` não é enviado, THE Validador_Backend SHALL retornar um erro de validação informando que o status é obrigatório.


### Requisito 2: Validação condicional de campos no frontend por direção

**User Story:** Como usuário, eu quero que a validação do formulário no navegador reflita as mesmas regras do backend, para que eu receba feedback imediato sobre campos obrigatórios.

#### Critérios de Aceitação

1. WHEN a direção selecionada é OUTFLOW, THE Validador_Frontend SHALL exigir os campos `account_uid`, `category_uid`, `amount`, `status` e `occurred_at` como obrigatórios.
2. WHEN a direção selecionada é INFLOW, THE Validador_Frontend SHALL exigir apenas os campos `account_uid`, `amount` e `occurred_at` como obrigatórios.
3. WHEN a direção selecionada é INFLOW, THE Validador_Frontend SHALL tratar os campos `category_uid`, `status`, `due_date` e `paid_at` como opcionais.


### Requisito 3: Componentes de interface separados por direção

**User Story:** Como usuário, eu quero que entradas e saídas tenham formulários separados, para que a interface seja limpa e cada componente tenha apenas a lógica relevante à sua direção.

#### Critérios de Aceitação

1. THE Botão_Nova_Transação SHALL exibir um dropdown com duas opções: "Entrada" e "Saída".
2. WHEN o usuário seleciona a opção "Entrada" no dropdown, THE Sistema_Transação SHALL abrir o Formulário_Entrada em um dialog dedicado.
3. WHEN o usuário seleciona a opção "Saída" no dropdown, THE Sistema_Transação SHALL abrir o Formulário_Saída (componente `TransactionForm.vue` existente) em um dialog dedicado.
4. THE Formulário_Entrada SHALL exibir os campos: conta, valor, descrição e data de ocorrência.
5. THE Formulário_Entrada SHALL definir automaticamente a direção como INFLOW sem exibir o campo de seleção de direção ao usuário.
6. THE Formulário_Saída SHALL continuar exibindo todos os campos existentes: conta, categoria, valor, direção, status, descrição, data de ocorrência, data de vencimento e data de pagamento.
7. THE Formulário_Entrada SHALL implementar operações de criação, edição e exclusão de transações INFLOW de forma independente do Formulário_Saída.
8. WHILE o usuário está editando uma transação INFLOW existente, THE Sistema_Transação SHALL abrir o Formulário_Entrada com os dados da transação carregados.
9. WHILE o usuário está editando uma transação OUTFLOW existente, THE Sistema_Transação SHALL abrir o Formulário_Saída com os dados da transação carregados.


### Requisito 4: Conta obrigatória e reserva de saldo para entradas

**User Story:** Como usuário, eu quero que ao cadastrar uma entrada o saldo da conta seja creditado imediatamente, para que o sistema reserve esse valor e eu saiba de onde o dinheiro veio quando for pagar uma saída.

#### Critérios de Aceitação

1. WHEN uma transação INFLOW é criada, THE Serviço_Transação SHALL creditar (somar) o valor da transação no saldo da conta selecionada imediatamente, independentemente do status.
2. WHEN uma transação INFLOW é excluída, THE Serviço_Transação SHALL debitar (subtrair) o valor da transação do saldo da conta associada.
3. WHEN uma transação INFLOW é atualizada e o valor muda, THE Serviço_Transação SHALL ajustar o saldo da conta refletindo a diferença entre o valor antigo e o novo.
4. WHEN uma transação INFLOW é criada sem o campo `status`, THE Serviço_Transação SHALL tratar a transação como `PAID` e creditar o saldo da conta imediatamente.
5. IF a conta selecionada para uma transação INFLOW não pertence ao usuário autenticado, THEN THE Validador_Backend SHALL retornar um erro de validação informando que a conta é inválida.


### Requisito 5: Consistência na atualização de saldo para saídas

**User Story:** Como usuário, eu quero que o saldo da conta só seja debitado quando eu marcar uma saída como paga, para que transações pendentes não afetem meu saldo disponível.

#### Critérios de Aceitação

1. WHEN uma transação OUTFLOW é criada com status PENDING, THE Serviço_Transação SHALL manter o saldo da conta inalterado.
2. WHEN uma transação OUTFLOW tem o status alterado para PAID, THE Serviço_Transação SHALL debitar (subtrair) o valor da transação do saldo da conta associada.
3. WHEN uma transação OUTFLOW com status PAID tem o status alterado para PENDING, THE Serviço_Transação SHALL creditar (somar) o valor de volta ao saldo da conta associada.
4. WHEN uma transação OUTFLOW com status PAID é excluída, THE Serviço_Transação SHALL creditar (somar) o valor de volta ao saldo da conta associada.
5. WHEN uma transação OUTFLOW com status PENDING é excluída, THE Serviço_Transação SHALL manter o saldo da conta inalterado.


### Requisito 6: Validação de atualização condicional por direção

**User Story:** Como usuário, eu quero que a edição de transações respeite as mesmas regras condicionais de validação, para que eu tenha uma experiência consistente.

#### Critérios de Aceitação

1. WHEN uma transação INFLOW é atualizada, THE Validador_Backend SHALL aplicar as mesmas regras de campos opcionais definidas no Requisito 1 para INFLOW.
2. WHEN uma transação OUTFLOW é atualizada, THE Validador_Backend SHALL aplicar as mesmas regras de campos obrigatórios definidas no Requisito 1 para OUTFLOW.
3. WHEN a direção de uma transação é alterada de INFLOW para OUTFLOW durante a edição, THE Validador_Backend SHALL exigir os campos obrigatórios de OUTFLOW (`category_uid`, `status`).
4. WHEN a direção de uma transação é alterada de OUTFLOW para INFLOW durante a edição, THE Validador_Backend SHALL tornar opcionais os campos `category_uid`, `status`, `due_date` e `paid_at`.


### Requisito 7: Validação de saldo suficiente ao pagar saída

**User Story:** Como usuário, eu quero que o sistema verifique se a conta tem saldo suficiente antes de marcar uma saída como paga, para que eu não fique com saldo negativo sem perceber.

#### Critérios de Aceitação

1. WHEN uma transação OUTFLOW tem o status alterado para PAID, THE Serviço_Transação SHALL verificar se o saldo da conta associada é maior ou igual ao valor da transação.
2. IF o saldo da conta associada é menor que o valor da transação OUTFLOW ao marcar como PAID, THEN THE Serviço_Transação SHALL retornar um erro informando que a conta não possui saldo suficiente.
3. WHEN o erro de saldo insuficiente é retornado, THE Sistema_Transação SHALL exibir uma mensagem ao usuário sugerindo que uma transferência entre contas pode ser realizada pelo Módulo_Transferência para disponibilizar o saldo necessário.
4. WHEN uma transação OUTFLOW é criada diretamente com status PAID, THE Serviço_Transação SHALL aplicar a mesma validação de saldo suficiente antes de debitar a conta.


### Requisito 8: Botão dropdown de nova transação com opções de direção

**User Story:** Como usuário, eu quero que o botão de criar transação ofereça opções separadas para entrada e saída, para que eu seja direcionado ao formulário correto sem precisar selecionar a direção dentro do formulário.

#### Critérios de Aceitação

1. THE Botão_Nova_Transação SHALL substituir o botão simples "Criar" na página de listagem de transações.
2. THE Botão_Nova_Transação SHALL exibir um ícone de "+" e o texto "Nova Transação" como label principal.
3. WHEN o usuário clica no Botão_Nova_Transação, THE Botão_Nova_Transação SHALL exibir um menu dropdown com as opções "Entrada" e "Saída".
4. WHEN o usuário seleciona "Entrada" no dropdown, THE Sistema_Transação SHALL abrir o dialog do Formulário_Entrada.
5. WHEN o usuário seleciona "Saída" no dropdown, THE Sistema_Transação SHALL abrir o dialog do Formulário_Saída.
6. THE Botão_Nova_Transação SHALL ser implementado na página de listagem de transações (`transactions/Index.vue`) e na página de detalhes do período (`periods/Show.vue`).
