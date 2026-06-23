# Tabelas de Preço e Regiões

## Objetivo

Definir a origem única de configuração das tabelas aplicáveis por região
comercial.

## Responsabilidades

### Tabelas de Preço

As tabelas de preço são gerenciadas dentro da listagem de Produtos:

- o botão `+` no cabeçalho Preço cria uma tabela ativa para a empresa;
- cada tabela ativa é exibida como uma coluna após Preço;
- o nome pode ser editado diretamente no cabeçalho da coluna;
- a tabela pode ser inativada no cabeçalho mediante confirmação dupla;
- nomes são únicos dentro da empresa;
- somente Admin e Manager podem criar, renomear ou inativar;
- representantes possuem acesso somente de leitura.

A listagem de Produtos não define região.

A inativação preserva preços, vínculos e pedidos históricos. A tabela deixa de
ser oferecida em novas operações. Pedidos em rascunho que ainda utilizem a
tabela inativa não podem ser enviados até selecionar outra tabela ativa.

### Regiões

O módulo Regiões gerencia:

- UF e abrangência municipal;
- prioridade de resolução;
- tabelas de preço vinculadas.

Regiões é a única origem de manutenção do vínculo entre região e tabela.

### Produtos

O produto recebe preços nas tabelas existentes. A própria listagem de Produtos
cria e renomeia tabelas, mas não as vincula a regiões.

## Cardinalidade

Na V1:

- uma Região possui zero ou várias Tabelas de Preço;
- uma Tabela de Preço pertence a zero ou várias Regiões;
- tabela sem região é global;
- vínculo direto de tabela ao cliente tem precedência sobre região e tabela
  global.

## Resolução

Para o endereço padrão do cliente:

1. resolver a região comercial pelo município IBGE;
2. usar a região de restante da UF quando não houver município explícito;
3. incluir tabelas da região resolvida;
4. incluir tabelas globais;
5. priorizar tabelas vinculadas diretamente ao cliente.

Somente tabelas ativas podem ser utilizadas em novos pedidos.
