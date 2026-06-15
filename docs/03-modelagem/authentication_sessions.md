# Authentication Sessions

## Objetivo

Controlar a sessão única de cada usuário nos canais Web e Mobile.

Um usuário pode possuir uma sessão Web e uma sessão Mobile ativas simultaneamente, mas nunca duas sessões ativas no mesmo canal.

---

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---------|---------|---------|---------|
| id | uuid | Sim | Identificador único |
| company_id | bigint | Sim | Empresa proprietária |
| user_id | bigint | Sim | Usuário autenticado |
| channel | varchar(20) | Sim | Canal Web ou Mobile |
| active_slot | boolean | Não | Marcador da sessão ativa |
| session_key_hash | char(64) | Não | Hash da chave de validação da sessão Web |
| web_session_id | varchar(255) | Não | Sessão Laravel associada |
| personal_access_token_id | bigint | Não | Token Sanctum associado |
| ip_address | varchar(45) | Não | IP da autenticação |
| user_agent | text | Não | Navegador ou dispositivo |
| last_activity_at | timestamp | Sim | Última atividade |
| revoked_at | timestamp | Não | Data de revogação |
| created_at | timestamp | Sim | Data de criação |
| updated_at | timestamp | Sim | Data de atualização |

---

## Authentication Challenges

Desafios temporários são criados quando um usuário com 2FA ativo inicia uma nova autenticação.

O desafio registra usuário, Company, canal e expiração. Ele não ativa nem revoga sessões até a confirmação do segundo fator.

---

## Regras de Negócio

### Sessão Única

Deve existir no máximo um registro com `active_slot = true` para cada combinação de usuário e canal.

### Substituição

A nova sessão e a revogação da anterior devem ocorrer na mesma transação.

### Histórico

Sessões revogadas permanecem registradas. Tokens Mobile e chaves de validação Web devem ser persistidos somente como hash.
