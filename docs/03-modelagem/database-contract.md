# Contrato Relacional da V1

## Objetivo

Definir constraints, índices e decisões técnicas necessárias para converter a modelagem conceitual da V1 em migrations.

Os documentos individuais de cada entidade continuam sendo a referência funcional. Este documento prevalece quando houver dúvida sobre integridade relacional ou comportamento técnico.

---

## Premissas

- MariaDB com suporte a transações, chaves estrangeiras, índices compostos e JSON.
- Identificadores internos do servidor usam `bigint`.
- Referências criadas no mobile usam UUID.
- Datas são persistidas em UTC e expostas pela API no formato ISO 8601.
- Valores monetários usam `decimal(15,2)`.
- Quantidades usam `decimal(15,3)`.
- Senhas armazenam apenas hash seguro; nunca texto puro ou valor reversível.

---

## Multiempresa

Toda consulta operacional deve ser limitada à Company do usuário autenticado.

### Entidades Raiz

Possuem `company_id` obrigatório:

- Users
- Customers
- Categories
- Brands
- Units
- Products
- PriceTables
- PaymentMethods
- PaymentTerms
- SalesRepresentatives
- Orders
- Devices
- SyncLogs
- SyncOperations
- SyncChanges
- AuditLogs
- AuthenticationSessions
- AuthenticationChallenges

### Entidades Filhas

Herdam a empresa por relacionamento obrigatório:

- CustomerAddresses por Customer;
- CustomerContacts por Customer;
- CustomerRepresentatives por Customer e SalesRepresentative;
- ProductPrices por Product e PriceTable;
- OrderItems por Order.

As FKs de uma mesma relação devem apontar para registros da mesma Company. Essa regra deve ser validada pela aplicação e, quando viável, reforçada por constraints compostas.

---

## Exclusão e Histórico

- Entidades com campo `active` usam inativação.
- Orders em Draft podem ser removidos fisicamente, desde que a remoção gere AuditLog e SyncChange.
- OrderItems podem ser removidos enquanto o pedido estiver em Draft.
- AuditLogs, SyncLogs, SyncOperations e SyncChanges são imutáveis e não podem ser removidos pela aplicação.
- Registros referenciados por histórico comercial não podem ser removidos fisicamente.

---

## Concorrência

Customers e Orders possuem campo `version integer` obrigatório, iniciado em `1`.

Toda atualização incrementa `version`. A API rejeita uma atualização quando a versão recebida não corresponde à versão atual, retornando conflito.

Products, preços e demais cadastros administrativos usam `updated_at` para sincronização, mas não exigem controle de versão na V1.

---

## Integridade por Entidade

| Entidade | Constraints principais |
|---------|---------|
| Companies | `document` único; `active` obrigatório |
| Users | `email` globalmente único na V1; `role` em `Admin`, `Manager`, `SalesRepresentative` |
| AuthenticationSessions | `(user_id, channel, active_slot)` único; `channel` em `Web`, `Mobile`; tokens e chaves de validação armazenados como hash |
| AuthenticationChallenges | desafio pendente vinculado a User e Company; `channel` em `Web`, `Mobile`; expiração obrigatória |
| Customers | `(company_id, document)` único; `(company_id, client_reference)` único quando informado; `version >= 1` |
| CustomerAddresses | FK obrigatória para Customer; somente um endereço padrão por cliente e tipo |
| CustomerContacts | FK obrigatória para Customer; somente um contato principal ativo por cliente |
| CustomerRepresentatives | `(customer_id, sales_representative_id)` único; somente um representante principal por cliente |
| Categories | categoria pai deve pertencer à mesma Company; ciclos são proibidos |
| Brands | `(company_id, name)` único |
| Units | `(company_id, code)` único |
| Products | `(company_id, sku)` único; `available_stock >= 0` quando informado |
| PriceTables | `(company_id, name)` único |
| ProductPrices | `(product_id, price_table_id, minimum_quantity)` único; `price > 0`; `minimum_quantity > 0` quando informada |
| PaymentMethods | `(company_id, code)` único; `(company_id, name)` único |
| PaymentTerms | `(company_id, code)` único; `(company_id, name)` único; `installment_days` não vazio; `minimum_order_amount >= 0` |
| SalesRepresentatives | `(company_id, user_id)` único; `(company_id, code)` único |
| Orders | `(company_id, order_number)` único; `(company_id, client_reference)` único quando informado; `version >= 1`; valores não negativos |
| OrderItems | `quantity > 0`; `unit_price >= 0`; `total_amount >= 0` |
| Devices | `(company_id, device_uuid)` único |
| SyncOperations | `(company_id, operation_id)` único |
| SyncChanges | `sequence` único e crescente |

