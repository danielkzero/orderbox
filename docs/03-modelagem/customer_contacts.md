# Customer Contacts

## Objetivo

Armazenar contatos vinculados a um cliente.

Um cliente pode possuir múltiplos contatos responsáveis por diferentes áreas da empresa.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| customer_id | bigint | Sim | Cliente proprietário |
| name | varchar(255) | Sim | Nome do contato |
| position | varchar(255) | Não | Cargo |
| department | varchar(255) | Não | Departamento |
| email | varchar(255) | Não | E-mail |
| phone | varchar(20) | Não | Telefone |
| mobile | varchar(20) | Não | Celular |
| whatsapp | varchar(20) | Não | WhatsApp |
| primary_contact | boolean | Sim | Contato principal |
| active | boolean | Sim | Contato ativo |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

CustomerContact

- N:1 Customer

---

## Tipos de Departamento

### Purchasing

Compras.

### Financial

Financeiro.

### Commercial

Comercial.

### Logistics

Logística.

### Management

Diretoria ou gestão.

### Other

Outros.

---

## Regras de Negócio

### Múltiplos Contatos

Um cliente pode possuir vários contatos.

### Contato Principal

Deve existir apenas um contato principal por cliente.

### Contato Inativo

Contatos inativos não devem receber comunicações automáticas.

### Exclusão

Utilizar exclusão lógica sempre que possível.

---

## Observações Futuras

Possíveis recursos futuros:

- integração WhatsApp
- integração e-mail marketing
- histórico de contatos
- registro de ligações
- CRM
- lembretes de follow-up