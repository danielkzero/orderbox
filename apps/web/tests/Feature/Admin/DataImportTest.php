<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Jobs\ProcessDataImport;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\User;
use App\Services\Import\DataImportService;
use App\Services\Import\DataImportTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DataImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsureAuthenticationSessionIsActive::class);
        Queue::fake();
        $this->admin = User::factory()->create([
            'company_id' => Company::factory()->create()->id,
            'role' => 'Admin',
        ]);
    }

    public function test_admin_can_download_the_product_template(): void
    {
        $this->actingAs($this->admin)
            ->get(route('imports.template', 'products'))
            ->assertOk()
            ->assertDownload('orderbox-importacao-products.xlsx');

        $path = app(DataImportTemplateService::class)->create('products');
        $productsSheet = IOFactory::load($path)->getSheetByName('Produtos');
        $headers = $productsSheet->rangeToArray('A1:U1')[0];

        $this->assertContains('quantidade_minima', $headers);
        $this->assertContains('multiplo', $headers);
        $this->assertContains('fator_peso', $headers);
        $this->assertContains('Varejo', $headers);
        $this->assertNotContains('descricao_curta', $headers);
        $this->assertNotContains('url_imagem', $headers);
        $this->assertNotContains('unidade_codigo', $headers);
        $this->assertSame(NumberFormat::FORMAT_TEXT, $productsSheet->getStyle('A2')->getNumberFormat()->getFormatCode());
        $this->assertSame(NumberFormat::FORMAT_TEXT, $productsSheet->getStyle('C2')->getNumberFormat()->getFormatCode());
        $this->assertSame(NumberFormat::FORMAT_TEXT, $productsSheet->getStyle('D2')->getNumberFormat()->getFormatCode());
    }

    public function test_product_import_preserves_identifiers_as_strings(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produtos');
        $sheet->fromArray([
            'codigo', 'nome', 'sku', 'barcode', 'categoria', 'unidade',
        ], null, 'A1');
        $sheet->fromArray([
            1234, 'Produto com códigos numéricos', 567, null, 'Geral', 'UN',
        ], null, 'A2');
        $sheet->getStyle('A2')->getNumberFormat()->setFormatCode('000000');
        $sheet->getStyle('C2')->getNumberFormat()->setFormatCode('000000');
        $sheet->setCellValueExplicit('D2', '000789123', DataType::TYPE_STRING);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'products',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $this->assertDatabaseHas('products', [
            'company_id' => $this->admin->company_id,
            'external_id' => '001234',
            'sku' => '000567',
            'barcode' => '000789123',
        ]);
    }

    public function test_product_import_creates_related_catalog_and_prices(): void
    {
        $file = $this->spreadsheet('Produtos', [
            'codigo', 'nome', 'sku', 'barcode', 'preco_base', 'estoque_disponivel',
            'situacao_estoque', 'quantidade_minima', 'multiplo', 'fator_peso',
            'ativo', 'categoria', 'categoria_pai', 'marca', 'unidade', 'Atacado',
        ], [
            'EXT-1', 'Produto importado', 'SKU-IMPORT-1', '7891234567890', '100,50',
            25, 'InStock', 5, 5, 'não', 'sim', 'Ferramentas', 'Catálogo',
            'Marca Teste', 'UN', '89,90',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'products', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        $this->assertSame('queued', $batch->status);
        Queue::assertPushed(ProcessDataImport::class, fn ($job): bool => $job->importBatchId === $batch->id);
        app(DataImportService::class)->process($batch);

        $this->assertDatabaseHas('categories', ['company_id' => $this->admin->company_id, 'name' => 'Ferramentas']);
        $this->assertDatabaseHas('brands', ['company_id' => $this->admin->company_id, 'name' => 'Marca Teste']);
        $this->assertDatabaseHas('units', ['company_id' => $this->admin->company_id, 'code' => 'UN']);
        $this->assertDatabaseHas('products', [
            'company_id' => $this->admin->company_id,
            'sku' => 'SKU-IMPORT-1',
            'base_price' => 100.50,
            'minimum_quantity' => 5,
            'quantity_multiple' => 5,
            'allows_fractional_quantity' => false,
        ]);
        $this->assertDatabaseHas('price_tables', ['company_id' => $this->admin->company_id, 'name' => 'Atacado']);
        $this->assertDatabaseHas('product_prices', ['price' => 89.90]);
        $this->assertDatabaseHas('import_batches', [
            'company_id' => $this->admin->company_id,
            'status' => 'completed',
            'created_rows' => 1,
        ]);
    }

    public function test_invalid_import_rolls_back_the_entire_file_and_records_the_error(): void
    {
        $file = $this->spreadsheet('Produtos', [
            'nome', 'sku', 'categoria', 'unidade',
        ], [
            'Produto sem SKU', '', 'Categoria temporária', 'UN',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'products', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $this->assertDatabaseMissing('categories', ['company_id' => $this->admin->company_id, 'name' => 'Categoria temporária']);
        $this->assertDatabaseCount('products', 0);
        $batch->refresh();
        $this->assertSame('failed', $batch->status);
        $this->assertStringContainsString('linha 2', $batch->errors[0]);
    }

    public function test_initial_workbook_imports_the_stage_zero_entities(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);
        $this->addSheet($spreadsheet, 'Formas de pagamento', [
            'codigo', 'nome', 'ordem', 'ativo',
        ], ['pix', 'PIX', 10, 'sim']);
        $this->addSheet($spreadsheet, 'Prazos de pagamento', [
            'codigo', 'nome', 'dias_parcelas', 'pedido_minimo', 'ordem', 'ativo',
        ], ['30_60', '30/60 dias', '30|60', 100, 10, 'sim']);
        $this->addSheet($spreadsheet, 'Produtos', [
            'nome', 'sku', 'categoria', 'unidade', 'ativo',
        ], ['Produto inicial', 'SKU-INICIAL', 'Geral', 'UN', 'sim']);
        $this->addSheet($spreadsheet, 'Clientes', [
            'razao_social', 'documento', 'ativo',
        ], ['Cliente inicial', '52998224725', 'sim']);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'initial',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $this->assertDatabaseHas('payment_methods', ['company_id' => $this->admin->company_id, 'code' => 'pix']);
        $this->assertDatabaseHas('payment_terms', ['company_id' => $this->admin->company_id, 'code' => '30_60']);
        $this->assertDatabaseHas('products', ['company_id' => $this->admin->company_id, 'sku' => 'SKU-INICIAL']);
        $this->assertDatabaseHas('customers', ['company_id' => $this->admin->company_id, 'document' => '52998224725']);
        $this->assertDatabaseHas('import_batches', ['status' => 'completed', 'created_rows' => 4]);
    }

    public function test_large_import_is_processed_in_chunks_and_updates_progress(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Formas de pagamento');
        $sheet->fromArray(['codigo', 'nome', 'ordem', 'ativo'], null, 'A1');

        foreach (range(1, 205) as $row) {
            $sheet->fromArray(
                ["forma-{$row}", "Forma {$row}", $row, 'sim'],
                null,
                'A'.($row + 1),
            );
        }

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'payment_methods',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);
        $batch->refresh();

        $this->assertSame('completed', $batch->status);
        $this->assertSame(205, $batch->total_rows);
        $this->assertSame(205, $batch->processed_rows);
        $this->assertSame(205, $batch->created_rows);
        $this->assertDatabaseHas('payment_methods', [
            'company_id' => $this->admin->company_id,
            'code' => 'forma-205',
        ]);
    }

    public function test_sales_representative_cannot_access_imports(): void
    {
        $representative = User::factory()->create([
            'company_id' => $this->admin->company_id,
            'role' => 'SalesRepresentative',
        ]);

        $this->actingAs($representative)->get(route('imports.index'))->assertForbidden();
        $this->actingAs($representative)->get(route('imports.template', 'products'))->assertForbidden();
    }

    private function spreadsheet(string $sheetName, array $headers, array $row): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($row, null, 'A2');

        return $this->uploadedSpreadsheet($spreadsheet);
    }

    private function addSheet(Spreadsheet $spreadsheet, string $name, array $headers, array $row): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($name);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($row, null, 'A2');
    }

    private function uploadedSpreadsheet(Spreadsheet $spreadsheet): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'orderbox-test-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'importacao.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
