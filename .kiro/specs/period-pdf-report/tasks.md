# Plano de Implementação: Relatório PDF do Período

## Visão Geral

Implementação incremental do sistema de geração de relatório PDF para períodos financeiros. Começa pela instalação da dependência e criação da classe base reutilizável, segue com a classe concreta do período e a Blade view, depois integra com o controller/rota, e finaliza com o frontend e testes.

## Tarefas

- [x] 1. Instalar dependência e criar a classe base do template PDF
  - [x] 1.1 Instalar `barryvdh/laravel-dompdf` via Composer
    - Executar `composer require barryvdh/laravel-dompdf`
    - _Requisitos: 3.1_
  - [x] 1.2 Criar `app/Domain/Shared/Pdf/BaseReportPdf.php`
    - Classe abstrata com propriedades `$title` e `$generatedAt`
    - Método `generate()` que carrega Blade view via `Pdf::loadView()`, configura papel A4 portrait e retorna `$pdf->download()`
    - Métodos abstratos: `getViewName()`, `getViewData()`, `getFileName()`
    - Métodos auxiliares: `formatCurrency(float): string` (formato R$ 1.234,56), `formatDate(?string): string` (formato dd/mm/aaaa), `getMonthName(int): string`
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 4.8, 4.9_

- [x] 2. Criar a classe do relatório do período e a Blade view
  - [x] 2.1 Criar `app/Domain/Period/Pdf/PeriodReportPdf.php`
    - Estende `BaseReportPdf`, recebe `array $periodData` no construtor
    - Implementa `getViewName()` retornando `'pdf.period-report'`
    - Implementa `getViewData()` passando period, summary, fixedExpenses, installments, cardBreakdown, inflowTransactions, outflowTransactions
    - Implementa `getFileName()` retornando `relatorio-periodo-{mes}-{ano}.pdf`
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.10_
  - [x] 2.2 Criar `resources/views/pdf/period-report.blade.php`
    - HTML completo com CSS inline (requisito DOMPDF)
    - Cabeçalho com título do relatório, placeholder para logo e data de geração
    - Seção de sumário financeiro com cards (entradas, saídas, saldo, composição)
    - Resumo por cartão de crédito
    - Tabela de despesas fixas (descrição, valor, categoria, dia vencimento) + subtotal
    - Tabela de parcelas de cartão (descrição com X/Y, valor, vencimento, cartão) + subtotal
    - Tabela de entradas (descrição, conta, valor, data) + subtotal
    - Tabela de saídas (descrição, categoria, conta, valor, vencimento, status) + subtotal
    - Mensagem "Nenhum registro neste período." para seções vazias
    - Rodapé com paginação "Página X de Y" via `{PAGE_NUM}` / `{PAGE_COUNT}`
    - Cores verde/vermelho para valores positivos/negativos, zebra striping, fonte mínima 8pt
    - Formato A4 com margens adequadas para impressão
    - _Requisitos: 3.2, 3.3, 3.4, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9, 4.10, 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 3. Integrar endpoint no controller e rota
  - [x] 3.1 Adicionar método `report(Request, string $uid)` ao `PeriodPageController`
    - Coletar dados via `PeriodService` (métodos existentes: `getByUidWithSummary`, `getTransactionsForPeriod`, `getFixedExpensesForPeriod`, `getInstallmentsForPeriod`, `getCardBreakdownForPeriod`)
    - Separar transações em inflow/outflow via `array_filter`
    - Instanciar `PeriodReportPdf` e retornar `$report->generate()`
    - Tratar erros com try/catch, log e `abort(500)`
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_
  - [x] 3.2 Adicionar rota GET `periods/{uid}/report` em `app/Domain/Period/Routes/web.php`
    - Nome da rota: `periods.report`
    - _Requisitos: 2.1_
  - [x] 3.3 Executar `php artisan wayfinder:generate` para gerar typed actions do novo endpoint
    - _Requisitos: 1.2_

