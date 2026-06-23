# Price Tables

## Objetivo

Representa as tabelas de preços utilizadas pela empresa.

Permite que um mesmo produto possua diferentes preços para diferentes públicos ou canais de venda.

Toda tabela de preço pertence a uma Company.

Tabelas inativas permanecem armazenadas para preservar preços, vínculos e
pedidos históricos. O campo `active = false` impede seu uso em novas operações.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| name | varchar(255) | Sim | Nome da tabela |
| description | text | Não | Descrição |
| active | boolean | Sim | Tabela ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

PriceTable

- N:1 Company
- N:N Region por `region_price_table`
- 1:N ProductPrices

---

## Exemplos

### Varejo

Preço padrão.

### Atacado

Preço reduzido para compras em volume.

### Distribuidor

Preço exclusivo para distribuidores.

### Marketplace

Preço utilizado em marketplaces.

### B2B

Preço utilizado no portal B2B.

---

## Regras de Negócio

### Nome Único

Não pode existir duas tabelas com o mesmo nome dentro da mesma empresa.

### Vínculo Regional

O vínculo é persistido em `region_price_table` e mantido exclusivamente pelo
módulo Regiões. Uma tabela pode atender várias regiões; sem vínculos, é global.

### Manutenção no Admin

A entidade não possui módulo administrativo independente. A criação e a
renomeação são realizadas no cabeçalho da listagem de Produtos, sempre com
filtro por `company_id`. A criação define a tabela como ativa e não altera
preços ou vínculos regionais existentes.

### Exclusão

Tabelas vinculadas a produtos não podem ser removidas.

Utilizar:

active = false

---

## Observações Futuras

Possíveis recursos:

- validade
- promoção
- desconto automático
- regras por cliente
- regras por região
