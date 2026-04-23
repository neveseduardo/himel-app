# Documento de Requisitos — Relatório PDF do Período

## Introdução

Funcionalidade para gerar e baixar um relatório PDF a partir da página de detalhe do período financeiro. O relatório espelha os dados exibidos na tela Show.vue do período (sumário, despesas fixas, parcelas de cartão, entradas e saídas) em um documento PDF com design elegante e moderno. O sistema de templates PDF deve ser reutilizável para futuros relatórios da aplicação.

## Glossário

- **Sistema_PDF**: Módulo backend responsável por gerar documentos PDF a partir de dados estruturados, utilizando uma biblioteca PHP de geração de PDF.
- **Template_PDF**: Classe PHP reutilizável que define a estrutura visual base dos relatórios PDF (cabeçalho com placeholder para logo, rodapé, tipografia, cores e layout).
- **Relatório_Período**: Documento PDF específico que contém o sumário financeiro e os dados detalhados de um período (despesas fixas, parcelas de cartão, entradas e saídas).
- **PeriodService**: Serviço existente do domínio Period que fornece os dados agregados do período (sumário, despesas fixas, parcelas, breakdown por cartão, transações).
- **Show_Vue**: Página Vue existente (`periods/Show.vue`) que exibe o detalhe do período com dropdown de ações no cabeçalho.
- **Dropdown_Menu**: Menu de opções existente no cabeçalho da Show_Vue que contém ações como "Nova Entrada", "Nova Saída", "Processar Período" e "Remover Transações".


## Requisitos

### Requisito 1: Opção de Gerar Relatório no Dropdown

**User Story:** Como usuário, quero ter uma opção "Gerar Relatório" no menu dropdown da página de detalhe do período, para que eu possa iniciar o download do PDF com um clique.

#### Critérios de Aceitação

1. THE Dropdown_Menu SHALL exibir a opção "Gerar Relatório" com um ícone representativo (ex: FileDown) entre as opções existentes.
2. WHEN o usuário clicar na opção "Gerar Relatório", THE Show_Vue SHALL iniciar uma requisição ao backend para gerar e baixar o arquivo PDF.
3. WHILE o download estiver em andamento, THE Show_Vue SHALL desabilitar a opção "Gerar Relatório" e exibir um indicador de carregamento no texto da opção.
4. WHEN o download for concluído com sucesso, THE Show_Vue SHALL restaurar o estado original da opção "Gerar Relatório".
5. IF o download falhar, THEN THE Show_Vue SHALL exibir uma notificação toast de erro informando a falha.

### Requisito 2: Endpoint de Geração do PDF

**User Story:** Como sistema, quero um endpoint dedicado que gere o PDF do período e retorne o arquivo para download, para que o frontend possa solicitar o relatório via requisição HTTP.

#### Critérios de Aceitação

1. THE Sistema_PDF SHALL expor uma rota GET `periods/{uid}/report` nomeada `periods.report` no arquivo de rotas do domínio Period.
2. WHEN uma requisição válida for recebida no endpoint, THE Sistema_PDF SHALL coletar os dados do período (sumário, despesas fixas, parcelas de cartão, breakdown por cartão e transações) via PeriodService.
3. WHEN os dados forem coletados, THE Sistema_PDF SHALL gerar um documento PDF e retornar como resposta HTTP com Content-Type `application/pdf` e header Content-Disposition para download.
4. THE Sistema_PDF SHALL nomear o arquivo PDF no formato `relatorio-periodo-{mes}-{ano}.pdf` (ex: `relatorio-periodo-01-2025.pdf`).
5. IF o período não for encontrado ou não pertencer ao usuário autenticado, THEN THE Sistema_PDF SHALL retornar HTTP 404.
6. IF ocorrer um erro durante a geração do PDF, THEN THE Sistema_PDF SHALL registrar o erro no log e retornar HTTP 500 com mensagem genérica.

### Requisito 3: Template PDF Reutilizável

**User Story:** Como desenvolvedor, quero um sistema de templates PDF reutilizável, para que futuros relatórios possam ser criados com consistência visual e mínimo esforço.

#### Critérios de Aceitação

1. THE Template_PDF SHALL definir uma classe base abstrata com métodos para cabeçalho, rodapé, corpo e estilos compartilhados.
2. THE Template_PDF SHALL incluir no cabeçalho um espaço reservado (placeholder) para a logo do projeto, que possa ser facilmente substituído por uma imagem no futuro.
3. THE Template_PDF SHALL incluir no cabeçalho o título do relatório e a data de geração.
4. THE Template_PDF SHALL incluir no rodapé a numeração de páginas no formato "Página X de Y".
5. THE Template_PDF SHALL definir uma paleta de cores, tipografia e espaçamentos consistentes para uso em todos os relatórios derivados.
6. THE Template_PDF SHALL fornecer métodos auxiliares para renderizar tabelas, cards de sumário e seções com título.

