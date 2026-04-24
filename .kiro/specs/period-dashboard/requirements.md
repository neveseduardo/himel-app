# Requirements Document

## Introduction

Tela de dashboard focada em períodos financeiros, oferecendo visualização gráfica dos dados de cada período (entradas, saídas, saldo, despesas fixas, parcelas de cartão, breakdown por cartão e categorias). O dashboard será o ponto de entrada principal da aplicação, acessível como primeiro item da sidebar, e utilizará exclusivamente componentes shadcn-vue (incluindo o componente Chart baseado em Unovis). A página deve ser responsiva e coberta por testes E2E com Playwright.

## Glossary

- **Dashboard**: Página principal da aplicação que exibe gráficos e resumos financeiros do período selecionado
- **Period**: Período financeiro mensal (mês/ano) que agrupa transações, despesas fixas e parcelas de cartão
- **Period_Summary**: Dados agregados de um período: total_inflow, total_outflow, balance, total_fixed_expenses, total_credit_card_installments, total_manual, total_transfer, inflow_manual, inflow_transfer
- **Card_Breakdown**: Agregação de totais de parcelas por cartão de crédito dentro de um período
- **Transaction**: Movimentação financeira com direction (INFLOW/OUTFLOW), status (PENDING/PAID/OVERDUE) e source (MANUAL/CREDIT_CARD/FIXED/TRANSFER)
- **Chart_Component**: Componente de gráfico do shadcn-vue baseado na biblioteca Unovis (@unovis/vue)
- **Sidebar**: Menu lateral de navegação da aplicação (AppSidebar.vue)
- **Period_Selector**: Controle que permite ao usuário escolher qual período visualizar no dashboard
- **Dashboard_Controller**: Controller Laravel responsável por fornecer os dados do dashboard via Inertia


## Requirements

### Requirement 1: Navegação — Link do Dashboard na Sidebar

**User Story:** Como usuário, quero acessar o dashboard como primeiro item da sidebar, para que eu tenha acesso rápido à visão geral financeira.

#### Acceptance Criteria

1. THE Sidebar SHALL exibir o item "Dashboard" como primeiro item do grupo "Financeiro", antes de "Períodos"
2. THE Sidebar SHALL utilizar o ícone BarChart3 do lucide-vue-next para o item "Dashboard"
3. WHEN o usuário clicar no item "Dashboard", THE Sidebar SHALL navegar para a rota raiz "/"
4. WHILE o usuário estiver na página de dashboard, THE Sidebar SHALL destacar o item "Dashboard" como ativo

### Requirement 2: Backend — Controller e Dados do Dashboard

**User Story:** Como desenvolvedor, quero um controller dedicado que forneça todos os dados necessários ao dashboard, para que a página tenha acesso aos dados do período via Inertia.

#### Acceptance Criteria

1. THE Dashboard_Controller SHALL retornar via Inertia os dados do período atual ou do período selecionado pelo usuário
2. THE Dashboard_Controller SHALL fornecer o Period_Summary completo (total_inflow, total_outflow, balance, total_fixed_expenses, total_credit_card_installments, total_manual, total_transfer, inflow_manual, inflow_transfer)
3. THE Dashboard_Controller SHALL fornecer o Card_Breakdown do período (lista de cartões com seus totais)
4. THE Dashboard_Controller SHALL fornecer a lista de períodos disponíveis para seleção
5. THE Dashboard_Controller SHALL fornecer a contagem de transações agrupadas por status (PENDING, PAID, OVERDUE) para o período
6. THE Dashboard_Controller SHALL fornecer a contagem e valores de transações agrupadas por categoria para o período
7. IF o usuário não possuir nenhum período cadastrado, THEN THE Dashboard_Controller SHALL retornar dados vazios com valores zerados
8. WHEN o usuário selecionar um período diferente via query parameter, THE Dashboard_Controller SHALL retornar os dados do período solicitado

### Requirement 3: Cards de Resumo Financeiro

