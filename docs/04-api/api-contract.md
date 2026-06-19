# Contrato da API V1

## Objetivo

Definir o contrato HTTP mínimo para implementação do Mobile e de integrações futuras.

O painel Admin utiliza Blade e sessão Web Laravel. As regras de negócio são compartilhadas com a API, mas suas rotas de interface não fazem parte deste contrato.

---

## Convenções HTTP

- Base URL: `/api/v1`
- Conteúdo: `application/json`
- Datas: ISO 8601 em UTC
- Autenticação: `Authorization: Bearer <token>`
- Identificadores internos: números inteiros
- Referências offline: UUID
- Nomes de campos: `snake_case`

---

## Resposta de Sucesso

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

`meta` é omitido quando não houver paginação ou metadados adicionais.

## Resposta de Erro

```json
{
  "success": false,
  "error": {
    "code": "validation_error",
    "message": "Não foi possível processar a solicitação.",
    "fields": {
      "document": ["Documento inválido."]
    }
  }
}
```

`code` é estável e deve ser utilizado pelos clientes. `message` pode ser ajustada sem quebrar o contrato.

---

## Códigos HTTP

| Código | Uso |
|---------|---------|
| 200 | Consulta ou atualização concluída |
| 201 | Recurso criado |
| 204 | Remoção de rascunho concluída |
| 400 | Requisição malformada |
| 401 | Token ausente ou inválido |
| 403 | Operação não permitida |
| 404 | Recurso inexistente dentro da Company |
| 409 | Conflito de versão, unicidade ou estado |
| 422 | Validação de negócio |
| 429 | Limite de requisições excedido |
| 500 | Erro interno sem detalhes sensíveis |

Recursos de outra Company devem responder como inexistentes, evitando exposição de IDs.

Campos `document` aceitam CPF, CNPJ numérico e CNPJ alfanumérico. Pontuação é opcional; o servidor normaliza letras para maiúsculas, remove a pontuação e valida os dígitos verificadores.

---

## Paginação e Filtros

Listagens usam paginação por cursor:

```text
?limit=50&cursor=<cursor_opaco>
```

- `limit` padrão: 50;
- `limit` máximo: 100;
- o cursor é opaco e não deve ser interpretado pelo cliente;
- filtros são combinados com AND;
- ordenação padrão deve ser estável e incluir `id` como desempate.

Resposta:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "next_cursor": null,
    "has_more": false
  }
}
```

---

## Autenticação

| Método | Rota | Uso |
|---------|---------|---------|
| POST | `/auth/login` | Autenticar usuário |
| POST | `/auth/2fa/confirm` | Confirmar login pendente protegido por 2FA |
| POST | `/auth/logout` | Revogar token atual |
| GET | `/auth/me` | Retornar usuário, Company e perfil |

O login recebe `email` e `password`. Na V1, o e-mail de acesso é globalmente único.

Cada usuário mantém no máximo uma autenticação Web e uma Mobile ativas. Um novo login concluído revoga a sessão anterior do mesmo canal. Quando 2FA estiver ativo, a revogação ocorre somente após a confirmação do segundo fator.

---

## Permissões

| Recurso | Admin | Manager | SalesRepresentative |
|---------|---------|---------|---------|
| Usuários e representantes | Gerenciar | Consultar | Sem acesso |
| Clientes | Gerenciar | Gerenciar | Consultar e editar carteira; criar cliente |
| Produtos e preços | Gerenciar | Gerenciar | Consultar |
| Pedidos | Gerenciar | Gerenciar | Gerenciar próprios rascunhos; consultar próprios pedidos |
| Cancelar pedido enviado | Sim | Sim | Não |
| Auditoria | Consultar | Consultar | Sem acesso |
| Sincronização mobile | Sem uso | Sem uso | Sim |

---

## Endpoints da V1

### Disponibilidade atual

Atualmente estão implementados:

- `POST /auth/login`;
- `POST /auth/2fa/confirm`;
- `GET /auth/me`;
- `POST /auth/logout`;
- `GET /health`;
- `GET /ready`.

Os endpoints funcionais descritos nas seções seguintes permanecem como
contrato planejado e não devem ser consumidos por Mobile ou integrações até
serem marcados como implementados e cobertos por testes de contrato.

### Users

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/users` | Listar usuários da Company |
| POST | `/users` | Criar usuário |
| GET | `/users/{id}` | Consultar usuário |
| PATCH | `/users/{id}` | Atualizar usuário |
| POST | `/users/{id}/deactivate` | Inativar usuário |

