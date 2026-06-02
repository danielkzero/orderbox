# Stock Balances

## Objetivo

Representa o saldo atual de estoque por produto e depósito.

Esta tabela existe apenas para otimização de consultas.

O saldo oficial é derivado das movimentações de estoque.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| warehouse_id | bigint | Sim | Depósito |
| product_id | bigint | Sim | Produto |
| quantity | decimal(15,3) | Sim | Saldo atual |
| updated_at | timestamp | Sim | Última atualização |

---

## Relacionamentos

StockBalance

- N:1 Warehouse
- N:1 Product

---

## Regras de Negócio

### Unicidade

Deve existir apenas um saldo por:

- warehouse_id
- product_id

### Atualização

O saldo deve ser atualizado automaticamente após cada movimentação.

### Fonte Oficial

A fonte oficial sempre será:

StockMovements

Esta tabela serve apenas para performance.

---

## Observações Futuras

Possíveis recursos:

- estoque reservado
- estoque disponível
- estoque mínimo
- estoque máximo