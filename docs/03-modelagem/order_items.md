# Order Items

## Objetivo

Representa os itens pertencentes a um pedido.

Cada registro corresponde a um produto vendido.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| order_id | bigint | Sim | Pedido |
| product_id | bigint | Sim | Produto |
| quantity | decimal(15,3) | Sim | Quantidade |
| unit_price | decimal(15,2) | Sim | Preço unitário |
| discount_amount | decimal(15,2) | Não | Desconto |
| total_amount | decimal(15,2) | Sim | Total do item |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

OrderItem

- N:1 Order
- N:1 Product

---

## Regras de Negócio

### Quantidade

Quantidade deve ser maior que zero.

### Produto Ativo

Somente produtos ativos podem ser vendidos.

### Integridade

Não pode existir item sem pedido.

### Alteração

Itens não podem ser alterados após faturamento.

---

## Cálculo

Total do Item

Quantidade × Preço Unitário

(-)

Desconto

=

Total do Item

---

## Observações Futuras

Possíveis recursos:

- comissão
- lote
- série
- rastreabilidade
- tributação por item