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
| minimum_quantity | decimal(15,3) | Não | Quantidade mínima |
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
- mesma quantidade mínima

Quando `minimum_quantity` não for informada, o registro representa a faixa base do produto na tabela.

### Quantidade Mínima

Permite preços escalonados.

Exemplo:

1 unidade = R$ 39,90

10 unidades = R$ 34,90

50 unidades = R$ 29,90

---

## Observações Futuras

Possíveis recursos:

- preço promocional
- validade inicial
- validade final
- preço por região
- preço por cliente
