<?php

namespace App\Services\Import;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ImportBatch;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Unit;
use App\Models\User;
use App\Support\BrazilianDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class DataImportService
{
    private const MAX_ROWS = 5000;

    public function import(User $user, string $type, UploadedFile $file): ImportBatch
    {
        $batch = ImportBatch::query()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'processing',
        ]);

        $totalRows = 0;

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $datasets = $this->datasets($spreadsheet, $type);
            $totalRows = collect($datasets)->sum(fn (array $rows): int => count($rows));

            if ($totalRows === 0) {
                throw ValidationException::withMessages(['file' => 'A planilha não possui linhas de dados.']);
            }

            if ($totalRows > self::MAX_ROWS) {
                throw ValidationException::withMessages(['file' => 'A planilha excede o limite de '.self::MAX_ROWS.' linhas.']);
            }

            $result = DB::transaction(function () use ($user, $datasets): array {
                $result = ['created' => 0, 'updated' => 0];

                foreach (['payment_methods', 'payment_terms', 'products', 'customers'] as $dataset) {
                    foreach ($datasets[$dataset] ?? [] as $row) {
                        $action = match ($dataset) {
                            'payment_methods' => $this->importPaymentMethod($user, $row),
                            'payment_terms' => $this->importPaymentTerm($user, $row),
                            'products' => $this->importProduct($user, $row),
                            'customers' => $this->importCustomer($user, $row),
                        };
                        $result[$action]++;
                    }
                }

                return $result;
            });

            $batch->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
                'created_rows' => $result['created'],
                'updated_rows' => $result['updated'],
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
                'failed_rows' => 1,
                'errors' => array_slice($errors, 0, 100),
                'completed_at' => now(),
            ]);
        }

        return $batch->refresh();
    }

    private function datasets($spreadsheet, string $type): array
    {
        $mapping = [
            'payment_methods' => ['Formas de pagamento', 'payment_methods'],
            'payment_terms' => ['Prazos de pagamento', 'payment_terms'],
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
                    $row[$header] = $values[$index] ?? null;
                }
            }
            $rows[] = $row;
        }

        return $rows;
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
}
