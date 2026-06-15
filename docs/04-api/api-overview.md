# API - Visão Geral

## Objetivo

A API do OrderBox será responsável por atender:

- Mobile
- Integrações futuras

O painel Admin é renderizado pelo Laravel com Blade e utiliza sessão Web. Admin e API compartilham as mesmas regras de negócio e o mesmo banco, mas possuem mecanismos de autenticação distintos.

---

## Autenticação

A API utiliza Bearer Token emitido pelo Laravel Sanctum.

O painel Admin utiliza sessão Web Laravel e não consome Bearer Token para navegação autenticada.

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
