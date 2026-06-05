# OrderBox - Mapa Geral do Banco de Dados

## Visão Geral

O OrderBox será um sistema SaaS multiempresa.

O OrderBox não é um ERP.

O OrderBox é uma plataforma de força de vendas responsável por conectar:

- Representantes
- Clientes
- Produtos
- Pedidos

de forma simples, online e offline.

Toda informação operacional pertence a uma Company (Empresa).

Nenhuma entidade operacional poderá existir sem vínculo com uma empresa.

O campo `company_id` será obrigatório em todas as entidades de negócio.

---

## Módulos do Sistema

### Administração

- Companies
- Users

### Cadastros

- Customers
- CustomerAddresses
- CustomerContacts

- Categories
- Brands
- Units

- Products

### Comercial

- SalesRepresentatives
- CustomerRepresentatives

- PriceTables
- ProductPrices

- Orders
- OrderItems

### Mobile

- Devices (Dispositivos)
- SyncLogs (Registro/Histórico de sincronização)

### Auditoria

- AuditLogs (Registro/Histórico de auditoria)

---

## Fluxo Comercial

```text
Representante
    ↓
Cliente
    ↓
Pedido
    ↓
ERP
```

---

## Relacionamentos Principais

### Company

- 1:N Users
- 1:N Customers
- 1:N Products
- 1:N Orders
- 1:N SalesRepresentatives

### Customer

- 1:N CustomerAddresses
- 1:N CustomerContacts
- 1:N Orders
- 1:N CustomerRepresentatives

### SalesRepresentative

- 1:N CustomerRepresentatives
- 1:N Orders

### Product

- 1:N ProductPrices
- 1:N OrderItems

### Order

- 1:N OrderItems

---

## Integração com ERP

O ERP continua responsável por:

- faturamento
- financeiro
- estoque oficial
- emissão fiscal
- entrega
- contabilidade

O OrderBox é responsável por:

- cadastro de clientes
- catálogo de produtos
- tabelas de preço
- representantes
- pedidos
- sincronização mobile

---

## Diagrama Geral

O diagrama visual completo será mantido no arquivo:

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
user_id
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
- Units

- Products

### Fase 3

- SalesRepresentatives
- CustomerRepresentatives

- PriceTables
- ProductPrices

### Fase 4

- Orders
- OrderItems

### Fase 5

- Devices
- SyncLogs

### Fase 6

- AuditLogs

---

## Fora do Escopo da V1

Funcionalidades mantidas para futuras versões:

- CRM
- Financeiro
- Estoque avançado
- Fiscal
- NFe
- Aprovações
- Workflow
- Portal B2B avançado
- Marketplace
- Integrações com marketplaces