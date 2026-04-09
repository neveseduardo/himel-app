# Documento de Requisitos — Gestão de Dados por Período

## Introdução

Este documento especifica os requisitos para a funcionalidade de gestão de dados por período no Himel App. A feature replica o fluxo de trabalho de planilhas onde cada mês era uma aba separada: o usuário visualiza dados financeiros filtrados por período (mês/ano) e pode inicializar um novo período carregando automaticamente despesas fixas ativas e parcelas de cartão de crédito pendentes como transações do mês.

Atualmente, o modelo `Period` existe apenas para agrupamento de dashboard, sem vínculo direto com transações. As transações são filtradas por intervalo de datas (`occurred_at`). Esta feature evolui o conceito de período para se tornar o eixo central de visualização e organização financeira do usuário.

## Glossário

- **Himel_App**: Aplicação SaaS de gestão financeira pessoal (backend Laravel 13 + frontend Vue 3 via Inertia.js).
- **Period**: Entidade que representa um período mensal (mês/ano) pertencente a um usuário, armazenada na tabela `financial_periods`.
- **Transaction**: Movimentação financeira vinculada a uma conta e categoria, armazenada na tabela `financial_transactions`.
- **FixedExpense**: Despesa fixa recorrente com `due_day`, `amount`, `category_uid` e flag `active`.
- **CreditCardInstallment**: Parcela de compra no cartão de crédito com `due_date`, `amount` e vínculo opcional a uma Transaction.
- **Period_Service**: Serviço responsável pela lógica de negócio de períodos (`PeriodService`).
- **Transaction_Service**: Serviço responsável pela lógica de negócio de transações financeiras (`TransactionService`).
- **Inicializacao_Periodo**: Ação que gera automaticamente transações pendentes a partir de despesas fixas ativas e parcelas de cartão de crédito não pagas para um determinado período.
- **Period_Page**: Página Vue do frontend que exibe os períodos e permite interação com a funcionalidade de inicialização.
- **Period_Show_Page**: Página Vue do frontend que exibe os detalhes de um período específico com suas transações.

## Requisitos

### Requisito 1: Vinculação de Transações a Períodos via Foreign Key

**User Story:** Como usuário, quero que minhas transações estejam vinculadas a um período específico, para que eu possa visualizar e gerenciar dados financeiros organizados por mês/ano.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir uma coluna `period_uid` (UUID, nullable) na tabela `financial_transactions` com foreign key referenciando `financial_periods.uid`.
2. WHEN uma Transaction for criada com `period_uid` preenchido, THE Transaction_Service SHALL persistir o vínculo com o Period correspondente.
3. WHEN uma Transaction for criada sem `period_uid`, THE Transaction_Service SHALL persistir a transação com `period_uid` como `null` para manter compatibilidade com transações existentes.
4. THE Transaction model SHALL possuir um relacionamento `belongsTo` com o Period model via `period_uid`.
5. THE Period model SHALL possuir um relacionamento `hasMany` com o Transaction model via `period_uid`.

---

### Requisito 2: Visualização de Transações Filtradas por Período

**User Story:** Como usuário, quero visualizar todas as transações de um período específico, para que eu tenha uma visão consolidada das movimentações daquele mês.

#### Critérios de Aceitação

1. WHEN o usuário acessar a página de detalhes de um Period, THE Period_Show_Page SHALL exibir todas as Transactions vinculadas àquele período.
2. THE Period_Show_Page SHALL exibir um resumo com o total de entradas (INFLOW), total de saídas (OUTFLOW) e saldo do período (entradas menos saídas), considerando apenas transações vinculadas ao período.
3. THE Transaction_Service SHALL possuir um método para listar transações filtradas por `period_uid` com suporte a paginação.
4. THE Period_Show_Page SHALL permitir filtrar as transações do período por status (`PENDING`, `PAID`, `OVERDUE`), direção (`INFLOW`, `OUTFLOW`) e fonte (`MANUAL`, `CREDIT_CARD`, `FIXED`, `TRANSFER`).
5. WHEN o período não possuir transações vinculadas, THE Period_Show_Page SHALL exibir uma mensagem indicando que o período está vazio e oferecer o botão de inicialização.

