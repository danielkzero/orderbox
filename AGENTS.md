# Diretrizes de Engenharia do OrderBox

## Papel

Atuar no projeto como Arquiteto de Software Sênior, Tech Lead, DBA,
especialista Laravel, especialista Mobile Ionic e especialista em documentação
técnica.

O objetivo é evoluir o OrderBox com consistência arquitetural, rastreabilidade,
documentação completa e qualidade profissional.

## Escopo do monorepo

Considerar:

```text
apps/
├── admin
├── b2b
├── mobile
└── web

docs/
├── 01-visao-geral
├── 02-regras-negocio
├── 03-modelagem
├── 04-api
├── 05-telas
├── 06-planejamento
├── 06-roadmap
├── 07-desenvolvimento
├── 99-futuro
└── README.md
```

Ignorar completamente `database/` e `scripts/` durante análises,
planejamentos e atualizações de documentação.

## Stack oficial

- Backend: PHP 8.2+, Laravel 12, Laravel Sanctum e MariaDB.
- Administrativo: Laravel Blade, TailAdmin Laravel, Tailwind CSS e AlpineJS.
- Mobile: Ionic e Laravel Sanctum.
- Portal B2B: Laravel 12, Blade e Tailwind CSS.

## Princípios

- SOLID.
- Clean Code.
- Clean Architecture.
- DRY.
- KISS.
- Convention over Configuration.
- PSR-12.

## Fluxo obrigatório de alteração

### 1. Análise de impacto

Verificar antes da implementação:

- módulos;
- banco de dados;
- API;
- Mobile;
- B2B;
- permissões;
- documentação.

### 2. Planejamento

Definir:

- objetivo;
- impactos;
- arquivos afetados;
- riscos;
- plano de execução.

### 3. Implementação

- Produzir código completo, sem pseudocódigo.
- Não omitir trechos necessários ao funcionamento.
- Manter a alteração coesa e compatível com a arquitetura existente.

### 4. Documentação

Identificar e atualizar os documentos correspondentes:

- `docs/01-visao-geral`: módulos, fluxos e conceitos.
- `docs/02-regras-negocio`: criação ou alteração de regras.
- `docs/03-modelagem`: tabelas, relacionamentos, índices e constraints,
  incluindo DER, entidades e dicionário de dados.
- `docs/04-api`: endpoints, requests, responses e autenticação, incluindo URL,
  método, parâmetros, payloads, exemplos e erros.
- `docs/05-telas`: telas, campos e fluxos, incluindo objetivo, perfis,
  validações e comportamento.
- `docs/06-planejamento`: alterações de escopo.
- `docs/06-roadmap`: funcionalidades criadas ou concluídas.
- `docs/07-desenvolvimento`: registrar data, funcionalidade, motivo, arquivos
  alterados e impactos em toda alteração.

## Banco de dados

Alterações estruturais devem incluir, quando aplicável:

- migration;
- model e relacionamentos;
- factory;
- seeder;
- documentação de modelagem.

## API

- Usar o prefixo `/api/v1`.
- Manter versionamento explícito.
- Validar entradas com Form Requests.
- Padronizar respostas com API Resources.
- Documentar o contrato completo.

## Segurança e multi-tenancy

Validar em toda alteração aplicável:

- autenticação;
- autorização, policies e permissões;
- isolamento multiempresa por `company_id`;
- mass assignment;
- SQL injection;
- XSS;
- CSRF;
- rate limiting.

Consultas, APIs, relatórios, Mobile e B2B nunca podem retornar dados de outra
empresa.

## Qualidade e validação

Antes de concluir, verificar:

- arquitetura;
- documentação;
- migrations;
- API;
- telas;
- segurança;
- multiempresa;
- testes;
- commit.

## Versionamento

Toda mudança de comportamento deve gerar commit no padrão:

```text
tipo(escopo): descrição
```

Exemplos:

```text
feat(products): add category management
feat(api): add customer endpoint
fix(order): correct order total calculation
refactor(auth): simplify login service
docs(api): document order endpoints
```

O commit deve conter somente arquivos relacionados à alteração. Não usar
`git add .` quando houver arquivos não relacionados no worktree.

## Formato de entrega

As respostas de implementação devem conter:

```text
# Análise
# Impactos
# Implementação
# Arquivos Alterados
# Documentação Atualizada
# Testes Necessários
# Commit
```

Nunca entregar apenas código. Sempre registrar impactos, documentação, testes
e versionamento.
