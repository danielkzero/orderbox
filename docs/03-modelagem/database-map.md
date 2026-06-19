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

Toda informação operacional pertence, direta ou indiretamente, a uma Company (Empresa).

Nenhuma entidade operacional poderá existir sem vínculo com uma empresa.

Entidades raiz possuem `company_id`. Entidades filhas podem herdar o vínculo por uma chave estrangeira obrigatória, evitando duplicação sem perder o isolamento multiempresa.

---

## Módulos do Sistema

### Administração

- Companies
- Users
- AuthenticationSessions
- AuthenticationChallenges

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
- PaymentMethods
- PaymentTerms
- Regions
- RegionMunicipalities

- Orders
- OrderItems

### Mobile

- Devices (Dispositivos)
- SyncLogs (Registro/Histórico de sincronização)
- SyncOperations (Operações idempotentes)
- SyncChanges (Mudanças incrementais)

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
- 1:N Categories
- 1:N Brands
- 1:N Units
- 1:N Products
- 1:N PriceTables
- 1:N PaymentMethods
- 1:N PaymentTerms
- 1:N Orders
- 1:N SalesRepresentatives
- 1:N AuditLogs
- 1:N Devices
- 1:N SyncLogs
- 1:N SyncOperations
- 1:N SyncChanges
- 1:N AuthenticationSessions
- 1:N AuthenticationChallenges

### Customer

- 1:N CustomerAddresses
- 1:N CustomerContacts
- 1:N Orders
- 1:N CustomerRepresentatives

### SalesRepresentative

- 1:N CustomerRepresentatives
- 1:N Orders

### Region

- 1:N RegionMunicipalities
- 1:N Customers
- 1:N SalesRepresentatives
- 1:N PriceTables

### Product

- 1:N ProductPrices
- 1:N OrderItems

### Order

- 1:N OrderItems

### Device

- 1:N SyncLogs
- 1:N SyncOperations

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

O contrato técnico para migrations, constraints e índices está em:

```text
docs/03-modelagem/database-contract.md
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

As chaves estrangeiras seguirão nomes em `snake_case`, por exemplo:

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
- AuthenticationSessions
- AuthenticationChallenges

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
- PaymentMethods
- PaymentTerms

### Fase 4

- Orders
- OrderItems

### Fase 5

- Devices
- SyncLogs
- SyncOperations
- SyncChanges

### Fase 6

- AuditLogs

---

## Fora do Escopo da V1

Funcionalidades mantidas para futuras versões:

- CRM
- Financeiro
- Controle próprio de estoque e movimentações
- Fiscal
- NFe
- Aprovações
- Workflow
- Portal B2B
- Marketplace
- Integrações com marketplaces
