# Stock Movements

## Objetivo

Registrar todas as movimentações de estoque realizadas no sistema.

Nenhuma alteração de estoque deve ocorrer sem uma movimentação registrada.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| warehouse_id | bigint | Sim | Depósito |
| product_id | bigint | Sim | Produto |
| user_id | bigint | Sim | Usuário responsável |
| movement_type | varchar(50) | Sim | Tipo de movimentação |
| quantity | decimal(15,3) | Sim | Quantidade |
| reference_type | varchar(100) | Não | Origem da movimentação |
| reference_id | bigint | Não | Registro de origem |
| notes | text | Não | Observações |
| movement_date | datetime | Sim | Data da movimentação |
| created_at | timestamp | Sim | Data de criação |

---

## Relacionamentos

StockMovement

- N:1 Company
- N:1 Warehouse
- N:1 Product
- N:1 User

---

## Tipos de Movimentação

### Entry

Entrada.

### Exit

Saída.

### Transfer

Transferência.

### Adjustment

Ajuste manual.

### Inventory

Inventário.

---

## Origens Possíveis

- Pedido
- Compra
- Inventário
- Ajuste Manual
- Transferência

---

## Regras de Negócio

### Obrigatoriedade

Toda movimentação deve possuir:

- produto
- depósito
- usuário

### Auditoria

Movimentações nunca podem ser removidas.

### Rastreabilidade

Toda movimentação deve possuir origem quando possível.

Uma transferência entre depósitos deve gerar uma saída no depósito de origem e uma entrada no depósito de destino, ambas vinculadas pela mesma referência.

---

## Exemplos

Pedido #123

Saída de:

10 unidades

Inventário

Ajuste de:

+5 unidades

---

## Observações Futuras

Possíveis recursos:

- lote
- validade
- número de série
- rastreabilidade completa
- múltiplos depósitos na mesma operação
