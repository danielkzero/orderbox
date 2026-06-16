<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogCrudController extends Controller
{
    public function create(Request $request, string $resource): View
    {
        return $this->form($request, $resource, $this->newModel($resource));
    }

    public function store(Request $request, string $resource, AuditService $audit): RedirectResponse
    {
        $config = $this->config($resource);
        $data = $this->validated($request, $resource);
        $model = $this->saveModel($request, $resource, $data);
        $audit->record($request->user(), 'Create', $model, null, $model->toArray());

        return redirect()->route($config['index'])->with('status', $config['label'].' criado.');
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        return $this->form($request, $resource, $this->findModel($request, $resource, $id));
    }

    public function update(Request $request, string $resource, int $id, AuditService $audit): RedirectResponse
    {
        $model = $this->findModel($request, $resource, $id);
        $oldValues = $model->toArray();
        $model = $this->saveModel($request, $resource, $this->validated($request, $resource, $model), $model);
        $audit->record($request->user(), 'Update', $model, $oldValues, $model->fresh()->toArray());

        return redirect()->route($this->config($resource)['index'])->with('status', $this->config($resource)['label'].' atualizado.');
    }

    public function deactivate(Request $request, string $resource, int $id, AuditService $audit): RedirectResponse
    {
        $model = $this->findModel($request, $resource, $id);
        $oldValues = $model->toArray();

        if ($resource === 'orders' && $model->status === 'Draft') {
            $model->items()->delete();
            $model->delete();
            $newValues = ['deleted' => true];
        } elseif ($resource === 'orders' && $model->status === 'Sent') {
            $model->update(['status' => 'Cancelled', 'cancelled_at' => now()]);
            $newValues = ['status' => 'Cancelled', 'cancelled_at' => $model->cancelled_at];
        } elseif ($resource === 'orders') {
            return back()->withErrors(['order' => 'Somente pedidos Draft podem ser removidos e somente pedidos Sent podem ser cancelados.']);
        } else {
            $model->update(['active' => false]);
            $newValues = ['active' => false];
        }

        $audit->record($request->user(), 'Deactivate', $model, $oldValues, $newValues);

        return back()->with('status', $resource === 'orders' ? 'Pedido atualizado.' : $this->config($resource)['label'].' inativado.');
    }

    public function storeProductPriceTable(Request $request, AuditService $audit): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('price_tables', 'name')->where('company_id', $companyId)],
            'region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
        ]);

        $priceTable = PriceTable::query()->create($data + [
            'company_id' => $companyId,
            'description' => 'Criada pelo datatable de produtos.',
            'active' => true,
        ]);

        $audit->record($request->user(), 'Create', $priceTable, null, $priceTable->toArray());

        return back()->with('status', 'Tabela de preço criada.');
    }

    public function updateProductPriceTable(Request $request, PriceTable $priceTable, AuditService $audit): RedirectResponse
    {
        abort_unless($priceTable->company_id === $request->user()->company_id, 404);

        $oldValues = $priceTable->toArray();
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('price_tables', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($priceTable),
            ],
        ]);

        $priceTable->update($data);
        $audit->record($request->user(), 'Update', $priceTable, $oldValues, $priceTable->fresh()->toArray());

        return back()->with('status', 'Tabela de preço atualizada.');
    }

    private function form(Request $request, string $resource, Model $model): View
    {
        $companyId = $request->user()->company_id;

        return view('admin.crud.form', [
            'resource' => $resource,
            'model' => $model,
            'config' => $this->config($resource),
            'categories' => Category::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'brands' => Brand::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'units' => Unit::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'regions' => Region::query()->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->where('company_id', $companyId)->where('active', true)->orderBy('trade_name')->orderBy('corporate_name')->get(),
            'representatives' => SalesRepresentative::query()->with('user')->where('company_id', $companyId)->where('active', true)->get()->sortBy('user.name'),
            'priceTables' => PriceTable::query()->with('region')->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'products' => Product::query()->with(['unit', 'prices'])->where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
        ]);
    }

    private function validated(Request $request, string $resource, ?Model $model = null): array
    {
        $companyId = $request->user()->company_id;

        return match ($resource) {
            'customers' => $request->validate([
                'corporate_name' => ['required', 'string', 'max:255'],
                'region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
                'trade_name' => ['nullable', 'string', 'max:255'],
                'document' => ['required', 'string', 'max:20', Rule::unique('customers')->where('company_id', $companyId)->ignore($model)],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'credit_limit' => ['nullable', 'numeric', 'min:0'],
                'representative_ids' => ['nullable', 'array'],
                'representative_ids.*' => [Rule::exists('sales_representatives', 'id')->where('company_id', $companyId)],
                'primary_representative_id' => ['nullable', Rule::exists('sales_representatives', 'id')->where('company_id', $companyId)],
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
                'available_stock' => ['nullable', 'numeric', 'min:0'],
                'stock_status' => ['nullable', 'in:InStock,LowStock,OutOfStock'],
                'table_prices' => ['nullable', 'array'],
                'table_prices.*.price_table_id' => ['required_with:table_prices', Rule::exists('price_tables', 'id')->where('company_id', $companyId)],
                'table_prices.*.minimum_quantity' => ['nullable', 'numeric', 'min:0.001'],
                'table_prices.*.price' => ['nullable', 'numeric', 'min:0'],
                'new_price_tables' => ['nullable', 'array'],
                'new_price_tables.*.name' => ['nullable', 'string', 'max:255', Rule::unique('price_tables', 'name')->where('company_id', $companyId)],
                'new_price_tables.*.region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
                'new_price_tables.*.minimum_quantity' => ['nullable', 'numeric', 'min:0.001'],
                'new_price_tables.*.price' => ['nullable', 'numeric', 'min:0'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'price-tables' => $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('price_tables')->where('company_id', $companyId)->ignore($model)],
                'region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
                'description' => ['nullable', 'string'],
                'product_prices' => ['nullable', 'array'],
                'product_prices.*.product_id' => ['required_with:product_prices', Rule::exists('products', 'id')->where('company_id', $companyId)],
                'product_prices.*.minimum_quantity' => ['nullable', 'numeric', 'min:0.001'],
                'product_prices.*.price' => ['required_with:product_prices', 'numeric', 'min:0'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'categories' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('company_id', $companyId)],
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
                'state' => ['nullable', 'string', 'size:2'],
                'city' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'representatives' => $request->validate([
                'user_id' => ['required', Rule::exists('users', 'id')->where('company_id', $companyId)],
                'region_id' => ['nullable', Rule::exists('regions', 'id')->where('company_id', $companyId)],
                'code' => ['required', 'string', 'max:50', Rule::unique('sales_representatives')->where('company_id', $companyId)->ignore($model)],
                'active' => ['sometimes', 'boolean'],
            ]),
            'orders' => $request->validate([
                'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)],
                'sales_representative_id' => ['required', Rule::exists('sales_representatives', 'id')->where('company_id', $companyId)],
                'price_table_id' => ['required', Rule::exists('price_tables', 'id')->where('company_id', $companyId)],
                'order_number' => ['required', 'string', 'max:50', Rule::unique('orders')->where('company_id', $companyId)->ignore($model)],
                'status' => ['required', 'in:Draft,Sent,Approved,Cancelled'],
                'order_date' => ['required', 'date'],
                'source' => ['required', 'in:Admin,Mobile'],
                'notes' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('company_id', $companyId)->where('active', true)],
                'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]),
            default => abort(404),
        };
    }

    private function saveModel(Request $request, string $resource, array $data, ?Model $model = null): Model
    {
        return match ($resource) {
            'customers' => $this->saveCustomer($request, $data, $model),
            'products' => $this->saveProduct($request, $data, $model),
            'price-tables' => $this->savePriceTable($request, $data, $model),
            'orders' => $this->saveOrder($request, $data, $model),
            default => $model
                ? tap($model)->update($data)
                : $this->config($resource)['model']::query()->create($data + ['company_id' => $request->user()->company_id]),
        };
    }

    private function saveProduct(Request $request, array $data, ?Model $model = null): Product
    {
        $tablePrices = collect($data['table_prices'] ?? []);
        $newPriceTables = collect($data['new_price_tables'] ?? []);
        unset($data['table_prices'], $data['new_price_tables']);
        unset($data['image']);

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

        return DB::transaction(function () use ($request, $data, $model, $tablePrices, $newPriceTables): Product {
            /** @var Product $product */
            $product = $model instanceof Product
                ? tap($model)->update($data)
                : Product::query()->create($data + ['company_id' => $request->user()->company_id]);

            $this->syncProductPrices($product, $tablePrices, $newPriceTables, $request->user()->company_id);

            return $product->load('prices');
        });
    }

    private function syncProductPrices(Product $product, $tablePrices, $newPriceTables, int $companyId): void
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
                'minimum_quantity' => $row['minimum_quantity'] ?: 1,
                'price' => $row['price'],
            ]);
        }

        foreach ($newPriceTables as $row) {
            if (! filled($row['name'] ?? null) || ! filled($row['price'] ?? null)) {
                continue;
            }

            $priceTable = PriceTable::query()->create([
                'company_id' => $companyId,
                'region_id' => $row['region_id'] ?? null,
                'name' => $row['name'],
                'description' => 'Criada no cadastro do produto.',
                'active' => true,
            ]);

            ProductPrice::query()->create([
                'product_id' => $product->id,
                'price_table_id' => $priceTable->id,
                'minimum_quantity' => $row['minimum_quantity'] ?: 1,
                'price' => $row['price'],
            ]);
        }
    }

    private function saveCustomer(Request $request, array $data, ?Model $model = null): Customer
    {
        return DB::transaction(function () use ($request, $data, $model): Customer {
            $representativeIds = collect($data['representative_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique()->values();
            $primaryId = isset($data['primary_representative_id']) && $data['primary_representative_id']
                ? (int) $data['primary_representative_id']
                : $representativeIds->first();
            if ($primaryId && ! $representativeIds->contains($primaryId)) {
                $representativeIds->prepend($primaryId);
            }
            unset($data['representative_ids'], $data['primary_representative_id']);

            /** @var Customer $customer */
            $customer = $model instanceof Customer
                ? tap($model)->update($data)
                : Customer::query()->create($data + ['company_id' => $request->user()->company_id]);

            $customer->representatives()->delete();
            foreach ($representativeIds as $representativeId) {
                $customer->representatives()->create([
                    'sales_representative_id' => $representativeId,
                    'is_primary' => $representativeId === $primaryId,
                ]);
            }

            return $customer->load('representatives');
        });
    }

    private function savePriceTable(Request $request, array $data, ?Model $model = null): PriceTable
    {
        return DB::transaction(function () use ($request, $data, $model): PriceTable {
            $prices = collect($data['product_prices'] ?? [])
                ->filter(fn (array $row): bool => filled($row['product_id'] ?? null) && filled($row['price'] ?? null))
                ->values();
            unset($data['product_prices']);

            /** @var PriceTable $priceTable */
            $priceTable = $model instanceof PriceTable
                ? tap($model)->update($data)
                : PriceTable::query()->create($data + ['company_id' => $request->user()->company_id]);

            $priceTable->prices()->delete();
            foreach ($prices as $row) {
                ProductPrice::query()->create([
                    'price_table_id' => $priceTable->id,
                    'product_id' => (int) $row['product_id'],
                    'minimum_quantity' => $row['minimum_quantity'] ?: 1,
                    'price' => $row['price'],
                ]);
            }

            return $priceTable->load('prices');
        });
    }

    private function saveOrder(Request $request, array $data, ?Model $model = null): Order
    {
        return DB::transaction(function () use ($request, $data, $model): Order {
            abort_if($model instanceof Order && $model->status !== 'Draft', 422, 'Somente pedidos Draft podem ser alterados.');

            $items = collect($data['items'])->map(function (array $row): array {
                $discount = (float) ($row['discount'] ?? 0);
                $subtotal = round((float) $row['quantity'] * (float) $row['unit_price'], 2);
                $total = round(max(0, $subtotal - ($subtotal * ($discount / 100))), 2);

                return [
                    'product_id' => (int) $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discounts' => $discount > 0 ? [['name' => 'Desconto Comercial', 'type' => 'percentage', 'value' => $discount]] : null,
                    'total_amount' => $total,
                ];
            });
            unset($data['items']);

            $data['company_id'] = $request->user()->company_id;
            $data['user_id'] = $request->user()->id;
            $data['subtotal'] = $items->sum(fn (array $item): float => round((float) $item['quantity'] * (float) $item['unit_price'], 2));
            $data['total_amount'] = $items->sum('total_amount');
            $data['sent_at'] = $data['status'] === 'Sent' ? now() : null;
            $data['cancelled_at'] = $data['status'] === 'Cancelled' ? now() : null;
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
            'price-tables' => ['model' => PriceTable::class, 'label' => 'Tabela de preço', 'index' => 'price-tables.index'],
            'categories' => ['model' => Category::class, 'label' => 'Categoria', 'index' => 'categories.index'],
            'brands' => ['model' => Brand::class, 'label' => 'Marca', 'index' => 'brands.index'],
            'units' => ['model' => Unit::class, 'label' => 'Unidade', 'index' => 'units.index'],
            'regions' => ['model' => Region::class, 'label' => 'Região', 'index' => 'regions.index'],
            'representatives' => ['model' => SalesRepresentative::class, 'label' => 'Representante', 'index' => 'representatives.index'],
            'orders' => ['model' => Order::class, 'label' => 'Pedido', 'index' => 'orders.index'],
            default => abort(404),
        };
    }
}
