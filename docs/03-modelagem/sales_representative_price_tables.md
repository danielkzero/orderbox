# Tabelas Visíveis por Representante

## Objetivo

Controlar quais tabelas de preço um representante pode visualizar e selecionar.

## Estrutura

Tabela pivô `sales_representative_price_table`:

| Campo | Tipo | Obrigatório |
|---|---|---|
| id | bigint | Sim |
| sales_representative_id | bigint | Sim |
| price_table_id | bigint | Sim |
| created_at | timestamp | Sim |
| updated_at | timestamp | Sim |

O par `(sales_representative_id, price_table_id)` é único.

O vínculo limita visibilidade e seleção, mas não substitui as regras de
aplicabilidade da tabela por cliente, região, produto e faixa de quantidade.
