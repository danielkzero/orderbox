# Orders

## Objetivo

Representa um pedido de venda realizado por um cliente.

Os pedidos podem ser originados através do painel administrativo, portal B2B, aplicativo mobile ou integrações externas.

Todo pedido pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| customer_id | bigint | Sim | Cliente |
| user_id | bigint | Sim | Usuário responsável |
| price_table_id | bigint | Sim | Tabela de preço utilizada |
| order_number | varchar(50) | Sim | Número do pedido |
| status | varchar(50) | Sim | Status atual |
| subtotal | decimal(15,2) | Sim | Subtotal |
| discount_amount | decimal(15,2) | Não | Desconto |
| freight_amount | decimal(15,2) | Não | Frete |
| total_amount | decimal(15,2) | Sim | Total |
| notes | text | Não | Observações |
| order_date | datetime | Sim | Data do pedido |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Order

- N:1 Company
- N:1 Customer
- N:1 User
- N:1 PriceTable
- 1:N OrderItems
- 1:N AccountsReceivable

---

## Status

### Draft

Pedido em edição.

### Pending

Aguardando aprovação.

### Approved

Pedido aprovado.

### Invoiced

Pedido faturado.

### Delivered

Pedido entregue.

### Canceled

Pedido cancelado.

---

## Regras de Negócio

### Número Único

O número do pedido deve ser único dentro da empresa.

### Pedido Cancelado

Pedidos cancelados não podem gerar faturamento.

### Alteração

Pedidos faturados não podem ser alterados.

### Exclusão

Pedidos nunca devem ser removidos fisicamente.

---

## Origens Futuras

- Admin
- PDV
- Mobile
- B2B
- API

---

## Observações Futuras

Possíveis recursos:

- múltiplos vendedores
- aprovação comercial
- aprovação financeira
- assinatura digital
- workflow personalizado