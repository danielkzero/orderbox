# Categories

## Objetivo

Organizar os produtos em grupos para facilitar buscas, filtros, relatórios e navegação.

Toda categoria pertence a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| parent_id | bigint | Não | Categoria pai |
| name | varchar(255) | Sim | Nome da categoria |
| description | text | Não | Descrição |
| active | boolean | Sim | Categoria ativa |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Category

- N:1 Company
- 1:N Products
- N:1 ParentCategory
- 1:N ChildCategories

---

## Estrutura Hierárquica

Uma categoria pode possuir subcategorias.

Exemplo:

Hidráulica
├── Torneiras
├── Boias
├── Conexões

Elétrica
├── Tomadas
├── Interruptores

---

## Regras de Negócio

### Nome Único

Não pode existir duas categorias com o mesmo nome dentro da mesma empresa e no mesmo nível hierárquico.

### Categoria Pai

Uma categoria pode possuir uma categoria pai.

### Exclusão

Categorias com produtos vinculados não podem ser removidas.

Utilizar:

active = false

---

## Observações Futuras

Possíveis recursos:

- imagem da categoria
- SEO para B2B
- ordem de exibição
- ícone personalizado
- categoria destaque