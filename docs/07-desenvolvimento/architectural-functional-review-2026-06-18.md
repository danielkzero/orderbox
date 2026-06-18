# Revisão Arquitetural e Funcional do OrderBox

Data da revisão: 2026-06-18

## Objetivo

Auditar a implementação atual do OrderBox quanto a responsabilidades,
consistência funcional, multiempresa, segurança, resiliência, usabilidade,
documentação e cobertura de testes.

Esta revisão não altera comportamento. Ela estabelece os problemas, decisões e
ordem de implementação recomendada.

## Estado após remediação

Uma implementação posterior na mesma data corrigiu os principais itens P0, P1
e P2:

- autorização por perfil, carteira e empresa;
- transições explícitas de pedidos;
- Regiões como origem única do vínculo com tabelas;
- remoção da criação de tabelas pelo módulo Produtos;
- concorrência otimista de clientes e pedidos;
- preservação de IDs dos agregados de clientes;
- mass assignment explícito;
- gateway backend para IBGE e ViaCEP;
- páginas de erro institucionais;
- rate limiting e erros JSON padronizados;
- remoção de controles simulados;
- filtros e ordenação nas listagens genéricas;
- resolução em lote das tabelas aplicáveis;
- reclassificação regional por job pós-commit.

Os itens ainda planejados estão identificados sem marcação de conclusão em
`docs/06-roadmap/remediation-roadmap.md`, principalmente a decomposição final
do controller CRUD e a implementação dos endpoints funcionais da API, Mobile e
B2B.

## Escopo analisado

- aplicação Laravel em `apps/web`;
- rotas Web e API V1;
- controllers, models, services e middlewares;
- telas Blade, Tailwind CSS e AlpineJS;
- contratos e documentação em `docs`;
- testes automatizados;
- dependências Composer e NPM.

Os diretórios `apps/admin`, `apps/b2b` e `apps/mobile` não possuem implementação
versionada no momento. Portanto, os impactos nesses clientes foram avaliados a
partir dos contratos documentados.

## Resumo executivo

O sistema possui uma base funcional executável, isolamento explícito por
`company_id` em grande parte das consultas e uma suíte automatizada estável.
Entretanto, ainda não atende integralmente aos contratos documentados.

Os principais riscos são:

1. ausência de autorização central para os módulos operacionais;
2. responsabilidade duplicada no vínculo entre região e tabela de preço;
3. máquina de estados de pedidos permissiva e divergente da documentação;
4. API e sincronização Mobile amplamente documentadas, mas não implementadas;
5. controles visuais sem comportamento real;
6. ausência de páginas de erro institucionais;
7. lacunas de validação e integridade multiempresa;
8. controller CRUD excessivamente centralizado e difícil de evoluir.

## Classificação

| Prioridade | Significado |
|---|---|
| P0 | risco de acesso indevido, vazamento ou corrupção de regra crítica |
| P1 | inconsistência funcional relevante ou risco operacional alto |
| P2 | problema de usabilidade, manutenção ou resiliência |
| P3 | melhoria incremental |

## Problemas encontrados

### P0 — Autorização insuficiente nos módulos operacionais

As rotas genéricas de CRUD exigem autenticação, sessão ativa e contexto de
empresa, mas não aplicam Policies ou uma matriz de permissões por recurso e
ação.

Consequências:

- `SalesRepresentative` pode abrir cadastros administrativos;
- pode criar, editar e inativar produtos, preços, regiões, marcas, categorias e
  unidades;
- pode consultar pedidos e clientes de toda a empresa;
- pode cancelar pedidos enviados;
- o menu ocultar ou exibir opções não constitui autorização.

Recomendação:

- criar Policies por agregado;
- aplicar `can` ou middleware equivalente nas rotas;
- centralizar a matriz Admin, Manager e SalesRepresentative;
- restringir representantes à própria carteira e aos próprios pedidos;
- retornar `404` para recursos de outra empresa e `403` para ação proibida
  dentro da empresa.

