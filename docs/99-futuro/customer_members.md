# Customer Members

## Objetivo

Relacionar usuários aos clientes para acesso ao portal B2B.

Permite que uma empresa cliente possua vários usuários com diferentes responsabilidades.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| customer_id | bigint | Sim | Cliente |
| user_id | bigint | Sim | Usuário |
| role | varchar(50) | Sim | Perfil dentro do cliente |
| active | boolean | Sim | Vínculo ativo |
| created_at | timestamp | Sim | Data de criação |

---

## Relacionamentos

CustomerMember

- N:1 Customer
- N:1 User

---

## Perfis

### Buyer

Comprador.

### Financial

Financeiro.

### Manager

Gestor.

### Viewer

Somente consulta.

---

## Regras de Negócio

### Múltiplos Usuários

Um cliente pode possuir vários usuários.

### Múltiplos Clientes

Um usuário poderá futuramente participar de mais de um cliente.

### Acesso

As permissões do portal B2B serão controladas através deste vínculo.

O usuário e o cliente vinculados devem pertencer à mesma Company.
