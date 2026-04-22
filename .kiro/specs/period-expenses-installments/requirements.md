# Documento de Requisitos — Despesas Fixas e Parcelas de Cartão na Visualização do Período

## Introdução

Atualmente, a visualização de um período financeiro (página Show do Period) exibe apenas um resumo genérico de entradas/saídas e a lista de transações. O usuário não consegue identificar facilmente quais despesas fixas estão incluídas no período, quais parcelas de cartão de crédito vencem naquele mês, nem qual cartão é responsável por cada parcela. Esta feature enriquece a visualização do período com informações detalhadas sobre despesas fixas, cobranças de cartão de crédito e suas parcelas, incluindo numeração de parcelas e breakdown por cartão.

## Glossário

- **Period_Service**: Serviço backend responsável pela lógica de negócio do domínio Period, incluindo consultas de resumo e agregações.
- **Period_Page_Controller**: Controller Inertia que renderiza as páginas do domínio Period, passando dados via props para o frontend.
- **Period_Show_Page**: Página Vue (frontend) que exibe os detalhes de um período financeiro específico.
- **Fixed_Expense**: Despesa fixa recorrente do usuário, com nome, valor, dia de vencimento e categoria.
- **Credit_Card_Charge**: Compra realizada em um cartão de crédito, com valor total, descrição e número de parcelas.
- **Credit_Card_Installment**: Parcela individual de uma compra de cartão de crédito, com número da parcela, valor e data de vencimento.
- **Credit_Card**: Cartão de crédito do usuário, com nome e tipo (físico/virtual).
- **Transaction**: Transação financeira vinculada a um período, com campo `source` indicando a origem (MANUAL, CREDIT_CARD, FIXED, TRANSFER) e `reference_id` apontando para a entidade de origem.
- **Breakdown_Por_Cartão**: Resumo agregado que mostra o valor total de parcelas de cartão de crédito no período, agrupado por cartão.

## Requisitos

### Requisito 1: Listar despesas fixas do período

**User Story:** Como usuário, quero ver quais despesas fixas estão incluídas no período, para que eu saiba exatamente quais gastos recorrentes compõem o total de saídas.

#### Critérios de Aceitação

1. WHEN a Period_Show_Page é carregada, THE Period_Service SHALL retornar a lista de despesas fixas ativas do usuário que foram incluídas como transações no período, contendo nome, valor, categoria e dia de vencimento de cada Fixed_Expense.
2. WHEN a Period_Show_Page é carregada, THE Period_Show_Page SHALL exibir uma seção dedicada "Despesas Fixas" listando cada despesa fixa do período com nome, valor formatado em moeda, categoria e data de vencimento.
3. WHEN nenhuma despesa fixa está vinculada ao período, THE Period_Show_Page SHALL exibir a mensagem "Nenhuma despesa fixa neste período." na seção de despesas fixas.
4. THE Period_Service SHALL calcular o subtotal de todas as despesas fixas do período e retorná-lo junto com a lista.
5. WHEN a seção de despesas fixas é exibida, THE Period_Show_Page SHALL mostrar o subtotal de despesas fixas no cabeçalho da seção.

### Requisito 2: Listar parcelas de cartão de crédito do período

**User Story:** Como usuário, quero ver quais parcelas de cartão de crédito vencem no período, para que eu saiba exatamente quais compras parceladas estão impactando meu orçamento.

#### Critérios de Aceitação

1. WHEN a Period_Show_Page é carregada, THE Period_Service SHALL retornar a lista de parcelas de cartão de crédito vinculadas ao período, contendo a descrição da compra (Credit_Card_Charge), o valor da parcela, a data de vencimento e o nome do cartão de crédito associado.
2. WHEN a Period_Show_Page é carregada, THE Period_Show_Page SHALL exibir uma seção dedicada "Parcelas de Cartão" listando cada parcela do período com descrição da compra, valor formatado em moeda, data de vencimento e nome do cartão.
3. WHEN nenhuma parcela de cartão está vinculada ao período, THE Period_Show_Page SHALL exibir a mensagem "Nenhuma parcela de cartão neste período." na seção de parcelas de cartão.
4. THE Period_Service SHALL calcular o subtotal de todas as parcelas de cartão do período e retorná-lo junto com a lista.
5. WHEN a seção de parcelas de cartão é exibida, THE Period_Show_Page SHALL mostrar o subtotal de parcelas de cartão no cabeçalho da seção.

