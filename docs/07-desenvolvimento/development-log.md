# Registro de Desenvolvimento

Este documento mantém a rastreabilidade técnica das alterações realizadas no
OrderBox.

## 2026-06-22 — Ações com ícones nas tabelas administrativas

### Funcionalidade

As ações de linha das tabelas administrativas foram padronizadas como botões
compactos com ícones, tooltip e identificação acessível.

### Motivo

Reduzir a largura ocupada pela coluna de ações e melhorar a leitura das
listagens, especialmente em pedidos e tabelas com várias operações.

### Arquivos alterados

- componentes de ícone, tooltip de ação e botão de tabela;
- partial compartilhada de ações dos módulos;
- tabelas de usuários, clientes de API e sessões;
- documentação de telas e registro de desenvolvimento;
- testes administrativos.

### Impactos

- pedidos usam ícones para editar, visualizar, enviar por e-mail, WhatsApp e
  enviar pedido;
- o menu `Outras ações` usa três pontos verticais;
- editar e inativar foram padronizados nos demais cadastros;
- regenerar, bloquear e revogar usam o mesmo padrão visual;
- menus expandidos preservam texto e ícone para clareza;
- sem alterações em banco de dados, API, Mobile ou B2B.

## 2026-06-22 — Paginação das sessões recentes

### Funcionalidade

A tela de Segurança passou a apresentar o histórico de sessões em páginas de
10 registros, mantendo a ordenação da sessão mais recente para a mais antiga.

### Motivo

Evitar o crescimento contínuo da lista na interface sem impedir o acesso ao
histórico completo.

### Arquivos alterados

- `apps/web/app/Http/Controllers/SecurityController.php`;
- `apps/web/resources/views/admin/security/index.blade.php`;
- `apps/web/tests/Feature/Admin/AdminPanelTest.php`;
- `docs/03-modelagem/authentication_sessions.md`;
- `docs/05-telas/admin-screens.md`;
- `docs/07-desenvolvimento/development-log.md`.

### Impactos

- limite visual de 10 sessões por página;
- navegação entre todas as sessões registradas;
- exibição da faixa atual e do total de registros;
- preservação do isolamento por usuário e empresa;
- sem alterações em banco de dados, API, Mobile ou B2B.

## 2026-06-19 — Ícone da página do pedido

A visualização independente do pedido passou a usar o favicon oficial do
OrderBox na aba do navegador.

## 2026-06-19 — Tarja de status no documento do pedido

O documento, PDF, impressão e anexo de e-mail agora exibem uma tarja obrigatória
para identificar pedidos em rascunho, enviados ou cancelados. Cancelamentos
usam destaque vermelho, rascunhos âmbar e enviados verde.

## 2026-06-19 — PDF retrato e configuração unificada

### Correção

Removida a orientação paisagem automática e consolidada a configuração do
pedido em um único botão.

### Impactos

- PDF sempre em A4 retrato;
- conteúdo usa largura automática, sem somar padding à largura da página;
- tipografia e células compactas evitam corte lateral;
- um único botão `Configurar pedido`;
- o mesmo modal contém itens, ordem e opções de impressão.

## 2026-06-19 — Normalização das colunas do documento

### Correção

Eliminada a dupla codificação JSON que fazia a lista inteira de colunas aparecer
como um único cabeçalho e ocultava os itens.

### Impactos

- migration repara configurações já gravadas;
- model aceita valores legados simples ou duplamente codificados;
- criação de configuração não reutiliza atributos já serializados;
- PDF, tela, impressão e Excel usam a mesma lista normalizada.

## 2026-06-19 — Ajuste de largura do PDF de pedido

### Funcionalidade

Correção do corte lateral em documentos com muitas colunas e identificação
explícita da configuração de itens.

### Impactos

- tabela com largura fixa, quebra de texto e espaçamento compacto;
- cabeçalho compatível com o motor de PDF;
- botão renomeado para `Configurar itens e ordem`;
- ordenação permanece disponível no mesmo configurador.

