# Accounts Receivable

## Objetivo

Representa os títulos financeiros gerados para recebimento.

Normalmente são originados a partir de pedidos faturados.

Todo título pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| customer_id | bigint | Sim | Cliente |
| order_id | bigint | Não | Pedido de origem |
| document_number | varchar(100) | Sim | Número do documento |
| issue_date | date | Sim | Data de emissão |
| due_date | date | Sim | Data de vencimento |
| amount | decimal(15,2) | Sim | Valor original |
| balance | decimal(15,2) | Sim | Saldo pendente |
| status | varchar(50) | Sim | Situação |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

AccountsReceivable

- N:1 Company
- N:1 Customer
- N:1 Order
- 1:N Payments

---

## Status

### Open

Aberto.

### Partial

Parcialmente pago.

### Paid

Pago.

### Overdue

Vencido.

### Cancelled

Cancelado.

---

## Regras de Negócio

### Geração

Normalmente será criado após faturamento do pedido.

### Baixa Automática

O saldo deverá ser atualizado automaticamente após pagamentos.

### Exclusão

Títulos financeiros não devem ser removidos.

### Auditoria

Toda alteração deve ser registrada.

---

## Observações Futuras

Possíveis recursos:

- boleto
- PIX
- cobrança automática
- protesto
- renegociação
- parcelamento