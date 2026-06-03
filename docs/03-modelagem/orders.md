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
| discounts | json | Não | Lista de descontos aplicados |
| total_amount | decimal(15,2) | Sim | Valor final do pedido |
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

---

## Status

### Draft

Pedido em edição.

Permissões:

- editar
- remover
- adicionar itens
- alterar quantidades
- alterar preços

---

### Sent

Pedido enviado.

Permissões:

- visualizar

Após o envio o pedido não pode mais ser alterado.

---

## Regras de Negócio

### Número Único

O número do pedido deve ser único dentro da empresa.

### Alteração

Somente pedidos em Draft podem ser alterados.

### Exclusão

Somente pedidos em Draft podem ser removidos.

### Auditoria

Toda alteração relevante deve gerar registro em AuditLog.

### Descontos

O pedido pode possuir múltiplos descontos.

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
    "type": "percentage",
    "value": 10
  }
]
```

---

## Origens

- Admin
- Mobile
- B2B

---

## Integração com ERP

O ERP continua responsável por:

- faturamento
- financeiro
- estoque oficial
- emissão fiscal
- entrega

O OrderBox apenas envia os pedidos para processamento.

---

## Observações Futuras

Possíveis recursos:

- múltiplos vendedores
- aprovação comercial
- aprovação financeira
- assinatura digital
- workflow personalizado
- integração ERP
- acompanhamento do status no ERP