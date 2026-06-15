# Sync Changes

## Objetivo

Registrar alterações que precisam ser entregues aos dispositivos mobile.

Permite sincronização incremental, revogação de acesso e propagação de remoções sem depender apenas de `updated_at`.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| sequence | bigint | Sim | Sequência global crescente |
| entity_type | varchar(100) | Sim | Tipo da entidade alterada |
| entity_id | bigint | Sim | Identificador no servidor |
| change_type | varchar(50) | Sim | Tipo da alteração |
| audience_type | varchar(50) | Sim | Escopo de destinatários |
| audience_id | bigint | Não | Representante ou usuário específico |
| created_at | timestamp | Sim | Data da alteração |

---

## Tipos de Alteração

- Upsert
- Delete
- Revoke

---

## Escopos

- Company: todos os dispositivos ativos da empresa;
- SalesRepresentative: dispositivos do representante;
- User: dispositivos do usuário.

---

## Regras de Negócio

### Cursor

O cursor da sincronização representa a última `sequence` confirmada pelo dispositivo.

### Imutabilidade

Registros não podem ser alterados ou removidos pela aplicação.

### Conteúdo

SyncChange registra apenas a referência da mudança. O payload atual é montado pela API no momento da leitura.