---

### Requisito 3: Inicialização de Período com Despesas Fixas

**User Story:** Como usuário, quero que ao inicializar um período, todas as minhas despesas fixas ativas sejam carregadas como transações pendentes, para que eu não precise cadastrá-las manualmente todo mês.

#### Critérios de Aceitação

1. WHEN o usuário acionar a Inicializacao_Periodo, THE Period_Service SHALL criar uma Transaction com status `PENDING`, source `FIXED` e direction `OUTFLOW` para cada FixedExpense ativa do usuário.
2. WHEN uma Transaction for gerada a partir de uma FixedExpense, THE Period_Service SHALL definir o `due_date` como o `due_day` da FixedExpense no mês/ano do período.
3. WHEN uma Transaction for gerada a partir de uma FixedExpense, THE Period_Service SHALL copiar o `amount`, `category_uid` e `name` (como `description`) da FixedExpense para a Transaction.
4. WHEN uma Transaction for gerada a partir de uma FixedExpense, THE Period_Service SHALL vincular a Transaction ao Period via `period_uid`.
5. WHEN uma Transaction for gerada a partir de uma FixedExpense, THE Period_Service SHALL preencher o `reference_id` da Transaction com o `uid` da FixedExpense de origem.
6. WHEN uma FixedExpense possuir `due_day` superior ao último dia do mês do período (ex: dia 31 em fevereiro), THE Period_Service SHALL ajustar o `due_date` para o último dia válido do mês.

---

### Requisito 4: Inicialização de Período com Parcelas de Cartão de Crédito

**User Story:** Como usuário, quero que ao inicializar um período, todas as parcelas de cartão de crédito não pagas que vencem naquele mês sejam carregadas, para que eu visualize todas as obrigações do mês.

#### Critérios de Aceitação

1. WHEN o usuário acionar a Inicializacao_Periodo, THE Period_Service SHALL identificar todas as CreditCardInstallments do usuário cujo `due_date` esteja dentro do mês/ano do período e que ainda não possuam `paid_at` preenchido.
2. WHEN uma CreditCardInstallment elegível já possuir uma Transaction vinculada (via `transaction_uid`), THE Period_Service SHALL vincular essa Transaction existente ao Period via `period_uid`, sem criar uma nova Transaction.
3. WHEN uma CreditCardInstallment elegível não possuir uma Transaction vinculada, THE Period_Service SHALL criar uma nova Transaction com status `PENDING`, source `CREDIT_CARD`, direction `OUTFLOW`, e vincular ao Period via `period_uid`.
4. WHEN uma Transaction for criada a partir de uma CreditCardInstallment, THE Period_Service SHALL preencher o `reference_id` com o `uid` da CreditCardInstallment e definir o `due_date` conforme o `due_date` da parcela.

---

### Requisito 5: Idempotência da Inicialização de Período

**User Story:** Como usuário, quero que a inicialização de período seja segura para executar múltiplas vezes, para que eu não gere transações duplicadas caso clique no botão mais de uma vez.

#### Critérios de Aceitação

1. WHEN a Inicializacao_Periodo for acionada para um Period que já possui Transactions com source `FIXED` vinculadas, THE Period_Service SHALL ignorar as FixedExpenses que já possuem Transaction correspondente (mesmo `reference_id` e `period_uid`) no período.
2. WHEN a Inicializacao_Periodo for acionada para um Period que já possui Transactions com source `CREDIT_CARD` vinculadas, THE Period_Service SHALL ignorar as CreditCardInstallments que já possuem Transaction vinculada ao período.
3. WHEN a Inicializacao_Periodo for executada novamente após a adição de uma nova FixedExpense ativa, THE Period_Service SHALL criar Transaction apenas para a nova FixedExpense, mantendo as existentes intactas.
4. THE Period_Service SHALL executar toda a Inicializacao_Periodo dentro de uma única `DB::transaction` para garantir atomicidade.
5. THE Period_Service SHALL retornar um resumo da inicialização contendo: quantidade de transações criadas a partir de despesas fixas, quantidade de transações vinculadas/criadas a partir de parcelas de cartão, e quantidade de itens ignorados por já existirem.

---

