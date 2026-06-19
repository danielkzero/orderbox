# Prazos de Pagamento

## Objetivo

Definir condições de vencimento disponíveis para os pedidos de uma empresa.

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| id | bigint | Sim | Identificador |
| company_id | bigint | Sim | Empresa proprietária |
| code | varchar(50) | Sim | Código persistido no pedido e usado em integrações |
| name | varchar(100) | Sim | Nome exibido ao usuário |
| installment_days | json | Sim | Dias corridos de cada parcela |
| description | text | Não | Orientação comercial |
| sort_order | smallint unsigned | Sim | Ordem de exibição |
| active | boolean | Sim | Disponibilidade para novos pedidos |
| created_at | timestamp | Sim | Criação |
| updated_at | timestamp | Sim | Atualização |

## Regras

- `code` e `name` são únicos dentro da empresa;
- `installment_days` possui ao menos um inteiro entre `0` e `3650`;
- o valor `0` representa pagamento à vista;
- dias repetidos são normalizados e armazenados em ordem crescente;
- somente registros ativos podem ser usados em novos pedidos;
- inativação não altera pedidos já criados;
- o pedido mantém o código como snapshot histórico.
