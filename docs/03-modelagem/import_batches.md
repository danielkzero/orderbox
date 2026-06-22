# Import Batches

## Objetivo

Registrar rastreabilidade e resultado das importações executadas por empresa.

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| id | bigint | Sim | Identificador |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário responsável |
| type | varchar(50) | Sim | Tipo da importação |
| original_filename | varchar(255) | Sim | Nome original |
| storage_path | varchar(255) | Não | Caminho temporário durante o processamento |
| status | varchar(30) | Sim | `queued`, `processing`, `completed` ou `failed` |
| total_rows | unsigned int | Sim | Linhas processadas |
| processed_rows | unsigned int | Sim | Linhas concluídas |
| created_rows | unsigned int | Sim | Entidades principais criadas |
| updated_rows | unsigned int | Sim | Entidades principais atualizadas |
| failed_rows | unsigned int | Sim | Linhas com falha |
| errors | json | Não | Mensagens sanitizadas |
| started_at | timestamp | Não | Início do processamento pelo worker |
| completed_at | timestamp | Não | Conclusão |
| created_at | timestamp | Sim | Criação |
| updated_at | timestamp | Sim | Atualização |

## Relacionamentos

- `import_batches.company_id -> companies.id`;
- `import_batches.user_id -> users.id`.

## Índices

- índice composto em `company_id, created_at`.

O arquivo é armazenado apenas enquanto aguarda ou executa na fila. Depois disso,
o histórico mantém somente metadados, progresso, contadores e erros
sanitizados.
