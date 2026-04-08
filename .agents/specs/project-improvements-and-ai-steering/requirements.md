# Documento de Requisitos — Melhorias do Projeto e Steering para IA

## Introdução

Este documento especifica os requisitos para duas frentes de melhoria do Himel App, um SaaS de gestão financeira pessoal construído com Laravel 12 + Inertia.js + Vue 3:

1. **Correções e melhorias técnicas no backend** — resolver problemas arquiteturais identificados (duplicação de `DB::transaction`, filtro de busca incompleto, ausência de API Resources, campo `description` faltando na criação de transações, e regras de negócio não implementadas).
2. **Conversão de instruções para steering files** — transformar o conteúdo da pasta `instructions/` em arquivos `.kiro/steering/` no formato adequado para agentes de IA, tornando as regras de negócio e infraestrutura automaticamente disponíveis no contexto de desenvolvimento.

## Glossário

- **Himel_App**: Aplicação SaaS de gestão financeira pessoal (backend Laravel 12 + frontend Vue 3 via Inertia.js).
- **Service_Layer**: Camada de serviço onde reside toda a lógica de negócio, implementada via interfaces e injeção de dependência.
- **Controller**: Classe responsável por receber requisições HTTP, delegar ao Service_Layer e retornar respostas. Não deve conter lógica de negócio.
- **DB_Transaction**: Wrapper `DB::transaction()` do Laravel que garante atomicidade de operações de banco de dados.
- **API_Resource**: Classe Eloquent API Resource do Laravel que transforma models em respostas JSON padronizadas.
- **Steering_File**: Arquivo Markdown em `.kiro/steering/` que fornece contexto automático para agentes de IA durante o desenvolvimento.
- **Transaction_Service**: Serviço responsável pela lógica de negócio de transações financeiras (`TransactionService`).
- **Transfer_Service**: Serviço responsável pela lógica de negócio de transferências entre contas (`TransferService`).
- **CreditCardCharge_Service**: Serviço responsável pela lógica de negócio de compras no cartão de crédito (`CreditCardChargeService`).
- **EARS**: Easy Approach to Requirements Syntax — padrão de escrita de requisitos utilizado neste documento.
- **Filtro_Search**: Parâmetro `search` utilizado na listagem filtrada de transações para busca textual.
- **OVERDUE**: Status automático de transações pendentes cuja `due_date` já passou.
- **Categorias_Padrao**: Conjunto de categorias financeiras básicas criadas automaticamente no primeiro acesso do usuário.

## Requisitos

### Requisito 1: Remoção de DB::transaction Duplicado nos Controllers

**User Story:** Como desenvolvedor, quero que o `DB::transaction` exista apenas no Service_Layer, para que a responsabilidade de atomicidade fique centralizada e não haja transações aninhadas desnecessárias.

#### Critérios de Aceitação

1. THE Controller SHALL delegar operações de escrita (store, update, destroy) diretamente ao Service_Layer sem encapsular a chamada em `DB::transaction`.
2. THE Service_Layer SHALL manter o `DB::transaction` internamente em todos os métodos de escrita (create, update, delete) para garantir atomicidade.
3. THE Controller SHALL manter o bloco `try-catch` para capturar exceções lançadas pelo Service_Layer e retornar respostas HTTP adequadas.
4. WHEN o Service_Layer lançar uma exceção durante uma operação de escrita, THE Controller SHALL retornar uma resposta JSON com código HTTP 422 e a mensagem de erro.
5. THE Controller SHALL registrar logs de erro via `Log::error()` no bloco `catch` antes de retornar a resposta de erro.

---

### Requisito 2: Melhoria do Filtro de Busca em Transações

**User Story:** Como usuário, quero que a busca textual em transações pesquise também no campo `description`, para que eu encontre transações pelo texto descritivo além do nome da conta.

#### Critérios de Aceitação

1. WHEN o parâmetro Filtro_Search for fornecido na listagem de transações, THE Transaction_Service SHALL pesquisar simultaneamente no campo `description` da transação e no campo `name` da conta associada.
2. WHEN o parâmetro Filtro_Search corresponder ao campo `description` de uma transação, THE Transaction_Service SHALL incluir essa transação nos resultados.
3. WHEN o parâmetro Filtro_Search corresponder ao campo `name` da conta associada, THE Transaction_Service SHALL incluir essa transação nos resultados.
4. WHEN o parâmetro Filtro_Search não corresponder a nenhum campo pesquisável, THE Transaction_Service SHALL retornar uma lista vazia de resultados com metadados de paginação válidos.

