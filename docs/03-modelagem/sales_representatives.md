# Sales Representatives

## Objetivo

Representa os vendedores e representantes comerciais da empresa.

Permite controlar a carteira de clientes de cada representante.

Todo representante está vinculado a um usuário.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário vinculado |
| code | varchar(50) | Sim | Código do representante |
| active | boolean | Sim | Representante ativo |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

SalesRepresentative

- N:1 Company
- 1:1 User
- 1:N CustomerRepresentatives
- 1:N Orders

---

## Regras de Negócio

### Código Único

O código deve ser único dentro da empresa.

### Exclusão

Representantes não devem ser removidos.

Utilizar:

active = false

---

## Observações Futuras

Possíveis recursos:

- supervisor relacionado (usuário do sistema)
- metas
- comissão
- região
- rota de visitas
- ranking de vendas
