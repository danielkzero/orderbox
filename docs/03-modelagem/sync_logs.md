# Sync Logs

## Objetivo

Registrar cada tentativa de sincronização realizada pelo aplicativo mobile.

Permite diagnosticar falhas e acompanhar o envio e o recebimento de dados.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| device_id | bigint | Sim | Dispositivo responsável |
| status | varchar(50) | Sim | Status da sincronização |
| records_sent | integer | Sim | Quantidade de registros enviados |
| records_received | integer | Sim | Quantidade de registros recebidos |
| error_message | text | Não | Descrição da falha |
| started_at | timestamp | Sim | Início da sincronização |
| finished_at | timestamp | Não | Fim da sincronização |

---

## Relacionamentos

SyncLog

- N:1 Company
- N:1 Device

---

## Status

### Running

Sincronização em andamento.

### Success

Sincronização concluída.

### Failed

Sincronização encerrada com falha.

---

## Regras de Negócio

### Imutabilidade

Após a conclusão, o registro de sincronização não pode ser alterado.

### Auditoria Operacional

Falhas devem manter a mensagem necessária para diagnóstico, sem armazenar senhas, tokens ou outros dados sensíveis.
