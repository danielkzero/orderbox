# Units

## Objetivo

Representa as unidades de medida utilizadas pelos produtos.

As unidades são utilizadas em estoque, vendas, compras, relatórios e integrações fiscais.

Toda unidade pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| code | varchar(10) | Sim | Código da unidade |
| name | varchar(100) | Sim | Nome da unidade |
| description | text | Não | Descrição |
| active | boolean | Sim | Unidade ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Unit

- N:1 Company
- 1:N Products

---

## Exemplos

| Código | Nome |
|----------|----------|
| UN | Unidade |
| PC | Peça |
| CX | Caixa |
| KG | Quilograma |
| G | Grama |
| MT | Metro |
| CM | Centímetro |
| LT | Litro |
| ML | Mililitro |

---

## Regras de Negócio

### Código Único

Não pode existir duas unidades com o mesmo código dentro da mesma empresa.

### Exclusão

Unidades vinculadas a produtos não podem ser removidas.

Utilizar:

active = false

---

## Casos de Uso

### Venda Unitária

Produto vendido individualmente.

Exemplo:

UN

### Venda por Caixa

Produto vendido em embalagem fechada.

Exemplo:

CX

### Venda por Peso

Produto vendido por peso.

Exemplo:

KG

---

## Observações Futuras

Possíveis recursos:

- fator de conversão
- unidade de compra
- unidade de venda
- unidade de estoque
- múltiplas unidades por produto