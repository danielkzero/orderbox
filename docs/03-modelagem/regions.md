# Regiões comerciais

## Objetivo

Agrupar municípios em regiões comerciais utilizadas por representantes,
clientes e tabelas de preço. Este módulo é a origem única do vínculo regional
das tabelas.

As divisões são definidas por cada Company e não precisam coincidir com as regiões administrativas do IBGE.

## Estrutura

### Region

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| company_id | bigint | Sim | Empresa proprietária |
| name | varchar(255) | Sim | Nome da região comercial |
| level | smallint | Sim | Prioridade; números menores são avaliados primeiro |
| state | char(2) | Sim | UF abrangida |
| coverage_type | varchar(30) | Sim | `municipalities` ou `state_remainder` |
| description | text | Não | Observações comerciais |
| active | boolean | Sim | Região disponível |

### RegionMunicipality

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| region_id | bigint | Sim | Região comercial |
| ibge_code | varchar(7) | Sim | Código oficial do município no IBGE |
| name | varchar(255) | Sim | Nome do município |
| state | char(2) | Sim | UF |
| microregion_name | varchar(255) | Não | Microrregião retornada pelo IBGE |
| mesoregion_name | varchar(255) | Não | Mesorregião retornada pelo IBGE |

## Tipos de abrangência

### Municípios selecionados

A região contém uma lista explícita de municípios.

Exemplo:

- São Paulo Capital, nível 1
- Embu das Artes - SP
- São Roque - SP
- Atibaia - SP
- Mairiporã - SP

### Restante da UF

A região recebe qualquer município da UF que não esteja vinculado a uma região explícita.

Exemplo:

- São Paulo Interior, nível 2
- Abrangência: todos os municípios restantes de SP

## Resolução

O endereço padrão do cliente fornece a UF e, quando disponível, o código IBGE retornado pelo ViaCEP.

A resolução segue esta ordem:

1. região explícita contendo o código IBGE do município, respeitando o menor nível;
2. comparação normalizada por nome da cidade, como contingência;
3. região configurada como `state_remainder` para a UF;
4. tabela de preço sem região, como opção global;
5. tabelas habilitadas diretamente ao cliente possuem prioridade sobre as anteriores.

## Tabelas de Preço

Uma região pode possuir várias tabelas. Na cardinalidade atual, cada tabela
pode pertencer a no máximo uma região.

Mover uma tabela para outra região substitui o vínculo anterior. Tabelas sem
região permanecem globais.

## Fonte de localidades

UFs, municípios, microrregiões e mesorregiões são consultados na API de Localidades do IBGE:

`https://servicodados.ibge.gov.br/api/v1/localidades`

Os identificadores armazenados são os códigos oficiais fornecidos pelo IBGE.

## Importação

Regiões podem ser criadas ou atualizadas pela área de Importação de Dados.

- chave natural: `company_id` + `name`;
- municípios são substituídos integralmente em cada linha importada;
- códigos IBGE são exclusivos entre regiões da mesma empresa;
- somente uma região `state_remainder` é permitida por UF e empresa;
- tabelas informadas são criadas quando necessário e vinculadas à região;
- tabelas removidas da lista tornam-se globais;
- o processamento nunca consulta ou altera regiões de outra empresa;
- após a conclusão, clientes são reclassificados de forma assíncrona.
