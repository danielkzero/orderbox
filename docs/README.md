# Documentação do OrderBox

Esta pasta descreve o escopo funcional, a modelagem e os contratos técnicos do OrderBox.

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
4. [Autenticação e sessão única](02-regras-negocio/authentication.md)
5. [Tabelas de preço e regiões](02-regras-negocio/pricing-and-regions.md)
6. [Mapa geral do banco de dados](03-modelagem/database-map.md)
7. [Diagramas de relacionamento](03-modelagem/er-diagram.md)
8. [Contrato relacional da V1](03-modelagem/database-contract.md)
9. [Sessões e desafios de autenticação](03-modelagem/authentication_sessions.md)
10. [Regras de importação](02-regras-negocio/data-import.md)
11. [Lotes de importação](03-modelagem/import_batches.md)
12. [Visão geral da API](04-api/api-overview.md)
13. [Contrato da API V1](04-api/api-contract.md)
14. [Contrato de sincronização mobile](04-api/sync-contract.md)
15. [Telas do Admin](05-telas/admin-screens.md)
16. [Telas do Mobile](05-telas/mobile-screens.md)
17. [Plano de implementação da V1](06-planejamento/v1-implementation-plan.md)
18. [Ambiente de desenvolvimento](07-desenvolvimento/getting-started.md)
19. [Registro de desenvolvimento](07-desenvolvimento/development-log.md)
20. [Revisão arquitetural e funcional](07-desenvolvimento/architectural-functional-review-2026-06-18.md)
21. [Roadmap de remediação arquitetural](06-roadmap/remediation-roadmap.md)
22. [Padrões de feedback e interação](07-desenvolvimento/ui-feedback-patterns.md)
23. [Formas de pagamento](03-modelagem/payment_methods.md)
24. [Prazos de pagamento](03-modelagem/payment_terms.md)
25. [Tabelas visíveis por representante](03-modelagem/sales_representative_price_tables.md)
24. [Envios de pedidos](03-modelagem/order_deliveries.md)
25. [Configuração do documento do pedido](03-modelagem/order_document_settings.md)

## Convenções

- O texto descritivo usa português; entidades, campos e valores persistidos usam inglês.
- Entidades da V1 ficam em `03-modelagem`.
- Propostas ainda fora do escopo ficam em `99-futuro`.
- Relacionamentos com entidades futuras não devem aparecer como parte da modelagem atual.
- Regras descritas nas telas devem estar alinhadas às regras de negócio e à modelagem.
- Os contratos técnicos detalham como implementar a modelagem conceitual.