### Validação

- renderização Blade;
- geração de PDF com modelos compactos e extensos;
- suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Configuração de impressão e margem do PDF

### Funcionalidade

Separação entre o modelo digital do pedido e as opções usadas na impressão do
navegador.

### Impactos

- PDF sem margem padrão adicional do DomPDF;
- único espaçamento interno controlado pelo documento;
- colunas e blocos específicos para impressão;
- tamanho de foto específico;
- margens sem margem, estreita ou padrão;
- pré-visualização própria;
- configurações restritas a Admin e Manager.

### Validação

- persistência da configuração de impressão;
- autorização;
- renderização CSS de página;
- PDF, testes completos, Laravel Pint e build Vite.

## 2026-06-19 — Excel e modelo configurável do pedido

### Funcionalidade

Inclusão de download `.xlsx` e configuração corporativa das informações do
documento do pedido.

### Impactos

- barra de ações com impressão, PDF, Excel e configuração;
- seleção de colunas reais do produto e do item;
- pré-visualização interativa antes de salvar;
- tamanho configurável da foto;
- ordenação por inserção, nome ou código;
- totais opcionais de quantidade, peso, subtotal e valor;
- configuração compartilhada por tela, PDF, e-mail e Excel;
- alteração restrita a Admin e Manager.

### Validação

- persistência multiempresa;
- geração de PDF e XLSX;
- autorização do configurador;
- suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Segurança comercial e distribuição de pedidos

### Funcionalidade

Restrição dos cadastros operados por representantes, vínculo de tabelas
visíveis e conjunto de ações para distribuição e acompanhamento de pedidos.

### Impactos

- SalesRepresentative não altera limite, vínculos comerciais ou tabelas do
  cliente;
- novos clientes são vinculados automaticamente ao próprio representante;
- representantes visualizam somente tabelas autorizadas pela gestão;
- pedidos possuem visualização, impressão, PDF, e-mail, WhatsApp, histórico,
  duplicação e cancelamento antes do envio;
- PDFs compartilhados usam URL assinada com expiração;
- e-mails recebem PDF anexo;
- toda distribuição e ação relevante é rastreada.

### Arquivos alterados

- migration, models, relacionamentos e seeder;
- controllers, services, mailable, rotas e views;
- dependência de geração de PDF;
- testes administrativos;
- documentação funcional, técnica e de modelagem.

### Validação

- autorização por empresa e representante;
- validação de campos proibidos;
- geração de documento e PDF;
- envio de e-mail simulado;
- duplicação, cancelamento e histórico;
- suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Identificação dos pedidos na auditoria

### Funcionalidade

Inclusão de uma identificação legível e imutável para os recursos registrados
na auditoria.

### Motivo

O tipo da entidade e o ID interno não eram suficientes para reconhecer
operacionalmente qual pedido havia sido cancelado.

### Impactos

- novos logs armazenam `entity_label`;
- pedidos usam o número comercial como identificação;
- cancelamentos exibem `Pedido cancelado` e `PED-... (#ID)`;
- a pesquisa da auditoria aceita o número do pedido;
- logs existentes de pedidos são preenchidos pela migration quando o pedido
  ainda existe.

### Validação

- teste do snapshot criado no cancelamento;
- teste de pesquisa e apresentação pelo número do pedido;
- migration, suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Altura do seletor de prazo

O controle fechado do dropdown de prazos foi padronizado em `44px`, mantendo o
alinhamento visual com os demais inputs e selects do formulário. As informações
complementares continuam disponíveis na lista aberta.

## 2026-06-19 — Dropdown visual de prazos no pedido

### Funcionalidade

Substituição do select de prazos por um dropdown com estados visuais de
disponibilidade.

### Motivo

Explicar diretamente no formulário por que um prazo não pode ser utilizado e
quanto falta no total do pedido para liberá-lo.

