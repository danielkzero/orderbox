# Payments

## Objetivo

Representa os pagamentos recebidos pela empresa.

Um título pode possuir múltiplos pagamentos.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| accounts_receivable_id | bigint | Sim | Título financeiro |
| payment_method | varchar(50) | Sim | Forma de pagamento |
| payment_date | datetime | Sim | Data do pagamento |
| amount | decimal(15,2) | Sim | Valor recebido |
| transaction_code | varchar(255) | Não | Código da transação |
| notes | text | Não | Observações |
| created_at | timestamp | Sim | Data de criação |

---

## Relacionamentos

Payment

- N:1 AccountsReceivable

---

## Formas de Pagamento

### Cash

Dinheiro.

### Pix

PIX.

### CreditCard

Cartão de Crédito.

### DebitCard

Cartão de Débito.

### BankSlip

Boleto.

### BankTransfer

Transferência Bancária.

---

## Regras de Negócio

### Valor Positivo

O valor recebido deve ser maior que zero.

### Pagamento Parcial

Um título pode receber vários pagamentos.

### Atualização Automática

O recebimento deve atualizar automaticamente:

- saldo
- status

do título financeiro.

### Exclusão

Pagamentos não devem ser removidos.

---

## Observações Futuras

Possíveis recursos:

- integração bancária
- PIX automático
- conciliação bancária
- gateway de pagamento
- estorno
- chargeback