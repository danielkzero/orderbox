# Fluxo de Pedidos

## Objetivo

Definir o ciclo de vida dos pedidos dentro do OrderBox.

O OrderBox é uma plataforma de força de vendas.

O sistema é responsável pela criação e envio de pedidos.

Processos como faturamento, financeiro, estoque oficial, emissão fiscal e entrega permanecem sob responsabilidade do ERP da empresa.

---

## Origens do Pedido

Um pedido pode ser criado por:

- Admin
- Mobile
- B2B

---

## Fluxo Principal

Draft (Rascunho)

↓

Sent (Enviado)

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

O pedido ainda não foi enviado para processamento.

---

### Sent

Pedido enviado.

Permissões:

- visualizar

Após o envio o pedido não pode mais ser alterado.

Representa um pedido entregue ao processo comercial da empresa.

---

## Regras Gerais

### Alterações

Somente pedidos em Draft podem ser alterados.

### Exclusão

Somente pedidos em Draft podem ser removidos.

### Auditoria

Toda alteração relevante deve gerar registro em AuditLog.

### Numeração

Todo pedido deve possuir um número único dentro da empresa.

---

## Fluxo Mobile

Representante

↓

Cria Pedido Offline

↓

Sincronização

↓

Pedido Registrado no Servidor

↓

Sent

---

## Fluxo B2B

Cliente

↓

Cria Pedido

↓

Sent

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

Possíveis evoluções:

- cancelamento de pedidos
- aprovação comercial
- aprovação financeira
- acompanhamento do pedido no ERP
- integração automática com ERP