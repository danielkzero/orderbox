# Product Prices

## Objetivo

Armazenar os preços dos produtos em diferentes tabelas de preço.

Permite que um produto possua múltiplos valores de venda.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| product_id | bigint | Sim | Produto |
| price_table_id | bigint | Sim | Tabela de preço |
| price | decimal(15,2) | Sim | Valor de venda |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

ProductPrice

- N:1 Product
- N:1 PriceTable

---

## Casos de Uso

### Produto

Torneira Boia Airlock

### Preços

Varejo

R$ 39,90

Atacado

R$ 34,90

Distribuidor

R$ 29,90

Marketplace

R$ 44,90

---

## Regras de Negócio

### Preço Obrigatório

Todo produto vendido deve possuir pelo menos um preço cadastrado em uma tabela de preço ativa.

### Unicidade

Não pode existir dois registros para:

- mesmo produto
- mesma tabela de preço

A quantidade mínima e o múltiplo pertencem ao produto e não alteram o preço da
tabela.

---

## Observações Futuras

Possíveis recursos:

- preço promocional
- validade inicial
- validade final
- preço por região
- preço por cliente
