# Documento de Requisitos

## Introdução

Reestruturação do frontend da aplicação de gestão financeira pessoal, migrando da organização atual baseada em `resources/js/modules/` (agrupamento por funcionalidade genérica: auth, finance, settings) para uma estrutura DDD (Domain-Driven Design) em `resources/js/domain/`, espelhando a organização do backend em `app/Domain/`. Cada domínio terá subpastas padronizadas: `stores/`, `components/`, `services/`, `composables/`, `types/` e `validations/`. Arquivos compartilhados entre domínios serão centralizados em `resources/js/domain/Shared/`. Estruturas gerenciadas pelo Wayfinder (`actions/`, `routes/`) e pelo Inertia (`pages/`) permanecem inalteradas.

Além da migração estrutural, a reestruturação adota princípios de Arquitetura Hexagonal (Ports & Adapters) para isolar a lógica de domínio da infraestrutura, práticas de Clean Code para garantir legibilidade e manutenibilidade, e princípios de Clean Architecture para estabelecer a Regra de Dependência entre camadas.

## Glossário

- **Sistema_Reestruturação**: O processo e ferramentas responsáveis por reorganizar os arquivos do frontend
- **Domínio_Frontend**: Uma pasta dentro de `resources/js/domain/` que agrupa stores, componentes, serviços, composables, tipos e validações de um contexto de negócio específico
- **Domínio_Backend**: Uma pasta dentro de `app/Domain/` que agrupa controllers, models, services e demais artefatos de um contexto de negócio no backend
- **Shared**: Domínio especial que contém arquivos reutilizáveis entre múltiplos domínios
- **Wayfinder**: Plugin Laravel que gera automaticamente funções TypeScript tipadas para rotas e controllers do backend
- **Inertia**: Framework que conecta o backend Laravel ao frontend Vue via file-based routing em `resources/js/pages/`
- **Alias_Import**: Atalho de importação configurado no bundler (ex: `@/domain/Account/stores/useAccountStore`)
- **Port**: Interface TypeScript que define um contrato de comunicação entre a lógica de domínio e sistemas externos (API, storage, notificações), localizada em `services/` de cada domínio
- **Adapter**: Implementação concreta de um Port que encapsula a dependência de infraestrutura (Inertia, Wayfinder, localStorage), localizada em `services/adapters/` de cada domínio
- **Camada_Domínio**: Camada interna que contém tipos (`types/`), stores (`stores/`) e validações (`validations/`) — independente de framework
- **Camada_Aplicação**: Camada intermediária composta por composables (`composables/`) que orquestram casos de uso consumindo stores e ports
- **Camada_Infraestrutura**: Camada externa que contém adapters concretos, integrações com Inertia/Wayfinder e detalhes de framework
- **Camada_Apresentação**: Camada externa composta por componentes Vue (`components/`) que consomem composables e stores
- **Regra_Dependência**: Princípio que determina que camadas internas não podem importar ou depender de camadas externas
- **SRP**: Single Responsibility Principle — cada módulo, componente ou função deve ter uma única razão para mudar
- **DRY**: Don't Repeat Yourself — evitar duplicação de lógica extraindo código comum em funções, composables ou utilitários reutilizáveis

## Requisitos

### Requisito 1: Criação da Estrutura de Diretórios por Domínio

**User Story:** Como desenvolvedor, quero que o frontend tenha uma estrutura de diretórios DDD espelhando o backend, para que a navegação entre camadas seja intuitiva e consistente.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL criar a pasta `resources/js/domain/` como raiz da nova estrutura DDD
2. WHEN o backend possuir um Domínio_Backend em `app/Domain/<Nome>/`, THE Sistema_Reestruturação SHALL criar o Domínio_Frontend correspondente em `resources/js/domain/<Nome>/`
3. THE Sistema_Reestruturação SHALL criar os seguintes domínios: Account, Auth, Category, CreditCard, CreditCardCharge, CreditCardInstallment, FixedExpense, Period, Settings, Transaction, Transfer, User
4. THE Sistema_Reestruturação SHALL criar um domínio Shared em `resources/js/domain/Shared/` para arquivos compartilhados entre domínios
5. WHEN um Domínio_Frontend for criado, THE Sistema_Reestruturação SHALL criar as subpastas: `stores/`, `components/`, `services/`, `composables/`, `types/` e `validations/`

### Requisito 2: Migração de Stores para Domínios Específicos