**User Story:** Como usuário, quero ver cards com os principais indicadores financeiros do período, para ter uma visão rápida da saúde financeira.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir quatro cards de resumo: Entradas, Saídas, Saldo e Total Cartões
2. THE Dashboard SHALL exibir o valor de total_inflow no card "Entradas" formatado como moeda brasileira (R$)
3. THE Dashboard SHALL exibir o valor de total_outflow no card "Saídas" formatado como moeda brasileira (R$)
4. THE Dashboard SHALL exibir o valor de balance no card "Saldo" formatado como moeda brasileira (R$)
5. WHEN o balance for positivo, THE Dashboard SHALL exibir o valor do card "Saldo" com cor verde
6. WHEN o balance for negativo, THE Dashboard SHALL exibir o valor do card "Saldo" com cor vermelha
7. THE Dashboard SHALL exibir o grand_total do Card_Breakdown no card "Total Cartões" formatado como moeda brasileira (R$)
8. THE Dashboard SHALL utilizar exclusivamente o componente Card do shadcn-vue para os cards de resumo

### Requirement 4: Seletor de Período

**User Story:** Como usuário, quero selecionar qual período visualizar no dashboard, para poder analisar dados de meses anteriores ou futuros.

#### Acceptance Criteria

1. THE Period_Selector SHALL exibir o período atual como seleção padrão ao carregar o dashboard
2. THE Period_Selector SHALL listar todos os períodos disponíveis do usuário ordenados por ano e mês decrescente
3. WHEN o usuário selecionar um período diferente, THE Dashboard SHALL atualizar todos os gráficos e cards com os dados do período selecionado
4. THE Period_Selector SHALL utilizar exclusivamente o componente Select do shadcn-vue
5. THE Period_Selector SHALL exibir cada período no formato "Mês Ano" (ex: "Janeiro 2025")
6. IF o usuário não possuir períodos cadastrados, THEN THE Dashboard SHALL exibir uma mensagem informativa orientando a criação de um período

### Requirement 5: Gráfico de Composição de Saídas

**User Story:** Como usuário, quero visualizar a composição das saídas do período em um gráfico, para entender a distribuição dos gastos por fonte.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir um gráfico de donut mostrando a composição das saídas do período
2. THE Dashboard SHALL representar no gráfico as quatro fontes de saída: Despesas Fixas, Parcelas de Cartão, Manuais e Transferências
3. THE Dashboard SHALL exibir os valores de cada fonte no gráfico formatados como moeda brasileira (R$)
4. THE Dashboard SHALL utilizar exclusivamente o Chart_Component do shadcn-vue (Unovis) para renderizar o gráfico
5. IF todas as fontes de saída tiverem valor zero, THEN THE Dashboard SHALL exibir uma mensagem indicando ausência de saídas no período

### Requirement 6: Gráfico de Entradas vs Saídas

**User Story:** Como usuário, quero comparar visualmente entradas e saídas do período, para avaliar rapidamente o equilíbrio financeiro.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir um gráfico de barras agrupadas comparando total_inflow e total_outflow do período
2. THE Dashboard SHALL utilizar cor verde para representar entradas e cor vermelha para representar saídas no gráfico
3. THE Dashboard SHALL exibir tooltip com os valores formatados como moeda brasileira (R$) ao passar o mouse sobre as barras
4. THE Dashboard SHALL utilizar exclusivamente o Chart_Component do shadcn-vue (Unovis) para renderizar o gráfico

### Requirement 7: Gráfico de Breakdown por Cartão de Crédito

**User Story:** Como usuário, quero visualizar quanto cada cartão de crédito representa nas saídas do período, para controlar os gastos por cartão.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir um gráfico de barras horizontais mostrando o total de parcelas por cartão de crédito
2. THE Dashboard SHALL exibir o nome do cartão e o valor total formatado como moeda brasileira (R$) para cada barra
3. THE Dashboard SHALL utilizar exclusivamente o Chart_Component do shadcn-vue (Unovis) para renderizar o gráfico
4. IF o período não possuir parcelas de cartão, THEN THE Dashboard SHALL exibir uma mensagem indicando ausência de dados de cartão

### Requirement 8: Gráfico de Transações por Status

