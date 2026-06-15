# Admin - Telas

## Objetivo

Definir as telas do painel administrativo do OrderBox.

O painel administrativo será utilizado por:

- Administradores
- Gestores
- Equipe Comercial
- Equipe de Cadastro

---

## Menu Principal

- Dashboard
- Clientes
- Produtos
- Tabelas de Preço
- Representantes
- Pedidos
- Usuários
- Auditoria

---

## Tela: Dashboard

### Objetivo

Apresentar indicadores gerais da operação.

### Informações

- Total de clientes
- Total de produtos
- Total de pedidos
- Total de representantes

### Indicadores

- Pedidos do dia
- Pedidos do mês
- Clientes cadastrados
- Última sincronização mobile por usuário

---

## Tela: Clientes

### Objetivo

Gerenciar clientes da empresa.

### Filtros

- Nome
- Documento
- Cidade
- Representante

### Ações

- Criar cliente
- Editar cliente
- Visualizar cliente
- Inativar cliente

---

## Tela: Cliente

### Informações

- Dados cadastrais
- Endereços
- Contatos
- Representantes vinculados

### Ações

- Editar
- Criar pedido

---

## Tela: Produtos

### Objetivo

Gerenciar catálogo de produtos.

### Filtros

- Código
- Nome
- Categoria
- Marca

### Ações

- Criar produto
- Editar produto
- Inativar produto

---

## Tela: Produto

### Informações

- Dados do produto
- Estoque disponível
- Tabelas de preço

### Ações

- Editar

---

## Tela: Tabelas de Preço

### Objetivo

Gerenciar tabelas de preço da empresa.

### Informações

- Nome
- Status
- Quantidade de produtos

### Ações

- Criar tabela
- Editar tabela
- Vincular produtos

### Regras Comerciais Futuras

As regras comerciais descritas nesta seção não fazem parte da V1.

Em uma versão futura, as tabelas de preço poderão ser vinculadas através de regras comerciais.

As regras serão avaliadas por prioridade.

A regra com maior prioridade será utilizada na formação do preço do pedido.

---

### Tipos de Regra

#### Localidade

Permite vincular uma tabela de preço a uma região geográfica.

Exemplo:

- Barra Mansa/RJ → Interior RJ

#### Cliente

Permite vincular uma tabela de preço diretamente a um cliente.

Exemplo:

- Distribuidora Múltipla → Atacado Especial Interior RJ

#### Representante

Permite vincular uma tabela de preço a um representante.

Exemplo:

- Matias Oliveira → Especial RJ

---

### Prioridade

Cada regra possuirá uma prioridade.

Quanto maior a prioridade, maior a precedência na seleção da tabela de preço.

Exemplo:

| Tipo | Origem | Tabela | Prioridade |
|--------|--------|--------|--------|
| Localidade | Barra Mansa/RJ (local_id) | Interior RJ | 1 |
| Cliente | Distribuidora Múltipla (customer_id) | Atacado Especial Interior RJ | 2 |
| Representante | Matias Oliveira (sales_representative_id) | Especial RJ | 3 |

Resultado:

Tabela utilizada:

Especial RJ

---

### Ordem de Avaliação

O sistema deverá avaliar todas as regras aplicáveis ao pedido.

A tabela de preço da regra com maior prioridade será utilizada.

Em caso de empate, deverá ser utilizada a regra criada mais recentemente.

---

### Fallback

Caso nenhuma regra seja encontrada, o sistema utilizará a tabela de preço padrão da empresa.

Antes da implementação, essa evolução exigirá modelar as regras comerciais e a identificação da tabela padrão.

---

## Tela: Representantes

### Objetivo

Gerenciar representantes comerciais.

### Informações

- Código
- Nome
- Status

### Ações

- Criar representante
- Editar representante
- Vincular clientes

---

## Tela: Pedidos

### Objetivo

Consultar pedidos gerados pela operação.

### Filtros

- Número
- Cliente
- Representante
- Status
- Data

### Ações

- Visualizar pedido
- Criar pedido
- Cancelar pedido enviado

---

## Tela: Pedido

### Informações

- Cliente
- Representante
- Itens
- Descontos
- Totais

### Ações

- Visualizar
- Cancelar pedido enviado

---

## Tela: Usuários

### Objetivo

Gerenciar usuários do sistema.

### Ações

- Criar usuário
- Editar usuário
- Inativar usuário

---

## Tela: Auditoria

### Objetivo

Consultar ações realizadas no sistema.

### Filtros

- Usuário
- Entidade
- Data

### Informações

- Ação
- Usuário
- Registro afetado
- Data