---

### Requisito 3: Inclusão do Campo description na Criação de Transações

**User Story:** Como usuário, quero poder informar uma descrição ao criar uma transação, para que eu tenha contexto sobre o motivo da movimentação.

#### Critérios de Aceitação

1. WHEN uma transação for criada com o campo `description` preenchido, THE Transaction_Service SHALL persistir o valor de `description` no registro da transação.
2. WHEN uma transação for criada sem o campo `description`, THE Transaction_Service SHALL persistir a transação com `description` como `null`.
3. THE Transaction_Service SHALL aceitar o campo `description` como string com no máximo 255 caracteres durante a criação.

---

### Requisito 4: Implementação de API Resources para Respostas JSON

**User Story:** Como desenvolvedor, quero que as respostas da API utilizem Eloquent API Resources, para que a transformação de dados seja padronizada, versionável e desacoplada da estrutura interna dos models.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir uma classe API Resource para cada domínio financeiro: Account, Category, Transaction, Transfer, FixedExpense, CreditCard, CreditCardCharge, CreditCardInstallment e Period.
2. THE Controller SHALL utilizar a API_Resource correspondente para formatar todas as respostas JSON de listagem e exibição individual.
3. THE API_Resource SHALL expor apenas os campos necessários para o consumo do frontend, omitindo campos internos como timestamps de auditoria quando não relevantes.
4. WHEN um recurso possuir relacionamentos carregados (ex: transação com conta e categoria), THE API_Resource SHALL incluir os relacionamentos formatados via suas respectivas API Resources aninhadas.

---

### Requisito 5: Transição Automática para Status OVERDUE

**User Story:** Como usuário, quero que transações pendentes com data de vencimento ultrapassada sejam automaticamente marcadas como OVERDUE, para que eu visualize atrasos sem intervenção manual.

#### Critérios de Aceitação

1. THE Himel_App SHALL executar um processo agendado (Scheduled Command) que identifique transações com status `PENDING` e `due_date` anterior à data atual.
2. WHEN o processo agendado identificar transações pendentes vencidas, THE Himel_App SHALL atualizar o status dessas transações para `OVERDUE`.
3. THE Himel_App SHALL executar o processo de transição de status OVERDUE uma vez por dia.
4. THE Himel_App SHALL registrar em log a quantidade de transações atualizadas para OVERDUE a cada execução do processo agendado.

---

### Requisito 6: Criação Automática de Categorias Padrão no Primeiro Acesso

**User Story:** Como novo usuário, quero que o sistema crie automaticamente categorias financeiras básicas no meu primeiro acesso, para que eu possa começar a registrar transações imediatamente.

#### Critérios de Aceitação

1. WHEN um usuário autenticado acessar o sistema e não possuir nenhuma categoria financeira cadastrada, THE Himel_App SHALL criar automaticamente um conjunto de Categorias_Padrao.
2. THE Himel_App SHALL criar as seguintes categorias de OUTFLOW: Alimentação, Moradia, Transporte, Saúde, Educação, Lazer, Vestuário e Outros.
3. THE Himel_App SHALL criar as seguintes categorias de INFLOW: Salário, Freelance, Investimentos e Outros.
4. THE Himel_App SHALL associar todas as Categorias_Padrao ao `user_uid` do usuário autenticado.
5. WHEN o usuário já possuir ao menos uma categoria cadastrada, THE Himel_App SHALL ignorar a criação de Categorias_Padrao.

---

### Requisito 7: Validação de Geração de Parcelas no Cartão de Crédito

**User Story:** Como usuário, quero que o sistema valide corretamente a geração de parcelas ao registrar uma compra no cartão, para que os valores e datas de vencimento estejam consistentes.

#### Critérios de Aceitação

1. WHEN uma compra no cartão de crédito for criada, THE CreditCardCharge_Service SHALL gerar exatamente o número de parcelas informado no campo `total_installments`.
2. WHEN as parcelas forem geradas, THE CreditCardCharge_Service SHALL calcular o valor de cada parcela dividindo o valor total pelo número de parcelas, distribuindo centavos residuais na última parcela.
3. WHEN as parcelas forem geradas, THE CreditCardCharge_Service SHALL calcular o `due_date` de cada parcela com base no `due_day` do cartão de crédito e no mês correspondente.
4. THE CreditCardCharge_Service SHALL gerar uma FinancialTransaction vinculada (via `reference_id`) para cada parcela criada, com source `CREDIT_CARD` e status `PENDING`.
5. IF o número de parcelas informado for menor que 1 ou maior que 48, THEN THE CreditCardCharge_Service SHALL rejeitar a operação com mensagem de erro descritiva.

