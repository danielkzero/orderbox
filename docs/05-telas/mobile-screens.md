# Mobile - Telas

## Objetivo

Definir as telas do aplicativo mobile do OrderBox.

O aplicativo será utilizado principalmente por representantes comerciais externos.

O aplicativo deverá funcionar online e offline.

---

## Fluxo Principal

Login

↓

Dashboard

↓

Clientes

↓

Cliente

↓

Novo Pedido

↓

Itens

↓

Enviar Pedido

---

## Tela: Login

### Objetivo

Permitir autenticação do usuário.

### Campos

- E-mail
- Senha

### Ações

- Entrar

---

## Escopo do Representante

O aplicativo mobile é focado na operação do representante comercial.

Por padrão o usuário visualizará apenas:

- clientes vinculados
- pedidos vinculados
- informações da própria carteira

Produtos, categorias, marcas e tabelas de preço são compartilhados pela empresa.

---

## Tela: Dashboard

### Objetivo

Apresentar informações rápidas para o representante.

### Informações

- Quantidade de clientes
- Quantidade de pedidos
- Última sincronização

### Ações

- Sincronizar dados
- Acessar clientes
- Acessar produtos
- Acessar pedidos

---

## Tela: Clientes (Listagem)

### Objetivo

Listar apenas os clientes vinculados ao representante.

### Filtros

- Nome
- Documento
- Cidade

### Ações

- Visualizar cliente
- Criar cliente
- Editar cliente
- Criar pedido

---

## Tela: Cliente (Informações individuais)

### Objetivo

Visualizar informações do cliente.

### Informações

- Razão social
- Nome fantasia
- Documento
- Endereços
- Contatos

### Ações

- Criar pedido
- Editar cliente

---

## Tela: Novo Cliente

### Objetivo

Permitir o cadastro de novos clientes diretamente pelo aplicativo.

### Informações

#### Dados Básicos

- Razão social
- Nome fantasia
- Documento
- Inscrição estadual
- Telefone
- E-mail

#### Endereço

- CEP
- Logradouro
- Número
- Complemento
- Bairro
- Cidade
- Estado

#### Contato Principal

- Nome
- Telefone
- E-mail

### Ações

- Salvar
- Salvar e Criar Pedido

### Regras

O cliente será automaticamente vinculado ao representante responsável pelo cadastro.

O cadastro poderá ser realizado offline.

A sincronização ocorrerá posteriormente.

---

## Tela: Histórico do Cliente

### Objetivo

Apresentar informações comerciais do cliente.

### Informações

- Última compra
- Último pedido
- Quantidade de pedidos
- Valor total comprado

### Produtos Mais Comprados

- Produto
- Quantidade

### Ações

- Criar pedido

---

## Tela: Produtos (Listagem)

### Objetivo

Consultar catálogo de produtos.

### Informações

- Imagem
- Código
- Nome
- Estoque disponível

### Filtros

- Nome
- Categoria
- Marca

### Ações

- Visualizar produto

---

## Tela: Produto (Informações individuais)

### Objetivo

Visualizar detalhes do produto.

### Informações

- Imagem
- Código
- Nome
- Descrição
- Estoque disponível
- Preços

### Ações

- Adicionar ao pedido

---

## Tela: Pedidos

### Objetivo

Listar apenas os pedidos criados pelo representante (usuário).

### Filtros

- Número
- Cliente
- Status

### Ações

- Visualizar pedido
- Criar pedido

---

## Tela: Novo Pedido

### Objetivo

Criar pedido para um cliente.

### Informações

- Cliente
- Tabela de preço
- Itens

### Ações

- Adicionar item
- Remover item
- Salvar rascunho
- Enviar pedido

---

## Tela: Pedido

### Objetivo

Visualizar pedido.

### Informações

- Número
- Cliente
- Itens
- Totais
- Status

### Ações

- Visualizar

---

## Tela: Sincronização

### Objetivo

Exibir informações da sincronização.

### Informações

- Última sincronização
- Pedidos pendentes de envio

### Ações

- Sincronizar agora

---

## Tela: Perfil

### Objetivo

Exibir informações do usuário.

### Informações

- Nome
- E-mail
- Empresa

### Ações

- Sair