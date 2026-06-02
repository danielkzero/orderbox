# Customer Addresses

## Objetivo

Armazenar os endereços vinculados a um cliente.

Um cliente pode possuir múltiplos endereços para diferentes finalidades, como entrega, cobrança e faturamento.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| customer_id | bigint | Sim | Cliente proprietário |
| type | varchar(50) | Sim | Tipo de endereço |
| zip_code | varchar(10) | Sim | CEP |
| street | varchar(255) | Sim | Logradouro |
| number | varchar(20) | Sim | Número |
| complement | varchar(255) | Não | Complemento |
| district | varchar(255) | Sim | Bairro |
| city | varchar(255) | Sim | Cidade |
| state | varchar(2) | Sim | UF |
| country | varchar(100) | Sim | País |
| default_address | boolean | Sim | Endereço padrão |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

CustomerAddress

- N:1 Customer

---

## Tipos de Endereço

### Billing

Endereço de cobrança.

### Delivery

Endereço de entrega.

### Headquarters

Endereço principal.

### Other

Outros tipos de endereço.

---

## Regras de Negócio

### Múltiplos Endereços

Um cliente pode possuir vários endereços.

### Endereço Padrão

Deve existir apenas um endereço padrão para cada tipo.

### Exclusão

Endereços podem ser removidos desde que não estejam vinculados a operações ativas.

---

## Observações Futuras

Possíveis recursos futuros:

- geolocalização
- latitude
- longitude
- roteirização de visitas
- cálculo automático de frete