# Order Items

## Objetivo

Representa os itens pertencentes a um pedido.

Cada registro corresponde a um produto incluído no pedido.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| order_id | bigint | Sim | Pedido |
| product_id | bigint | Sim | Produto |
| quantity | decimal(15,3) | Sim | Quantidade |
| unit_price | decimal(15,2) | Sim | Preço unitário |
| discounts | json | Não | Lista de descontos aplicados |
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

A quantidade deve ser maior que zero.

### Produto Ativo

Somente produtos ativos podem ser adicionados ao pedido.

### Integridade

Não pode existir item sem pedido.

### Alteração

Itens podem ser alterados somente enquanto o pedido estiver em Draft.

### Preço

O preço unitário deve ser armazenado no momento da venda.

Alterações futuras na tabela de preços não devem afetar pedidos já criados.

### Descontos

O item pode possuir múltiplos descontos.

Exemplo:

```json
[
  {
    "name": "Desconto Comercial",
    "type": "percentage",
    "value": 5
  },
  {
    "name": "Campanha Junho",
    "type": "fixed",
    "value": 10.00
  }
]
```

---

## Cálculo

Subtotal do Item

```text
Quantidade × Preço Unitário
```

↓

Aplicação dos descontos

↓

Total do Item

---

## Observações Futuras

Possíveis recursos:

- comissão
- lote
- série
- rastreabilidade
- tributação por item
- brindes
- kits de produtos
- observações por item