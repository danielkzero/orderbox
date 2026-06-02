# Brands

## Objetivo

Representa as marcas associadas aos produtos.

Permite organizar o catálogo, gerar relatórios comerciais e separar produtos próprios de produtos de terceiros.

Toda marca pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| name | varchar(255) | Sim | Nome da marca |
| description | text | Não | Descrição |
| logo | varchar(255) | Não | Caminho do logotipo |
| active | boolean | Sim | Marca ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Brand

- N:1 Company
- 1:N Products

---

## Regras de Negócio

### Nome Único

Não pode existir duas marcas com o mesmo nome dentro da mesma empresa.

### Exclusão

Marcas vinculadas a produtos não podem ser removidas.

Utilizar:

active = false

---

## Casos de Uso

### Marca Própria

Exemplo:

- Valeplast

### Marca de Terceiros

Exemplo:

- Tigre
- Amanco
- Krona

### Marca Branca

Produtos fabricados para terceiros.

---

## Observações Futuras

Possíveis recursos:

- website
- fabricante
- país de origem
- imagem da marca
- página exclusiva no B2B