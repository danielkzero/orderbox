# Devices

## Objetivo

Representa um dispositivo autorizado a utilizar o aplicativo mobile.

Permite identificar a origem das sincronizações, controlar acessos e registrar a versão do aplicativo em uso.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | bigint | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário vinculado |
| device_uuid | varchar(255) | Sim | Identificador único do dispositivo |
| platform | varchar(50) | Sim | Plataforma do dispositivo |
| app_version | varchar(50) | Não | Versão instalada do aplicativo |
| last_sync_at | timestamp | Não | Data da última sincronização concluída |
| active | boolean | Sim | Dispositivo autorizado |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Relacionamentos

Device

- N:1 Company
- N:1 User
- 1:N SyncLogs
- 1:N SyncOperations

---

## Regras de Negócio

### Identificador Único

O `device_uuid` deve ser único dentro da empresa.

### Dispositivo Inativo

Dispositivos inativos não podem iniciar novas sincronizações.

### Exclusão

Dispositivos com histórico de sincronização não devem ser removidos fisicamente.

Utilizar:

active = false