**User Story:** Como desenvolvedor, quero que cada store Pinia esteja dentro do domínio correspondente, para que a lógica de estado fique co-localizada com o contexto de negócio.

#### Critérios de Aceitação

1. WHEN uma store existir em `resources/js/modules/finance/stores/`, THE Sistema_Reestruturação SHALL mover a store para `resources/js/domain/<Domínio>/stores/`
2. THE Sistema_Reestruturação SHALL mover `useAccountStore.ts` para `resources/js/domain/Account/stores/`
3. THE Sistema_Reestruturação SHALL mover `useCategoryStore.ts` para `resources/js/domain/Category/stores/`
4. THE Sistema_Reestruturação SHALL mover `useCreditCardStore.ts` para `resources/js/domain/CreditCard/stores/`
5. THE Sistema_Reestruturação SHALL mover `useCreditCardChargeStore.ts` para `resources/js/domain/CreditCardCharge/stores/`
6. THE Sistema_Reestruturação SHALL mover `useFixedExpenseStore.ts` para `resources/js/domain/FixedExpense/stores/`
7. THE Sistema_Reestruturação SHALL mover `useTransactionStore.ts` para `resources/js/domain/Transaction/stores/`
8. THE Sistema_Reestruturação SHALL mover `useTransferStore.ts` para `resources/js/domain/Transfer/stores/`

### Requisito 3: Migração de Componentes para Domínios Específicos

**User Story:** Como desenvolvedor, quero que cada componente de formulário e UI de domínio esteja dentro do domínio correspondente, para que a camada de apresentação fique co-localizada com o contexto de negócio.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL mover `AccountForm.vue` para `resources/js/domain/Account/components/`
2. THE Sistema_Reestruturação SHALL mover `CategoryForm.vue` para `resources/js/domain/Category/components/`
3. THE Sistema_Reestruturação SHALL mover `CreditCardForm.vue` para `resources/js/domain/CreditCard/components/`
4. THE Sistema_Reestruturação SHALL mover `CreditCardChargeForm.vue` para `resources/js/domain/CreditCardCharge/components/`
5. THE Sistema_Reestruturação SHALL mover `FixedExpenseForm.vue` para `resources/js/domain/FixedExpense/components/`
6. THE Sistema_Reestruturação SHALL mover `TransactionForm.vue` para `resources/js/domain/Transaction/components/`
7. THE Sistema_Reestruturação SHALL mover `TransferForm.vue` para `resources/js/domain/Transfer/components/`
8. THE Sistema_Reestruturação SHALL mover `DeleteUser.vue`, `PasswordInput.vue`, `TwoFactorRecoveryCodes.vue` e `TwoFactorSetupModal.vue` para `resources/js/domain/Auth/components/`
9. THE Sistema_Reestruturação SHALL mover os componentes de settings (`AlertError.vue`, `AppearanceTabs.vue`, `Heading.vue`, `InputError.vue`, `TextLink.vue`, `UserInfo.vue`, `UserMenuContent.vue`) para `resources/js/domain/Settings/components/`

### Requisito 4: Migração de Componentes Compartilhados para o Domínio Shared

**User Story:** Como desenvolvedor, quero que componentes genéricos usados por múltiplos domínios fiquem no domínio Shared, para evitar dependências circulares e facilitar o reuso.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL mover `DataTable.vue`, `DirectionBadge.vue`, `FilterBar.vue` e `StatusBadge.vue` para `resources/js/domain/Shared/components/`
2. THE Sistema_Reestruturação SHALL manter os componentes de `resources/js/components/` (AppContent, AppHeader, AppShell, AppSidebar, etc.) no local atual, pois são componentes de layout da aplicação e não pertencem a um domínio de negócio

### Requisito 5: Migração de Validações (Schemas Zod) para Domínios Específicos

**User Story:** Como desenvolvedor, quero que cada schema de validação Zod esteja dentro do domínio correspondente, para que as regras de validação fiquem co-localizadas com o contexto de negócio.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL mover `account-schema.ts` para `resources/js/domain/Account/validations/`
2. THE Sistema_Reestruturação SHALL mover `category-schema.ts` para `resources/js/domain/Category/validations/`
3. THE Sistema_Reestruturação SHALL mover `credit-card-schema.ts` para `resources/js/domain/CreditCard/validations/`
4. THE Sistema_Reestruturação SHALL mover `credit-card-charge-schema.ts` para `resources/js/domain/CreditCardCharge/validations/`
5. THE Sistema_Reestruturação SHALL mover `fixed-expense-schema.ts` para `resources/js/domain/FixedExpense/validations/`
6. THE Sistema_Reestruturação SHALL mover `transaction-schema.ts` para `resources/js/domain/Transaction/validations/`
7. THE Sistema_Reestruturação SHALL mover `transfer-schema.ts` para `resources/js/domain/Transfer/validations/`

