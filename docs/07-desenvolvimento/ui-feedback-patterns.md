# Padrões de Feedback e Interação

## Objetivo

Padronizar mensagens, confirmações e estados transitórios do painel Web,
preservando acessibilidade, clareza e previsibilidade.

## Componentes

### Alerts

Usar `x-alert` para mensagens persistentes dentro do conteúdo:

- informação contextual;
- avisos que exigem leitura;
- erros locais;
- credenciais exibidas uma única vez.

Variações: `info`, `success`, `warning` e `error`.

### Notifications

Mensagens de sessão e falhas gerais são exibidas pelo `x-feedback-center` no
canto superior direito.

- sucesso fecha automaticamente em cinco segundos;
- erro permanece até fechamento manual;
- novas notificações podem ser publicadas pelo evento JavaScript `notify`;
- a região usa `aria-live` para leitores de tela.

### Confirmações

Formulários usam atributos `data-confirm-*` e o modal global
`x-confirmation-dialog`.

Confirmação simples:

- envio de pedido;
- inativação reversível;
- revogação de sessão;
- regeneração de segredo.

Confirmação dupla:

- exclusão definitiva;
- cancelamento de pedido enviado;
- inativação de usuário com revogação de sessões;
- bloqueio de integração;
- desativação do 2FA.

Não utilizar `window.confirm()` ou `alert()`.

### Loading e Spinners

Todo submit:

- bloqueia submissões repetidas;
- define `aria-busy`;
- reduz interação dos botões do formulário;
- adiciona spinner ao botão acionado.

O componente `x-spinner` atende carregamentos locais que não dependem de submit.

### Tooltips e Popovers

- `x-tooltip`: texto curto para controles compactos ou somente com ícone;
- `x-popover`: explicação complementar acionada por um botão textual;
- tooltip não substitui label de formulário nem informação essencial.
- tooltips são renderizados em portal no `body`, evitando recorte por
  `overflow` de tabelas, painéis e modais.

### Tabs

A navegação contextual do Admin é o padrão de tabs entre responsabilidades do
mesmo módulo. Deve manter item ativo, ícone e rolagem horizontal no mobile.

### Ribbons

`x-ribbon` identifica estados especiais em cards, como novo, crítico ou
recomendado. Não utilizar como decoração sem significado funcional.

### Progress Bar

`x-progress-bar` representa progresso mensurável entre 0 e 100. Não utilizar
para carregamentos indeterminados; nesses casos, usar spinner.

### Lists

`x-list` e `x-list-item` padronizam listas de informações e ações. Tabelas
continuam sendo usadas quando a comparação entre colunas for relevante.

## Acessibilidade

- modais usam `role="dialog"` e `aria-modal`;
- alerts e notificações usam `status` ou `alert`;
- tooltips respondem a hover e foco;
- spinners informam estado por `role="status"`;
- cores são acompanhadas por texto e ícones;
- ações destrutivas apresentam consequência antes da confirmação.
