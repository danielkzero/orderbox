# Price Tables

## Objetivo

Representa as tabelas de preços utilizadas pela empresa.

Permite que um mesmo produto possua diferentes preços para diferentes públicos ou canais de venda.

Toda tabela de preço pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| region_id | bigint | Não | Região responsável pelo vínculo; mantido exclusivamente pelo módulo Regiões |
| name | varchar(255) | Sim | Nome da tabela |
| description | text | Não | Descrição |
| active | boolean | Sim | Tabela ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

PriceTable

- N:1 Company
- N:0..1 Region
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

O campo `region_id` é um detalhe de persistência da cardinalidade atual. Ele
não pode ser alterado no módulo Tabelas de Preço nem no módulo Produtos.

O vínculo é mantido exclusivamente pelo módulo Regiões.

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
