<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
    }

    public function test_product_import_creates_related_catalog_and_prices(): void
    {
        $file = $this->spreadsheet('Produtos', [
            'codigo', 'nome', 'sku', 'barcode', 'preco_base', 'estoque_disponivel',
            'situacao_estoque', 'ativo', 'categoria', 'categoria_pai', 'marca',
            'unidade_codigo', 'unidade_nome', 'preco_01_tabela', 'preco_01_valor',
            'preco_01_quantidade_minima',
        ], [
            'EXT-1', 'Produto importado', 'SKU-IMPORT-1', '7891234567890', '100,50',
            25, 'InStock', 'sim', 'Ferramentas', 'Catálogo', 'Marca Teste', 'UN',
            'Unidade', 'Atacado', '89,90', 1,
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'products', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('categories', ['company_id' => $this->admin->company_id, 'name' => 'Ferramentas']);
        $this->assertDatabaseHas('brands', ['company_id' => $this->admin->company_id, 'name' => 'Marca Teste']);
        $this->assertDatabaseHas('units', ['company_id' => $this->admin->company_id, 'code' => 'UN']);
        $this->assertDatabaseHas('products', [
            'company_id' => $this->admin->company_id,
            'sku' => 'SKU-IMPORT-1',
            'base_price' => 100.50,
        ]);
        $this->assertDatabaseHas('price_tables', ['company_id' => $this->admin->company_id, 'name' => 'Atacado']);
        $this->assertDatabaseHas('product_prices', ['price' => 89.90, 'minimum_quantity' => 1]);
        $this->assertDatabaseHas('import_batches', [
            'company_id' => $this->admin->company_id,
            'status' => 'completed',
            'created_rows' => 1,
        ]);
    }

    public function test_invalid_import_rolls_back_the_entire_file_and_records_the_error(): void
    {
        $file = $this->spreadsheet('Produtos', [
            'nome', 'sku', 'categoria', 'unidade_codigo', 'unidade_nome',
        ], [
            'Produto sem SKU', '', 'Categoria temporária', 'UN', 'Unidade',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'products', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseMissing('categories', ['company_id' => $this->admin->company_id, 'name' => 'Categoria temporária']);
        $this->assertDatabaseCount('products', 0);
        $batch = ImportBatch::query()->firstOrFail();
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
            'nome', 'sku', 'categoria', 'unidade_codigo', 'unidade_nome', 'ativo',
        ], ['Produto inicial', 'SKU-INICIAL', 'Geral', 'UN', 'Unidade', 'sim']);
        $this->addSheet($spreadsheet, 'Clientes', [
            'razao_social', 'documento', 'ativo',
        ], ['Cliente inicial', '52998224725', 'sim']);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'initial',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('payment_methods', ['company_id' => $this->admin->company_id, 'code' => 'pix']);
        $this->assertDatabaseHas('payment_terms', ['company_id' => $this->admin->company_id, 'code' => '30_60']);
        $this->assertDatabaseHas('products', ['company_id' => $this->admin->company_id, 'sku' => 'SKU-INICIAL']);
        $this->assertDatabaseHas('customers', ['company_id' => $this->admin->company_id, 'document' => '52998224725']);
        $this->assertDatabaseHas('import_batches', ['status' => 'completed', 'created_rows' => 4]);
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