- [ ] 4. Checkpoint — Verificar geração do PDF
  - Garantir que todos os testes passam, perguntar ao usuário se houver dúvidas.
  - Testar manualmente acessando a rota `GET /periods/{uid}/report` para confirmar que o PDF é gerado corretamente.

- [ ] 5. Integrar opção no frontend
  - [~] 5.1 Adicionar opção "Gerar Relatório" no dropdown de `resources/js/pages/periods/Show.vue`
    - Importar ícone `FileDown` do lucide-vue-next
    - Importar action `report` do Wayfinder (`@/actions/App/Domain/Period/Controllers/PeriodPageController`)
    - Adicionar ref `generatingReport` para estado de loading
    - Adicionar `DropdownMenuItem` com ícone `FileDown` e texto "Gerar Relatório" / "Gerando..." entre "Processar Período" e o separador de "Remover Transações"
    - Implementar `handleGenerateReport()` usando `window.open(report.url(props.period.uid), '_blank')` com try/catch e toast de erro
    - Timeout de 2s para restaurar estado do botão
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 6. Testes backend
  - [~] 6.1 Criar `tests/Feature/PeriodReportTest.php`
    - Teste: endpoint retorna HTTP 200 com Content-Type `application/pdf` para período válido
    - Teste: endpoint retorna 404 para período inexistente
    - Teste: endpoint retorna 404 para período de outro usuário
    - Teste: Content-Disposition contém filename no formato `relatorio-periodo-{mes}-{ano}.pdf`
    - Teste: PDF gerado com sucesso para período sem transações (cenário vazio)
    - Teste: PDF gerado com sucesso para período com todos os tipos de dados
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
  - [~] 6.2 Criar `tests/Unit/BaseReportPdfTest.php`
    - Testes unitários para `formatCurrency` e `formatDate` com casos comuns e edge cases
    - _Requisitos: 4.8, 4.9_
  - [ ]* 6.3 Escrever teste de propriedade para formatação de moeda
    - **Property 1: Formatação de moeda no padrão brasileiro**
    - Para qualquer valor float, `formatCurrency(value)` deve produzir string começando com "R$ ", vírgula como separador decimal com 2 casas, ponto como separador de milhares
    - Implementar com Faker gerando 100+ valores aleatórios em loop
    - **Valida: Requisito 4.8**
  - [ ]* 6.4 Escrever teste de propriedade para formatação de data
    - **Property 2: Formatação de data no padrão brasileiro**
    - Para qualquer string de data válida (Y-m-d), `formatDate(date)` deve produzir string no formato dd/mm/aaaa
    - Implementar com Faker gerando 100+ datas aleatórias em loop
    - **Valida: Requisito 4.9**

- [ ] 7. Checkpoint — Garantir que todos os testes backend passam
  - Executar `php artisan test --compact` e garantir que todos os testes passam, perguntar ao usuário se houver dúvidas.

- [ ] 8. Testes E2E
  - [ ] 8.1 Estender `e2e/pages/PeriodPage.ts` com métodos para o relatório PDF
    - Método para abrir dropdown e clicar em "Gerar Relatório"
    - Método para verificar visibilidade da opção no dropdown
    - _Requisitos: 7.1, 7.2_
  - [ ] 8.2 Criar `e2e/tests/period-report.spec.ts`
    - Teste: opção "Gerar Relatório" visível no dropdown da página de detalhe do período
    - Teste: clicar em "Gerar Relatório" inicia download de arquivo PDF
    - Teste: arquivo baixado possui nome correto no formato `relatorio-periodo-{mes}-{ano}.pdf`
    - _Requisitos: 7.1, 7.2, 7.3_

- [ ] 9. Checkpoint final — Garantir que todos os testes passam
  - Executar `php artisan test --compact` e `npm run lint` e `npm run types:check`.
  - Garantir que todos os testes passam, perguntar ao usuário se houver dúvidas.

## Notas

- Tarefas marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada tarefa referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental
- Testes de propriedade validam propriedades universais de corretude (formatação de moeda e data)
- Testes unitários validam exemplos específicos e edge cases
