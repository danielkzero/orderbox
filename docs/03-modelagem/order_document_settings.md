# Configuração do Documento do Pedido

## Objetivo

Definir, por empresa, quais informações aparecem na visualização, impressão,
PDF, anexo de e-mail e exportação Excel do pedido.

## Campos

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| id | bigint | Sim | Identificador |
| company_id | bigint | Sim | Empresa, com registro único |
| columns | json | Sim | Colunas visíveis e respectiva ordem |
| print_columns | json | Não | Colunas visíveis exclusivamente na impressão |
| image_size | varchar(20) | Sim | `small`, `medium` ou `large` |
| print_image_size | varchar(20) | Sim | Tamanho da foto na impressão |
| item_order | varchar(30) | Sim | Critério de ordenação dos itens |
| print_margin | varchar(20) | Sim | `none`, `narrow` ou `standard` |
| show_customer_address | boolean | Sim | Exibe endereço |
| show_commercial_terms | boolean | Sim | Exibe condições comerciais |
| show_notes | boolean | Sim | Exibe observações |
| show_subtotal | boolean | Sim | Exibe subtotal |
| show_total_quantity | boolean | Sim | Exibe quantidade total |
| show_total_weight | boolean | Sim | Exibe peso bruto calculado |
| show_total | boolean | Sim | Exibe valor total |
| print_* | boolean | Sim | Equivalentes de visibilidade usados na impressão |
| created_at | timestamp | Sim | Criação |
| updated_at | timestamp | Sim | Atualização |

## Regras

- somente Admin e Manager alteram o modelo;
- SalesRepresentative utiliza o modelo da empresa sem poder modificá-lo;
- no mínimo três colunas devem permanecer selecionadas;
- opções inexistentes no domínio atual, como NCM, IPI, ICMS-ST, frete e volumes,
  não são apresentadas;
- a configuração de impressão é independente do modelo digital;
- o PDF remove a margem padrão do motor e aplica somente o espaçamento interno
  do documento, evitando margem duplicada;
- a impressão usa exclusivamente a margem configurada;
- a configuração não altera os valores do pedido.
- `columns` e `print_columns` devem armazenar arrays JSON, nunca uma string que
  contenha outro JSON; valores legados são normalizados na leitura e por
  migration.