**User Story:** Como usuário, quero visualizar a distribuição de transações por status (Pendente, Pago, Vencido), para acompanhar o andamento financeiro do período.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir um gráfico de donut mostrando a contagem de transações por status (PENDING, PAID, OVERDUE)
2. THE Dashboard SHALL utilizar cores distintas para cada status: amarelo para PENDING, verde para PAID, vermelho para OVERDUE
3. THE Dashboard SHALL exibir a contagem total de transações no centro do gráfico de donut
4. THE Dashboard SHALL utilizar exclusivamente o Chart_Component do shadcn-vue (Unovis) para renderizar o gráfico
5. IF o período não possuir transações, THEN THE Dashboard SHALL exibir uma mensagem indicando ausência de transações

### Requirement 9: Gráfico de Gastos por Categoria

**User Story:** Como usuário, quero visualizar os gastos agrupados por categoria, para identificar onde estou gastando mais.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir um gráfico de barras horizontais mostrando o total de saídas (OUTFLOW) agrupadas por categoria
2. THE Dashboard SHALL ordenar as categorias por valor decrescente no gráfico
3. THE Dashboard SHALL exibir o nome da categoria e o valor total formatado como moeda brasileira (R$)
4. THE Dashboard SHALL utilizar exclusivamente o Chart_Component do shadcn-vue (Unovis) para renderizar o gráfico
5. IF o período não possuir transações de saída com categoria, THEN THE Dashboard SHALL exibir uma mensagem indicando ausência de dados

### Requirement 10: Responsividade

**User Story:** Como usuário, quero acessar o dashboard em dispositivos móveis e desktop, para ter uma experiência consistente em qualquer tela.

#### Acceptance Criteria

1. THE Dashboard SHALL reorganizar os cards de resumo em uma coluna em telas menores que 768px e em quatro colunas em telas maiores
2. THE Dashboard SHALL reorganizar os gráficos em uma coluna em telas menores que 1024px e em duas colunas em telas maiores
3. THE Dashboard SHALL redimensionar os gráficos proporcionalmente ao tamanho do container
4. THE Dashboard SHALL manter todos os textos e valores legíveis em telas a partir de 320px de largura
5. THE Dashboard SHALL utilizar exclusivamente classes utilitárias do Tailwind CSS para responsividade

### Requirement 11: Instalação do Componente Chart do shadcn-vue

**User Story:** Como desenvolvedor, quero que o componente Chart do shadcn-vue e a dependência Unovis sejam adicionados ao projeto, para que os gráficos possam ser renderizados.

#### Acceptance Criteria

1. THE Dashboard SHALL utilizar o componente Chart do shadcn-vue instalado via CLI (`npx shadcn-vue@latest add chart`)
2. THE Dashboard SHALL utilizar a biblioteca @unovis/vue (dependência do Chart do shadcn-vue) para renderização dos gráficos
3. THE Dashboard SHALL utilizar os componentes ChartContainer, ChartTooltip e ChartCrosshair do shadcn-vue para padronização visual

### Requirement 12: Testes E2E com Playwright

**User Story:** Como desenvolvedor, quero testes E2E que validem todas as funcionalidades do dashboard, para garantir que a página funciona corretamente.

#### Acceptance Criteria

1. THE Dashboard_E2E_Tests SHALL verificar que a página de dashboard carrega com o título correto
2. THE Dashboard_E2E_Tests SHALL verificar que os quatro cards de resumo são exibidos com valores formatados em R$
3. THE Dashboard_E2E_Tests SHALL verificar que o link "Dashboard" aparece como primeiro item da sidebar
4. THE Dashboard_E2E_Tests SHALL verificar que o Period_Selector exibe o período atual como padrão
5. WHEN o período for alterado no Period_Selector, THE Dashboard_E2E_Tests SHALL verificar que os cards e gráficos são atualizados
6. THE Dashboard_E2E_Tests SHALL verificar que os gráficos de composição de saídas, entradas vs saídas, breakdown por cartão, transações por status e gastos por categoria são renderizados
7. THE Dashboard_E2E_Tests SHALL verificar que a página é responsiva redimensionando o viewport para mobile (375px) e desktop (1280px)
8. THE Dashboard_E2E_Tests SHALL verificar que o estado vazio é exibido corretamente quando o período não possui dados
9. THE Dashboard_E2E_Tests SHALL seguir o padrão Page Object existente no projeto (DashboardPage.ts em e2e/pages/)
10. THE Dashboard_E2E_Tests SHALL seguir o padrão de seeder existente, atualizando o E2eTestSeeder com dados necessários para o dashboard
