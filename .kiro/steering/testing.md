---
inclusion: fileMatch
fileMatchPattern: "tests/**/*.php"
priority: 42
---

# Testes — Himel App

> Regras e padrões para testes no projeto.

## Framework

- PHPUnit 12 via `php artisan test`.
- Testes de feature (padrão) e unitários quando necessário.
- Criar testes via `php artisan make:test --phpunit {name}`.
- Se encontrar testes Pest, DEVE converter para PHPUnit.

## Convenções

- Todo código alterado DEVE ter teste correspondente.
- Testes DEVEM cobrir: happy path, failure paths e edge cases.
- Usar factories com Faker para gerar dados de teste.
- DEVE verificar se a factory tem custom states antes de configurar manualmente.
- Usar `fake()` ou `$this->faker` conforme convenção existente no projeto.

## Execução

- Rodar o mínimo necessário para validar a alteração:
  - Teste específico: `php artisan test --compact --filter=testName`
  - Arquivo: `php artisan test --compact tests/Feature/ExampleTest.php`
  - Suite completa: `php artisan test --compact`
- Após testes do escopo passarem, perguntar ao usuário se quer rodar a suite completa.

## Estrutura

```
tests/
├── Feature/
│   ├── Account/
│   ├── Category/
│   ├── CreditCard/
│   ├── FixedExpense/
│   ├── Period/
│   ├── Transaction/
│   └── Transfer/
└── Unit/
```

## O que Testar por Entidade

- CRUD completo (store, update, destroy)
- Validação de FormRequest (campos obrigatórios, formatos, limites)
- Isolamento multi-tenant (usuário A NÃO acessa dados do B)
- Regras de negócio no Service (idempotência, cálculos, restrições)
- Edge cases (valores limítrofes, campos nullable, duplicatas)
- Erros esperados (409 conflito, 422 validação, 403 autorização)

## Proibições

- NUNCA remover testes existentes sem aprovação do usuário.
- NUNCA criar scripts de verificação quando testes cobrem a funcionalidade.
- NUNCA usar `php artisan tinker` para criar models — usar testes com factories.
