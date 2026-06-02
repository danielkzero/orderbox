# Sales Representatives

## Objetivo

Representa os vendedores e representantes comerciais da empresa.

Permite controlar carteira de clientes, regiões de atuação, metas e comissões.

Todo representante está vinculado a um usuário.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário vinculado |
| supervisor_id | bigint | Não | Supervisor responsável |
| code | varchar(50) | Sim | Código do representante |
| commission_percentage | decimal(5,2) | Não | Percentual de comissão |
| active | boolean | Sim | Representante ativo |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

SalesRepresentative

- N:1 Company
- 1:1 User
- N:1 Supervisor
- 1:N CustomerRepresentatives

---

## Regras de Negócio

### Usuário Obrigatório

Todo representante deve possuir um usuário associado.

### Código Único

O código deve ser único dentro da empresa.

### Exclusão

Representantes não devem ser removidos.

Utilizar:

active = false

---

## Observações Futuras

Possíveis recursos:

- metas
- comissão avançada
- região
- rota de visitas
- ranking de vendas