### Customers

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/customers` | Listar clientes acessíveis |
| POST | `/customers` | Criar cliente |
| GET | `/customers/{id}` | Consultar agregado do cliente |
| PATCH | `/customers/{id}` | Atualizar cliente usando `version` |
| POST | `/customers/{id}/deactivate` | Inativar cliente |
| PUT | `/customers/{id}/representatives` | Substituir vínculos de representantes |

O agregado de Customer inclui endereços, contatos e representantes vinculados quando solicitado individualmente.

### Sales Representatives

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/sales-representatives` | Listar representantes |
| POST | `/sales-representatives` | Criar representante vinculado a usuário |
| GET | `/sales-representatives/{id}` | Consultar representante |
| PATCH | `/sales-representatives/{id}` | Atualizar representante |
| POST | `/sales-representatives/{id}/deactivate` | Inativar representante |

### Catalog

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/categories` | Listar categorias |
| POST | `/categories` | Criar categoria |
| PATCH | `/categories/{id}` | Atualizar categoria |
| GET | `/brands` | Listar marcas |
| POST | `/brands` | Criar marca |
| PATCH | `/brands/{id}` | Atualizar marca |
| GET | `/units` | Listar unidades |
| POST | `/units` | Criar unidade |
| PATCH | `/units/{id}` | Atualizar unidade |
| GET | `/products` | Listar produtos ativos |
| POST | `/products` | Criar produto |
| GET | `/products/{id}` | Consultar produto e preços |
| PATCH | `/products/{id}` | Atualizar produto |
| POST | `/products/{id}/deactivate` | Inativar produto |
| GET | `/price-tables` | Listar tabelas ativas |
| POST | `/price-tables` | Criar tabela de preço |
| GET | `/price-tables/{id}` | Consultar tabela e preços |
| PATCH | `/price-tables/{id}` | Atualizar tabela |
| PUT | `/price-tables/{id}/products` | Substituir preços da tabela |

As rotas de escrita de catálogo são restritas a Admin e Manager.

### Orders

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/orders` | Listar pedidos acessíveis |
| POST | `/orders` | Criar Draft |
| GET | `/orders/{id}` | Consultar pedido completo |
| PUT | `/orders/{id}` | Substituir agregado do Draft usando `version` |
| DELETE | `/orders/{id}` | Remover Draft |
| POST | `/orders/{id}/send` | Validar, numerar e enviar Draft |
| POST | `/orders/{id}/cancel` | Cancelar pedido Sent |

`PUT /orders/{id}` substitui itens e descontos do Draft em uma transação.

### Audit

| Método | Rota | Comportamento |
|---------|---------|---------|
| GET | `/audit-logs` | Listar eventos auditáveis |
| GET | `/audit-logs/{id}` | Consultar evento |

### Sync

O contrato de sincronização está em [sync-contract.md](sync-contract.md).

---

## Contrato de Pedido

Exemplo de criação:

```json
{
  "client_reference": "834d63b0-6988-47d5-ac8d-3500e6056e93",
  "customer_id": 123,
  "sales_representative_id": 45,
  "price_table_id": 8,
  "payment_method": "boleto",
  "payment_terms": "15/30/45",
  "notes": "Entregar pela manhã",
  "items": [
    {
      "product_id": 900,
      "quantity": 10,
      "unit_price": 34.90,
      "discounts": []
    }
  ]
}
```

Regras:

- Admin e Mobile selecionam explicitamente a tabela de preço na V1.
- Forma e prazo usam os códigos ativos disponibilizados pela empresa.
- O servidor recalcula subtotal e total; valores calculados enviados pelo cliente são ignorados.
- O prazo é rejeitado quando o total final recalculado for inferior ao
  `minimum_order_amount` configurado para ele.
- O preço informado deve corresponder a uma faixa válida da tabela selecionada, salvo permissão administrativa explícita.
- Descontos são aplicados na ordem enviada; percentuais aceitam valores entre `0` e `100`, e valores fixos não podem ser negativos.
- O total de um item e do pedido possui piso zero.
- `order_number` é atribuído pelo servidor quando o pedido é aceito.
- O envio exige ao menos um item, Customer ativo, representante ativo, tabela de preço ativa e condições de pagamento válidas.
- Aplicações que operam como representante devem respeitar também as tabelas
  visíveis vinculadas ao representante.

---

## Idempotência

Rotas de comando originadas online podem receber:

```text
Idempotency-Key: <uuid>
```

O Mobile deve usar o contrato de SyncOperations. Repetir uma chave deve retornar o resultado original sem duplicar efeitos.

---

## Concorrência e Conflitos

Atualizações de Customer e Order recebem `version`.

Quando a versão estiver desatualizada:

```json
{
  "success": false,
  "error": {
    "code": "version_conflict",
    "message": "O registro foi alterado por outro usuário."
  }
}
```

O cliente deve atualizar os dados antes de tentar novamente.
