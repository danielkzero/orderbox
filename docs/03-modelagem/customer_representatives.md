# Customer Representatives

## Objetivo

Relacionar clientes e representantes comerciais.

Permite controlar a carteira de clientes de cada representante.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| customer_id | bigint | Sim | Cliente |
| sales_representative_id | bigint | Sim | Representante |
| is_primary | boolean | Sim | Representante principal |
| created_at | timestamp | Sim | Data de criação |

---

## Relacionamentos

CustomerRepresentative

- N:1 Customer
- N:1 SalesRepresentative

---

## Regras de Negócio

### Representante Principal

Todo cliente deve possuir apenas um representante principal.

### Múltiplos Representantes

Um cliente pode possuir mais de um representante.

### Carteira

O relacionamento define a carteira comercial do representante.

---

## Exemplos

Cliente ABC

- João (Principal)
- Carlos (Auxiliar)

Cliente XYZ

- Maria (Principal)