### P0 — Fluxo de pedidos permite estados inválidos

A validação aceita `Draft`, `Sent`, `Approved` e `Cancelled`, enquanto a
documentação e a interface definem apenas `Draft`, `Sent` e `Cancelled`.

Também é possível:

- criar pedido diretamente como `Sent` ou `Cancelled`;
- alterar um Draft diretamente para `Cancelled`;
- usar o estado não documentado `Approved`;
- informar o número do pedido manualmente;
- salvar pedido com cliente ou representante inativo;
- um representante selecionar cliente fora da própria carteira.

Recomendação:

- introduzir serviço de domínio para transições;
- criar sempre em `Draft`;
- separar comandos `save`, `send` e `cancel`;
- gerar a numeração no servidor;
- exigir cliente, representante, produtos e tabela ativos;
- implementar concorrência otimista por `version`;
- auditar envio e cancelamento dentro da mesma transação.

### P0 — Contrato da API diverge da implementação

A API implementada contém autenticação, health e readiness. Os endpoints
documentados de usuários, clientes, representantes, catálogo, pedidos,
auditoria e sincronização não existem.

Impactos:

- Mobile e integrações não conseguem operar conforme o contrato;
- a documentação pode induzir consumidores a integrar endpoints inexistentes;
- Form Requests e API Resources ainda não são utilizados nesses recursos;
- idempotência, cursor e conflitos de versão não estão implementados.

Recomendação:

- marcar claramente contratos planejados versus disponíveis;
- implementar a API em entregas verticais;
- não iniciar o Mobile operacional antes dos endpoints mínimos;
- adicionar testes de contrato para cada endpoint publicado.

### P1 — Duplicidade Região × Tabela de Preço

O vínculo é persistido por `price_tables.region_id`, mas pode ser configurado
em vários pontos:

- cadastro de tabela de preço;
- criação de tabela dentro do cadastro de produto;
- criação rápida na matriz de produtos;
- apresentação e seleção dentro do fluxo de pedido.

Ao mesmo tempo, o módulo Regiões exibe a quantidade de tabelas, mas não possui
controle do vínculo. Isso contradiz a responsabilidade funcional desejada.

#### Decisão arquitetural recomendada

O módulo **Regiões** deve ser a única origem de manutenção do vínculo.

- Tabela de Preço mantém nome, descrição, status e preços.
- Região mantém abrangência geográfica e tabelas habilitadas.
- Produto mantém preços nas tabelas existentes, mas não cria tabelas nem define
  região.
- Pedido apenas consome as tabelas aplicáveis calculadas pelo domínio.

Se uma tabela puder pertencer a apenas uma região, `price_tables.region_id`
pode permanecer como detalhe de persistência, sendo alterado exclusivamente
pelo serviço do módulo Regiões.

Se uma tabela puder atender várias regiões, a cardinalidade correta é N:N por
uma tabela associativa `price_table_region`. Essa decisão deve ser confirmada
antes da migration. Não se deve criar a associação N:N apenas para resolver um
problema de tela.

#### Migração funcional

1. inventariar vínculos atuais;
2. definir a cardinalidade oficial;
3. adicionar o seletor de tabelas no formulário de Região;
4. remover `region_id` dos formulários e validações de Tabela e Produto;
5. remover criação de tabela dentro do Produto;
6. encapsular a resolução de tabelas aplicáveis em serviço;
7. atualizar testes e documentação.

### P1 — Duplicidade na gestão de tabelas e preços

Tabelas podem ser criadas no módulo Tabelas de Preço, no formulário de Produto
e na matriz de Produtos. Os preços também podem ser alterados pelo lado da
tabela ou pelo lado do produto.

Isso aumenta o risco de:

- regras diferentes entre telas;
- alterações parciais;
- treinamento operacional confuso;
- dificuldade de auditoria.

Recomendação:

- Tabelas de Preço: criar e configurar tabela;
- Matriz de Preços: alterar preços por produto/tabela em uma tela dedicada;
- Produto: apenas visualizar resumo e acessar a matriz filtrada;
- remover criação embutida de tabela no Produto.

### P1 — Integridade multiempresa depende excessivamente da aplicação

As consultas principais usam `company_id`, mas várias tabelas filhas não
possuem constraint composta que garanta que as duas pontas pertençam à mesma
empresa.

Exemplos:

- produto e tabela em `product_prices`;
- cliente e tabela em `customer_price_table`;
- cliente e representante em `customer_representatives`;
- pedido, cliente, representante, usuário, produto e tabela;
- usuário e representante.

As validações Web reduzem o risco, porém imports, jobs, futuras APIs e scripts
internos podem criar vínculos cruzados.

Recomendação:

- manter validação na aplicação;
- avaliar chaves e constraints compostas onde o MariaDB permitir;
- adicionar testes de integração que tentem cruzar empresas;
- criar scopes ou repositories tenant-aware;
- evitar models operacionais com `$guarded = []`.

### P1 — Mass assignment amplo

A maioria dos models operacionais utiliza `$guarded = []`.

Recomendação:

- declarar `$fillable` explicitamente;
- manter `company_id`, totais, status, versões e campos de auditoria fora da
  entrada mass assignable quando forem calculados pelo servidor;
- usar DTOs ou dados validados por caso de uso.

### P1 — Regras documentadas não estão implementadas

Foram identificadas divergências:

- `Customer.version` não é validado nem incrementado;
- restrição de um endereço padrão não é garantida;
- restrição de um contato principal ativo não é garantida;
- restrição de um representante principal não é garantida no banco;
- ciclos de categorias não são validados;
- unicidade de categoria não é validada de forma consistente;
- `ProductPrices` documenta preço maior que zero, mas aceita zero;
- envio e cancelamento não são comandos transacionais separados;
- SyncChange obrigatório em remoção de Draft não existe;
- estoque é tratado como campo editável, apesar de documentado como origem ERP.

### P1 — Resolução de tabela de preço concentrada no Model

`Customer::applicablePriceTables()` consulta banco, resolve região e aplica
prioridade. O Model passou a concentrar persistência e regra comercial.

Recomendação:

- extrair `ApplicablePriceTableService`;
- retornar resultado ordenado com motivo da elegibilidade;
- definir explicitamente a precedência entre vínculo direto, região e tabela
  global;
- reutilizar o serviço na Web, API, Mobile e B2B;
- testar todas as combinações.

### P1 — Operações destrutivas de agregados

Ao atualizar cliente, endereços, contatos e representantes são apagados e
recriados. Isso altera IDs e dificulta auditoria, sincronização e referências
futuras.

Recomendação:

- sincronizar filhos por ID;
- criar, atualizar e inativar individualmente;
- preservar identidade e histórico;
- incluir `version` para concorrência.

### P1 — Dependência externa do IBGE no navegador

O formulário de Região consulta diretamente a API do IBGE, sem tratamento
adequado de timeout, erro, retry ou indisponibilidade.

Recomendação:

- criar gateway backend com timeout, cache e resposta padronizada;
- disponibilizar fallback e mensagem clara;
- limitar requisições;
- registrar falhas sem bloquear a edição de uma região existente.

### P2 — Controller CRUD monolítico

`CatalogCrudController` reúne clientes, produtos, preços, regiões,
representantes e pedidos, além de upload, cálculo, transações e regras
comerciais.

Impactos:

- alta complexidade;
- testes pouco isolados;
- risco de regressão;
- difícil aplicação de Policies e Form Requests;
- responsabilidades incompatíveis com SOLID.

Recomendação:

- controllers por recurso;
- Form Requests por operação;
- Actions ou Services por caso de uso;
- Policies por agregado;
- Resources para API;
- componentes Blade por formulário.

