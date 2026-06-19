# Envios de Pedidos

## Objetivo

Registrar cada distribuição de um pedido por e-mail ou WhatsApp.

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| id | bigint | Sim | Identificador |
| company_id | bigint | Sim | Empresa proprietária |
| order_id | bigint | Sim | Pedido compartilhado |
| user_id | bigint | Sim | Usuário responsável |
| channel | varchar(20) | Sim | `Email` ou `WhatsApp` |
| recipient | varchar(255) | Não | E-mail ou telefone utilizado |
| status | varchar(20) | Sim | Resultado operacional |
| details | text | Não | Informações complementares |
| sent_at | timestamp | Não | Data da ação |
| created_at | timestamp | Sim | Criação |
| updated_at | timestamp | Sim | Atualização |

## Segurança

- todo acesso respeita `company_id`;
- representantes acessam somente envios dos próprios pedidos;
- links públicos de PDF exigem assinatura temporária.
