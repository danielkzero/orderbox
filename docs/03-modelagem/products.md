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
- aparecer no B2B
- aparecer no aplicativo mobile

### Exclusão

Produtos não devem ser removidos fisicamente.

Utilizar:

active = false

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
- weight (peso)
- width (largura)
- height (altura)
- length (comprimento)
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
