# API - Visão Geral

## Objetivo

A API do OrderBox será responsável por atender:

- Admin
- Mobile
- Integrações futuras

Todas as aplicações utilizarão a mesma API.

---

## Autenticação

A autenticação será realizada através de:

Bearer Token

---

## Padrão de Resposta

Sucesso:

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

`meta` será omitido quando não houver metadados adicionais.

Erro:

```json
{
  "success": false,
  "error": {
    "code": "validation_error",
    "message": "Mensagem de erro",
    "fields": {}
  }
}
```

---

## Recursos da API

### Auth

- Login
- Logout
- Perfil

### Customers

- Listar
- Visualizar
- Criar
- Atualizar

### Products

- Listar
- Visualizar

### Orders

- Listar
- Visualizar
- Criar
- Atualizar rascunho
- Remover rascunho
- Enviar
- Cancelar

### Sync

- Sincronizar dados
- Enviar pedidos

---

## Versionamento

Todas as rotas utilizarão:

```text
/api/v1
```

Exemplo:

```text
/api/v1/customers
/api/v1/products
/api/v1/orders
```

---

## Contratos Detalhados

- [Contrato da API V1](api-contract.md)
- [Contrato de sincronização mobile](sync-contract.md)
