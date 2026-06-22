<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DataImportTemplateService
{
    public const TYPES = [
        'initial' => 'Carga inicial completa',
        'products' => 'Produtos',
        'customers' => 'Clientes',
        'payment_methods' => 'Formas de pagamento',
        'payment_terms' => 'Prazos de pagamento',
    ];

    public function create(string $type): string
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        if (in_array($type, ['initial', 'payment_methods'], true)) {
            $this->addSheet($spreadsheet, 'Formas de pagamento', [
                'codigo', 'nome', 'descricao', 'ordem', 'ativo',
            ], [
                'boleto', 'Boleto bancário', 'Pagamento por boleto', 10, 'sim',
            ]);
        }

        if (in_array($type, ['initial', 'payment_terms'], true)) {
            $this->addSheet($spreadsheet, 'Prazos de pagamento', [
                'codigo', 'nome', 'dias_parcelas', 'pedido_minimo', 'descricao', 'ordem', 'ativo',
            ], [
                '30_60_90', '30/60/90 dias', '30|60|90', 500, 'Três parcelas', 10, 'sim',
            ]);
        }

        if (in_array($type, ['initial', 'products'], true)) {
            $headers = [
                'codigo', 'nome', 'sku', 'barcode', 'peso_kg', 'comprimento_cm',
                'largura_cm', 'altura_cm', 'preco_base', 'estoque_disponivel',
                'situacao_estoque', 'quantidade_minima', 'multiplo',
                'fator_peso', 'ativo', 'categoria', 'categoria_pai', 'marca',
                'unidade', 'Varejo', 'Atacado',
            ];
            $example = [
                'PROD-001', 'Produto de exemplo', 'SKU-001', '7891234567890',
                1.25, 30, 20, 10, 99.9, 100, 'InStock', 5, 5, 'não', 'sim',
                'Categoria exemplo', '', 'Marca exemplo', 'UN', 89.9, 79.9,
            ];

            $this->addSheet($spreadsheet, 'Produtos', $headers, $example);
        }

        if (in_array($type, ['initial', 'customers'], true)) {
            $this->addSheet($spreadsheet, 'Clientes', [
                'razao_social', 'nome_fantasia', 'documento', 'inscricao_estadual', 'email',
                'telefone', 'limite_credito', 'ativo', 'endereco_tipo', 'cep', 'logradouro',
                'numero', 'complemento', 'bairro', 'cidade', 'uf', 'codigo_ibge', 'pais',
                'contato_nome', 'contato_cargo', 'contato_departamento', 'contato_email',
                'contato_telefone', 'contato_celular', 'contato_whatsapp', 'tabelas_preco',
            ], [
                'Empresa Exemplo LTDA', 'Empresa Exemplo', '11222333000181', '123456789',
                'contato@exemplo.com', '1133334444', 10000, 'sim', 'Comercial', '01001000',
                'Praça da Sé', '100', 'Sala 1', 'Sé', 'São Paulo', 'SP', '3550308', 'Brasil',
                'Maria Silva', 'Compradora', 'Compras', 'maria@exemplo.com', '1133335555',
                '11999999999', '11999999999', 'Tabela 1|Tabela 2',
            ]);
        }

        $this->addInstructions($spreadsheet, $type);
        $spreadsheet->setActiveSheetIndex(0);

        $path = tempnam(sys_get_temp_dir(), 'orderbox-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function addSheet(Spreadsheet $spreadsheet, string $title, array $headers, array $example): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($example, null, 'A2');
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF465FFF');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");

        for ($index = 1; $index <= Coordinate::columnIndexFromString($lastColumn); $index++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
    }

    private function addInstructions(Spreadsheet $spreadsheet, string $type): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Instruções');
        $sheet->fromArray([
            ['Regra', 'Descrição'],
            ['Arquivo', 'Use o modelo sem alterar os nomes das abas ou cabeçalhos. XLSX, XLS ou CSV; carga completa exige XLSX/XLS.'],
            ['Limite', 'Máximo de 5.000 linhas de dados e 10 MB por arquivo.'],
            ['Atualização', 'Produtos são identificados pelo SKU; clientes pelo CPF/CNPJ; formas e prazos pelo código.'],
            ['Valores', 'Decimais podem usar vírgula ou ponto. Campos booleanos aceitam sim/não, 1/0, true/false.'],
            ['Produtos', 'Categoria, categoria pai, marca, unidade e tabelas de preço são criadas quando ainda não existem.'],
            ['Tabelas de preço', 'Depois da coluna unidade, use cada cabeçalho como nome de tabela e informe somente o preço nas linhas. São aceitas até 20 tabelas.'],
            ['Quantidades', 'Quantidade mínima e múltiplo pertencem ao produto. Fator peso aceita decimais para venda por peso ou medida.'],
            ['Clientes', 'O documento deve ser CPF/CNPJ válido. Tabelas de preço são separadas por |.'],
            ['Prazos', 'Dias das parcelas são separados por |, /, vírgula ou ponto e vírgula.'],
            ['Transação', 'Se uma linha for inválida, nenhuma alteração do arquivo será gravada.'],
            ['Tipo do modelo', self::TYPES[$type]],
        ], null, 'A1');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF465FFF');
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(110);
        $sheet->getStyle('A:B')->getAlignment()->setWrapText(true)->setVertical('top');
    }
}