### Requisito 4: Conteúdo do Relatório do Período

**User Story:** Como usuário, quero que o relatório PDF contenha todas as informações financeiras do período, para que eu tenha um documento completo para consulta offline ou impressão.

#### Critérios de Aceitação

1. THE Relatório_Período SHALL exibir na primeira seção um sumário financeiro contendo: total de entradas, total de saídas, saldo, composição das entradas (manuais e transferências) e composição das saídas (despesas fixas, parcelas de cartão, manuais e transferências).
2. THE Relatório_Período SHALL exibir o resumo por cartão de crédito com o nome de cada cartão e o respectivo total, seguido do total geral de cartões.
3. THE Relatório_Período SHALL exibir uma tabela de despesas fixas com as colunas: descrição, valor, categoria e dia de vencimento, seguida do subtotal.
4. THE Relatório_Período SHALL exibir uma tabela de parcelas de cartão com as colunas: descrição (com numeração X/Y da parcela), valor, vencimento e nome do cartão, seguida do subtotal.
5. THE Relatório_Período SHALL exibir uma tabela de entradas com as colunas: descrição, conta, valor e data de ocorrência, seguida do subtotal.
6. THE Relatório_Período SHALL exibir uma tabela de saídas com as colunas: descrição, categoria, conta, valor, vencimento e status, seguida do subtotal.
7. WHEN uma seção não possuir dados (ex: nenhuma despesa fixa), THE Relatório_Período SHALL exibir a mensagem "Nenhum registro neste período." na respectiva seção.
8. THE Relatório_Período SHALL formatar todos os valores monetários no padrão brasileiro (R$ 1.234,56).
9. THE Relatório_Período SHALL formatar todas as datas no padrão brasileiro (dd/mm/aaaa).
10. THE Relatório_Período SHALL exibir o título "Relatório Financeiro — {Nome do Mês} {Ano}" no topo do documento.

### Requisito 5: Design Visual do PDF

**User Story:** Como usuário, quero que o relatório PDF tenha um design bonito e profissional, para que eu possa compartilhá-lo ou imprimi-lo com confiança.

#### Critérios de Aceitação

1. THE Relatório_Período SHALL utilizar cores diferenciadas para valores positivos (entradas/saldo positivo) e negativos (saídas/saldo negativo), seguindo o padrão verde/vermelho da aplicação.
2. THE Relatório_Período SHALL utilizar cabeçalhos de seção visualmente distintos com fundo colorido e texto em destaque.
3. THE Relatório_Período SHALL utilizar tabelas com linhas alternadas (zebra striping) para facilitar a leitura.
4. THE Relatório_Período SHALL utilizar o formato de página A4 com margens adequadas para impressão.
5. THE Relatório_Período SHALL manter a legibilidade com tamanhos de fonte apropriados (mínimo 8pt para tabelas, 10pt para texto corrido).

### Requisito 6: Testes Unitários Backend

**User Story:** Como desenvolvedor, quero testes unitários cobrindo a geração do PDF, para garantir que o relatório é gerado corretamente com os dados esperados.

#### Critérios de Aceitação

1. THE Sistema_PDF SHALL ter testes que verifiquem que o endpoint retorna HTTP 200 com Content-Type `application/pdf` para um período válido.
2. THE Sistema_PDF SHALL ter testes que verifiquem que o endpoint retorna HTTP 404 para um período inexistente.
3. THE Sistema_PDF SHALL ter testes que verifiquem que o endpoint retorna HTTP 404 para um período de outro usuário.
4. THE Sistema_PDF SHALL ter testes que verifiquem que o nome do arquivo no header Content-Disposition segue o formato `relatorio-periodo-{mes}-{ano}.pdf`.
5. THE Sistema_PDF SHALL ter testes que verifiquem que o PDF é gerado com sucesso para um período sem transações (cenário vazio).
6. THE Sistema_PDF SHALL ter testes que verifiquem que o PDF é gerado com sucesso para um período com todos os tipos de dados (despesas fixas, parcelas, entradas e saídas).

### Requisito 7: Testes E2E

**User Story:** Como desenvolvedor, quero testes E2E que validem o fluxo completo de download do relatório, para garantir que a integração frontend-backend funciona corretamente.

#### Critérios de Aceitação

1. THE Sistema_PDF SHALL ter um teste E2E que verifique que a opção "Gerar Relatório" está visível no dropdown menu da página de detalhe do período.
2. THE Sistema_PDF SHALL ter um teste E2E que verifique que clicar em "Gerar Relatório" inicia o download de um arquivo PDF.
3. THE Sistema_PDF SHALL ter um teste E2E que verifique que o arquivo baixado possui o nome correto no formato `relatorio-periodo-{mes}-{ano}.pdf`.
