# Plano de Implementação da V1

## Objetivo

Organizar a implementação em entregas verticais revisáveis, mantendo Admin, Mobile, API e banco alinhados.

---

## Critérios Gerais de Pronto

Cada entrega deve possuir:

- migrations e constraints aplicáveis;
- autorização e isolamento multiempresa;
- validações de negócio;
- AuditLogs para eventos obrigatórios;
- testes automatizados do caminho principal e das restrições críticas;
- contrato da API atualizado;
- tratamento de erros sem exposição de dados sensíveis.

---

## Decisões Consolidadas

- aplicação principal em Laravel 12 com Blade;
- API Mobile autenticada com Laravel Sanctum;
- aplicativo Mobile em Ionic;
- banco de dados MariaDB;
- sessão Web armazenada em banco;
- uma sessão ativa por usuário e canal;
- 2FA por código TOTP antes da substituição de sessão.

---

## Etapa 0 - Fundação

### Entregas

- estrutura dos projetos;
- configuração por ambiente;
- banco de dados e execução de migrations;
- padrão de respostas e erros da API;
- observabilidade básica;
- pipeline de testes.

### Saída

Aplicação executável com health check, migration inicial e testes no pipeline.

---

## Etapa 1 - Autenticação e Multiempresa

### Entregas

- Companies e Users;
- login, logout e perfil;
- sessão única por canal Web e Mobile;
- confirmação por 2FA antes da substituição de sessão;
- perfis Admin, Manager e SalesRepresentative;
- filtro obrigatório por Company;
- AuditLog de login e alterações administrativas.

### Testes Críticos

- usuário não acessa dados de outra Company;
- usuário inativo não autentica;
- recurso de outra Company responde como inexistente.

---

## Etapa 2 - Clientes e Carteira

### Entregas

- Customers, CustomerAddresses e CustomerContacts;
- SalesRepresentatives e CustomerRepresentatives;
- CRUD do Admin;
- consulta e criação pelo Mobile;
- concorrência otimista por `version`.

### Testes Críticos

- documento único dentro da Company;
- somente um contato principal e um representante principal;
- representante acessa apenas sua carteira;
- conflito de versão não sobrescreve alteração mais recente.

---

## Etapa 3 - Catálogo e Preços

### Entregas

- Categories, Brands e Units;
- Products, PriceTables e ProductPrices;
- gerenciamento de tabelas no cabeçalho da listagem de Produtos;
- criação pelo botão `+` e renomeação inline das colunas de preço;
- consulta pelo Mobile;
- preço escalonado por quantidade;
- estoque disponível apenas como informação recebida do ERP.

### Testes Críticos

- SKU e nomes/códigos únicos conforme contrato;
- produto inativo não pode entrar em novo pedido;
- faixa correta de preço é selecionada;
- alteração futura de preço não modifica pedidos existentes.

---

## Etapa 4 - Pedidos

### Entregas

- Orders e OrderItems;
- PaymentMethods e PaymentTerms;
- cadastros de formas e prazos por empresa;
- valor mínimo configurável por prazo e validação sobre o total final;
- tabelas de preço visíveis por representante;
- visualização, PDF, e-mail, WhatsApp, histórico e duplicação de pedidos;
- exportação Excel e configuração corporativa do documento do pedido;
- configuração independente da impressão e margem única no PDF;
- criação no Admin e Mobile;
- edição e remoção de Draft;
- envio e numeração;
- cancelamento online por Admin ou Manager;
- cálculo de descontos e totais no servidor.

### Testes Críticos

- apenas Draft pode ser alterado ou removido;
- apenas Sent pode ser cancelado;
- pedido enviado é imutável;
- totais são recalculados no servidor;
- número do pedido é único dentro da Company.
- forma e prazo inativos ou de outra empresa são rejeitados.

---

## Etapa 5 - Sincronização Mobile

### Entregas

- Devices, SyncLogs, SyncOperations e SyncChanges;
- base local e fila de operações no Mobile;
- carga inicial e sincronização incremental;
- idempotência;
- conflitos de versão;
- tela de estado e erros de sincronização.

### Testes Críticos

- reenvio da mesma operação não duplica Customer ou Order;
- falha parcial pode ser retomada;
- perda de carteira remove dados locais por Revoke;
- cursor só avança após aplicação completa;
- operações pendentes sobrevivem a uma carga inicial refeita.

---

## Etapa 6 - Auditoria e Estabilização

### Entregas

- cobertura final de AuditLogs;
- tela de auditoria;
- revisão de índices e consultas;
- limites de requisição e payload;
- revisão de segurança;
- testes completos dos fluxos Admin e Mobile.

### Saída

Release candidate da V1 com documentação, migrations e contratos alinhados.

---

## Dependências

```text
Fundação
  ↓
Autenticação e Multiempresa
  ↓
Clientes e Carteira
  ↓
Catálogo e Preços
  ↓
Pedidos
  ↓
Sincronização Mobile
  ↓
Auditoria e Estabilização
```

Catálogo e Clientes podem avançar em paralelo após a fundação multiempresa. Pedidos depende de ambos.

---

## Decisões para Revisão

Antes da implementação, confirmar:

- formato final da numeração de pedidos;
- se Manager pode cancelar pedidos;
- se representante pode alterar qualquer cliente da carteira ou apenas clientes criados por ele;
- se preços informados por Admin ou Manager podem divergir da tabela selecionada;
- política de retenção de AuditLogs, SyncLogs, SyncOperations e SyncChanges;
- limite máximo esperado de produtos e clientes por representante;
- integração responsável por atualizar `available_stock`.