### P2 — Formulário Blade monolítico

O formulário genérico possui aproximadamente 1.400 linhas e concentra vários
agregados e estados Alpine.

Recomendação:

- componentes por módulo;
- componentes menores para endereço, contato, preço e item de pedido;
- JavaScript testável para fluxos complexos;
- reduzir dados serializados integralmente no HTML.

### P2 — Problemas de desempenho

- tela de pedido calcula tabelas aplicáveis cliente a cliente, gerando N+1;
- formulário carrega todos os clientes, produtos, representantes e tabelas;
- matriz de produtos cria uma coluna por tabela e não escala;
- salvar Região recalcula todos os clientes de forma síncrona;
- busca genérica consulta o schema em tempo de requisição;
- dashboard executa consultas repetidas para indicadores e canais.

Recomendação:

- endpoints de autocomplete paginados;
- eager loading e consultas em lote;
- job para reclassificação de clientes;
- matriz virtualizada ou paginada;
- configuração estática de colunas pesquisáveis;
- agregações otimizadas no dashboard.

### P2 — Controles visuais sem funcionalidade

- busca global com atalho `Ctrl K` não executa busca;
- botão genérico “Filtrar” não abre nem aplica filtros;
- notificações são dados fixos;
- badges `NEW` são permanentes;
- link “Ver todas as notificações” redireciona para auditoria ou manual;
- documentação menciona fluxos ainda indisponíveis como se estivessem ativos.

Recomendação:

- implementar ou remover cada affordance;
- nunca exibir dados simulados em ambiente operacional;
- indicar claramente funcionalidades planejadas.

### P2 — Feedback operacional incompleto

Existem mensagens de sucesso e erros de validação, porém faltam:

- confirmação antes de inativar, excluir ou cancelar;
- feedback de processamento e bloqueio contra duplo envio;
- mensagens específicas para falha de rede no IBGE e ViaCEP;
- estado vazio com ação recomendada;
- diferenciação visual entre erro, alerta e informação;
- padrão único para toasts e mensagens persistentes;
- mensagens amigáveis para exceções HTTP.

### P2 — Páginas de erro ausentes

Não existem páginas institucionais para:

- 401;
- 403;
- 404;
- 419;
- 422;
- 429;
- 500;
- 503.

Recomendação:

- criar layout compartilhado com identidade OrderBox;
- informar o ocorrido sem expor detalhes internos;
- oferecer retorno, login ou dashboard conforme o contexto;
- cobrir responsividade, tema escuro e acessibilidade;
- testar cada status.

### P2 — Tratamento de erro da API incompleto

A API padroniza 401 e 422, mas não padroniza 403, 404, 409, 429, 500 e 503.

Recomendação:

- criar catálogo estável de códigos;
- adicionar `request_id` para correlação;
- não retornar stack trace ou mensagens internas;
- cobrir exceções de domínio separadamente.

### P2 — Rate limiting parcial

Há throttling em login e confirmação 2FA. Não há limites explícitos para:

- clientes de API inválidos;
- comandos autenticados futuros;
- exportações;
- consultas externas de localidades;
- operações administrativas sensíveis.

Recomendação:

- definir limiters nomeados por IP, usuário, empresa e cliente de API;
- aplicar limites diferentes para leitura, escrita, autenticação e exportação;
- documentar respostas 429.

### P2 — Auditoria incompleta

Há AuditLog em várias escritas, porém:

- transições de pedido não possuem ações específicas consistentes;
- auditoria pode ficar fora da transação principal;
- alterações automáticas de região de clientes não são auditadas;
- não há filtros adequados na tela;
- não há política de retenção;
- valores completos podem registrar PII além do necessário.

### P2 — Acessibilidade e ergonomia

