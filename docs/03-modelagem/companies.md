# Companies

## Objetivo

Representa uma empresa cliente do OrderBox.

Toda informação do sistema pertence a uma empresa.

O isolamento dos dados deve ser garantido por `company_id` nas entidades raiz e pelo vínculo obrigatório com uma entidade raiz nas entidades filhas.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| corporate_name | varchar(255) | Sim | Razão social |
| trade_name | varchar(255) | Sim | Nome fantasia |
| document | varchar(20) | Sim | CPF ou CNPJ |
| email | varchar(255) | Sim | E-mail principal |
| phone | varchar(20) | Não | Telefone principal |
| active | boolean | Sim | Empresa ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Company

- 1:N Users
- 1:N Customers
- 1:N Categories
- 1:N Brands
- 1:N Units
- 1:N Products
- 1:N PriceTables
- 1:N Orders
- 1:N SalesRepresentatives
- 1:N AuditLogs
- 1:N Devices
- 1:N SyncLogs

---

## Regras de Negócio

### Isolamento de Dados

Uma empresa nunca pode visualizar dados de outra empresa.

### Empresa Inativa

Empresas inativas não podem:

- criar pedidos
- cadastrar produtos
- sincronizar dados

### Exclusão

Empresas não devem ser excluídas fisicamente.

Utilizar apenas:

active = false

---

## Observações Futuras

Possíveis campos para versões futuras:

- logo
- plano_contratado
- data_vencimento
- limite_usuarios
- limite_produtos
- configurações fiscais
