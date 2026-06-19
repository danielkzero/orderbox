# Contrato de Sincronização Mobile V1

## Objetivo

Definir um processo incremental, idempotente e recuperável para operação offline do representante.

---

## Princípios

- O servidor é a fonte de verdade.
- O mobile mantém uma base local para operação offline.
- Cada comando offline possui `operation_id` UUID.
- Cada entidade criada offline possui `client_reference` UUID.
- O cursor de leitura é opaco para o mobile.
- Uma falha parcial não deve duplicar operações já aplicadas.
- O mobile só recebe dados permitidos para o representante autenticado.

---

## Dados Disponíveis Offline

O mobile mantém:

- perfil, Company e representante;
- clientes da carteira, endereços e contatos;
- categorias, marcas, unidades, produtos e preços ativos;
- pedidos criados pelo representante;
- fila local de operações pendentes.

Imagens de produtos podem ser mantidas por cache separado e não fazem parte do payload transacional.

---

## Registro do Dispositivo

| Método | Rota | Uso |
|---------|---------|---------|
| POST | `/sync/devices/register` | Registrar ou reativar dispositivo |

Requisição:

```json
{
  "device_uuid": "41892a58-6656-4cd8-a246-5e8830bbabe4",
  "platform": "android",
  "app_version": "1.0.0"
}
```

---

## Execução da Sincronização

| Método | Rota | Uso |
|---------|---------|---------|
| POST | `/sync` | Enviar operações e receber mudanças |

Requisição:

```json
{
  "device_uuid": "41892a58-6656-4cd8-a246-5e8830bbabe4",
  "cursor": "cursor_opaco_ou_null",
  "operations": [
    {
      "operation_id": "41e57308-bb08-4ba9-bce6-26989814a3cd",
      "type": "customer.create",
      "client_reference": "7ed7d944-6ac6-45b9-9ed8-96ef3ecbd7b9",
      "payload": {}
    }
  ]
}
```

Resposta:

```json
{
  "success": true,
  "data": {
    "operation_results": [
      {
        "operation_id": "41e57308-bb08-4ba9-bce6-26989814a3cd",
        "status": "applied",
        "entity_id": 123,
        "client_reference": "7ed7d944-6ac6-45b9-9ed8-96ef3ecbd7b9"
      }
    ],
    "changes": [],
    "next_cursor": "novo_cursor_opaco",
    "has_more": false,
    "server_time": "2026-06-15T15:00:00Z"
  }
}
```

---

## Operações Aceitas

| Operação | Observação |
|---------|---------|
| `customer.create` | Cria Customer, endereço, contato principal e vínculo ao representante |
| `customer.update` | Exige `entity_id` e `version` |
| `order.create` | Cria Draft; aceita Customer por `entity_id` ou `client_reference` já resolvida |
| `order.update` | Substitui agregado do Draft e exige `version` |
| `order.delete` | Remove Draft |
| `order.send` | Envia Draft após validação |

Cancelamento de pedido não é permitido offline na V1.

---

## Ordem de Processamento

1. Validar token, Company, usuário e dispositivo.
2. Criar SyncLog com status Running.
3. Processar operações na ordem recebida.
4. Para cada operação, consultar SyncOperation pelo `operation_id`.
5. Retornar o resultado existente ou executar a operação em transação.
6. Ler SyncChanges posteriores ao cursor e permitidos ao representante.
7. Finalizar SyncLog e retornar o próximo cursor.

Uma operação rejeitada não impede o processamento das operações independentes seguintes. Operações dependentes devem permanecer pendentes no mobile.

Quando um `order.create` depender de um `customer.create` no mesmo lote, a operação do cliente deve aparecer primeiro. O pedido referencia o Customer pelo `client_reference`, que o servidor resolve após aplicar a criação.

---

## Mudanças Recebidas

Cada mudança possui:

```json
{
  "type": "upsert",
  "entity": "product",
  "entity_id": 900,
  "payload": {}
}
```

Tipos:

- `upsert`: inserir ou substituir versão local;
- `delete`: remover registro local;
- `revoke`: remover registro local por perda de permissão.

Customers e Orders são entregues como agregados completos. Uma atualização substitui os dados locais do agregado.

---

## Conflitos

### Customer

Atualizações usam concorrência otimista.

Quando `version` estiver desatualizada, a operação é rejeitada com `version_conflict`. O servidor não combina campos automaticamente.

### Order

- Draft pode ser alterado apenas pelo usuário responsável ou por perfil administrativo.
- Sent e Cancelled são imutáveis para o mobile.
- Conflito de versão exige atualizar o pedido antes de reenviar alterações.

### Catálogo e Preços

São somente leitura no mobile. A versão recebida do servidor substitui a versão local.

Formas e prazos de pagamento ativos também são sincronizados como cadastros de
leitura para composição de novos pedidos.

---

## Inicialização e Recuperação

- Primeiro acesso usa `cursor = null` e recebe uma carga inicial paginada.
- Enquanto `has_more = true`, o mobile repete a sincronização com o `next_cursor`.
- O cursor só é persistido localmente após aplicar todas as mudanças da resposta.
- Se o cursor expirar ou ficar inválido, a API retorna `sync_reset_required`.
- Ao receber `sync_reset_required`, o mobile limpa dados sincronizados, preserva operações pendentes e executa nova carga inicial.

---

## Limites

- Máximo de 100 operações por requisição.
- Máximo de 500 mudanças por resposta.
- Payload máximo deve ser definido na infraestrutura e documentado antes da publicação.
- Operações rejeitadas permanecem visíveis na tela de sincronização até correção ou descarte pelo usuário.

---

## Segurança

- Tokens e senhas não podem ser registrados em SyncLogs ou SyncOperations.
- O servidor valida novamente preços, carteira, Company e estado das entidades.
- O mobile não pode definir `company_id`, `user_id`, totais calculados ou status final diretamente.