- botões destrutivos não possuem confirmação;
- alguns botões apenas com símbolo não possuem nome acessível;
- tabelas largas dependem de rolagem horizontal;
- o formulário de pedido e a matriz de preços possuem alta densidade;
- estados de carregamento não são anunciados;
- notificações simuladas prejudicam confiança;
- textos e componentes legados não seguem integralmente o padrão TailAdmin.

## Melhorias funcionais recomendadas

### Dashboard

- filtros por representante, cliente, região, status e canal;
- indicadores de rascunhos, pedidos enviados e cancelamentos;
- comparação com período anterior;
- ranking de representantes, produtos e clientes;
- exportação assíncrona para grandes volumes;
- links dos indicadores para listagens filtradas.

### Clientes

- filtros por região, representante, status e tabela aplicável;
- busca por nome, documento, cidade, telefone e e-mail;
- histórico de alterações;
- ação rápida “Criar pedido”;
- validação de duplicidade antes do envio;
- importação e exportação com relatório de erros;
- ações em lote de inativação e vínculo de representante.

### Produtos e preços

- separar catálogo da matriz de preços;
- filtro por tabela, categoria, marca, estoque e status;
- edição em lote com validação e preview;
- histórico de preço;
- importação e exportação;
- indicador de produto sem preço em tabela ativa;
- impedir preço zero quando não for explicitamente permitido.

### Regiões

- tornar o módulo origem única dos vínculos com tabelas;
- mostrar conflitos e sobreposição de cobertura;
- simular resolução por CEP, município ou IBGE;
- reclassificação assíncrona com progresso;
- listar clientes e representantes impactados antes de salvar;
- histórico de alterações de abrangência.

### Representantes

- filtros por região, status e tamanho da carteira;
- visão da carteira;
- transferência em lote de clientes;
- indicadores de pedidos e receita;
- impedir vínculo com usuário de perfil incompatível.

### Pedidos

- ações explícitas Salvar rascunho, Enviar e Cancelar;
- timeline de status;
- validação de estoque informativo e tabela;
- busca paginada de produtos;
- duplicar pedido;
- exportação e impressão;
- histórico e auditoria;
- bloqueio de edição após envio;
- confirmação de cancelamento com motivo.

### Auditoria

- filtros por período, usuário, ação, entidade e registro;
- visualização estruturada de antes/depois;
- exportação;
- mascaramento de dados sensíveis;
- correlação por request e operação.

## UI/UX recomendada

1. manter TailAdmin, Tailwind e AlpineJS;
2. padronizar cabeçalhos, filtros, estados vazios e ações;
3. exibir ação primária única por tela;
4. mover ações secundárias para menus contextuais;
5. usar confirmação para ações irreversíveis;
6. usar autocomplete remoto para grandes cadastros;
7. preservar filtros na URL;
8. permitir ordenação por colunas relevantes;
9. adicionar skeleton/loading em chamadas externas;
10. remover recursos simulados até existirem dados reais.

## Impactos por aplicação

### Web

Impacto alto. Concentra a implementação atual e receberá as primeiras
correções.

### API

Impacto alto. O contrato precisa ser classificado entre implementado e
planejado, seguido de implementação vertical.

### Mobile

Impacto futuro alto. Não deve assumir endpoints ou regras ainda inexistentes.
A resolução de tabela e permissões precisa ser compartilhada com a API.

### B2B

Impacto futuro médio. Deve consumir os mesmos serviços de preços, catálogo,
clientes e pedidos, sem duplicar regra comercial.

### Banco

Impacto depende da cardinalidade Região × Tabela. Outras melhorias exigem
constraints, índices e eventualmente migrations de integridade.

## Documentação que precisa ser atualizada durante a implementação

