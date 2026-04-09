# Segurança — Himel App

> Regras de segurança e isolamento de dados obrigatórias em toda implementação.

## Multi-Tenancy e Isolamento de Dados

- Todo registro financeiro DEVE possuir `user_uid` (ou `user_id`).
- SEMPRE restringir consultas ao usuário autenticado. Usar scopes como `forUser($uid)`.
- Cada usuário DEVE ter acesso APENAS aos seus próprios dados.
- Relations entre models DEVEM respeitar o isolamento por usuário.
- É PROIBIDO expor dados de um usuário para outro.

## Authorization (Policies)

- Toda operação de escrita DEVE ser autorizada via Laravel Policy ou verificação no Controller.
- Policies DEVEM verificar que o recurso pertence ao usuário autenticado.
- Localização: `app/Domain/{Entity}/Policies/{Entity}Policy.php`

## CSRF

- Inertia.js inclui automaticamente o token CSRF em todas as requisições.
- NÃO é necessário tratamento manual de CSRF no frontend.

## Validação como Segurança

- Validação no frontend (Zod) é para UX.
- Validação no backend (FormRequest) é para SEGURANÇA — é a fonte de verdade.
- NUNCA confiar apenas na validação frontend.
- FormRequests DEVEM validar ownership de recursos referenciados (ex: `exists:financial_accounts,uid` + verificar que pertence ao usuário).

## Tratamento de Erros

- NUNCA expor stack traces ou detalhes internos ao usuário.
- Erros de negócio → exceções de domínio com mensagens amigáveis.
- Erros inesperados → `Log::error()` com contexto + mensagem genérica ao usuário.
- Controllers DEVEM ter `try-catch` em operações de escrita.

## Dados Sensíveis

- Stores Pinia contêm apenas dados já visíveis na UI. Nenhum dado adicional sensível é armazenado no frontend.
- Cartões de crédito armazenam apenas `last_four_digits` — NUNCA o número completo.
- Senhas são gerenciadas pelo Fortify — NUNCA manipular diretamente.

## Autenticação

- Autenticação via Laravel Fortify (login, registro, reset de senha, verificação de email).
- Middleware `auth` DEVE proteger todas as rotas financeiras.
- Sessão gerenciada pelo Laravel — sem tokens JWT no frontend.
