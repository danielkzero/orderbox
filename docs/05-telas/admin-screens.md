# Admin - Telas

## Objetivo

Definir as telas do painel administrativo do OrderBox.

O painel administrativo será utilizado por:

- Administradores
- Gestores
- Equipe Comercial
- Equipe de Cadastro

---

## Menu Principal

- Dashboard
- Clientes
- Produtos
- Pedidos
- Configurações
- Ajuda

O menu lateral prioriza somente destinos frequentes. Cadastros auxiliares e
funções administrativas são apresentados como navegação contextual dentro do
módulo responsável.

### Navegação Contextual

#### Clientes

- Clientes
- Representantes
- Regiões

Representantes e Regiões são exibidos somente para Admin e Manager.

#### Produtos

- Produtos
- Categorias
- Marcas
- Unidades

Categorias, Marcas e Unidades são configurações do catálogo e não ocupam itens
independentes no menu lateral.

#### Pedidos

- Pedidos
- Formas de pagamento
- Prazos

Formas e prazos são administrados por Admin e Manager. Representantes utilizam
as opções ativas diretamente no pedido. O cadastro de prazo informa os dias das
parcelas e o valor mínimo do pedido. No formulário do pedido, opções abaixo do
total mínimo permanecem visíveis em vermelho, bloqueadas para seleção e
informam quanto falta para serem habilitadas. Um dropdown visual separa os
prazos disponíveis dos bloqueados e atualiza o estado conforme o total muda.

#### Configurações

- Meu perfil
- Segurança
- Usuários
- Integrações
- Importação
- Auditoria

Meu perfil e Segurança são pessoais. Usuários e Integrações são exclusivos de
Admin. Auditoria é disponibilizada para Admin e Manager.

Importação é disponibilizada para Admin e Manager. A tela permite baixar
modelos oficiais, enviar planilhas e consultar o histórico da própria empresa.

Na tela de Segurança, o histórico de sessões recentes é ordenado da sessão
mais nova para a mais antiga e paginado em 10 registros. A navegação informa a
faixa e o total de sessões do usuário autenticado. Cada sessão ativa pode ser
revogada individualmente.

#### Ajuda

- Manual de uso
- Guia da API

A navegação contextual é horizontal, mantém indicação visual clara do item
ativo e permite rolagem lateral em telas menores.

### Feedback e Confirmações

O painel utiliza componentes padronizados:

- notifications para sucesso e erros globais;
- alerts para mensagens persistentes no contexto da tela;
- modal de confirmação simples para ações relevantes e reversíveis;
- confirmação dupla para exclusões, cancelamentos e perda de acesso;
- spinner automático durante submissões;
- tooltips em controles compactos;
- popovers para explicações complementares;
- progress bars somente quando existir progresso mensurável;
- ribbons somente para estados especiais com significado funcional.

Não são utilizados `window.confirm()` ou alerts nativos do navegador.

### Ações em Tabelas

As ações diretas das tabelas administrativas são exibidas como botões
compactos com ícones. Cada botão possui tooltip e nome acessível para
identificar sua função, incluindo editar, visualizar, enviar, inativar,
regenerar, bloquear e revogar.

O tooltip segue o padrão `top`: é centralizado imediatamente acima do botão
correspondente e possui uma seta inferior apontando para o controle. Nos
limites laterais da janela, a caixa é ajustada sem perder o alinhamento da seta
com o botão.

Quando uma linha possui ações secundárias, o acesso ocorre pelo ícone de três
pontos verticais. O menu aberto mantém ícone e texto em cada opção para evitar
ambiguidade.

### Experiência do Painel

O painel administrativo oferece navegação responsiva, feedback imediato e
fluxos consistentes para as atividades comerciais.

Os módulos do menu consultam exclusivamente os dados da Company autenticada. Usuários são gerenciados apenas por Admin; auditoria pode ser consultada por Admin e Manager; segurança e 2FA são gerenciados pelo próprio usuário.

Representantes visualizam catálogo, clientes da própria carteira e pedidos
próprios. Cadastros administrativos e cancelamento de pedidos enviados são
restritos a Admin e Manager.

---

## Tela: Dashboard

### Objetivo

Apresentar indicadores gerais da operação.

### Informações

