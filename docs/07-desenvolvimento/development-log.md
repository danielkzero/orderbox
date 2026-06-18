# Registro de Desenvolvimento

Este documento mantém a rastreabilidade técnica das alterações realizadas no
OrderBox.

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
