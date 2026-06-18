# Tabelas de Preço e Regiões

## Objetivo

Definir a origem única de configuração das tabelas aplicáveis por região
comercial.

## Responsabilidades

### Tabelas de Preço

O módulo Tabelas de Preço gerencia:

- nome;
- descrição;
- status;
- produtos;
- faixas de quantidade;
- preços.

O módulo não define região.

### Regiões

O módulo Regiões gerencia:

- UF e abrangência municipal;
- prioridade de resolução;
- tabelas de preço vinculadas.

Regiões é a única origem de manutenção do vínculo entre região e tabela.

### Produtos

O produto pode receber preços nas tabelas existentes, mas não cria, renomeia
ou vincula tabelas a regiões.

## Cardinalidade

Na implementação atual:

- uma Região possui zero ou várias Tabelas de Preço;
- uma Tabela de Preço pertence a zero ou uma Região;
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

