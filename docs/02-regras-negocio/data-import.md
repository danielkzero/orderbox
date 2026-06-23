# Importação de Dados

## Objetivo

Permitir a carga inicial e a atualização controlada dos cadastros essenciais de
uma empresa por planilhas Excel ou CSV.

## Perfis

- Admin;
- Manager.

Representantes comerciais não podem acessar modelos, histórico ou execução de
importações.

## Formatos

- `.xlsx`;
- `.xls`;
- `.csv` para importações de uma única entidade.

A carga inicial completa exige Excel porque utiliza múltiplas abas.

## Limites

- 10 MB por arquivo;
- 20 tabelas de preço por linha de produto.

## Processamento Assíncrono

O upload apenas registra o lote e armazena temporariamente o arquivo. A
importação é executada pela fila em blocos sequenciais de 100 linhas.
Não há limite fixo de quantidade de linhas; o tamanho máximo do arquivo é a
restrição de entrada.

Cada bloco usa sua própria transação. Se um bloco falhar, ele é revertido e o
lote é encerrado como falho. Blocos concluídos anteriormente permanecem
gravados; uma nova execução é segura porque as entidades são atualizadas pelas
chaves naturais documentadas.

O arquivo temporário é removido ao concluir ou falhar.

## Identificação para Atualização

| Entidade | Chave |
|---|---|
| Região | `nome` dentro da empresa |
| Produto | `sku` dentro da empresa |
| Cliente | CPF/CNPJ dentro da empresa |
| Forma de pagamento | `codigo` dentro da empresa |
| Prazo de pagamento | `codigo` dentro da empresa |

## Carga Inicial

O arquivo consolidado possui as abas:

1. Formas de pagamento;
2. Prazos de pagamento;
3. Regiões;
4. Produtos;
5. Clientes.

Essa é também a ordem de processamento.

## Regiões

A aba Regiões cria ou atualiza regiões comerciais pelo nome dentro da empresa.
Cada linha representa uma região completa e substitui seus municípios e
vínculos com tabelas de preço.

As colunas são:

| Coluna | Obrigatória | Destino/Regra |
|---|---|---|
| nome | Sim | Chave de criação ou atualização |
| nivel | Não | Prioridade de `1` a `99`; padrão `1` |
| uf | Sim | Sigla da unidade federativa |
| tipo_abrangencia | Sim | `municipios` ou `restante_uf` |
| codigos_ibge | Para `municipios` | Códigos de sete dígitos separados por `|` |
| municipios | Para `municipios` | Nomes separados por `|`, na mesma ordem dos códigos |
| microrregioes | Não | Nomes separados por `|`, na mesma ordem dos códigos |
| mesorregioes | Não | Nomes separados por `|`, na mesma ordem dos códigos |
| tabelas_preco | Não | Tabelas separadas por `|`; são criadas quando inexistentes |
| descricao | Não | Observações comerciais |
| ativo | Não | Padrão verdadeiro |

As listas geográficas paralelas devem possuir a mesma quantidade de itens.
Microrregiões e mesorregiões podem ficar vazias. Para `restante_uf`, os campos
de municípios são ignorados.

Somente uma região `restante_uf` pode existir por UF e empresa. Um código IBGE
não pode pertencer a duas regiões da mesma empresa, mas pode ser usado por
empresas diferentes. Ao concluir uma importação com regiões, a reclassificação
dos clientes da empresa é enviada para a fila.

## Produtos

A aba Produtos concentra dados de:

- produto;
- categoria e categoria pai;
- marca;
- unidade;
- tabelas de preço;
- preços;
- quantidade mínima, múltiplo e venda fracionada do produto.

Categorias, marcas, unidades e tabelas de preço inexistentes são criadas
automaticamente dentro da empresa autenticada.

`codigo` (`products.external_id`), `sku` e `barcode` são identificadores
textuais. O modelo formata essas colunas como texto e o importador preserva
zeros à esquerda. Códigos longos devem ser preenchidos no modelo fornecido sem
alterar essa formatação, evitando a conversão ou perda de precisão pelo Excel.

### Colunas de Produto

| Coluna | Obrigatória | Destino/Regra |
|---|---|---|
| codigo | Não | Texto preservado em `products.external_id` |
| nome | Sim | Nome do produto |
| sku | Sim | Texto usado como chave de criação/atualização |
| barcode | Não | Código de barras preservado como texto |
| peso_kg | Não | Peso em quilogramas |
| comprimento_cm | Não | Comprimento |
| largura_cm | Não | Largura |
| altura_cm | Não | Altura |
| preco_base | Não | Preço base |
| estoque_disponivel | Não | Quantidade disponível |
| situacao_estoque | Não | `InStock`, `LowStock` ou `OutOfStock` |
| quantidade_minima | Não | Menor quantidade aceita; padrão `1` |
| multiplo | Não | Obriga venda em múltiplos do valor |
| fator_peso | Não | `sim` permite decimais para peso ou medida |
| ativo | Não | Padrão verdadeiro |
| categoria | Sim | Categoria criada ou localizada pelo nome |
| categoria_pai | Não | Categoria pai criada ou localizada pelo nome |
| marca | Não | Marca criada ou localizada pelo nome |
| unidade | Sim | Código/nome curto da unidade, por exemplo `UN`, `KG` ou `MT` |

Depois de `unidade`, cada cabeçalho adicional representa diretamente o nome de
uma tabela de preço:

```text
... | unidade | Varejo | Atacado | Distribuidor
... | UN      | 39,90  | 34,90   | 29,90
```

São aceitas no máximo 20 colunas de tabelas de preço.

## Clientes

A aba Clientes suporta:

- dados cadastrais;
- um endereço principal;
- um contato principal;
- vínculo direto com tabelas de preço separadas por `|`.

CPF ou CNPJ inválido impede a importação.

### Colunas de Cliente

| Grupo | Colunas |
|---|---|
| Cadastro | `razao_social`, `nome_fantasia`, `documento`, `inscricao_estadual`, `email`, `telefone`, `limite_credito`, `ativo` |
| Endereço | `endereco_tipo`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `codigo_ibge`, `pais` |
| Contato | `contato_nome`, `contato_cargo`, `contato_departamento`, `contato_email`, `contato_telefone`, `contato_celular`, `contato_whatsapp` |
| Preços | `tabelas_preco`, com nomes separados por `|` |

## Prazos

Os dias das parcelas podem ser separados por `|`, `/`, vírgula, ponto e vírgula
ou espaço.

As colunas são `codigo`, `nome`, `dias_parcelas`, `pedido_minimo`,
`descricao`, `ordem` e `ativo`.

## Formas de Pagamento

As colunas são `codigo`, `nome`, `descricao`, `ordem` e `ativo`.

## Segurança

- todas as consultas e gravações usam o `company_id` do usuário autenticado;
- IDs internos não são aceitos na planilha;
- arquivos não são mantidos após o processamento;
- erros internos não expõem SQL ou estrutura do banco;
- a execução possui rate limiting e registro de auditoria.
- o mesmo lote não pode ser processado simultaneamente por dois workers.

## Fora do Estágio Zero

- representantes, pois dependem da criação prévia de usuários;
- pedidos, pois dependem de cliente, representante, produto e regras comerciais.
