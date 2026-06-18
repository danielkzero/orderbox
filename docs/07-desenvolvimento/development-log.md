# Registro de Desenvolvimento

Este documento mantém a rastreabilidade técnica das alterações realizadas no
OrderBox.

## 2026-06-18 — Revisão arquitetural e funcional

### Funcionalidade

Auditoria completa da implementação Web, contratos da API, regras de negócio,
multiempresa, segurança, resiliência, UI/UX e testes.

### Motivo

Identificar inconsistências antes de alterar o vínculo entre Regiões e Tabelas
de Preço e estabelecer uma ordem segura de remediação.

### Arquivos alterados

- `docs/README.md`;
- `docs/06-roadmap/remediation-roadmap.md`;
- `docs/07-desenvolvimento/architectural-functional-review-2026-06-18.md`;
- `docs/07-desenvolvimento/development-log.md`.

### Impactos

- Não altera comportamento funcional.
- Não altera banco, API, Web, Mobile ou B2B.
- Define Regiões como origem única recomendada para o vínculo com tabelas.
- Registra riscos críticos de autorização e fluxo de pedidos.
- Cria roadmap priorizado para as próximas implementações.

## 2026-06-18 — Diretrizes de engenharia

### Funcionalidade

Padronização do processo de análise, implementação, documentação, validação e
versionamento do monorepo.

### Motivo

Garantir consistência arquitetural, segurança multiempresa, rastreabilidade e
qualidade profissional em todas as evoluções do OrderBox.

### Arquivos alterados

- `AGENTS.md`;
- `docs/README.md`;
- `docs/07-desenvolvimento/development-log.md`.

### Impactos

- Não altera comportamento funcional.
- Não altera banco de dados, API, Mobile ou B2B.
- Define a atualização obrigatória da documentação técnica.
- Define validações de segurança e isolamento por `company_id`.
- Define o padrão de commits e o formato das entregas.
