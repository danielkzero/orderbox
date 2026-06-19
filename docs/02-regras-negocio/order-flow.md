# Fluxo de Pedidos

## Objetivo

Definir o ciclo de vida dos pedidos dentro do OrderBox.

O OrderBox é uma plataforma de força de vendas.

O sistema é responsável pela criação e envio de pedidos.

Processos como faturamento, financeiro, estoque oficial, emissão fiscal e entrega permanecem sob responsabilidade do ERP da empresa.

---

## Origens do Pedido

Um pedido pode ser criado por:

- Admin
- Mobile

---

## Fluxo Principal

Draft (Rascunho)

↓

Sent (Enviado)

Opcionalmente, um pedido em Sent pode ser cancelado:

Sent (Enviado) → Cancelled (Cancelado)

---

## Status

### Draft

Pedido em edição.

Permissões:

- editar
- remover
- adicionar itens
- alterar quantidades
- alterar preços

O pedido ainda não foi enviado para processamento.

---

### Sent

Pedido enviado.

Permissões:

- visualizar

Após o envio o pedido não pode mais ser alterado.

Representa um pedido entregue ao processo comercial da empresa.

---

### Cancelled

Pedido cancelado após o envio.

Permissões:

- visualizar

Restrições:

- não pode ser alterado;
- não pode ser reenviado;
- não pode voltar para Draft.

O cancelamento deve gerar registro em AuditLog.
O registro deve preservar o `order_number` como identificação legível do pedido
cancelado, além do ID interno.

Somente pedidos em rascunho, ainda não enviados, podem ser cancelados por quem
possui acesso ao pedido. Pedidos enviados permanecem imutáveis.
Pedidos não podem ser excluídos fisicamente pela interface.

### Distribuição do pedido

- a visualização utiliza um documento próprio para impressão;
- o PDF contém os mesmos dados comerciais da visualização;
- o envio por e-mail utiliza o e-mail principal e os contatos ativos do cliente,
  remove duplicidades e anexa o PDF;
- o compartilhamento por WhatsApp usa prioritariamente o contato principal com
  WhatsApp e inclui um link assinado para o PDF, válido por sete dias;
- sem telefone cadastrado, o compartilhamento abre sem destinatário;
- envios são registrados em OrderDeliveries;
- duplicar cria um novo pedido em Draft, com novo número e cópia dos itens.

---

## Regras Gerais

### Criação

Todo pedido é criado como Draft. Status, número, totais, origem e versão são
controlados pelo servidor.

### Envio

O envio é um comando explícito e exige:

- pedido em Draft;
- cliente ativo;
- representante ativo;
- tabela de preço ativa e aplicável;
- forma de pagamento ativa da empresa;
- prazo de pagamento ativo da empresa;
- ao menos um item;
- produtos ativos.

### Alterações

Somente pedidos em Draft podem ser alterados.

### Exclusão

Somente pedidos em Draft podem ser removidos.

### Cancelamento

Somente Admin e Manager podem cancelar pedidos em Sent.

Representantes não podem cancelar pedidos enviados.

## Origem e Representante no Admin

- pedidos criados ou editados no painel recebem origem `Web` no servidor;
- o formulário não permite informar ou alterar a origem;
- Admin e Manager pesquisam o representante por código, nome ou e-mail;
- usuários representantes são vinculados automaticamente ao próprio cadastro;
- o `sales_representative_id` sempre deve pertencer à mesma empresa e estar
  ativo.

Ao cadastrar ou editar cliente como SalesRepresentative:

- o próprio representante é o único vínculo e o representante principal;
- limite de crédito não pode ser informado ou alterado;
- tabelas diretas do cliente não podem ser vinculadas ou removidas;
- tentativas de envio desses campos são rejeitadas pelo backend.

As tabelas visíveis no catálogo e no pedido são limitadas pelos vínculos do
representante. Essa limitação não altera as regras existentes de aplicabilidade.

## Condições de Pagamento

- Admin e Manager mantêm formas e prazos no contexto de Pedidos;
- representantes apenas selecionam opções ativas;
- forma e prazo devem pertencer à mesma empresa do pedido;
- o prazo registra os dias corridos de cada parcela;
- cada prazo pode exigir um valor mínimo do pedido;
- o prazo somente é aceito quando o total final recalculado pelo servidor,
  após descontos e acréscimos, for igual ou superior ao mínimo configurado;
- a interface mantém os prazos indisponíveis visíveis, identifica o bloqueio e
  informa a diferença necessária para atingir o mínimo;
- o código selecionado é preservado no pedido para manter o histórico;
- inativar uma opção impede novos usos sem alterar pedidos anteriores.

### Concorrência

A edição de Draft exige a versão atual. Uma versão desatualizada é rejeitada
para evitar sobrescrita concorrente.

### Auditoria

Toda alteração relevante deve gerar registro em AuditLog.

### Numeração

Todo pedido deve possuir um número único dentro da empresa.

---

## Fluxo Mobile

Representante

↓

Cria Pedido Offline

↓

Sincronização

↓

Pedido Registrado no Servidor

↓

Draft

↓

Envio solicitado pelo representante

↓

Sent

O mobile pode sincronizar um pedido como Draft e enviá-lo posteriormente.

---

## Integração com ERP

O ERP continua responsável por:

- faturamento
- financeiro
- estoque oficial
- emissão fiscal
- entrega

O OrderBox apenas envia os pedidos para processamento.

---

## Observações Futuras

Possíveis evoluções:

- aprovação comercial
- aprovação financeira
- acompanhamento do pedido no ERP
- integração automática com ERP
- criação de pedidos pelo Portal B2B
- criação de pedidos por integrações externas
