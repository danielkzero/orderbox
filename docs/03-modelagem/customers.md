# Customers

## Objetivo

Representa um cliente da empresa.

Na V1, clientes realizam pedidos por meio de representantes ou do painel administrativo.

Todo cliente pertence obrigatoriamente a uma Company.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| client_reference | uuid | Não | Referência gerada pelo mobile |
| corporate_name | varchar(255) | Sim | Razão social |
| trade_name | varchar(255) | Não | Nome fantasia |
| document | varchar(20) | Sim | CPF ou CNPJ |
| state_registration | varchar(50) | Não | Inscrição estadual |
| email | varchar(255) | Não | E-mail principal |
| phone | varchar(20) | Não | Telefone principal |
| credit_limit | decimal(15,2) | Não | Limite de crédito |
| active | boolean | Sim | Cliente ativo |
| version | integer | Sim | Versão para controle de concorrência |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Customer

- N:1 Company
- 1:N CustomerAddresses
- 1:N CustomerContacts
- 1:N CustomerRepresentatives
- 1:N Orders

---

## Tipos de Cliente

### Pessoa Física

Utiliza CPF.

### Pessoa Jurídica

Utiliza CNPJ.

---

## Regras de Negócio

### Documento Único

Não pode existir dois clientes com o mesmo CPF ou CNPJ dentro da mesma empresa.

### Cliente Inativo

Clientes inativos não podem:

- criar pedidos

### Limite de Crédito

Na V1, o limite de crédito é apenas informativo. Bloqueio financeiro depende de integração e regras futuras.

### Concorrência

Atualizações devem informar a versão atual do cliente. Alterações com versão desatualizada são rejeitadas.

### Exclusão

Clientes não devem ser removidos fisicamente.

Utilizar apenas:

active = false

---

## Observações Futuras

Possíveis recursos futuros:

- classificação de cliente
- segmento de mercado
- vendedor responsável
- tabela de preço personalizada
- limite financeiro avançado
- score de crédito