### Arquivos alterados

- formulário administrativo de pedidos;
- testes administrativos;
- regras de negócio, documentação de telas e histórico.

### Impactos

- prazos bloqueados permanecem visíveis em vermelho;
- cada bloqueio informa o valor restante para habilitação;
- prazos disponíveis apresentam o mínimo aplicável;
- a seleção é removida automaticamente se uma alteração no pedido tornar o
  prazo incompatível;
- a validação autoritativa permanece no servidor.

### Validação

- teste do contrato visual do dropdown;
- suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Valor mínimo por prazo de pagamento

### Funcionalidade

Inclusão do valor mínimo do pedido no cadastro de prazos e aplicação da regra
na seleção e gravação do pedido.

### Motivo

Impedir que condições comerciais mais longas sejam utilizadas em pedidos cujo
total não atinja o mínimo definido pela empresa.

### Arquivos alterados

- migration, model, factory e seeder de prazos;
- controllers e formulário administrativo;
- testes de cadastro e pedido;
- regras de negócio, modelagem, API, telas, planejamento e roadmap.

### Impactos

- Admin e Manager definem o valor mínimo de cada prazo;
- o formulário desabilita prazos incompatíveis com o total atual;
- o servidor recalcula o pedido e rejeita prazos abaixo do mínimo;
- a regra respeita o isolamento por empresa;
- prazos existentes recebem mínimo zero e mantêm o comportamento atual.

### Validação

- migration de evolução com valor padrão;
- teste de persistência do mínimo;
- teste de rejeição após o cálculo autoritativo do total;
- suíte completa, Laravel Pint e build Vite.

## 2026-06-19 — Cadastros de formas e prazos de pagamento

### Funcionalidade

Criação dos cadastros multiempresa de formas e prazos de pagamento e integração
com o formulário de pedidos.

### Motivo

Substituir opções fixas no código por condições comerciais administráveis pela
empresa.

### Arquivos alterados

- migrations, models, factories e seeders;
- controllers, rotas, navegação e formulário de pedidos;
- testes administrativos;
- regras de negócio, modelagem, API, telas, planejamento e roadmap.

### Impactos

- Admin e Manager gerenciam formas e prazos no contexto de Pedidos;
- cada prazo armazena os dias corridos de suas parcelas;
- representantes selecionam somente opções ativas;
- pedidos validam códigos dentro da empresa autenticada;
- pedidos existentes preservam os códigos históricos;
- migration cria opções iniciais para empresas já existentes;
- API e sincronização passam a prever os códigos das condições comerciais;
- não altera o escopo financeiro: recebimento e faturamento permanecem no ERP.

### Validação

- migrations e seeders idempotentes;
- testes de CRUD, pedido, autorização e isolamento multiempresa;
- suíte completa;
- Laravel Pint;
- build Vite.

## 2026-06-19 — Consolidação da linguagem do produto

### Funcionalidade

Revisão dos textos da interface, documentação e diretrizes para apresentar o
OrderBox como produto final.

### Motivo

Remover referências a bases visuais, templates, bastidores de implementação e
frases com caráter demonstrativo.

### Arquivos alterados

- diretrizes do monorepo;
- interface pública e administrativa;
- manual e documentação técnica;
- revisão arquitetural;
- testes administrativos;
- avisos de terceiros.

### Impactos

- remove todas as menções ao nome da base visual anteriormente utilizada;
- substitui textos técnicos e demonstrativos por linguagem orientada ao usuário;
- remove referências fixas à empresa de desenvolvimento na interface pública;
- preserva arquitetura, comportamento, permissões e isolamento multiempresa;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- busca integral por referências residuais;
- suíte completa;
- Laravel Pint;
- build Vite.

## 2026-06-19 — Busca de representante no pedido

### Funcionalidade

Substituição do select de representantes por autocomplete no formulário Web de
pedidos e remoção do campo Origem.

### Motivo