### Requisito 6: Migração de Composables para Domínios Específicos e Shared

**User Story:** Como desenvolvedor, quero que composables fiquem organizados por domínio ou no Shared quando forem genéricos, para manter a coesão do código.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL mover `useFinanceFilters.ts` para `resources/js/domain/Shared/composables/`, pois é utilizado por múltiplos domínios financeiros
2. THE Sistema_Reestruturação SHALL mover `useCrudToast.ts` para `resources/js/domain/Shared/composables/`, pois é utilizado por múltiplos domínios
3. THE Sistema_Reestruturação SHALL mover `useFlashMessages.ts` para `resources/js/domain/Shared/composables/`, pois é utilizado por múltiplos domínios
4. THE Sistema_Reestruturação SHALL mover `usePagination.ts` para `resources/js/domain/Shared/composables/`, pois é utilizado por múltiplos domínios
5. THE Sistema_Reestruturação SHALL mover `useTwoFactorAuth.ts` para `resources/js/domain/Auth/composables/`
6. THE Sistema_Reestruturação SHALL manter `useAppearance.ts`, `useCurrentUrl.ts` e `useInitials.ts` em `resources/js/composables/`, pois são composables de infraestrutura da aplicação

### Requisito 7: Migração de Services para Domínios Específicos

**User Story:** Como desenvolvedor, quero que os serviços do frontend fiquem organizados por domínio, para que a lógica de comunicação com o backend fique co-localizada.

#### Critérios de Aceitação

1. WHEN o arquivo `finance.services.ts` contiver lógica específica de um único domínio, THE Sistema_Reestruturação SHALL mover o arquivo para o domínio correspondente em `resources/js/domain/<Domínio>/services/`
2. WHEN o arquivo `finance.services.ts` contiver lógica compartilhada entre múltiplos domínios, THE Sistema_Reestruturação SHALL dividir o arquivo em serviços específicos por domínio ou mover para `resources/js/domain/Shared/services/`

### Requisito 8: Migração e Separação de Types por Domínio

**User Story:** Como desenvolvedor, quero que as interfaces e tipos TypeScript fiquem organizados por domínio, para que a definição de tipos fique co-localizada com o contexto de negócio.

#### Critérios de Aceitação

1. WHEN o arquivo `finance.ts` contiver interfaces de múltiplos domínios, THE Sistema_Reestruturação SHALL separar as interfaces em arquivos por domínio dentro de `resources/js/domain/<Domínio>/types/`
2. THE Sistema_Reestruturação SHALL manter os arquivos de tipos globais (`auth.ts`, `global.d.ts`, `index.ts`, `navigation.ts`, `ui.ts`, `vue-shims.d.ts`, `auto-imports.d.ts`, `components.d.ts`) em `resources/js/types/`, pois são tipos de infraestrutura da aplicação

### Requisito 9: Atualização de Imports em Todos os Arquivos Consumidores

**User Story:** Como desenvolvedor, quero que todos os imports sejam atualizados automaticamente após a migração, para que a aplicação continue funcionando sem erros.

#### Critérios de Aceitação

1. WHEN um arquivo for movido para a nova estrutura de domínio, THE Sistema_Reestruturação SHALL atualizar todos os imports que referenciam o caminho antigo em arquivos dentro de `resources/js/pages/`
2. WHEN um arquivo for movido para a nova estrutura de domínio, THE Sistema_Reestruturação SHALL atualizar todos os imports que referenciam o caminho antigo em arquivos dentro de `resources/js/domain/`
3. WHEN um arquivo for movido para a nova estrutura de domínio, THE Sistema_Reestruturação SHALL atualizar todos os imports que referenciam o caminho antigo em arquivos dentro de `resources/js/components/`
4. IF um import atualizado resultar em erro de resolução de módulo, THEN THE Sistema_Reestruturação SHALL reportar o erro e corrigir o caminho

### Requisito 10: Preservação de Estruturas Gerenciadas por Ferramentas Externas

