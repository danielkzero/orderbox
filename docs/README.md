# Documentação do OrderBox

Esta pasta descreve o escopo funcional e a modelagem conceitual do OrderBox.

## Escopo de Referência

A versão V1 contempla:

- painel administrativo;
- aplicativo mobile com operação online e offline;
- cadastros comerciais;
- catálogo e tabelas de preço;
- carteira de representantes;
- criação, envio e cancelamento de pedidos;
- consulta de estoque disponível sincronizado do ERP;
- auditoria.

Os documentos em `99-futuro` são propostas de evolução e não fazem parte da V1.

## Navegação

1. [Posicionamento do produto](01-visao-geral/product-positioning.md)
2. [Escopo da V1](01-visao-geral/product-scope.md)
3. [Fluxo de pedidos](02-regras-negocio/order-flow.md)
4. [Mapa geral do banco de dados](03-modelagem/database-map.md)
5. [Diagramas de relacionamento](03-modelagem/er-diagram.md)
6. [Visão geral da API](04-api/api-overview.md)
7. [Telas do Admin](05-telas/admin-screens.md)
8. [Telas do Mobile](05-telas/mobile-screens.md)

## Convenções

- O texto descritivo usa português; entidades, campos e valores persistidos usam inglês.
- Entidades da V1 ficam em `03-modelagem`.
- Propostas ainda fora do escopo ficam em `99-futuro`.
- Relacionamentos com entidades futuras não devem aparecer como parte da modelagem atual.
- Regras descritas nas telas devem estar alinhadas às regras de negócio e à modelagem.