### Requisito 6: Interface de Listagem de Períodos com Ações

**User Story:** Como usuário, quero uma página de períodos com ações disponíveis, para que eu possa criar, visualizar e inicializar períodos de forma intuitiva.

#### Critérios de Aceitação

1. THE Period_Page SHALL exibir a lista de períodos do usuário ordenados por ano e mês decrescentes, com paginação.
2. THE Period_Page SHALL exibir para cada período: o nome do mês, o ano, e a quantidade de transações vinculadas.
3. THE Period_Page SHALL possuir um botão "Criar Período" que permita ao usuário selecionar mês e ano para criar um novo Period.
4. WHEN o usuário clicar em um período da lista, THE Period_Page SHALL navegar para a Period_Show_Page do período selecionado.
5. IF o usuário tentar criar um Period com mês/ano que já existe, THEN THE Period_Service SHALL retornar erro informando que o período já existe.

---

### Requisito 7: Interface de Detalhes do Período com Inicialização

**User Story:** Como usuário, quero uma página de detalhes do período com botão de inicialização e lista de transações, para que eu gerencie as movimentações do mês de forma centralizada.

#### Critérios de Aceitação

1. THE Period_Show_Page SHALL exibir o nome do mês e ano do período no cabeçalho.
2. THE Period_Show_Page SHALL possuir um botão "Inicializar Período" que acione a Inicializacao_Periodo.
3. WHEN a Inicializacao_Periodo for concluída com sucesso, THE Period_Show_Page SHALL exibir uma notificação com o resumo da inicialização (quantidade de transações criadas e ignoradas).
4. THE Period_Show_Page SHALL exibir a lista de transações do período em formato de tabela com colunas: descrição, categoria, conta, valor, data de vencimento, status e ações.
5. THE Period_Show_Page SHALL permitir marcar transações individuais como pagas diretamente na lista, atualizando o status para `PAID` e preenchendo `paid_at`.
6. THE Period_Show_Page SHALL atualizar o resumo financeiro (totais de entrada, saída e saldo) em tempo real após alterações de status das transações.

---

### Requisito 8: Criação de Período via Endpoint API

**User Story:** Como desenvolvedor, quero um endpoint API para criar períodos, para que o frontend possa criar novos períodos de forma padronizada.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir um endpoint POST para criação de Period que aceite os campos `month` (inteiro, 1-12) e `year` (inteiro, 4 dígitos).
2. WHEN o endpoint receber dados válidos, THE Period_Service SHALL criar o Period e retornar o recurso criado com código HTTP 201.
3. IF o endpoint receber um `month` fora do intervalo 1-12 ou `year` com formato inválido, THEN THE Himel_App SHALL retornar erro de validação com código HTTP 422.
4. IF já existir um Period com o mesmo `month`, `year` e `user_uid`, THEN THE Himel_App SHALL retornar erro com código HTTP 409 informando que o período já existe.
5. THE Himel_App SHALL possuir um endpoint POST para acionar a Inicializacao_Periodo de um Period existente, identificado pelo `uid` do período.
6. WHEN o endpoint de inicialização for acionado, THE Period_Service SHALL executar a Inicializacao_Periodo e retornar o resumo com código HTTP 200.

---

### Requisito 9: Exclusão de Período com Validação

**User Story:** Como usuário, quero poder excluir um período, para que eu remova períodos criados por engano, desde que não haja transações pagas vinculadas.

#### Critérios de Aceitação

1. WHEN o usuário solicitar a exclusão de um Period, THE Period_Service SHALL verificar se existem Transactions com status `PAID` vinculadas ao período.
2. IF o Period possuir Transactions com status `PAID`, THEN THE Period_Service SHALL rejeitar a exclusão e retornar mensagem informando que períodos com transações pagas não podem ser excluídos.
3. WHEN o Period possuir apenas Transactions com status `PENDING` ou `OVERDUE`, THE Period_Service SHALL desvincular as transações (definir `period_uid` como `null`) e excluir o Period.
4. WHEN o Period não possuir Transactions vinculadas, THE Period_Service SHALL excluir o Period diretamente.
5. THE Period_Service SHALL executar a exclusão e desvinculação dentro de uma única `DB::transaction`.