**User Story:** Como desenvolvedor, quero que as estruturas geradas automaticamente pelo Wayfinder e pelo Inertia permaneçam intactas, para que a integração com o backend continue funcionando.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL manter `resources/js/actions/` inalterado, pois é gerenciado pelo Wayfinder
2. THE Sistema_Reestruturação SHALL manter `resources/js/routes/` inalterado, pois é gerenciado pelo Wayfinder
3. THE Sistema_Reestruturação SHALL manter `resources/js/pages/` inalterado, pois é gerenciado pelo Inertia file-based routing
4. THE Sistema_Reestruturação SHALL manter `resources/js/wayfinder/` inalterado, pois é gerenciado pelo Wayfinder
5. THE Sistema_Reestruturação SHALL manter `resources/js/components/` (componentes de layout) inalterado
6. THE Sistema_Reestruturação SHALL manter `resources/js/lib/` inalterado

### Requisito 11: Remoção da Estrutura Antiga

**User Story:** Como desenvolvedor, quero que a estrutura antiga `modules/` seja removida após a migração completa, para evitar confusão e duplicação de código.

#### Critérios de Aceitação

1. WHEN todos os arquivos de `resources/js/modules/` tiverem sido migrados para `resources/js/domain/`, THE Sistema_Reestruturação SHALL remover o diretório `resources/js/modules/` e todos os seus subdiretórios
2. IF algum arquivo em `resources/js/modules/` ainda possuir referências ativas, THEN THE Sistema_Reestruturação SHALL atualizar as referências antes de remover o arquivo

### Requisito 12: Atualização da Documentação de Convenções

**User Story:** Como desenvolvedor, quero que a documentação do projeto reflita a nova estrutura DDD, para que novos desenvolvedores entendam a organização do código.

#### Critérios de Aceitação

1. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL atualizar o arquivo `conventions.md` substituindo referências a `resources/js/modules/` por `resources/js/domain/`
2. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL atualizar a seção "Estrutura de Diretórios" do `conventions.md` para refletir a nova organização DDD do frontend

### Requisito 13: Validação Pós-Migração

**User Story:** Como desenvolvedor, quero garantir que a aplicação funcione corretamente após a reestruturação, para que nenhuma funcionalidade seja quebrada.

#### Critérios de Aceitação

1. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL executar `npm run types:check` sem erros de TypeScript
2. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL executar `npm run lint` sem erros de ESLint
3. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL executar `npm run build` sem erros de compilação
4. WHEN a migração estiver completa, THE Sistema_Reestruturação SHALL executar `php artisan test --compact` sem falhas nos testes existentes

### Requisito 14: Arquitetura Hexagonal (Ports & Adapters)

**User Story:** Como desenvolvedor, quero que cada domínio do frontend implemente Arquitetura Hexagonal com Ports e Adapters, para que a lógica de domínio fique isolada das dependências de infraestrutura e seja facilmente testável e substituível.

#### Critérios de Aceitação

1. WHEN um Domínio_Frontend necessitar comunicação com sistemas externos (API, storage, notificações), THE Sistema_Reestruturação SHALL definir um Port como interface TypeScript em `resources/js/domain/<Domínio>/services/`
2. WHEN um Port for definido, THE Sistema_Reestruturação SHALL criar a subpasta `resources/js/domain/<Domínio>/services/adapters/` para conter as implementações concretas do Port
3. THE Sistema_Reestruturação SHALL criar Adapters específicos para cada dependência de infraestrutura: Adapter Inertia (para navegação e submissão de formulários), Adapter Wayfinder (para chamadas tipadas ao backend) e Adapter localStorage (para persistência local)
4. THE Sistema_Reestruturação SHALL garantir que cada Port defina métodos com tipos de entrada e retorno explícitos, sem referências a bibliotecas de infraestrutura (Inertia, Wayfinder, axios)
5. THE Sistema_Reestruturação SHALL garantir que cada Adapter importe e implemente o Port correspondente, encapsulando a dependência de infraestrutura internamente
6. WHEN um composable ou store necessitar acessar infraestrutura externa, THE Sistema_Reestruturação SHALL injetar o Adapter via parâmetro ou provider, consumindo apenas a interface do Port
7. THE Sistema_Reestruturação SHALL criar Ports e Adapters compartilhados em `resources/js/domain/Shared/services/` e `resources/js/domain/Shared/services/adapters/` para contratos reutilizados por múltiplos domínios
8. IF um Adapter precisar ser substituído (ex: trocar Inertia por outra solução de roteamento), THEN THE Sistema_Reestruturação SHALL permitir a substituição alterando apenas o Adapter concreto, sem modificar Ports, stores ou composables

### Requisito 15: Práticas de Clean Code

