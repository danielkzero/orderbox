# Users

## Objetivo

Representa um usuário autenticado dentro de uma empresa.

Usuários são responsáveis por acessar o sistema, executar operações e registrar ações auditáveis.

Todo usuário pertence obrigatoriamente a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| name | varchar(255) | Sim | Nome completo |
| email | varchar(255) | Sim | E-mail de acesso |
| password | varchar(255) | Sim | Senha criptografada |
| role | varchar(50) | Sim | Perfil do usuário |
| active | boolean | Sim | Usuário ativo |
| last_login_at | timestamp | Não | Último acesso |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

User

- N:1 Company
- 1:N Orders
- 1:N AuditLogs
- 1:N Devices
- 1:0..1 SalesRepresentative

---

## Perfis (Role)

### Admin

Possui acesso total ao sistema.

### Manager

Possui acesso gerencial.

### SalesRepresentative

Representante comercial.

Pode acessar:

- aplicativo mobile
- carteira de clientes
- pedidos
- indicadores comerciais

## Regras de Negócio

### Empresa Obrigatória

Todo usuário deve pertencer a uma Company.

### Login

O login será realizado através de:

- email
- senha

Na V1, o e-mail de acesso deve ser globalmente único para permitir autenticação sem seleção prévia da Company.

### Usuário Inativo

Usuários inativos não podem acessar o sistema.

### Exclusão

Usuários não devem ser excluídos fisicamente.

Utilizar apenas:

active = false

---

## Permissões Futuras

Em versões futuras o campo role poderá ser substituído por:

- Roles
- Permissions

Permitindo permissões mais granulares.

Perfis para estoque, financeiro e clientes do Portal B2B também poderão ser adicionados quando esses módulos entrarem no escopo.

---

## Observações Futuras

Possíveis campos:

- avatar
- telefone
- cargo
- foto_perfil
- token_2fa
- ultimo_ip