---

### Requisito 8: Conversão de Regras de Negócio para Steering File

**User Story:** Como desenvolvedor utilizando agentes de IA, quero que as regras de negócio do arquivo `instructions/REGRAS_NEGOCIO.md` estejam disponíveis como steering file em `.kiro/steering/`, para que o agente de IA tenha acesso automático ao contexto de negócio durante o desenvolvimento.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir um arquivo `.kiro/steering/business-rules.md` contendo todas as regras de negócio do domínio financeiro.
2. THE Steering_File de regras de negócio SHALL seguir o formato de steering com seções claras: visão do produto, regras de integridade, fluxo de onboarding, regras por domínio (transações, cartão de crédito, despesas fixas, transferências, períodos), matriz de enums e restrições de edição.
3. THE Steering_File de regras de negócio SHALL utilizar linguagem diretiva e concisa, otimizada para consumo por agentes de IA.
4. THE Steering_File de regras de negócio SHALL incluir o glob pattern adequado no cabeçalho para ativar automaticamente em arquivos relevantes do projeto.

---

### Requisito 9: Conversão de Regras de Infraestrutura para Steering File

**User Story:** Como desenvolvedor utilizando agentes de IA, quero que as regras de infraestrutura e protocolo de desenvolvimento do arquivo `instructions/REGRAS_INFRAESTRUTURA.md` estejam disponíveis como steering file, para que o agente de IA siga os padrões arquiteturais do projeto automaticamente.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir um arquivo `.kiro/steering/development-protocol.md` contendo as regras de infraestrutura e padrões de desenvolvimento.
2. THE Steering_File de infraestrutura SHALL cobrir: stack tecnológica, regras de backend (UUID, Service Layer, Controllers), regras de frontend (Vue 3, Shadcn, modularização, Wayfinder), checklist de validação e mapeamento de models.
3. THE Steering_File de infraestrutura SHALL utilizar linguagem diretiva e concisa, otimizada para consumo por agentes de IA.
4. THE Steering_File de infraestrutura SHALL incluir o glob pattern adequado no cabeçalho para ativar automaticamente em arquivos relevantes do projeto.

---

### Requisito 10: Conversão do Schema de Banco de Dados para Steering File

**User Story:** Como desenvolvedor utilizando agentes de IA, quero que o schema do banco de dados esteja disponível como steering file, para que o agente de IA conheça a estrutura de tabelas e relacionamentos ao gerar código.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir um arquivo `.kiro/steering/database-schema.md` contendo o schema completo do banco de dados financeiro.
2. THE Steering_File de schema SHALL documentar todas as tabelas, colunas, tipos, constraints, foreign keys e índices únicos.
3. THE Steering_File de schema SHALL incluir o glob pattern adequado no cabeçalho para ativar automaticamente em arquivos de migrations e models.

---

### Requisito 11: Testes Automatizados para Services do Domínio Financeiro

**User Story:** Como desenvolvedor, quero que os services do domínio financeiro possuam testes automatizados, para que eu tenha confiança na corretude da lógica de negócio ao fazer alterações.

#### Critérios de Aceitação

1. THE Himel_App SHALL possuir testes de feature para o Transaction_Service cobrindo: criação, atualização, exclusão, marcação como pago, marcação como pendente e listagem com filtros.
2. THE Himel_App SHALL possuir testes de feature para o Transfer_Service cobrindo: criação (com atualização de saldo em ambas as contas), exclusão (com reversão de saldo) e listagem com filtros.
3. THE Himel_App SHALL possuir testes de feature para o CreditCardCharge_Service cobrindo: criação com geração automática de parcelas, validação de valores de parcelas e exclusão em cascata.
4. WHEN um teste de criação de transação for executado com categoria de direction incompatível, THE teste SHALL verificar que uma exceção `InvalidArgumentException` é lançada.
5. WHEN um teste de criação de transação com status PAID for executado, THE teste SHALL verificar que o saldo da conta associada foi atualizado corretamente.
6. THE Himel_App SHALL utilizar factories existentes para criação de dados de teste em todos os testes de services.