### Requisito 3: Exibir numeração de parcelas

**User Story:** Como usuário, quero ver o número da parcela atual em relação ao total (ex: "3/10"), para que eu saiba em que ponto do parcelamento cada compra está.

#### Critérios de Aceitação

1. WHEN uma parcela de cartão é exibida na Period_Show_Page, THE Period_Service SHALL retornar o número da parcela (installment_number) e o total de parcelas da compra (total_installments da Credit_Card_Charge) para cada Credit_Card_Installment.
2. WHEN uma parcela de cartão é exibida na Period_Show_Page, THE Period_Show_Page SHALL formatar a numeração como "X/Y" onde X é o installment_number e Y é o total_installments (ex: "3/10", "7/7").
3. THE Period_Show_Page SHALL exibir a numeração da parcela na mesma linha da descrição da compra, de forma visualmente distinta (badge ou texto secundário).

### Requisito 4: Exibir breakdown de gastos por cartão de crédito

**User Story:** Como usuário, quero ver um resumo de quanto do total do período vem de cada cartão de crédito, para que eu identifique quais cartões estão concentrando mais gastos.

#### Critérios de Aceitação

1. WHEN a Period_Show_Page é carregada, THE Period_Service SHALL calcular o valor total de parcelas agrupado por cartão de crédito para o período, retornando o nome do cartão e o valor total de parcelas daquele cartão no período.
2. WHEN a Period_Show_Page é carregada, THE Period_Show_Page SHALL exibir uma seção ou card de "Resumo por Cartão" mostrando cada cartão de crédito com parcelas no período e o valor total correspondente, formatado em moeda.
3. WHEN nenhuma parcela de cartão está vinculada ao período, THE Period_Show_Page SHALL ocultar a seção de resumo por cartão.
4. THE Period_Show_Page SHALL exibir o total geral de cartões de crédito no período como soma de todos os cartões no resumo.

### Requisito 5: Integração dos dados no resumo financeiro do período

**User Story:** Como usuário, quero que o resumo financeiro do período inclua a composição detalhada das saídas, para que eu entenda de onde vêm meus gastos.

#### Critérios de Aceitação

1. WHEN a Period_Show_Page é carregada, THE Period_Service SHALL retornar, além dos totais existentes (total_inflow, total_outflow, balance), os subtotais de despesas fixas (total_fixed_expenses), parcelas de cartão (total_credit_card_installments) e transações manuais (total_manual) que compõem o total de saídas.
2. WHEN a Period_Show_Page é carregada, THE Period_Show_Page SHALL exibir os subtotais de composição de saídas (despesas fixas, parcelas de cartão, manuais) nos cards de resumo financeiro ou em uma seção complementar.
3. THE Period_Service SHALL garantir que a soma dos subtotais por fonte (fixed + credit_card + manual + transfer) seja igual ao total_outflow retornado no resumo.

### Requisito 6: Tratamento de erros na consulta de dados do período

**User Story:** Como usuário, quero que a página do período funcione corretamente mesmo quando há dados inconsistentes, para que eu não perca acesso à visualização.

#### Critérios de Aceitação

1. IF uma Transaction com source FIXED possui um reference_id que não corresponde a nenhuma Fixed_Expense existente, THEN THE Period_Service SHALL retornar a transação com os dados disponíveis e o campo de despesa fixa como nulo.
2. IF uma Transaction com source CREDIT_CARD possui um reference_id que não corresponde a nenhuma Credit_Card_Installment existente, THEN THE Period_Service SHALL retornar a transação com os dados disponíveis e os campos de parcela/cartão como nulos.
3. IF os dados de parcela ou despesa fixa estão nulos, THEN THE Period_Show_Page SHALL exibir "—" nos campos correspondentes em vez de causar erro de renderização.