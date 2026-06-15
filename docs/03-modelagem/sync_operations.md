# Sync Operations

## Objetivo

Registrar cada comando enviado pelo mobile para garantir processamento idempotente.

O reenvio da mesma operação deve retornar o resultado já registrado sem executar novamente a alteração.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| device_id | bigint | Sim | Dispositivo responsável |
| sync_log_id | bigint | Sim | Sincronização que recebeu a operação |
| operation_id | uuid | Sim | Identificador idempotente gerado pelo mobile |
| operation_type | varchar(100) | Sim | Tipo do comando |
| entity_type | varchar(100) | Sim | Tipo da entidade afetada |
| client_reference | uuid | Não | Referência local enviada pelo mobile |
| entity_id | bigint | Não | Registro correspondente no servidor |
| status | varchar(50) | Sim | Resultado do processamento |
| error_code | varchar(100) | Não | Código estável do erro |
| error_message | text | Não | Mensagem para diagnóstico |
| created_at | timestamp | Sim | Data do processamento |

---

## Relacionamentos

SyncOperation

- N:1 Company
- N:1 Device
- N:1 SyncLog

---

## Status

- Applied
- Rejected

---

## Regras de Negócio

### Idempotência

`operation_id` deve ser único dentro da Company.

### Imutabilidade

Após registrada, a operação não pode ser alterada ou removida.

### Segurança

O payload completo não deve ser armazenado quando contiver dados sensíveis.
