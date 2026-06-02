# OrderBox - Mapa Geral do Banco de Dados

## Visão Geral

O OrderBox será um sistema SaaS multiempresa.

Toda informação operacional pertence a uma Company.

Nenhuma entidade operacional poderá existir sem vínculo com uma empresa.

O campo `company_id` será obrigatório em todas as entidades de negócio.

---

## Módulos do Sistema

### Administração

- Companies
- Users
- Roles (futuro)
- Permissions (futuro)

### Comercial

- Customers
- CustomerAddresses
- CustomerContacts
- Products
- Categories
- Brands
- ProductPrices
- Orders
- OrderItems

### Estoque

- Warehouses
- StockBalances
- StockMovements

### Financeiro

- AccountsReceivable
- Payments

### Auditoria

- AuditLogs

### CRM (Planejado)

- Leads
- Activities
- Opportunities

### Portal B2B (Planejado)

- CustomerUsers
- Carts
- CartItems
- Favorites

### Mobile (Planejado)

- SyncLogs
- SyncQueue
- DeviceTokens

---

## Fluxo Comercial

```text
Customer
    ↓
Order
    ↓
OrderItem
    ↓
StockMovement
    ↓
AccountsReceivable
    ↓
Payment
```

---

## Relacionamentos Principais

### Company

- 1:N Users
- 1:N Customers
- 1:N Products
- 1:N Orders
- 1:N Warehouses
- 1:N AccountsReceivable

### Customer

- 1:N CustomerAddresses
- 1:N CustomerContacts
- 1:N Orders

### Product

- 1:N ProductPrices
- 1:N OrderItems

### Order

- 1:N OrderItems

### Warehouse

- 1:N StockBalances
- 1:N StockMovements

### AccountsReceivable

- 1:N Payments

---

## Diagrama Geral

> O diagrama visual completo será mantido no arquivo:

```text
docs/03-modelagem/er-diagram.md
```

---

## Convenções do Projeto

### Nomenclatura

Todas as entidades utilizarão nomes em inglês.

Exemplos:

- Company
- User
- Customer
- Product
- Order

### Chaves Estrangeiras

Todas as tabelas seguirão o padrão:

```text
company_id
customer_id
product_id
order_id
```

### Exclusão

Sempre que possível utilizar exclusão lógica:

```text
active = false
```

Evitar remoção física de registros.

---

## Roadmap da Modelagem

### Fase 1

- Companies
- Users
- Customers
- CustomerAddresses
- CustomerContacts

### Fase 2

- Categories
- Brands
- Products
- ProductPrices

### Fase 3

- Orders
- OrderItems

### Fase 4

- Warehouses
- StockBalances
- StockMovements

### Fase 5

- AccountsReceivable
- Payments

### Fase 6

- CRM
- B2B
- Mobile