- Total de clientes
- Total de produtos
- Total de pedidos
- Total de representantes

### Indicadores

- Pedidos do dia
- Pedidos do mês
- Clientes cadastrados
- Última sincronização mobile por usuário

---

## Tela: Clientes

### Objetivo

Gerenciar clientes da empresa.

### Filtros

- Nome
- Documento
- Cidade
- Representante

### Ações

- Criar cliente
- Editar cliente
- Visualizar cliente
- Inativar cliente

---

## Tela: Cliente

### Informações

- Dados cadastrais
- Endereços
- Contatos
- Representantes vinculados

### Ações

- Editar
- Criar pedido

---

## Tela: Produtos

### Objetivo

Gerenciar catálogo de produtos.

### Filtros

- Código
- Nome
- Categoria
- Marca

### Ações

- Criar produto
- Editar produto
- Inativar produto
- criar tabela de preço pelo botão `+` no cabeçalho Preço;
- renomear uma tabela diretamente no cabeçalho da respectiva coluna.

Cada tabela de preço ativa é exibida como uma coluna após a coluna Preço. A
criação e a renomeação são permitidas somente para Admin e Manager. O
representante visualiza os preços, sem controles de manutenção.

---

## Tela: Produto

### Informações

- Dados do produto
- Estoque disponível
- Tabelas de preço
- Quantidade mínima
- Múltiplo de venda
- Venda fracionada por peso ou medida

### Ações

- Editar

No pedido, a quantidade inicial assume o mínimo configurado no produto. O
campo informa mínimo, múltiplo e permissão de fracionamento. O servidor rejeita
quantidades fora dessas regras.

---

## Tabelas de Preço em Produtos

Não existe módulo ou item de menu independente para Tabelas de Preço.

O cabeçalho da listagem de Produtos concentra:

- criação de tabela pelo botão `+` ao lado de Preço;
- exibição de cada tabela ativa como coluna;
- renomeação inline no cabeçalho da coluna;
- consulta dos preços cadastrados para cada produto.

Os valores são mantidos no formulário do produto. O vínculo com região
continua sendo configurado exclusivamente em Regiões.

### Regras Comerciais Futuras

As regras comerciais descritas nesta seção não fazem parte da V1.

Em uma versão futura, as tabelas de preço poderão ser vinculadas através de regras comerciais.

As regras serão avaliadas por prioridade.

A regra com maior prioridade será utilizada na formação do preço do pedido.

---

### Tipos de Regra

#### Localidade

Permite vincular uma tabela de preço a uma região geográfica.

Exemplo:

- Barra Mansa/RJ → Interior RJ

#### Cliente

Permite vincular uma tabela de preço diretamente a um cliente.

Exemplo:

- Distribuidora Múltipla → Atacado Especial Interior RJ

#### Representante

Permite vincular uma tabela de preço a um representante.

Exemplo:

- Matias Oliveira → Especial RJ

---

### Prioridade

Cada regra possuirá uma prioridade.

Quanto maior a prioridade, maior a precedência na seleção da tabela de preço.

Exemplo:

| Tipo | Origem | Tabela | Prioridade |
|--------|--------|--------|--------|
| Localidade | Barra Mansa/RJ (local_id) | Interior RJ | 1 |
| Cliente | Distribuidora Múltipla (customer_id) | Atacado Especial Interior RJ | 2 |
| Representante | Matias Oliveira (sales_representative_id) | Especial RJ | 3 |

Resultado:

Tabela utilizada:

Especial RJ

---

### Ordem de Avaliação

O sistema deverá avaliar todas as regras aplicáveis ao pedido.

A tabela de preço da regra com maior prioridade será utilizada.

Em caso de empate, deverá ser utilizada a regra criada mais recentemente.

---

### Fallback

Caso nenhuma regra seja encontrada, o sistema utilizará a tabela de preço padrão da empresa.

Antes da implementação, essa evolução exigirá modelar as regras comerciais e a identificação da tabela padrão.

---

## Tela: Representantes

### Objetivo

Gerenciar representantes comerciais.

### Informações

- Código
- Nome
- Status

### Ações

- Criar representante
- Editar representante
- Vincular clientes

---

## Tela: Regiões

### Objetivo