- `docs/01-visao-geral/product-scope.md`;
- `docs/02-regras-negocio/order-flow.md`;
- novo documento de regras de preços e regiões;
- `docs/03-modelagem/price_tables.md`;
- `docs/03-modelagem/regions.md`;
- `docs/03-modelagem/customers.md`;
- `docs/03-modelagem/orders.md`;
- `docs/03-modelagem/database-contract.md`;
- `docs/03-modelagem/database-map.md`;
- `docs/03-modelagem/er-diagram.md`;
- `docs/04-api/api-contract.md`;
- `docs/04-api/sync-contract.md`;
- `docs/05-telas/admin-screens.md`;
- `docs/05-telas/mobile-screens.md`;
- `docs/06-planejamento/v1-implementation-plan.md`;
- `docs/06-roadmap/remediation-roadmap.md`;
- `docs/07-desenvolvimento/development-log.md`.

## Plano de implementação

### Fase 1 — Segurança e consistência crítica

1. criar matriz de permissões e Policies;
2. aplicar autorização nas rotas e ações;
3. restringir carteira e pedidos do representante;
4. implementar máquina de estados de pedidos;
5. validar registros ativos e empresa em todos os agregados;
6. adicionar testes de autorização e cross-tenant.

### Fase 2 — Single Source of Truth de preços

1. confirmar cardinalidade Região × Tabela;
2. mover manutenção do vínculo para Regiões;
3. remover vínculo de Tabelas e Produtos;
4. extrair serviço de tabelas aplicáveis;
5. consolidar matriz de preços;
6. migrar dados sem perda;
7. atualizar DER, dicionário e telas.

### Fase 3 — Integridade e serviços de domínio

1. decompor `CatalogCrudController`;
2. criar Form Requests;
3. substituir mass assignment amplo;
4. preservar IDs dos agregados de cliente;
5. implementar concorrência por versão;
6. reforçar constraints e testes no MariaDB.

### Fase 4 — Resiliência, erros e feedback

1. criar páginas 401, 403, 404, 419, 422, 429, 500 e 503;
2. padronizar erros da API;
3. criar rate limiters;
4. adicionar confirmação, loading e prevenção de duplo envio;
5. criar gateway cacheado para IBGE e ViaCEP;
6. revisar logs e auditoria.

### Fase 5 — UX e produtividade

1. remover controles simulados;
2. implementar filtros, ordenação e buscas reais;
3. separar matriz de preços;
4. adicionar ações rápidas e em lote;
5. otimizar telas grandes e autocompletes;
6. melhorar dashboard e auditoria.

### Fase 6 — API, Mobile e B2B

1. classificar endpoints disponíveis e planejados;
2. implementar recursos da API por vertical;
3. adicionar Resources, Form Requests e testes de contrato;
4. implementar sincronização;
5. iniciar clientes Mobile e B2B sobre contratos validados.

## Testes necessários

- matriz completa de permissões por perfil, recurso e ação;
- isolamento entre duas empresas para listagem, leitura e escrita;
- carteira de representante;
- estados e transições de pedido;
- concorrência por versão;
- registros inativos;
- resolução e prioridade de tabelas;
- cardinalidade Região × Tabela;
- constraints de filhos cross-tenant;
- rate limiting;
- respostas padronizadas da API;
- páginas de erro;
- falhas de IBGE e ViaCEP;
- acessibilidade básica;
- testes de browser para fluxos críticos;
- execução final em MariaDB, além do SQLite de testes.

## Validações executadas nesta revisão

- Laravel Pint: aprovado;
- PHPUnit: 46 testes e 195 assertions aprovados;
- build Vite: aprovado;
- Composer audit: sem vulnerabilidades conhecidas;
- NPM audit de dependências de produção: sem vulnerabilidades conhecidas.

## Commits sugeridos

```text
feat(authz): enforce module policies and tenant permissions
refactor(order): enforce explicit order state transitions
refactor(pricing): move region assignments to region management
refactor(catalog): centralize price matrix responsibilities
fix(tenant): prevent cross-company aggregate relationships
refactor(crud): split catalog controller into resource actions
feat(errors): add branded http error pages
feat(api): standardize error responses and rate limits
feat(ui): replace simulated controls with operational workflows
docs(architecture): align contracts with implemented capabilities
```