Um select não é adequado para empresas com centenas de representantes. A
origem também não deve ser escolhida pelo usuário quando o próprio canal Web é
capaz de determiná-la.

### Arquivos alterados

- controller e formulário CRUD de pedidos;
- testes administrativos;
- regras de negócio, telas e registro de desenvolvimento.

### Impactos

- Admin e Manager pesquisam representantes por código, nome ou e-mail;
- o dropdown limita a exibição aos oito primeiros resultados;
- representantes autenticados continuam vinculados ao próprio cadastro;
- a origem deixa de aparecer no formulário e é definida pelo servidor;
- preserva validação de representante ativo e isolamento por `company_id`;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- teste de criação e edição do pedido;
- suíte completa;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Posicionamento dos tooltips de Produtos

### Funcionalidade

Definição explícita da posição superior para os tooltips das ações no cabeçalho
de preços da listagem de Produtos.

### Motivo

Manter os textos auxiliares acima dos controles e afastados das informações de
preço exibidas nas linhas da tabela.

### Arquivos alterados

- listagem de Produtos;
- testes administrativos;
- registro de desenvolvimento.

### Impactos

- tooltips de criação e renomeação de tabelas são exibidos no topo;
- preserva o portal global que impede recorte pelo DataTable;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- teste de renderização da posição;
- suíte administrativa;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Correção de tooltips em tabelas

### Funcionalidade

Alteração do posicionamento dos tooltips para uma camada global da interface.

### Motivo

Tooltips renderizados dentro de DataTables eram recortados por contêineres com
`overflow-x-auto`, independentemente do `z-index`.

### Arquivos alterados

- componente global de tooltip;
- testes administrativos;
- padrões de UI e registro de desenvolvimento.

### Impactos

- tooltips passam a ser renderizados diretamente no `body`;
- posicionamento é recalculado em hover, foco, scroll e resize;
- conteúdo permanece visível sobre tabelas, painéis e modais;
- mantém suporte a teclado e leitores de tela;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- teste de renderização do portal;
- suíte completa;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Correção da edição de pedidos

### Funcionalidade

Correção da montagem das opções de clientes e tabelas de preço no formulário de
edição de pedidos.

### Motivo

A closure que transforma clientes em opções do formulário não importava a
coleção `$applicablePriceTables`, causando erro 500 ao acessar a edição.

### Arquivos alterados

- formulário CRUD do Admin;
- testes administrativos;
- registro de desenvolvimento.

### Impactos

- restaura a abertura de `/crud/orders/{id}/edit`;
- mantém a resolução em lote das tabelas aplicáveis;
- preserva autorização e isolamento por `company_id`;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- teste de regressão da renderização da edição de pedido;
- suíte completa;
- Laravel Pint;
- build Vite.

## 2026-06-18 — Padronização de feedback e confirmações

### Funcionalidade

Criação do sistema visual de alerts, notifications, confirmações, tooltips,
popovers, spinners, ribbons, progress bars e listas.

### Motivo

Eliminar mensagens nativas e comportamentos inconsistentes, mantendo feedback
claro, acessível e coerente em todo o OrderBox.

### Arquivos alterados

- componentes Blade de feedback e interação;
- comportamento global Alpine.js para notificações, confirmações e submits;
- telas com ações críticas e mensagens persistentes;
- testes administrativos;
- documentação de telas, desenvolvimento e padrões de UI.

### Impactos

- substitui `window.confirm()` por modal padronizado;
- exige confirmação dupla em ações destrutivas ou de perda de acesso;
- transforma mensagens de sessão em notificações fecháveis;
- mantém erros visíveis até ação do usuário;
- mostra spinner e bloqueia submissões repetidas;
- adiciona componentes reutilizáveis para tooltips, popovers, ribbons,
  progress bars e listas;
- não altera banco de dados, API, Mobile ou B2B.

### Validação

- testes de renderização e comportamento esperado;
- Laravel Pint;
- build Vite.

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
