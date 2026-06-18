# Roadmap de Remediação Arquitetural

Origem: revisão arquitetural e funcional de 2026-06-18.

## P0 — Segurança e pedidos

- [ ] Implementar Policies e matriz de permissões.
- [ ] Restringir representante à própria carteira.
- [ ] Restringir representante aos próprios pedidos.
- [ ] Implementar máquina de estados de pedido.
- [ ] Impedir uso de registros inativos.
- [ ] Criar testes cross-tenant e por perfil.

## P1 — Preços, regiões e integridade

- [ ] Confirmar cardinalidade Região × Tabela de Preço.
- [ ] Tornar Regiões a origem única do vínculo.
- [ ] Remover configuração regional de Tabelas e Produtos.
- [ ] Criar serviço de resolução de tabelas aplicáveis.
- [ ] Consolidar a matriz de preços.
- [ ] Preservar IDs de endereços e contatos.
- [ ] Implementar concorrência otimista.
- [ ] Reforçar integridade multiempresa.

## P2 — Arquitetura e resiliência

- [ ] Decompor o controller CRUD.
- [ ] Criar Form Requests por caso de uso.
- [ ] Substituir `$guarded = []`.
- [ ] Criar gateway backend para IBGE e ViaCEP.
- [ ] Criar páginas de erro institucionais.
- [ ] Padronizar erros da API.
- [ ] Definir rate limiters.
- [ ] Revisar auditoria e retenção.

## P2 — UI/UX

- [ ] Remover notificações e badges simulados.
- [ ] Implementar ou remover busca global.
- [ ] Implementar filtros e ordenação reais.
- [ ] Adicionar confirmações para ações destrutivas.
- [ ] Padronizar loading, sucesso, erro e informação.
- [ ] Separar formulários Blade por módulo.
- [ ] Criar autocompletes paginados.

## P1 — Contratos e clientes

- [ ] Identificar endpoints implementados e planejados.
- [ ] Implementar API V1 por entregas verticais.
- [ ] Criar testes de contrato.
- [ ] Implementar sincronização Mobile.
- [ ] Iniciar Mobile e B2B somente sobre contratos estáveis.

