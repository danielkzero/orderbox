# Roadmap de Remediação Arquitetural

Origem: revisão arquitetural e funcional de 2026-06-18.

## P0 — Segurança e pedidos

- [x] Implementar matriz de permissões na camada de aplicação.
- [x] Restringir representante à própria carteira.
- [x] Restringir representante aos próprios pedidos.
- [x] Implementar máquina de estados de pedido.
- [x] Impedir uso de registros inativos nos novos pedidos.
- [x] Criar testes cross-tenant e por perfil.

## P1 — Preços, regiões e integridade

- [x] Confirmar cardinalidade 1:N Região × Tabela de Preço.
- [x] Tornar Regiões a origem única do vínculo.
- [x] Remover configuração regional de Tabelas e Produtos.
- [x] Criar serviço de resolução de tabelas aplicáveis.
- [x] Consolidar criação e renomeação de tabelas no cabeçalho de Produtos.
- [x] Preservar IDs de endereços e contatos.
- [x] Implementar concorrência otimista para clientes e pedidos.
- [x] Reforçar validações multiempresa na aplicação e nos testes.

## P2 — Arquitetura e resiliência

- [ ] Decompor o controller CRUD.
- [ ] Criar Form Requests por caso de uso.
- [x] Substituir `$guarded = []`.
- [x] Criar gateway backend para IBGE e ViaCEP.
- [x] Criar páginas de erro institucionais.
- [x] Padronizar erros HTTP da API existente.
- [x] Definir rate limiters para autenticação, exportação e comandos sensíveis.
- [ ] Revisar auditoria e retenção.
- [x] Remover N+1 da resolução de tabelas no formulário de pedidos.
- [x] Executar reclassificação regional de clientes em job pós-commit.

## P2 — UI/UX

- [x] Remover notificações e badges simulados.
- [x] Remover busca global sem implementação.
- [x] Adicionar filtros de status, busca e ordenação nas listagens genéricas.
- [x] Adicionar confirmações para ações destrutivas.
- [x] Manter mensagens de sucesso, erro e estados vazios no layout.
- [ ] Separar formulários Blade por módulo.
- [ ] Criar autocompletes paginados.
- [x] Criar área de importação para os cadastros essenciais do estágio zero.

## P1 — Contratos e clientes

- [ ] Identificar endpoints implementados e planejados.
- [ ] Implementar API V1 por entregas verticais.
- [ ] Criar testes de contrato.
- [ ] Implementar sincronização Mobile.
- [ ] Iniciar Mobile e B2B somente sobre contratos estáveis.

## P1 — Condições comerciais

- [x] Criar cadastros multiempresa de formas de pagamento.
- [x] Criar cadastros multiempresa de prazos e dias das parcelas.
- [x] Validar opções ativas na criação e edição de pedidos.
- [x] Restringir cada prazo pelo valor mínimo configurado para o pedido.
- [x] Restringir tabelas visíveis por representante sem substituir regras comerciais.
- [x] Criar documento, PDF, e-mail, WhatsApp, histórico e duplicação de pedidos.
- [x] Criar exportação Excel e modelo configurável de pedido por empresa.
- [x] Separar configuração de impressão e corrigir margem duplicada do PDF.
- [ ] Expor formas e prazos no contrato de sincronização Mobile.