Gerenciar abrangência comercial e tabelas aplicáveis.

### Ações

- selecionar UF e municípios;
- configurar restante da UF;
- vincular tabelas de preço;
- mover uma tabela de outra região mediante indicação visual.

Esta é a única tela responsável pelo vínculo Região × Tabela de Preço.

---

## Tela: Pedidos

### Objetivo

Consultar pedidos gerados pela operação.

### Filtros

- Número
- Cliente
- Representante
- Status
- Data

### Ações

- Visualizar pedido
- Criar pedido
- Cancelar pedido enviado

---

## Tela: Pedido

### Informações

- Cliente
- Representante
- Itens
- Descontos
- Totais
- Forma de pagamento
- Prazo de pagamento

Para Admin e Manager, o representante é selecionado por autocomplete com busca
por código, nome ou e-mail. A lista exibe no máximo oito correspondências por
vez, evitando um `select` extenso em empresas com muitos representantes.

Quando o usuário autenticado é representante, o campo permanece fixo no
próprio representante e não permite alteração.

A origem não é exibida nem enviada pelo formulário Web. O servidor registra
automaticamente `Web`; canais autenticados pela API registram a origem
correspondente ao canal.

### Formas de Pagamento

Permite criar, editar e inativar opções com código, nome, descrição e ordem de
exibição.

### Prazos de Pagamento

Permite criar, editar e inativar condições com código, nome, dias das parcelas,
descrição e ordem de exibição.

### Ações

- Visualizar
- Enviar rascunho
- Cancelar pedido enviado

Criação e edição sempre salvam o pedido como rascunho. Envio e cancelamento são
ações explícitas e confirmadas.

Na lista de pedidos:

- `Visualizar` abre o documento preparado para impressão;
- a barra do documento oferece Imprimir, Download PDF e Download Excel;
- `Configurar pedido` abre um único modal neutro com itens, tamanho de foto,
  ordenação, informações gerais, totais e opções específicas de impressão;
- `E-mail` envia o PDF aos e-mails ativos do cliente;
- `WhatsApp` abre a conversa com um link temporário para o PDF;
- `Outros` reúne histórico de envios, duplicação e cancelamento;
- cancelamento aparece somente enquanto o pedido ainda não foi enviado.

O documento apresenta uma tarja de status no topo. Pedidos cancelados recebem
destaque vermelho; rascunhos, âmbar; e enviados, verde. A identificação também
é mantida no PDF, impressão e anexo de e-mail.

No cadastro de cliente por SalesRepresentative, limite de crédito,
representantes e tabelas diretas não são exibidos. O usuário autenticado é
vinculado automaticamente como representante principal.

No cadastro administrativo de representante, a gestão define as tabelas de
preço visíveis. No catálogo e no pedido, o representante visualiza somente
essas tabelas.

O configurador mostra somente dados suportados atualmente pelo produto. Campos
tributários, frete e volumes devem ser adicionados apenas quando seus módulos e
regras de cálculo existirem.

---

## Tela: Usuários

### Objetivo

Gerenciar usuários do sistema.

### Ações

- Criar usuário
- Editar usuário
- Inativar usuário

---

## Tela: Auditoria

### Objetivo

Consultar ações realizadas no sistema.

### Filtros

- Usuário
- Entidade
- Data

### Informações

- Ação
- Usuário
- Registro afetado, com identificação legível e ID interno
- Data

Para pedidos, a coluna Registro exibe o número comercial, por exemplo
`PED-202606-000005 (#5)`. A ação `CancelOrder` é apresentada como
`Pedido cancelado`, e a pesquisa aceita o número do pedido.

---

## Tela: Importação de Dados

### Objetivo

Carregar os cadastros essenciais para iniciar a operação da empresa.

### Modelos

- carga inicial completa;
- produtos;
- clientes;
- formas de pagamento;
- prazos de pagamento.

### Campos

- tipo da importação;
- arquivo Excel ou CSV.

### Comportamento

- download de modelo preenchido com exemplo e aba de instruções;
- limite de 10 MB e 5.000 linhas;
- validação e gravação em transação única;
- histórico paginado com arquivo, tipo, responsável, resultado e primeiro erro;
- nenhuma alteração é mantida quando a importação falha.
