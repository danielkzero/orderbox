# Warehouses

## Objetivo

Representa os locais de armazenamento de produtos.

Permite controle de estoque por depósito, loja, fábrica ou centro de distribuição.

Todo depósito pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| name | varchar(255) | Sim | Nome do depósito |
| code | varchar(50) | Sim | Código interno |
| address | varchar(255) | Não | Endereço |
| active | boolean | Sim | Depósito ativo |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Warehouse

- N:1 Company
- 1:N StockBalances
- 1:N StockMovements

---

## Exemplos

- Fábrica
- Centro de Distribuição
- Loja Física
- Estoque Marketplace

---

## Regras de Negócio

### Código Único

O código deve ser único dentro da empresa.

### Exclusão

Depósitos com movimentações não podem ser removidos.

Utilizar:

active = false

---

## Observações Futuras

Possíveis recursos:

- múltiplos endereços
- setor
- corredor
- prateleira
- posição logística