**User Story:** Como desenvolvedor, quero que o código do frontend siga práticas de Clean Code, para que a base de código seja legível, manutenível e consistente entre todos os domínios.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL garantir que cada componente Vue tenha uma única responsabilidade conforme o SRP: componentes de formulário gerenciam entrada de dados, componentes de listagem gerenciam exibição de coleções, componentes de layout gerenciam estrutura visual
2. THE Sistema_Reestruturação SHALL garantir que cada composable tenha uma única responsabilidade conforme o SRP: um composable orquestra um caso de uso específico ou gerencia um aspecto específico de estado
3. THE Sistema_Reestruturação SHALL garantir que funções dentro de composables, stores e utilitários tenham no máximo 30 linhas de código executável, extraindo lógica complexa em funções auxiliares nomeadas
4. THE Sistema_Reestruturação SHALL garantir que nomes de variáveis, funções, composables e componentes sejam descritivos e revelem a intenção (ex: `isLoadingAccounts` em vez de `loading`, `fetchTransactionsByPeriod` em vez de `getData`)
5. THE Sistema_Reestruturação SHALL garantir que lógica duplicada entre domínios seja extraída para `resources/js/domain/Shared/` como composable, utilitário ou componente reutilizável, seguindo o princípio DRY
6. THE Sistema_Reestruturação SHALL garantir que cada domínio siga a mesma organização interna: `types/` para interfaces, `stores/` para estado, `services/` para ports e adapters, `composables/` para casos de uso, `components/` para UI, `validations/` para schemas Zod
7. IF um componente Vue ultrapassar 200 linhas de código no bloco `<script setup>`, THEN THE Sistema_Reestruturação SHALL dividir o componente em subcomponentes menores ou extrair lógica para composables
8. THE Sistema_Reestruturação SHALL garantir que cada função de tratamento de erro utilize um padrão consistente: erros de validação tratados via Zod, erros de API tratados via composable de notificação (toast/flash), erros inesperados propagados com mensagem descritiva
9. THE Sistema_Reestruturação SHALL garantir que imports dentro de cada arquivo sigam uma ordem consistente: (1) dependências externas, (2) imports do domínio Shared, (3) imports do próprio domínio, (4) imports relativos

### Requisito 16: Princípios de Clean Architecture

**User Story:** Como desenvolvedor, quero que o frontend siga princípios de Clean Architecture com Regra de Dependência clara entre camadas, para que a lógica de negócio seja independente de frameworks e facilmente testável.

#### Critérios de Aceitação

1. THE Sistema_Reestruturação SHALL garantir que a Camada_Domínio (`types/`, `stores/`, `validations/`) de cada domínio contenha apenas lógica de negócio pura, sem imports de Vue, Inertia, Wayfinder ou qualquer biblioteca de UI
2. THE Sistema_Reestruturação SHALL garantir que arquivos em `types/` de cada domínio definam entidades e value objects como interfaces TypeScript puras, sem decorators, refs ou dependências de framework
3. THE Sistema_Reestruturação SHALL garantir que a Camada_Aplicação (`composables/`) orquestre casos de uso consumindo stores e ports via interfaces, sem acessar diretamente Adapters concretos ou bibliotecas de infraestrutura
4. THE Sistema_Reestruturação SHALL garantir que a Regra_Dependência seja respeitada: `types/` não importa de nenhuma outra pasta do domínio; `stores/` importa apenas de `types/`; `composables/` importa de `types/`, `stores/` e `services/` (ports); `components/` importa de `composables/`, `stores/` e `types/`
5. THE Sistema_Reestruturação SHALL garantir que a Camada_Infraestrutura (`services/adapters/`) seja a única camada que importa bibliotecas externas de infraestrutura (Inertia router, Wayfinder actions, localStorage API)
6. WHEN um composable precisar de funcionalidade de infraestrutura, THE Sistema_Reestruturação SHALL aplicar Inversão de Controle: o composable recebe o Adapter como parâmetro ou utiliza um provider, em vez de instanciar a dependência diretamente
7. THE Sistema_Reestruturação SHALL garantir que dependências entre domínios fluam exclusivamente através do domínio Shared: um Domínio_Frontend não importa diretamente de outro Domínio_Frontend, utilizando `resources/js/domain/Shared/` como intermediário
8. IF uma store precisar de dados de outro domínio, THEN THE Sistema_Reestruturação SHALL mediar a comunicação via composable do domínio Shared ou via eventos, sem criar acoplamento direto entre stores de domínios distintos
