<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Jobs\ProcessDataImport;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\Region;
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

    public function test_admin_can_download_the_region_template(): void
    {
        $this->actingAs($this->admin)
            ->get(route('imports.template', 'regions'))
            ->assertOk()
            ->assertDownload('orderbox-importacao-regions.xlsx');

        $path = app(DataImportTemplateService::class)->create('regions');
        $regionsSheet = IOFactory::load($path)->getSheetByName('Regiões');
        $headers = $regionsSheet->rangeToArray('A1:K1')[0];

        $this->assertSame([
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
            'microrregioes', 'mesorregioes', 'tabelas_preco', 'descricao', 'ativo',
        ], $headers);
        $this->assertSame(NumberFormat::FORMAT_TEXT, $regionsSheet->getStyle('E2')->getNumberFormat()->getFormatCode());
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

    public function test_region_import_creates_municipalities_and_links_price_tables(): void
    {
        $file = $this->spreadsheet('Regiões', [
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
            'microrregioes', 'mesorregioes', 'tabelas_preco', 'descricao', 'ativo',
        ], [
            'Vale do Paraíba', 1, 'sp', 'municipios', '3549904|3554102',
            'São José dos Campos|Taubaté', 'São José dos Campos|São José dos Campos',
            'Vale do Paraíba Paulista|Vale do Paraíba Paulista', 'Varejo|Atacado',
            'Região importada', 'sim',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'regions', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $region = Region::query()
            ->where('company_id', $this->admin->company_id)
            ->where('name', 'Vale do Paraíba')
            ->firstOrFail();

        $this->assertSame('SP', $region->state);
        $this->assertSame('municipalities', $region->coverage_type);
        $this->assertDatabaseHas('region_municipalities', [
            'region_id' => $region->id,
            'ibge_code' => '3549904',
            'name' => 'São José dos Campos',
        ]);
        $this->assertDatabaseHas('region_municipalities', [
            'region_id' => $region->id,
            'ibge_code' => '3554102',
            'name' => 'Taubaté',
        ]);
        $this->assertSame(
            2,
            $region->priceTables()->count(),
        );
        $this->assertSame('completed', $batch->refresh()->status);
        $this->assertSame(1, $batch->created_rows);
    }

    public function test_region_import_consolidates_one_municipality_per_row_by_name_and_state(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Regiões');
        $sheet->fromArray([
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
            'microrregioes', 'mesorregioes', 'tabelas_preco', 'ativo',
        ], null, 'A1');
        $sheet->fromArray([
            'Tudo', 1, 'RO', 'municipios', '1100452', 'BURITIS',
            'PORTO VELHO', 'MADEIRA-GUAPORÉ', 'NIVEL 5', 'sim',
        ], null, 'A2');
        $sheet->fromArray([
            'Tudo', 1, 'RO', 'municipios', '1100700', 'CAMPO NOVO DE RONDÔNIA',
            'PORTO VELHO', 'MADEIRA-GUAPORÉ', 'NIVEL 5', 'sim',
        ], null, 'A3');
        $sheet->fromArray([
            'Tudo', 1, 'RO', 'municipios', '1100080', 'COSTA MARQUES',
            'GUAJARÁ-MIRIM', 'MADEIRA-GUAPORÉ', 'NIVEL 6', 'sim',
        ], null, 'A4');
        $sheet->fromArray([
            'Tudo', 1, 'AC', 'municipios', '1200401', 'RIO BRANCO',
            'RIO BRANCO', 'VALE DO ACRE', 'NIVEL 5', 'sim',
        ], null, 'A5');

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'regions',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $rondonia = Region::query()
            ->where('company_id', $this->admin->company_id)
            ->where('name', 'Tudo')
            ->where('state', 'RO')
            ->firstOrFail();
        $acre = Region::query()
            ->where('company_id', $this->admin->company_id)
            ->where('name', 'Tudo')
            ->where('state', 'AC')
            ->firstOrFail();

        $this->assertSame(3, $rondonia->municipalities()->count());
        $this->assertSame(1, $acre->municipalities()->count());
        $this->assertEqualsCanonicalizing(
            ['NIVEL 5', 'NIVEL 6'],
            $rondonia->priceTables()->pluck('name')->all(),
        );
        $levelFiveId = $rondonia->priceTables()->where('name', 'NIVEL 5')->value('price_tables.id');
        $this->assertDatabaseHas('region_price_table', [
            'region_id' => $rondonia->id,
            'price_table_id' => $levelFiveId,
        ]);
        $this->assertDatabaseHas('region_price_table', [
            'region_id' => $acre->id,
            'price_table_id' => $levelFiveId,
        ]);
        $this->assertSame('completed', $batch->refresh()->status);
        $this->assertSame(4, $batch->total_rows);
        $this->assertSame(4, $batch->processed_rows);
        $this->assertSame(2, $batch->created_rows);
    }

    public function test_region_import_keeps_municipality_isolated_between_companies(): void
    {
        $otherCompany = Company::factory()->create();
        $otherRegion = Region::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Outra empresa',
            'level' => 1,
            'state' => 'SP',
            'coverage_type' => 'municipalities',
            'active' => true,
        ]);
        $otherRegion->municipalities()->create([
            'ibge_code' => '3550308',
            'name' => 'São Paulo',
            'state' => 'SP',
        ]);

        $file = $this->spreadsheet('Regiões', [
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
        ], [
            'Capital', 1, 'SP', 'municipios', '3550308', 'São Paulo',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'regions', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        app(DataImportService::class)->process($batch);

        $this->assertSame('completed', $batch->refresh()->status);
        $this->assertDatabaseHas('regions', [
            'company_id' => $this->admin->company_id,
            'name' => 'Capital',
        ]);
    }

    public function test_region_import_rejects_municipality_already_used_by_same_company(): void
    {
        $existingRegion = Region::query()->create([
            'company_id' => $this->admin->company_id,
            'name' => 'Capital existente',
            'level' => 1,
            'state' => 'SP',
            'coverage_type' => 'municipalities',
            'active' => true,
        ]);
        $existingRegion->municipalities()->create([
            'ibge_code' => '3550308',
            'name' => 'São Paulo',
            'state' => 'SP',
        ]);

        $file = $this->spreadsheet('Regiões', [
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
        ], [
            'Capital duplicada', 1, 'SP', 'municipios', '3550308', 'São Paulo',
        ]);

        $this->actingAs($this->admin)
            ->post(route('imports.store'), ['type' => 'regions', 'file' => $file])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);

        $this->assertSame('failed', $batch->refresh()->status);
        $this->assertStringContainsString('3550308', $batch->errors[0]);
        $this->assertDatabaseMissing('regions', [
            'company_id' => $this->admin->company_id,
            'name' => 'Capital duplicada',
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
        $this->addSheet($spreadsheet, 'Regiões', [
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'ativo',
        ], ['Interior SP', 2, 'SP', 'restante_uf', 'sim']);
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
        $this->assertDatabaseHas('regions', ['company_id' => $this->admin->company_id, 'name' => 'Interior SP']);
        $this->assertDatabaseHas('products', ['company_id' => $this->admin->company_id, 'sku' => 'SKU-INICIAL']);
        $this->assertDatabaseHas('customers', ['company_id' => $this->admin->company_id, 'document' => '52998224725']);
        $this->assertDatabaseHas('import_batches', ['status' => 'completed', 'created_rows' => 5]);
    }

    public function test_import_above_five_thousand_rows_is_processed_in_chunks_and_updates_progress(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Regiões');
        $sheet->fromArray([
            'nome', 'nivel', 'uf', 'tipo_abrangencia', 'codigos_ibge', 'municipios',
            'tabelas_preco', 'ativo',
        ], null, 'A1');

        foreach (range(1, 5001) as $row) {
            $sheet->fromArray(
                ['Carga nacional', 1, 'SP', 'municipios', str_pad((string) $row, 7, '0', STR_PAD_LEFT), "Município {$row}", 'NIVEL 5', 'sim'],
                null,
                'A'.($row + 1),
            );
        }

        $this->actingAs($this->admin)
            ->post(route('imports.store'), [
                'type' => 'regions',
                'file' => $this->uploadedSpreadsheet($spreadsheet),
            ])
            ->assertRedirect(route('imports.index'));

        $batch = ImportBatch::query()->firstOrFail();
        app(DataImportService::class)->process($batch);
        $batch->refresh();

        $this->assertSame('completed', $batch->status);
        $this->assertSame(5001, $batch->total_rows);
        $this->assertSame(5001, $batch->processed_rows);
        $this->assertSame(1, $batch->created_rows);
        $region = Region::query()->where([
            'company_id' => $this->admin->company_id,
            'name' => 'Carga nacional',
            'state' => 'SP',
        ])->firstOrFail();
        $this->assertSame(5001, $region->municipalities()->count());
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
