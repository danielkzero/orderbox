<?php

namespace App\Http\Controllers;

use App\Jobs\ReclassifyCompanyCustomers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Models\User;
use App\Rules\ValidBrazilianDocument;
use App\Services\ApplicablePriceTableService;
use App\Services\AuditService;
use App\Services\CommercialRegionResolver;
use App\Services\OperationalAccess;
use App\Support\BrazilianDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogCrudController extends Controller
{
    public function __construct(
        private readonly OperationalAccess $access,
        private readonly ApplicablePriceTableService $applicablePriceTables,
    ) {}

    public function create(Request $request, string $resource): View
    {
        $this->access->authorize($request->user(), $resource, 'create');

        return $this->form($request, $resource, $this->newModel($resource));
    }

    public function store(Request $request, string $resource, AuditService $audit): RedirectResponse
    {
        $this->access->authorize($request->user(), $resource, 'create');
        $config = $this->config($resource);
        $data = $this->validated($request, $resource);
        $model = $this->saveModel($request, $resource, $data);
        $audit->record($request->user(), 'Create', $model, null, $model->toArray());

        return redirect()->route($config['index'])->with('status', $config['label'].' criado.');
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        $model = $this->findModel($request, $resource, $id);
        $this->access->authorize($request->user(), $resource, 'update', $model);

        return $this->form($request, $resource, $model);
    }

    public function update(Request $request, string $resource, int $id, AuditService $audit): RedirectResponse
    {
        $model = $this->findModel($request, $resource, $id);
        $this->access->authorize($request->user(), $resource, 'update', $model);
        $oldValues = $model->toArray();
        $model = $this->saveModel($request, $resource, $this->validated($request, $resource, $model), $model);
        $audit->record($request->user(), 'Update', $model, $oldValues, $model->fresh()->toArray());

        return redirect()->route($this->config($resource)['index'])->with('status', $this->config($resource)['label'].' atualizado.');
    }

    public function deactivate(Request $request, string $resource, int $id, AuditService $audit): RedirectResponse
    {
        $model = $this->findModel($request, $resource, $id);
        $this->access->authorize($request->user(), $resource, 'delete', $model);
        $oldValues = $model->toArray();

        if ($resource === 'orders') {
            return back()->withErrors(['order' => 'Pedidos não são excluídos. Use a ação Cancelar para preservar o histórico.']);
        } else {
            $model->update(['active' => false]);
            $newValues = ['active' => false];
        }

        $audit->record($request->user(), 'Deactivate', $model, $oldValues, $newValues);

        return back()->with('status', $resource === 'orders' ? 'Pedido atualizado.' : $this->config($resource)['label'].' inativado.');
    }

    public function sendOrder(Request $request, Order $order, AuditService $audit): RedirectResponse
    {
        abort_unless($order->company_id === $request->user()->company_id, 404);
        $this->access->authorize($request->user(), 'orders', 'update', $order);
        abort_unless($order->status === 'Draft', 422, 'Somente pedidos em rascunho podem ser enviados.');

        $order->loadMissing(['customer', 'salesRepresentative', 'priceTable', 'items.product']);
        abort_unless($order->customer?->active, 422, 'O cliente do pedido está inativo.');
        abort_unless($order->salesRepresentative?->active, 422, 'O representante do pedido está inativo.');
        abort_unless($order->priceTable?->active, 422, 'A tabela de preço do pedido está inativa.');
        abort_if($order->items->isEmpty(), 422, 'O pedido deve possuir ao menos um item.');
        abort_if($order->items->contains(fn (OrderItem $item) => ! $item->product?->active), 422, 'O pedido possui produto inativo.');

        DB::transaction(function () use ($request, $order, $audit): void {
            $oldValues = $order->only(['status', 'sent_at', 'version']);
            $order->update([
                'status' => 'Sent',
                'sent_at' => now(),
                'cancelled_at' => null,
                'version' => $order->version + 1,
            ]);
            $audit->record($request->user(), 'SendOrder', $order, $oldValues, $order->only(['status', 'sent_at', 'version']));
        });

        return back()->with('status', 'Pedido enviado com sucesso.');
    }

    public function cancelOrder(Request $request, Order $order, AuditService $audit): RedirectResponse
    {
        abort_unless($order->company_id === $request->user()->company_id, 404);
        $this->access->authorize($request->user(), 'orders', 'update', $order);
        abort_unless($order->status === 'Draft', 422, 'Somente pedidos ainda não enviados podem ser cancelados.');

        DB::transaction(function () use ($request, $order, $audit): void {
            $oldValues = $order->only(['status', 'cancelled_at', 'version']);
            $order->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'version' => $order->version + 1,
            ]);
            $audit->record($request->user(), 'CancelOrder', $order, $oldValues, $order->only(['status', 'cancelled_at', 'version']));
        });

        return back()->with('status', 'Pedido cancelado.');
    }

    private function form(Request $request, string $resource, Model $model): View
    {
        $companyId = $request->user()->company_id;
        $customers = $this->access->scopeCustomers(Customer::query(), $request->user())
            ->with('priceTables')
            ->where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('trade_name')
            ->orderBy('corporate_name')
            ->get();

        if ($resource === 'customers' && $model->exists) {
            $model->loadMissing(['addresses', 'contacts', 'representatives', 'priceTables']);
        }

        if ($resource === 'orders' && $model->exists) {
            $model->loadMissing(['items.product']);
        }

        if ($resource === 'representatives' && $model->exists) {
            $model->loadMissing('priceTables');
        }

        if ($resource === 'regions' && $model->exists) {
            $model->loadMissing(['municipalities', 'priceTables']);
        }

        $priceTables = PriceTable::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->when(
                $request->user()->role === 'SalesRepresentative',
                fn ($query) => $query->whereHas(
                    'salesRepresentatives',
                    fn ($relation) => $relation->whereKey($this->access->representativeId($request->user())),
                ),
            )
            ->orderBy('name')
            ->get();
        $applicablePriceTables = $this->applicablePriceTables->forCustomers($customers);

        if ($request->user()->role === 'SalesRepresentative') {
            $visibleTableIds = $priceTables->pluck('id');
            $applicablePriceTables = $applicablePriceTables->map(
                fn ($tables) => $tables->whereIn('id', $visibleTableIds)->values(),
            );
        }

        return view('admin.crud.form', [
            'resource' => $resource,
            'model' => $model,
            'config' => $this->config($resource),
            'categories' => Category::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'brands' => Brand::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'units' => Unit::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'regions' => Region::query()->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'customers' => $customers,
            'applicablePriceTables' => $applicablePriceTables,
            'representatives' => SalesRepresentative::query()
                ->with('user')
                ->where('company_id', $companyId)
                ->where('active', true)
                ->when(
                    $request->user()->role === 'SalesRepresentative',
                    fn ($query) => $query->whereKey($this->access->representativeId($request->user())),
                )
                ->get()
                ->sortBy('user.name'),
            'priceTables' => $priceTables,
            'paymentMethods' => PaymentMethod::query()
                ->where('company_id', $companyId)
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'paymentTerms' => PaymentTerm::query()
                ->where('company_id', $companyId)
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'products' => Product::query()->with(['unit', 'prices'])->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'addressTypes' => CustomerAddress::query()
                ->whereHas('customer', fn ($query) => $query->where('company_id', $companyId))
                ->select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type'),
            'contactPositions' => CustomerContact::query()
                ->whereHas('customer', fn ($query) => $query->where('company_id', $companyId))
                ->whereNotNull('position')
                ->select('position')
                ->distinct()
                ->orderBy('position')
                ->pluck('position'),
            'contactDepartments' => CustomerContact::query()
                ->whereHas('customer', fn ($query) => $query->where('company_id', $companyId))
                ->whereNotNull('department')
                ->select('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
        ]);
    }

    private function validated(Request $request, string $resource, ?Model $model = null): array
    {
        $companyId = $request->user()->company_id;

        if ($resource === 'customers' && $request->has('document')) {
            $request->merge(['document' => BrazilianDocument::normalize($request->string('document')->toString())]);
        }

        if ($resource === 'payment-methods' && $request->has('code')) {
            $request->merge(['code' => str($request->string('code')->toString())->lower()->slug()->toString()]);
        }

        if ($resource === 'payment-terms') {
            $installmentDays = collect(preg_split('/[\s,;\/]+/', $request->string('installment_days_input')->toString()))
                ->filter(fn ($day): bool => $day !== '')
                ->map(fn ($day): int => (int) $day)
                ->unique()
                ->sort()
                ->values()
                ->all();
            $request->merge([
                'code' => trim($request->string('code')->toString()),
                'installment_days' => $installmentDays,
            ]);
        }

        return match ($resource) {
            'customers' => $request->validate([
                'corporate_name' => ['required', 'string', 'max:255'],
                'trade_name' => ['nullable', 'string', 'max:255'],
                'document' => ['required', 'string', 'max:20', new ValidBrazilianDocument, Rule::unique('customers')->where('company_id', $companyId)->ignore($model)],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'credit_limit' => [
                    Rule::prohibitedIf($request->user()->role === 'SalesRepresentative'),
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'version' => [$model ? 'required' : 'nullable', 'integer', 'min:1'],
                'addresses' => ['nullable', 'array'],
                'addresses.*.id' => ['nullable', 'integer'],
                'addresses.*.type' => ['required_with:addresses', 'string', 'max:50'],
                'addresses.*.zip_code' => ['required_with:addresses', 'string', 'max:10'],
                'addresses.*.street' => ['required_with:addresses', 'string', 'max:255'],
                'addresses.*.number' => ['required_with:addresses', 'string', 'max:20'],
                'addresses.*.complement' => ['nullable', 'string', 'max:255'],
                'addresses.*.district' => ['required_with:addresses', 'string', 'max:255'],
                'addresses.*.city' => ['required_with:addresses', 'string', 'max:255'],
                'addresses.*.state' => ['required_with:addresses', 'string', 'size:2'],
                'addresses.*.municipality_ibge_code' => ['nullable', 'string', 'size:7', 'regex:/^[0-9]{7}$/'],
                'addresses.*.country' => ['nullable', 'string', 'max:100'],
                'addresses.*.default_address' => ['nullable', 'boolean'],
                'contacts' => ['nullable', 'array'],
                'contacts.*.id' => ['nullable', 'integer'],
                'contacts.*.name' => ['required_with:contacts', 'string', 'max:255'],
                'contacts.*.position' => ['nullable', 'string', 'max:255'],
                'contacts.*.department' => ['nullable', 'string', 'max:255'],
                'contacts.*.email' => ['nullable', 'email', 'max:255'],
                'contacts.*.phone' => ['nullable', 'string', 'max:20'],
                'contacts.*.mobile' => ['nullable', 'string', 'max:20'],
                'contacts.*.whatsapp' => ['nullable', 'string', 'max:20'],
                'contacts.*.primary_contact' => ['nullable', 'boolean'],
                'contacts.*.active' => ['nullable', 'boolean'],
                'representative_ids' => [Rule::prohibitedIf($request->user()->role === 'SalesRepresentative'), 'nullable', 'array'],
                'representative_ids.*' => [Rule::exists('sales_representatives', 'id')->where('company_id', $companyId)],
                'primary_representative_id' => [
                    Rule::prohibitedIf($request->user()->role === 'SalesRepresentative'),
                    'nullable',
                    Rule::exists('sales_representatives', 'id')->where('company_id', $companyId),
                ],
                'price_table_ids' => [Rule::prohibitedIf($request->user()->role === 'SalesRepresentative'), 'nullable', 'array'],
                'price_table_ids.*' => [Rule::exists('price_tables', 'id')->where('company_id', $companyId)->where('active', true)],
                'active' => ['sometimes', 'boolean'],
            ]),
            'products' => $request->validate([
                'category_id' => ['required', Rule::exists('categories', 'id')->where('company_id', $companyId)],
                'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('company_id', $companyId)],
                'unit_id' => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
                'sku' => ['required', 'string', 'max:100', Rule::unique('products')->where('company_id', $companyId)->ignore($model)],
                'barcode' => ['nullable', 'string', 'max:50'],
                'image_url' => ['nullable', 'url', 'max:255'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
                'name' => ['required', 'string', 'max:255'],
                'short_description' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string'],
                'color' => ['nullable', 'string', 'max:80'],
                'weight_kg' => ['nullable', 'numeric', 'min:0'],
                'length_cm' => ['nullable', 'numeric', 'min:0'],
                'width_cm' => ['nullable', 'numeric', 'min:0'],
                'height_cm' => ['nullable', 'numeric', 'min:0'],
                'base_price' => ['nullable', 'numeric', 'min:0'],
                'minimum_quantity' => ['required', 'numeric', 'min:0.001'],
                'quantity_multiple' => ['nullable', 'numeric', 'min:0.001'],
                'allows_fractional_quantity' => ['sometimes', 'boolean'],
                'available_stock' => ['nullable', 'numeric', 'min:0'],
                'stock_status' => ['nullable', 'in:InStock,LowStock,OutOfStock'],
                'table_prices' => ['nullable', 'array'],
                'table_prices.*.price_table_id' => ['required_with:table_prices', Rule::exists('price_tables', 'id')->where('company_id', $companyId)],
                'table_prices.*.price' => ['nullable', 'numeric', 'min:0.01'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'categories' => $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('parent_id', $request->input('parent_id')))->ignore($model)],
                'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('company_id', $companyId), Rule::notIn([$model?->id])],
                'description' => ['nullable', 'string'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'brands' => $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('brands')->where('company_id', $companyId)->ignore($model)],
                'description' => ['nullable', 'string'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'units' => $request->validate([
                'code' => ['required', 'string', 'max:10', Rule::unique('units')->where('company_id', $companyId)->ignore($model)],
                'name' => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'regions' => $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('regions')->where('company_id', $companyId)->ignore($model)],
                'level' => ['required', 'integer', 'min:1', 'max:99'],
                'state' => ['required', 'string', 'size:2'],
                'coverage_type' => ['required', 'in:municipalities,state_remainder'],
                'municipalities' => ['required_if:coverage_type,municipalities', 'array', 'min:1'],
                'municipalities.*.ibge_code' => ['required_with:municipalities', 'string', 'size:7', 'regex:/^[0-9]{7}$/'],
                'municipalities.*.name' => ['required_with:municipalities', 'string', 'max:255'],
                'municipalities.*.state' => ['required_with:municipalities', 'string', 'size:2'],
                'municipalities.*.microregion_name' => ['nullable', 'string', 'max:255'],
                'municipalities.*.mesoregion_name' => ['nullable', 'string', 'max:255'],
                'price_table_ids' => ['nullable', 'array'],
                'price_table_ids.*' => [Rule::exists('price_tables', 'id')->where('company_id', $companyId)->where('active', true)],
                'description' => ['nullable', 'string'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'representatives' => $request->validate([
                'user_id' => ['required', Rule::exists('users', 'id')->where('company_id', $companyId)->where('role', 'SalesRepresentative')->where('active', true)],
                'region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
                'code' => ['required', 'string', 'max:50', Rule::unique('sales_representatives')->where('company_id', $companyId)->ignore($model)],
                'price_table_ids' => ['nullable', 'array'],
                'price_table_ids.*' => [Rule::exists('price_tables', 'id')->where('company_id', $companyId)->where('active', true)],
                'active' => ['sometimes', 'boolean'],
            ]),
            'payment-methods' => $request->validate([
                'code' => ['required', 'string', 'max:50', Rule::unique('payment_methods')->where('company_id', $companyId)->ignore($model)],
                'name' => ['required', 'string', 'max:100', Rule::unique('payment_methods')->where('company_id', $companyId)->ignore($model)],
                'description' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'payment-terms' => $request->validate([
                'code' => ['required', 'string', 'max:50', Rule::unique('payment_terms')->where('company_id', $companyId)->ignore($model)],
                'name' => ['required', 'string', 'max:100', Rule::unique('payment_terms')->where('company_id', $companyId)->ignore($model)],
                'installment_days' => ['required', 'array', 'min:1'],
                'installment_days.*' => ['integer', 'min:0', 'max:3650'],
                'minimum_order_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
                'description' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'orders' => $request->validate([
                'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)->where('active', true)],
                'sales_representative_id' => ['required', Rule::exists('sales_representatives', 'id')->where('company_id', $companyId)->where('active', true)],
                'price_table_id' => ['required', Rule::exists('price_tables', 'id')->where('company_id', $companyId)->where('active', true)],
                'version' => [$model ? 'required' : 'nullable', 'integer', 'min:1'],
                'order_date' => ['required', 'date'],
                'payment_method' => ['required', Rule::exists('payment_methods', 'code')->where('company_id', $companyId)->where('active', true)],
                'payment_terms' => ['required', Rule::exists('payment_terms', 'code')->where('company_id', $companyId)->where('active', true)],
                'notes' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('company_id', $companyId)->where('active', true)],
                'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'items.*.adjustments' => ['nullable', 'array'],
                'items.*.adjustments.*.name' => ['nullable', 'string', 'max:100'],
                'items.*.adjustments.*.type' => ['required_with:items.*.adjustments', 'in:discount,surcharge'],
                'items.*.adjustments.*.mode' => ['required_with:items.*.adjustments', 'in:percentage,fixed'],
                'items.*.adjustments.*.value' => ['required_with:items.*.adjustments', 'numeric', 'min:0'],
            ]),
            default => abort(404),
        };
    }

    private function saveModel(Request $request, string $resource, array $data, ?Model $model = null): Model
    {
        return match ($resource) {
            'customers' => $this->saveCustomer($request, $data, $model),
            'products' => $this->saveProduct($request, $data, $model),
            'regions' => $this->saveRegion($request, $data, $model),
            'representatives' => $this->saveRepresentative($request, $data, $model),
            'categories' => $this->saveCategory($request, $data, $model),
            'orders' => $this->saveOrder($request, $data, $model),
            default => $model
                ? tap($model)->update($data)
                : $this->config($resource)['model']::query()->create($data + ['company_id' => $request->user()->company_id]),
        };
    }

    private function saveProduct(Request $request, array $data, ?Model $model = null): Product
    {
        $tablePrices = collect($data['table_prices'] ?? []);
        unset($data['table_prices']);
        unset($data['image']);
        $data['allows_fractional_quantity'] = (bool) ($data['allows_fractional_quantity'] ?? false);

        if (! $data['allows_fractional_quantity']) {
            foreach (['minimum_quantity', 'quantity_multiple'] as $field) {
                $value = $data[$field] ?? null;
                if ($value !== null && abs((float) $value - round((float) $value)) > 0.000001) {
                    throw ValidationException::withMessages([
                        $field => 'Use um valor inteiro ou habilite a venda fracionada.',
                    ]);
                }
            }
        }

        if ($request->hasFile('image')) {
            if ($model instanceof Product && $model->image_url && str_starts_with($model->image_url, 'storage/products/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $model->image_url));
            }

            $data['image_url'] = 'storage/'.$request->file('image')->store('products', 'public');
        }

        $data['active'] = (bool) ($data['active'] ?? false);
        $data['stock_status'] = $data['stock_status'] ?? 'InStock';
        $data['published_at'] = $data['active']
            ? ($model instanceof Product && $model->published_at ? $model->published_at : now())
            : null;

        return DB::transaction(function () use ($request, $data, $model, $tablePrices): Product {
            /** @var Product $product */
            $product = $model instanceof Product
                ? tap($model)->update($data)
                : Product::query()->create($data + ['company_id' => $request->user()->company_id]);

            $this->syncProductPrices($product, $tablePrices);

            return $product->load('prices');
        });
    }

    private function saveRepresentative(Request $request, array $data, ?Model $model = null): SalesRepresentative
    {
        $priceTableIds = collect($data['price_table_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique();
        unset($data['price_table_ids']);

        return DB::transaction(function () use ($request, $data, $model, $priceTableIds): SalesRepresentative {
            /** @var SalesRepresentative $representative */
            $representative = $model instanceof SalesRepresentative
                ? tap($model)->update($data)
                : SalesRepresentative::query()->create($data + ['company_id' => $request->user()->company_id]);

            $representative->priceTables()->sync($priceTableIds);

            return $representative->load('priceTables');
        });
    }

    private function syncProductPrices(Product $product, $tablePrices): void
    {
        $existingTableIds = $tablePrices
            ->pluck('price_table_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($existingTableIds->isNotEmpty()) {
            ProductPrice::query()
                ->where('product_id', $product->id)
                ->whereIn('price_table_id', $existingTableIds)
                ->delete();
        }

        foreach ($tablePrices as $row) {
            if (! filled($row['price'] ?? null)) {
                continue;
            }

            ProductPrice::query()->create([
                'product_id' => $product->id,
                'price_table_id' => (int) $row['price_table_id'],
                'price' => $row['price'],
            ]);
        }

    }

    private function saveCustomer(Request $request, array $data, ?Model $model = null): Customer
    {
        return DB::transaction(function () use ($request, $data, $model): Customer {
            $addresses = collect($data['addresses'] ?? [])
                ->filter(fn (array $row): bool => filled($row['type'] ?? null) && filled($row['zip_code'] ?? null))
                ->values();
            $contacts = collect($data['contacts'] ?? [])
                ->filter(fn (array $row): bool => filled($row['name'] ?? null))
                ->values();
            $representativeIds = collect($data['representative_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique()->values();
            $priceTableIds = collect($data['price_table_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique()->values();
            if ($model instanceof Customer && (int) ($data['version'] ?? 0) !== (int) $model->version) {
                throw ValidationException::withMessages([
                    'version' => 'O cliente foi alterado por outro usuário. Atualize a página antes de tentar novamente.',
                ]);
            }
            unset($data['version']);
            $primaryId = isset($data['primary_representative_id']) && $data['primary_representative_id']
                ? (int) $data['primary_representative_id']
                : $representativeIds->first();

            if ($request->user()->role === 'SalesRepresentative') {
                if ($model instanceof Customer) {
                    $representativeIds = $model->representatives()->pluck('sales_representative_id');
                    $primaryId = $model->representatives()
                        ->where('is_primary', true)
                        ->value('sales_representative_id');
                    $priceTableIds = $model->priceTables()->pluck('price_tables.id');
                } else {
                    $representativeIds = collect([$this->access->representativeId($request->user())]);
                    $primaryId = $representativeIds->first();
                    $priceTableIds = collect();
                }
                $data['credit_limit'] = $model instanceof Customer ? $model->credit_limit : null;
            }
            if ($primaryId && ! $representativeIds->contains($primaryId)) {
                $representativeIds->prepend($primaryId);
            }
            unset($data['addresses'], $data['contacts'], $data['representative_ids'], $data['primary_representative_id'], $data['price_table_ids']);

            $data['region_id'] = $this->resolveCustomerRegionId($addresses, $request->user()->company_id);
            $data['version'] = $model instanceof Customer ? $model->version + 1 : 1;

            /** @var Customer $customer */
            $customer = $model instanceof Customer
                ? tap($model)->update($data)
                : Customer::query()->create($data + ['company_id' => $request->user()->company_id]);

            $addressIds = $addresses->pluck('id')->filter()->map(fn ($id): int => (int) $id);
            $customer->addresses()->whereNotIn('id', $addressIds)->delete();
            foreach ($addresses as $index => $address) {
                $attributes = [
                    'type' => $address['type'],
                    'zip_code' => $address['zip_code'],
                    'street' => $address['street'],
                    'number' => $address['number'],
                    'complement' => $address['complement'] ?? null,
                    'district' => $address['district'],
                    'city' => $address['city'],
                    'state' => strtoupper($address['state']),
                    'municipality_ibge_code' => $address['municipality_ibge_code'] ?? null,
                    'country' => $address['country'] ?? 'Brasil',
                    'default_address' => $index === (int) ($addresses->search(fn ($row) => (bool) ($row['default_address'] ?? false)) ?: 0),
                ];
                $addressId = isset($address['id']) ? (int) $address['id'] : null;
                $existingAddress = $addressId ? $customer->addresses()->whereKey($addressId)->first() : null;
                $existingAddress ? $existingAddress->update($attributes) : $customer->addresses()->create($attributes);
            }

            $contactIds = $contacts->pluck('id')->filter()->map(fn ($id): int => (int) $id);
            $customer->contacts()->whereNotIn('id', $contactIds)->delete();
            foreach ($contacts as $index => $contact) {
                $attributes = [
                    'name' => $contact['name'],
                    'position' => $contact['position'] ?? null,
                    'department' => $contact['department'] ?? null,
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'mobile' => $contact['mobile'] ?? null,
                    'whatsapp' => $contact['whatsapp'] ?? null,
                    'primary_contact' => $index === (int) ($contacts->search(fn ($row) => (bool) ($row['primary_contact'] ?? false)) ?: 0),
                    'active' => (bool) ($contact['active'] ?? true),
                ];
                $contactId = isset($contact['id']) ? (int) $contact['id'] : null;
                $existingContact = $contactId ? $customer->contacts()->whereKey($contactId)->first() : null;
                $existingContact ? $existingContact->update($attributes) : $customer->contacts()->create($attributes);
            }

            $customer->representatives()->whereNotIn('sales_representative_id', $representativeIds)->delete();
            foreach ($representativeIds as $representativeId) {
                $customer->representatives()->updateOrCreate(
                    ['sales_representative_id' => $representativeId],
                    ['is_primary' => $representativeId === $primaryId],
                );
            }

            $customer->priceTables()->sync($priceTableIds);

            return $customer->load(['addresses', 'contacts', 'representatives', 'priceTables']);
        });
    }

    private function saveCategory(Request $request, array $data, ?Model $model = null): Category
    {
        if ($model instanceof Category && filled($data['parent_id'] ?? null)) {
            $descendantIds = $model->children()
                ->with('children')
                ->get()
                ->flatMap(function (Category $category): array {
                    $ids = [$category->id];
                    $queue = $category->children->all();

                    while ($child = array_shift($queue)) {
                        $ids[] = $child->id;
                        array_push($queue, ...$child->children->all());
                    }

                    return $ids;
                });

            if ($descendantIds->contains((int) $data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Uma categoria não pode ser vinculada a uma de suas descendentes.',
                ]);
            }
        }

        return $model instanceof Category
            ? tap($model)->update($data)
            : Category::query()->create($data + ['company_id' => $request->user()->company_id]);
    }

    private function resolveCustomerRegionId($addresses, int $companyId): ?int
    {
        $defaultAddress = $addresses->firstWhere('default_address', '1')
            ?? $addresses->firstWhere('default_address', true)
            ?? $addresses->first();

        if (! $defaultAddress || ! filled($defaultAddress['state'] ?? null)) {
            return null;
        }

        return app(CommercialRegionResolver::class)->resolve(
            $companyId,
            $defaultAddress['state'],
            $defaultAddress['city'] ?? null,
            $defaultAddress['municipality_ibge_code'] ?? null,
        )?->id;
    }

    private function saveRegion(Request $request, array $data, ?Model $model = null): Region
    {
        return DB::transaction(function () use ($request, $data, $model): Region {
            $municipalities = collect($data['municipalities'] ?? [])->unique('ibge_code')->values();
            $priceTableIds = collect($data['price_table_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique()->values();
            unset($data['municipalities'], $data['price_table_ids']);

            if ($data['coverage_type'] === 'state_remainder') {
                $municipalities = collect();
            }

            $data['state'] = strtoupper($data['state']);
            $data['city'] = null;

            if ($municipalities->contains(fn (array $municipality): bool => strtoupper($municipality['state']) !== $data['state'])) {
                throw ValidationException::withMessages([
                    'municipalities' => 'Todos os municípios devem pertencer à UF selecionada.',
                ]);
            }

            if ($data['coverage_type'] === 'state_remainder' && Region::query()
                ->where('company_id', $request->user()->company_id)
                ->where('state', $data['state'])
                ->where('coverage_type', 'state_remainder')
                ->when($model instanceof Region, fn ($query) => $query->whereKeyNot($model->id))
                ->exists()) {
                throw ValidationException::withMessages([
                    'coverage_type' => 'Já existe uma região configurada para os demais municípios desta UF.',
                ]);
            }

            /** @var Region $region */
            $region = $model instanceof Region
                ? tap($model)->update($data)
                : Region::query()->create($data + ['company_id' => $request->user()->company_id]);

            $duplicateCode = $municipalities->pluck('ibge_code')->first(fn ($code) => Region::query()
                ->where('company_id', $request->user()->company_id)
                ->whereKeyNot($region->id)
                ->whereHas('municipalities', fn ($query) => $query->where('ibge_code', $code))
                ->exists());

            if ($duplicateCode) {
                throw ValidationException::withMessages([
                    'municipalities' => "O município IBGE {$duplicateCode} já pertence a outra região comercial.",
                ]);
            }

            $region->municipalities()->delete();
            $region->municipalities()->createMany($municipalities->all());
            PriceTable::query()
                ->where('company_id', $request->user()->company_id)
                ->where('region_id', $region->id)
                ->when($priceTableIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $priceTableIds))
                ->update(['region_id' => null]);
            PriceTable::query()
                ->where('company_id', $request->user()->company_id)
                ->whereIn('id', $priceTableIds)
                ->update(['region_id' => $region->id]);
            ReclassifyCompanyCustomers::dispatch(
                $request->user()->company_id,
                $request->user()->id,
            )->afterCommit();

            return $region->load(['municipalities', 'priceTables']);
        });
    }

    private function saveOrder(Request $request, array $data, ?Model $model = null): Order
    {
        if ($model instanceof Order) {
            abort_unless($model->status === 'Draft', 422, 'Somente pedidos em rascunho podem ser alterados.');

            if ((int) ($data['version'] ?? 0) !== (int) $model->version) {
                throw ValidationException::withMessages([
                    'version' => 'O pedido foi alterado por outro usuário. Atualize a página antes de tentar novamente.',
                ]);
            }
        }

        unset($data['version']);

        if ($request->user()->role === 'SalesRepresentative') {
            $data['sales_representative_id'] = $this->access->representativeId($request->user());
        }

        $customer = Customer::query()
            ->with(['addresses', 'priceTables'])
            ->where('company_id', $request->user()->company_id)
            ->where('active', true)
            ->findOrFail($data['customer_id']);

        if ($request->user()->role === 'SalesRepresentative') {
            $this->access->authorize($request->user(), 'customers', 'view', $customer);
        }

        if (! $customer->applicablePriceTables()->pluck('id')->contains((int) $data['price_table_id'])) {
            throw ValidationException::withMessages([
                'price_table_id' => 'A tabela de preço não está habilitada para o endereço padrão ou para o cliente selecionado.',
            ]);
        }

        if ($request->user()->role === 'SalesRepresentative') {
            $representativeHasPriceTable = $request->user()->salesRepresentative
                ->priceTables()
                ->whereKey((int) $data['price_table_id'])
                ->exists();

            if (! $representativeHasPriceTable) {
                throw ValidationException::withMessages([
                    'price_table_id' => 'A tabela de preço não está habilitada para este representante.',
                ]);
            }
        }

        return DB::transaction(function () use ($request, $data, $model): Order {
            $products = Product::query()
                ->where('company_id', $request->user()->company_id)
                ->whereIn('id', collect($data['items'])->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $items = collect($data['items'])->map(function (array $row) use ($data, $products): array {
                $product = $products->get((int) $row['product_id']);
                $this->validateOrderProductQuantity($product, (float) $row['quantity']);
                $adjustments = $this->normalizeOrderAdjustments($row);
                $unitPrice = $this->resolveOrderProductPrice((int) $row['product_id'], (int) $data['price_table_id']);
                $subtotal = round((float) $row['quantity'] * $unitPrice, 2);
                $total = $this->applyOrderAdjustments($subtotal, $adjustments);

                return [
                    'product_id' => (int) $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $unitPrice,
                    'discounts' => $adjustments->isNotEmpty() ? $adjustments->values()->all() : null,
                    'total_amount' => $total,
                ];
            });
            unset($data['items']);

            $data['company_id'] = $request->user()->company_id;
            $data['user_id'] = $request->user()->id;
            $data['source'] = $request->bearerToken() ? 'App' : 'Web';
            $data['order_number'] = $model instanceof Order
                ? $model->order_number
                : $this->nextOrderNumber($request->user()->company_id);
            $data['status'] = 'Draft';
            $data['subtotal'] = $items->sum(fn (array $item): float => round((float) $item['quantity'] * (float) $item['unit_price'], 2));
            $data['total_amount'] = $items->sum('total_amount');

            $paymentTerm = PaymentTerm::query()
                ->where('company_id', $request->user()->company_id)
                ->where('code', $data['payment_terms'])
                ->where('active', true)
                ->firstOrFail();

            if ((float) $data['total_amount'] < (float) $paymentTerm->minimum_order_amount) {
                throw ValidationException::withMessages([
                    'payment_terms' => sprintf(
                        'O prazo %s exige pedido mínimo de R$ %s.',
                        $paymentTerm->name,
                        number_format((float) $paymentTerm->minimum_order_amount, 2, ',', '.'),
                    ),
                ]);
            }

            $data['sent_at'] = null;
            $data['cancelled_at'] = null;
            $data['version'] = $model instanceof Order ? $model->version + 1 : 1;

            /** @var Order $order */
            $order = $model instanceof Order ? tap($model)->update($data) : Order::query()->create($data);
            $order->items()->delete();
            foreach ($items as $item) {
                OrderItem::query()->create($item + ['order_id' => $order->id]);
            }

            return $order->load('items');
        });
    }

    private function nextOrderNumber(int $companyId): string
    {
        $prefix = 'PED-'.now()->format('Ym').'-';
        $lastNumber = Order::query()
            ->where('company_id', $companyId)
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');
        $sequence = $lastNumber ? ((int) str($lastNumber)->afterLast('-')->toString()) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function resolveOrderProductPrice(int $productId, int $priceTableId): float
    {
        $price = ProductPrice::query()
            ->where('product_id', $productId)
            ->where('price_table_id', $priceTableId)
            ->value('price');

        if ($price !== null) {
            return (float) $price;
        }

        return (float) Product::query()->whereKey($productId)->value('base_price');
    }

    private function validateOrderProductQuantity(?Product $product, float $quantity): void
    {
        if (! $product) {
            throw ValidationException::withMessages(['items' => 'Produto inválido no pedido.']);
        }

        $minimum = (float) $product->minimum_quantity;
        if ($quantity + 0.000001 < $minimum) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'O produto %s exige quantidade mínima de %s.',
                    $product->name,
                    number_format($minimum, 3, ',', '.'),
                ),
            ]);
        }

        if (! $product->allows_fractional_quantity && abs($quantity - round($quantity)) > 0.000001) {
            throw ValidationException::withMessages([
                'items' => "O produto {$product->name} aceita somente quantidades inteiras.",
            ]);
        }

        $multiple = (float) ($product->quantity_multiple ?? 0);
        if ($multiple > 0 && abs(($quantity / $multiple) - round($quantity / $multiple)) > 0.000001) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'O produto %s deve ser vendido em múltiplos de %s.',
                    $product->name,
                    number_format($multiple, 3, ',', '.'),
                ),
            ]);
        }
    }

    private function normalizeOrderAdjustments(array $row)
    {
        $adjustments = collect($row['adjustments'] ?? [])
            ->filter(fn (array $adjustment): bool => filled($adjustment['value'] ?? null) && (float) $adjustment['value'] > 0)
            ->map(fn (array $adjustment): array => [
                'name' => $adjustment['name'] ?: ($adjustment['type'] === 'surcharge' ? 'Acréscimo comercial' : 'Desconto comercial'),
                'type' => $adjustment['type'],
                'mode' => $adjustment['mode'],
                'value' => (float) $adjustment['value'],
            ]);

        if ($adjustments->isEmpty() && filled($row['discount'] ?? null) && (float) $row['discount'] > 0) {
            $adjustments->push([
                'name' => 'Desconto comercial',
                'type' => 'discount',
                'mode' => 'percentage',
                'value' => (float) $row['discount'],
            ]);
        }

        return $adjustments;
    }

    private function applyOrderAdjustments(float $subtotal, $adjustments): float
    {
        $total = $subtotal;

        foreach ($adjustments as $adjustment) {
            $value = (float) $adjustment['value'];
            $amount = $adjustment['mode'] === 'percentage' ? $total * ($value / 100) : $value;
            $total = $adjustment['type'] === 'surcharge' ? $total + $amount : $total - $amount;
            $total = max(0, $total);
        }

        return round($total, 2);
    }

    private function findModel(Request $request, string $resource, int $id): Model
    {
        return $this->config($resource)['model']::query()
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($id);
    }

    private function newModel(string $resource): Model
    {
        $class = $this->config($resource)['model'];

        return new $class;
    }

    private function config(string $resource): array
    {
        return match ($resource) {
            'customers' => ['model' => Customer::class, 'label' => 'Cliente', 'index' => 'customers.index'],
            'products' => ['model' => Product::class, 'label' => 'Produto', 'index' => 'products.index'],
            'categories' => ['model' => Category::class, 'label' => 'Categoria', 'index' => 'categories.index'],
            'brands' => ['model' => Brand::class, 'label' => 'Marca', 'index' => 'brands.index'],
            'units' => ['model' => Unit::class, 'label' => 'Unidade', 'index' => 'units.index'],
            'regions' => ['model' => Region::class, 'label' => 'Região', 'index' => 'regions.index'],
            'representatives' => ['model' => SalesRepresentative::class, 'label' => 'Representante', 'index' => 'representatives.index'],
            'payment-methods' => ['model' => PaymentMethod::class, 'label' => 'Forma de pagamento', 'index' => 'payment-methods.index'],
            'payment-terms' => ['model' => PaymentTerm::class, 'label' => 'Prazo de pagamento', 'index' => 'payment-terms.index'],
            'orders' => ['model' => Order::class, 'label' => 'Pedido', 'index' => 'orders.index'],
            default => abort(404),
        };
    }
}
