# OrderBox - Mapa Geral do Banco de Dados

## Visão Geral

O OrderBox será um sistema SaaS multiempresa.

Toda informação operacional pertence a uma Company.

---

## Estrutura Principal

Company
│
├── Users
│
├── Customers
│   ├── Addresses
│   └── Contacts
│
├── Products
│   ├── Categories
│   ├── Brands
│   └── ProductPrices
│
├── Orders
│   └── OrderItems
│
├── Stock
│   ├── Warehouses
│   ├── StockBalances
│   └── StockMovements
│
├── Financial
│   ├── AccountsReceivable
│   └── Payments
│
└── Audit
    └── AuditLogs

---

## Fluxo Comercial

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

---

## Relacionamentos Principais

Company
├── Users
├── Customers
├── Products
├── Orders
├── Warehouses
└── AccountsReceivable

Customer
└── Orders

Order
└── OrderItems

Product
└── OrderItems

Warehouse
└── StockMovements

AccountsReceivable
└── Payments

---

## Módulos Planejados

### Administração

- Usuários
- Empresas
- Configurações

### Comercial

- Clientes
- Produtos
- Pedidos

### Estoque

- Depósitos
- Movimentações
- Inventário

### Financeiro

- Contas a Receber
- Pagamentos

### CRM

- Leads
- Atividades
- Oportunidades

### B2B

- Portal do Cliente
- Carrinho
- Pedidos Online

### Mobile

- Catálogo Offline
- Clientes
- Pedidos
- Sincronização

---

## Observações

Nenhuma entidade operacional poderá existir sem vínculo com uma Company.

O campo company_id será obrigatório em todas as entidades de negócio.