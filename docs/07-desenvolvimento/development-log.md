# Registro de Desenvolvimento

Este documento mantém a rastreabilidade técnica das alterações realizadas no
OrderBox.

## 2026-06-18 — Simplificação da navegação administrativa

### Funcionalidade

Reorganização do menu lateral e introdução de navegação contextual por módulo.

### Motivo

Priorizar os fluxos operacionais de uso frequente, reduzir carga visual e
manter cadastros auxiliares próximos da responsabilidade funcional correta.

### Arquivos alterados

- layout e componentes de navegação em `apps/web/resources/views`;
- testes administrativos;
- documentação das telas e registro de desenvolvimento.

### Impactos

- menu lateral reduzido a Dashboard, Clientes, Produtos, Pedidos,
  Configurações e Ajuda;
- Representantes e Regiões passam a ser acessados no contexto de Clientes;
- Categorias, Marcas e Unidades passam a ser acessadas no contexto de Produtos;
- perfil, segurança, usuários, integrações e auditoria ficam agrupados em
  Configurações;
- manual e guia da API ficam agrupados em Ajuda;
- itens respeitam as permissões atuais de cada perfil;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- testes de renderização, navegação contextual e autorização;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Tabelas de preço integradas a Produtos

### Funcionalidade

Consolidação da manutenção básica das tabelas de preço na listagem de Produtos.

### Motivo

Eliminar o módulo administrativo independente e reduzir o fluxo de criação e
renomeação ao contexto em que as tabelas são exibidas como colunas de preço.

### Arquivos alterados

- controller e rotas de manutenção de tabelas em `apps/web`;
- listagem e formulário de Produtos;
- menu lateral e manual do Admin;
- testes administrativos;
- regras de negócio, modelagem, telas, planejamento, roadmap e desenvolvimento.

### Impactos

- remove o menu e a rota de listagem independente de Tabelas de Preço;
- adiciona o botão `+` ao cabeçalho Preço;
- cria cada nova tabela como coluna ativa após Preço;
- permite renomeação inline no cabeçalho da coluna;
- restringe criação e renomeação a Admin e Manager;
- preserva isolamento por `company_id`, auditoria e vínculo regional em Regiões;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- testes administrativos de criação, renomeação, autorização e multiempresa;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Remediação arquitetural e funcional

### Funcionalidade

Correção dos riscos prioritários identificados na auditoria arquitetural.

### Motivo

Eliminar acesso indevido, configurações duplicadas, transições inconsistentes
de pedidos, dependências externas frágeis e feedback HTTP genérico.

### Arquivos alterados

- controllers, services, models, rotas e views de `apps/web`;
- páginas institucionais em `apps/web/resources/views/errors`;
- testes administrativos;
- regras de negócio, modelagem, API, telas, roadmap e desenvolvimento.

### Impactos

- representantes limitados à carteira e aos pedidos próprios;
- pedidos criados como rascunho, com envio e cancelamento explícitos;
- Regiões passa a ser a origem única do vínculo com tabelas;
- criação de tabelas removida do módulo Produtos;
- concorrência otimista aplicada a clientes e pedidos;
- gateway backend para IBGE e ViaCEP;
- resolução em lote das tabelas aplicáveis no formulário de pedidos;
- reclassificação regional executada por job pós-commit com auditoria;
- páginas 401, 403, 404, 419, 422, 429, 500 e 503;
- rate limiting para autenticação, exportação e comandos sensíveis;
- API documentada conforme disponibilidade real.

### Validação

- Laravel Pint aprovado;
- 49 testes e 214 assertions aprovados;
- build Vite aprovado.

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