Quando `minimum_quantity` estiver ausente em ProductPrices, a migration deve normalizar o valor para `1.000` ou usar uma estratégia equivalente que preserve a unicidade da faixa base.

---

## Chaves Estrangeiras

| Relação | Política de remoção |
|---------|---------|
| Company → entidades raiz | Restrict |
| Customer → Addresses, Contacts e Representatives | Restrict |
| Customer → Orders | Restrict |
| Product → ProductPrices e OrderItems | Restrict |
| PriceTable → ProductPrices e Orders | Restrict |
| Order → OrderItems | Cascade apenas quando o Order estiver em Draft |
| User → AuditLogs, Orders e Devices | Restrict |
| User → AuthenticationSessions | Cascade |
| User → AuthenticationChallenges | Cascade |
| Device → SyncLogs e SyncOperations | Restrict |

Regras condicionais, como cascade somente para Draft, devem ser aplicadas pela camada de serviço dentro de transação.

---

## Índices Mínimos

Além de PKs, FKs e constraints únicas:

| Entidade | Índices |
|---------|---------|
| Customers | `(company_id, active, corporate_name)`, `(company_id, updated_at)` |
| CustomerRepresentatives | `(sales_representative_id, customer_id)` |
| Products | `(company_id, active, name)`, `(company_id, category_id)`, `(company_id, updated_at)` |
| ProductPrices | `(price_table_id, product_id, minimum_quantity)` |
| PaymentMethods | `(company_id, active, sort_order)` |
| PaymentTerms | `(company_id, active, sort_order)` |
| Orders | `(company_id, status, order_date)`, `(sales_representative_id, order_date)`, `(customer_id, order_date)`, `(company_id, updated_at)` |
| AuditLogs | `(company_id, created_at)`, `(company_id, entity_type, entity_id)` |
| AuthenticationSessions | `(company_id, user_id, channel)`, `(last_activity_at)`, `(revoked_at)` |
| AuthenticationChallenges | `(user_id, channel, expires_at)` |
| SyncLogs | `(company_id, device_id, started_at)` |
| SyncOperations | `(company_id, device_id, created_at)` |
| SyncChanges | `(company_id, sequence)` |

---

## Transações Obrigatórias

Devem ocorrer em uma única transação:

- criação ou atualização de Customer com endereço, contato principal e vínculo ao representante;
- criação ou atualização de Order com seus itens e totais;
- envio de Order, incluindo validação, mudança para Sent, numeração e auditoria;
- cancelamento de Order e auditoria;
- aplicação de uma operação de sincronização e registro de SyncOperation;
- gravação de alteração sincronizável e respectivo SyncChange.

---

## Ordem Sugerida das Migrations

1. Companies
2. Users
3. AuthenticationSessions e AuthenticationChallenges
4. Customers, CustomerAddresses e CustomerContacts
5. SalesRepresentatives e CustomerRepresentatives
6. Categories, Brands e Units
7. Products, PriceTables e ProductPrices
8. PaymentMethods e PaymentTerms
9. Orders e OrderItems
10. Devices, SyncLogs, SyncOperations e SyncChanges
11. AuditLogs
