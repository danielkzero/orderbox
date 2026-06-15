# Autenticação e Sessão Única

## Objetivo

Definir a política de autenticação do OrderBox para Web e Mobile.

---

## Canais

Cada autenticação pertence a um canal:

- Web: painel administrativo com Laravel Blade;
- Mobile: aplicativo Ionic autenticado pela API.

Um usuário pode manter simultaneamente:

- uma sessão Web ativa;
- uma sessão Mobile ativa.

O usuário nunca pode manter duas sessões ativas no mesmo canal.

---

## Substituição de Sessão

Quando um novo login é concluído em um canal que já possui sessão ativa:

1. a nova sessão é criada;
2. a sessão anterior do mesmo canal é revogada;
3. a sessão ativa do outro canal não é afetada.

Exemplos:

- novo login Web revoga o login Web anterior, mas preserva o Mobile;
- novo login Mobile revoga o login Mobile anterior, mas preserva o Web.

---

## Usuários com 2FA

Quando o usuário possui 2FA ativo e já existe uma sessão no mesmo canal:

1. as credenciais primárias são validadas;
2. o novo login permanece pendente;
3. o usuário recebe uma segunda confirmação informando que a sessão anterior será invalidada;
4. a sessão anterior permanece válida enquanto o desafio estiver pendente;
5. somente após validar o segundo fator a nova sessão é ativada e a anterior é revogada.

Falha, expiração ou abandono do desafio não deve invalidar a sessão anterior.

---

## Revogação

Uma sessão deve ser revogada quando:

- ocorrer novo login concluído no mesmo canal;
- o usuário executar logout;
- o usuário for inativado;
- a Company for inativada;
- um Admin revogar explicitamente a sessão;
- houver alteração de senha.

---

## Segurança

- Identificadores e tokens de sessão devem ser armazenados somente como hash quando possível.
- Tokens Mobile devem usar Laravel Sanctum.
- Sessões Web devem utilizar o driver de banco de dados.
- Login, logout, substituição e revogação administrativa devem gerar AuditLog.
- A API nunca deve informar se uma credencial pertence a outra Company.

---

## Concorrência

A ativação da nova sessão e a revogação da anterior devem ocorrer na mesma transação.

A unicidade de sessão ativa por usuário e canal deve ser garantida no banco de dados e na aplicação.
