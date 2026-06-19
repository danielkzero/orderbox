# Audit Logs

## Objetivo

Registrar todas as ações importantes realizadas pelos usuários dentro do sistema.

A auditoria permite rastrear alterações, identificar responsáveis e garantir segurança operacional.

Todo registro de auditoria pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário responsável |
| action | varchar(100) | Sim | Ação executada |
| entity_type | varchar(100) | Sim | Entidade afetada |
| entity_id | bigint | Sim | Registro afetado |
| entity_label | varchar(255) | Não | Identificação legível preservada no momento da ação |
| old_values | json | Não | Valores anteriores |
| new_values | json | Não | Novos valores |
| ip_address | varchar(45) | Não | IP do usuário |
| user_agent | text | Não | Navegador ou dispositivo |
| created_at | timestamp | Sim | Data da ação |

---

## Relacionamentos

AuditLog

- N:1 Company
- N:1 User

---

## Tipos de Ação

### Create

Criação de registro.

### Update

Alteração de registro.

### Delete

Exclusão lógica.

### Login

Entrada no sistema.

### Logout

Saída do sistema.

### Approve

Aprovação. Ação reservada para fluxos futuros.

### Cancel

Cancelamento.

### Export

Exportação de dados.

---

## Exemplos

### Produto

Usuário alterou:

Produto #123

Preço:

39,90

↓

34,90

---

### Pedido

Pedido #456

Status:

Draft

↓

Sent

---

### Cliente

Cliente #789

Status:

Ativo

↓

Inativo

---

## Regras de Negócio

### Imutabilidade

Registros de auditoria nunca podem ser alterados.

### Exclusão

Registros de auditoria nunca podem ser removidos.

### Rastreabilidade

Toda alteração crítica deve gerar auditoria.

O log deve preservar uma identificação operacional do registro. Para pedidos,
`entity_label` armazena o `order_number`, permitindo reconhecer diretamente qual
pedido foi enviado, cancelado ou alterado sem depender apenas do ID interno.

---

## Eventos Obrigatórios

Devem gerar auditoria:

- Login
- Logout
- Substituição de sessão
- Revogação administrativa de sessão
- Cadastro de cliente
- Cadastro de produto
- Alteração de preços
- Criação de pedidos
- Envio de pedidos
- Cancelamento de pedidos
- Alteração de permissões

---

## Observações Futuras

Possíveis recursos:

- auditoria avançada
- trilha completa de alterações
- exportação LGPD
- monitoramento de segurança
- alertas automáticos
- auditoria de movimentações de estoque
- auditoria de recebimentos financeiros
