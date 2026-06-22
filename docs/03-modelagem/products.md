# Products

## Objetivo

Representa os produtos comercializados pela empresa.

Na V1, os produtos compõem o catálogo utilizado pelo painel administrativo e pelo aplicativo mobile.

Todo produto pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| category_id | bigint | Sim | Categoria |
| brand_id | bigint | Não | Marca |
| unit_id | bigint | Sim | Unidade de medida |
| external_id | varchar(100) | Não | Código do produto no ERP |
| sku | varchar(100) | Sim | Código interno |
| barcode | varchar(50) | Não | Código de barras |
| image_url | varchar(255) | Não | Imagem principal |
| name | varchar(255) | Sim | Nome do produto |
| short_description | varchar(500) | Não | Descrição resumida |
| description | text | Não | Descrição completa |
| weight_kg | decimal(10,3) | Não | Peso em quilogramas |
| length_cm | decimal(10,2) | Não | Comprimento |
| width_cm | decimal(10,2) | Não | Largura |
| height_cm | decimal(10,2) | Não | Altura |
| base_price | decimal(15,2) | Não | Preço base |
| minimum_quantity | decimal(15,3) | Sim | Quantidade mínima por item de pedido |
| quantity_multiple | decimal(15,3) | Não | Múltiplo obrigatório de venda |
| allows_fractional_quantity | boolean | Sim | Permite quantidade decimal por peso ou medida |
| active | boolean | Sim | Produto ativo |
| available_stock | decimal(15,3) | Não | Estoque disponível sincronizado do ERP |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Product

- N:1 Company
- N:1 Category
- N:1 Brand
- N:1 Unit
- 1:N ProductPrices
- 1:N OrderItems

---

## Regras de Negócio

### SKU Único

Não pode existir dois produtos com o mesmo SKU dentro da mesma empresa.

### Produto Inativo

Produtos inativos não podem:

- ser vendidos
- aparecer no aplicativo mobile

### Exclusão

Produtos não devem ser removidos fisicamente.

Utilizar:

active = false

### Quantidade de Venda

`minimum_quantity` define a menor quantidade aceita no pedido.

Quando `quantity_multiple` for preenchido, a quantidade deve ser divisível pelo
múltiplo. Exemplo: múltiplo `5` aceita `5`, `10`, `15` e assim por diante.

Quando `allows_fractional_quantity = false`, somente quantidades inteiras são
aceitas. Produtos vendidos por peso ou medida usam
`allows_fractional_quantity = true` e podem receber valores como `0,750`.

---

## Casos de Uso

### Produto Próprio

Fabricado pela empresa.

Exemplo:

- Torneira Boia Airlock Valeplast

### Produto de Revenda

Comprado de terceiros para revenda.

Exemplo:

- Produto Tigre
- Produto Amanco

---

## Canais Futuros

Possíveis integrações de catálogo:

- Shopee
- Mercado Livre
- Amazon
- Tray
- Nuvemshop

---

## Observações Futuras

Possíveis recursos:

- cost_price (preço do custo)
- múltiplas imagens
- vídeos
- NCM
- CFOP
- GTIN
- EAN
- ficha técnica
- variações
- kits
- composição de produto
- produto digital
- produto controlado

---

## Importante

Preço não será armazenado diretamente na tabela de produtos.

Os preços serão gerenciados através da entidade:

ProductPrices

Permitindo múltiplas tabelas de preço.
