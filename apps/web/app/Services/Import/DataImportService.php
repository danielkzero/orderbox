<?php

namespace App\Services\Import;

use App\Jobs\ProcessDataImport;
use App\Jobs\ReclassifyCompanyCustomers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ImportBatch;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\Unit;
use App\Models\User;
use App\Support\BrazilianDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class DataImportService
{
    private const MAX_ROWS = 5000;

    private const CHUNK_SIZE = 100;

    public function queue(User $user, string $type, UploadedFile $file): ImportBatch
    {
        $storagePath = $file->storeAs(
            "imports/{$user->company_id}",
            Str::uuid().'.'.strtolower($file->getClientOriginalExtension()),
        );

        try {
            $batch = ImportBatch::query()->create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'type' => $type,
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $storagePath,
                'status' => 'queued',
            ]);

            ProcessDataImport::dispatch($batch->id);

            return $batch;
        } catch (Throwable $exception) {
            Storage::delete($storagePath);
            throw $exception;
        }
    }

    public function process(ImportBatch $batch): ImportBatch
    {
        $totalRows = 0;

        try {
            $batch->update([
                'status' => 'processing',
                'started_at' => now(),
                'errors' => null,
            ]);

            if (! $batch->storage_path || ! Storage::exists($batch->storage_path)) {
                throw ValidationException::withMessages(['file' => 'O arquivo temporário da importação não foi encontrado.']);
            }

            $spreadsheet = IOFactory::load(Storage::path($batch->storage_path));
            $datasets = $this->datasets($spreadsheet, $batch->type);
            $totalRows = collect($datasets)->sum(fn (array $rows): int => count($rows));

            if ($totalRows === 0) {
                throw ValidationException::withMessages(['file' => 'A planilha não possui linhas de dados.']);
            }

            if ($totalRows > self::MAX_ROWS) {
                throw ValidationException::withMessages(['file' => 'A planilha excede o limite de '.self::MAX_ROWS.' linhas.']);
            }

            $batch->update(['total_rows' => $totalRows]);
            $result = ['created' => 0, 'updated' => 0, 'processed' => 0];

            foreach (['payment_methods', 'payment_terms', 'regions', 'products', 'customers'] as $dataset) {
                foreach (array_chunk($datasets[$dataset] ?? [], self::CHUNK_SIZE) as $chunk) {
                    $chunkResult = DB::transaction(function () use ($batch, $dataset, $chunk): array {
                        $user = $batch->user;
                        $result = ['created' => 0, 'updated' => 0];

                        foreach ($chunk as $row) {
                            $action = match ($dataset) {
                                'payment_methods' => $this->importPaymentMethod($user, $row),
                                'payment_terms' => $this->importPaymentTerm($user, $row),
                                'regions' => $this->importRegion($user, $row),
                                'products' => $this->importProduct($user, $row),
                                'customers' => $this->importCustomer($user, $row),
                            };
                            $result[$action]++;
                        }

                        return $result;
                    });

                    $result['created'] += $chunkResult['created'];
                    $result['updated'] += $chunkResult['updated'];
                    $result['processed'] += count($chunk);
                    $batch->update([
                        'created_rows' => $result['created'],
                        'updated_rows' => $result['updated'],
                        'processed_rows' => $result['processed'],
                    ]);
                }
            }

            if (filled($datasets['regions'] ?? [])) {
                ReclassifyCompanyCustomers::dispatch($batch->company_id, $batch->user_id);
            }

            $batch->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
                'created_rows' => $result['created'],
                'updated_rows' => $result['updated'],
                'processed_rows' => $totalRows,
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            if (! $exception instanceof ValidationException) {
                report($exception);
            }
            $errors = $exception instanceof ValidationException
                ? collect($exception->errors())->flatten()->values()->all()
                : ['Falha interna ao processar o arquivo. Verifique o modelo e tente novamente.'];
            $batch->update([
                'status' => 'failed',
                'total_rows' => $totalRows,
                'failed_rows' => max(1, $totalRows - $batch->processed_rows),
                'errors' => array_slice($errors, 0, 100),
                'completed_at' => now(),
            ]);
        } finally {
            if ($batch->storage_path) {
                Storage::delete($batch->storage_path);
                $batch->update(['storage_path' => null]);
            }
        }

        return $batch->refresh();
    }

    private function datasets($spreadsheet, string $type): array
    {
        $mapping = [
            'payment_methods' => ['Formas de pagamento', 'payment_methods'],
            'payment_terms' => ['Prazos de pagamento', 'payment_terms'],
            'regions' => ['Regiões', 'regions'],
            'products' => ['Produtos', 'products'],
            'customers' => ['Clientes', 'customers'],
        ];
        $selected = $type === 'initial' ? array_keys($mapping) : [$type];
        $datasets = [];

        foreach ($selected as $dataset) {
            $sheet = $spreadsheet->getSheetByName($mapping[$dataset][0])
                ?? ($type !== 'initial' ? $spreadsheet->getActiveSheet() : null);

            if (! $sheet instanceof Worksheet) {
                throw ValidationException::withMessages([
                    'file' => "A aba obrigatória \"{$mapping[$dataset][0]}\" não foi encontrada.",
                ]);
            }

            $datasets[$dataset] = $this->rows($sheet);
        }

        return $datasets;
    }

    private function rows(Worksheet $sheet): array
    {
        $data = $sheet->toArray(null, true, true, false);
        $rawHeaders = array_shift($data) ?? [];
        $headers = array_map(fn ($header): string => $this->normalizeHeader((string) $header), $rawHeaders);
        $rows = [];

        foreach ($data as $offset => $values) {
            if (collect($values)->every(fn ($value): bool => blank($value))) {
                continue;
            }

            $row = [
                '_row' => $offset + 2,
                '_sheet' => $sheet->getTitle(),
                '_columns' => array_combine($headers, array_map(fn ($header): string => trim((string) $header), $rawHeaders)),
            ];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $row[$header] = in_array($header, ['codigo', 'sku', 'barcode', 'codigos_ibge'], true)
                        ? $this->identifierCellValue($sheet, $index + 1, $offset + 2)
                        : ($values[$index] ?? null);
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function importRegion(User $user, array $row): string
    {
        $coverageType = match (Str::lower($this->text($row['tipo_abrangencia'] ?? null) ?? '')) {
            'municipios', 'municipalities' => 'municipalities',
            'restante_uf', 'state_remainder' => 'state_remainder',
            default => $this->text($row['tipo_abrangencia'] ?? null),
        };
        $ibgeCodes = $this->orderedList($row['codigos_ibge'] ?? null);
        $municipalityNames = $this->orderedList($row['municipios'] ?? null);
        $microregions = $this->orderedList($row['microrregioes'] ?? null);
        $mesoregions = $this->orderedList($row['mesorregioes'] ?? null);
        $priceTableNames = $this->list($row['tabelas_preco'] ?? null);
        $data = [
            'name' => $this->text($row['nome'] ?? null),
            'level' => $this->decimal($row['nivel'] ?? null) ?? 1,
            'state' => Str::upper($this->text($row['uf'] ?? null)),
            'coverage_type' => $coverageType,
            'description' => $this->text($row['descricao'] ?? null),
            'active' => $this->boolean($row['ativo'] ?? true),
        ];

        $this->validateRow($row, $data, [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:99'],
            'state' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'coverage_type' => ['required', 'in:municipalities,state_remainder'],
            'description' => ['nullable', 'string'],
        ]);

        if ($coverageType === 'state_remainder') {
            $ibgeCodes = collect();
            $municipalityNames = collect();
            $microregions = collect();
            $mesoregions = collect();
        }

        if ($coverageType === 'municipalities' && ($ibgeCodes->isEmpty() || $ibgeCodes->count() !== $municipalityNames->count())) {
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: códigos IBGE e municípios são obrigatórios e devem ter a mesma quantidade.",
            ]);
        }

        if ($ibgeCodes->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: cada código IBGE pode aparecer somente uma vez na região.",
            ]);
        }

        foreach (['microrregiões' => $microregions, 'mesorregiões' => $mesoregions] as $label => $items) {
            if ($items->isNotEmpty() && $items->count() !== $ibgeCodes->count()) {
                throw ValidationException::withMessages([
                    'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: {$label} deve ter a mesma quantidade de códigos IBGE.",
                ]);
            }
        }

        $municipalities = $coverageType === 'municipalities'
            ? $ibgeCodes->map(fn (string $code, int $index): array => [
                'ibge_code' => $code,
                'name' => $municipalityNames->get($index),
                'state' => $data['state'],
                'microregion_name' => $microregions->get($index),
                'mesoregion_name' => $mesoregions->get($index),
            ])->values()
            : collect();

        foreach ($municipalities as $municipality) {
            $this->validateRow($row, $municipality, [
                'ibge_code' => ['required', 'regex:/^[0-9]{7}$/'],
                'name' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'size:2'],
                'microregion_name' => ['nullable', 'string', 'max:255'],
                'mesoregion_name' => ['nullable', 'string', 'max:255'],
            ]);
        }

        $region = Region::query()
            ->where('company_id', $user->company_id)
            ->where('name', $data['name'])
            ->first();
        $action = $region ? 'updated' : 'created';

        if ($coverageType === 'state_remainder' && Region::query()
            ->where('company_id', $user->company_id)
            ->where('state', $data['state'])
            ->where('coverage_type', 'state_remainder')
            ->when($region, fn ($query) => $query->whereKeyNot($region->id))
            ->exists()) {
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: já existe uma região para os demais municípios de {$data['state']}.",
            ]);
        }

        $duplicateCode = DB::table('region_municipalities')
            ->join('regions', 'regions.id', '=', 'region_municipalities.region_id')
            ->where('regions.company_id', $user->company_id)
            ->when($region, fn ($query) => $query->where('regions.id', '!=', $region->id))
            ->whereIn('region_municipalities.ibge_code', $municipalities->pluck('ibge_code'))
            ->value('region_municipalities.ibge_code');

        if ($duplicateCode) {
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: o município IBGE {$duplicateCode} já pertence a outra região comercial.",
            ]);
        }

        $payload = $data + ['city' => null];
        $region = $region
            ? tap($region)->update($payload)
            : Region::query()->create($payload + ['company_id' => $user->company_id]);
        $region->municipalities()->delete();
        $region->municipalities()->createMany($municipalities->all());

        $priceTableIds = $priceTableNames->map(fn (string $name): int => PriceTable::query()->firstOrCreate([
            'company_id' => $user->company_id,
            'name' => $name,
        ], ['active' => true])->id);
        PriceTable::query()
            ->where('company_id', $user->company_id)
            ->where('region_id', $region->id)
            ->when($priceTableIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $priceTableIds))
            ->update(['region_id' => null]);
        PriceTable::query()
            ->where('company_id', $user->company_id)
            ->whereIn('id', $priceTableIds)
            ->update(['region_id' => $region->id]);

        return $action;
    }

    private function importProduct(User $user, array $row): string
    {
        $fixedColumns = collect([
            'codigo', 'nome', 'sku', 'barcode', 'peso_kg', 'comprimento_cm', 'largura_cm',
            'altura_cm', 'preco_base', 'estoque_disponivel', 'situacao_estoque',
            'quantidade_minima', 'multiplo', 'fator_peso', 'venda_fracionada', 'ativo', 'categoria',
            'categoria_pai', 'marca', 'unidade',
        ]);
        $priceColumns = collect($row['_columns'])
            ->except($fixedColumns)
            ->filter(fn (string $header, string $column): bool => $column !== '' && $header !== '');

        if ($priceColumns->count() > 20) {
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: são permitidas no máximo 20 tabelas de preço.",
            ]);
        }

        $data = [
            'external_id' => $this->text($row['codigo'] ?? null),
            'name' => $this->text($row['nome'] ?? null),
            'sku' => $this->text($row['sku'] ?? null),
            'barcode' => $this->text($row['barcode'] ?? null),
            'weight_kg' => $this->decimal($row['peso_kg'] ?? null),
            'length_cm' => $this->decimal($row['comprimento_cm'] ?? null),
            'width_cm' => $this->decimal($row['largura_cm'] ?? null),
            'height_cm' => $this->decimal($row['altura_cm'] ?? null),
            'base_price' => $this->decimal($row['preco_base'] ?? null),
            'available_stock' => $this->decimal($row['estoque_disponivel'] ?? null),
            'stock_status' => $this->text($row['situacao_estoque'] ?? null) ?: 'InStock',
            'minimum_quantity' => $this->decimal($row['quantidade_minima'] ?? null) ?? 1,
            'quantity_multiple' => $this->decimal($row['multiplo'] ?? null),
            'allows_fractional_quantity' => $this->boolean($row['fator_peso'] ?? $row['venda_fracionada'] ?? false),
            'active' => $this->boolean($row['ativo'] ?? true),
            'category' => $this->text($row['categoria'] ?? null),
            'category_parent' => $this->text($row['categoria_pai'] ?? null),
            'brand' => $this->text($row['marca'] ?? null),
            'unit' => Str::upper($this->text($row['unidade'] ?? null)),
        ];

        $this->validateRow($row, $data, [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'available_stock' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['required', 'in:InStock,LowStock,OutOfStock'],
            'minimum_quantity' => ['required', 'numeric', 'min:0.001'],
            'quantity_multiple' => ['nullable', 'numeric', 'min:0.001'],
            'category' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:10'],
        ]);
        $this->validateQuantityConfiguration($row, $data);

        $parent = $data['category_parent'] ? Category::query()->firstOrCreate([
            'company_id' => $user->company_id,
            'parent_id' => null,
            'name' => $data['category_parent'],
        ], ['active' => true]) : null;
        $category = Category::query()->firstOrCreate([
            'company_id' => $user->company_id,
            'parent_id' => $parent?->id,
            'name' => $data['category'],
        ], ['active' => true]);
        $brand = $data['brand'] ? Brand::query()->firstOrCreate([
            'company_id' => $user->company_id,
            'name' => $data['brand'],
        ], ['active' => true]) : null;
        $unit = Unit::query()->updateOrCreate([
            'company_id' => $user->company_id,
            'code' => $data['unit'],
        ], ['name' => $data['unit'], 'active' => true]);

        $product = Product::query()->where('company_id', $user->company_id)->where('sku', $data['sku'])->first();
        $action = $product ? 'updated' : 'created';
        $payload = collect($data)->except(['category', 'category_parent', 'brand', 'unit'])->all() + [
            'company_id' => $user->company_id,
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'unit_id' => $unit->id,
            'published_at' => $data['active'] ? ($product?->published_at ?? now()) : null,
        ];
        $product = $product ? tap($product)->update($payload) : Product::query()->create($payload);

        foreach ($priceColumns as $column => $tableName) {
            $price = $this->decimal($row[$column] ?? null);
            if ($price === null) {
                continue;
            }

            $this->validateRow($row, compact('tableName', 'price'), [
                'tableName' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0.01'],
            ]);
            $priceTable = PriceTable::query()->firstOrCreate([
                'company_id' => $user->company_id,
                'name' => $tableName,
            ], ['active' => true]);
            ProductPrice::query()->updateOrCreate([
                'product_id' => $product->id,
                'price_table_id' => $priceTable->id,
            ], ['price' => $price]);
        }

        return $action;
    }

    private function importCustomer(User $user, array $row): string
    {
        $document = BrazilianDocument::normalize($this->text($row['documento'] ?? null));
        $data = [
            'corporate_name' => $this->text($row['razao_social'] ?? null),
            'trade_name' => $this->text($row['nome_fantasia'] ?? null),
            'document' => $document,
            'state_registration' => $this->text($row['inscricao_estadual'] ?? null),
            'email' => $this->text($row['email'] ?? null),
            'phone' => $this->text($row['telefone'] ?? null),
            'credit_limit' => $this->decimal($row['limite_credito'] ?? null),
            'active' => $this->boolean($row['ativo'] ?? true),
        ];
        $this->validateRow($row, $data, [
            'corporate_name' => ['required', 'string', 'max:255'],
            'document' => ['required', fn ($attribute, $value, $fail) => BrazilianDocument::isValid($value) ?: $fail('CPF/CNPJ inválido.')],
            'email' => ['nullable', 'email', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $customer = Customer::query()->where('company_id', $user->company_id)->where('document', $document)->first();
        $action = $customer ? 'updated' : 'created';
        $customer = $customer
            ? tap($customer)->update($data)
            : Customer::query()->create($data + ['company_id' => $user->company_id]);

        if ($this->text($row['logradouro'] ?? null)) {
            $address = [
                'type' => $this->text($row['endereco_tipo'] ?? null) ?: 'Comercial',
                'zip_code' => $this->text($row['cep'] ?? null),
                'street' => $this->text($row['logradouro'] ?? null),
                'number' => $this->text($row['numero'] ?? null),
                'complement' => $this->text($row['complemento'] ?? null),
                'district' => $this->text($row['bairro'] ?? null),
                'city' => $this->text($row['cidade'] ?? null),
                'state' => Str::upper($this->text($row['uf'] ?? null)),
                'municipality_ibge_code' => $this->text($row['codigo_ibge'] ?? null),
                'country' => $this->text($row['pais'] ?? null) ?: 'Brasil',
                'default_address' => true,
            ];
            $this->validateRow($row, $address, [
                'zip_code' => ['required', 'string', 'max:10'],
                'street' => ['required', 'string', 'max:255'],
                'number' => ['required', 'string', 'max:20'],
                'district' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'size:2'],
                'municipality_ibge_code' => ['nullable', 'regex:/^[0-9]{7}$/'],
            ]);
            $customer->addresses()->updateOrCreate(['type' => $address['type']], $address);
        }

        if ($this->text($row['contato_nome'] ?? null)) {
            $contact = [
                'name' => $this->text($row['contato_nome']),
                'position' => $this->text($row['contato_cargo'] ?? null),
                'department' => $this->text($row['contato_departamento'] ?? null),
                'email' => $this->text($row['contato_email'] ?? null),
                'phone' => $this->text($row['contato_telefone'] ?? null),
                'mobile' => $this->text($row['contato_celular'] ?? null),
                'whatsapp' => $this->text($row['contato_whatsapp'] ?? null),
                'primary_contact' => true,
                'active' => true,
            ];
            $this->validateRow($row, $contact, ['name' => ['required', 'max:255'], 'email' => ['nullable', 'email']]);
            $customer->contacts()->updateOrCreate(['name' => $contact['name']], $contact);
        }

        $tableNames = $this->list($row['tabelas_preco'] ?? null);
        if ($tableNames->isNotEmpty()) {
            $ids = $tableNames->map(fn (string $name): int => PriceTable::query()->firstOrCreate([
                'company_id' => $user->company_id,
                'name' => $name,
            ], ['active' => true])->id);
            $customer->priceTables()->sync($ids);
        }

        return $action;
    }

    private function importPaymentMethod(User $user, array $row): string
    {
        $data = [
            'code' => Str::slug($this->text($row['codigo'] ?? null)),
            'name' => $this->text($row['nome'] ?? null),
            'description' => $this->text($row['descricao'] ?? null),
            'sort_order' => (int) ($row['ordem'] ?? 0),
            'active' => $this->boolean($row['ativo'] ?? true),
        ];
        $this->validateRow($row, $data, [
            'code' => ['required', 'max:50'],
            'name' => ['required', 'max:100'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
        ]);
        $model = PaymentMethod::query()->where('company_id', $user->company_id)->where('code', $data['code'])->first();
        $action = $model ? 'updated' : 'created';
        $model ? $model->update($data) : PaymentMethod::query()->create($data + ['company_id' => $user->company_id]);

        return $action;
    }

    private function importPaymentTerm(User $user, array $row): string
    {
        $data = [
            'code' => $this->text($row['codigo'] ?? null),
            'name' => $this->text($row['nome'] ?? null),
            'installment_days' => $this->list($row['dias_parcelas'] ?? null, '/[\s,;\/|]+/')->map(fn ($day): int => (int) $day)->sort()->values()->all(),
            'minimum_order_amount' => $this->decimal($row['pedido_minimo'] ?? null) ?? 0,
            'description' => $this->text($row['descricao'] ?? null),
            'sort_order' => (int) ($row['ordem'] ?? 0),
            'active' => $this->boolean($row['ativo'] ?? true),
        ];
        $this->validateRow($row, $data, [
            'code' => ['required', 'max:50'],
            'name' => ['required', 'max:100'],
            'installment_days' => ['required', 'array', 'min:1'],
            'installment_days.*' => ['integer', 'min:0', 'max:3650'],
            'minimum_order_amount' => ['numeric', 'min:0'],
        ]);
        $model = PaymentTerm::query()->where('company_id', $user->company_id)->where('code', $data['code'])->first();
        $action = $model ? 'updated' : 'created';
        $model ? $model->update($data) : PaymentTerm::query()->create($data + ['company_id' => $user->company_id]);

        return $action;
    }

    private function validateRow(array $row, array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $messages = collect($validator->errors()->all())->implode(' ');
            throw ValidationException::withMessages([
                'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: {$messages}",
            ]);
        }
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
    }

    private function identifierCellValue(Worksheet $sheet, int $column, int $row): ?string
    {
        $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column).$row);
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        if ($cell->getDataType() === DataType::TYPE_STRING) {
            return trim((string) $value);
        }

        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
        if ($format !== NumberFormat::FORMAT_GENERAL) {
            return trim((string) $cell->getFormattedValue());
        }

        if (is_numeric($value) && abs((float) $value - round((float) $value)) < 0.000001) {
            return number_format((float) $value, 0, '.', '');
        }

        return trim((string) $cell->getFormattedValue());
    }

    private function validateQuantityConfiguration(array $row, array $data): void
    {
        if ($data['allows_fractional_quantity']) {
            return;
        }

        foreach (['minimum_quantity', 'quantity_multiple'] as $field) {
            $value = $data[$field] ?? null;
            if ($value !== null && abs((float) $value - round((float) $value)) > 0.000001) {
                throw ValidationException::withMessages([
                    'file' => "Aba {$row['_sheet']}, linha {$row['_row']}: {$field} deve ser inteiro quando fator_peso for não.",
                ]);
            }
        }
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function decimal(mixed $value): mixed
    {
        $value = $this->text($value);
        if ($value === null) {
            return null;
        }
        $normalized = str_contains($value, ',')
            ? str_replace(',', '.', str_replace('.', '', $value))
            : $value;

        return is_numeric($normalized) ? (float) $normalized : $value;
    }

    private function boolean(mixed $value): bool
    {
        return in_array(Str::lower(trim((string) $value)), ['1', 'sim', 's', 'true', 'yes', 'ativo'], true);
    }

    private function list(mixed $value, string $pattern = '/\|+/'): Collection
    {
        return collect(preg_split($pattern, (string) ($value ?? '')))
            ->map(fn ($item): string => trim((string) $item))
            ->filter()
            ->unique()
            ->values();
    }

    private function orderedList(mixed $value): Collection
    {
        $value = (string) ($value ?? '');

        if (trim($value) === '') {
            return collect();
        }

        return collect(explode('|', $value))
            ->map(fn ($item): ?string => $this->text($item))
            ->values();
    }
}
