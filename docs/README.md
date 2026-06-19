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
10. [Visão geral da API](04-api/api-overview.md)
11. [Contrato da API V1](04-api/api-contract.md)
12. [Contrato de sincronização mobile](04-api/sync-contract.md)
13. [Telas do Admin](05-telas/admin-screens.md)
14. [Telas do Mobile](05-telas/mobile-screens.md)
15. [Plano de implementação da V1](06-planejamento/v1-implementation-plan.md)
16. [Ambiente de desenvolvimento](07-desenvolvimento/getting-started.md)
17. [Registro de desenvolvimento](07-desenvolvimento/development-log.md)
18. [Revisão arquitetural e funcional](07-desenvolvimento/architectural-functional-review-2026-06-18.md)
19. [Roadmap de remediação arquitetural](06-roadmap/remediation-roadmap.md)
20. [Padrões de feedback e interação](07-desenvolvimento/ui-feedback-patterns.md)
21. [Formas de pagamento](03-modelagem/payment_methods.md)
22. [Prazos de pagamento](03-modelagem/payment_terms.md)

## Convenções

- O texto descritivo usa português; entidades, campos e valores persistidos usam inglês.
- Entidades da V1 ficam em `03-modelagem`.
- Propostas ainda fora do escopo ficam em `99-futuro`.
- Relacionamentos com entidades futuras não devem aparecer como parte da modelagem atual.
- Regras descritas nas telas devem estar alinhadas às regras de negócio e à modelagem.
- Os contratos técnicos detalham como implementar a modelagem conceitual.
