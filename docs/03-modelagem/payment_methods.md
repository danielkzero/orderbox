# Formas de Pagamento

## Objetivo

Definir as formas de pagamento disponíveis para os pedidos de uma empresa.

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| id | bigint | Sim | Identificador |
| company_id | bigint | Sim | Empresa proprietária |
| code | varchar(50) | Sim | Código persistido no pedido e usado em integrações |
| name | varchar(100) | Sim | Nome exibido ao usuário |
| description | text | Não | Orientação de uso |
| sort_order | smallint unsigned | Sim | Ordem de exibição |
| active | boolean | Sim | Disponibilidade para novos pedidos |
| created_at | timestamp | Sim | Criação |
| updated_at | timestamp | Sim | Atualização |

## Regras

- `code` e `name` são únicos dentro da empresa;
- somente registros ativos podem ser usados em novos pedidos;
- inativação não altera pedidos já criados;
- o pedido mantém o código como snapshot histórico;
- consultas e validações são limitadas por `company_id`.
