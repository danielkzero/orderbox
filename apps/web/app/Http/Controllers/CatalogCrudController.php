<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $data['company_id'] = $request->user()->company_id;
        $model = $config['model']::query()->create($data);
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
        $model->update($this->validated($request, $resource, $model));
        $audit->record($request->user(), 'Update', $model, $oldValues, $model->fresh()->toArray());

        return redirect()->route($this->config($resource)['index'])->with('status', $this->config($resource)['label'].' atualizado.');
    }

    public function deactivate(Request $request, string $resource, int $id, AuditService $audit): RedirectResponse
    {
        $model = $this->findModel($request, $resource, $id);
        $model->update(['active' => false]);
        $audit->record($request->user(), 'Deactivate', $model, ['active' => true], ['active' => false]);

        return back()->with('status', $this->config($resource)['label'].' inativado.');
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
        ]);
    }

    private function validated(Request $request, string $resource, ?Model $model = null): array
    {
        $companyId = $request->user()->company_id;

        return match ($resource) {
            'customers' => $request->validate([
                'corporate_name' => ['required', 'string', 'max:255'],
                'trade_name' => ['nullable', 'string', 'max:255'],
                'document' => ['required', 'string', 'max:20', Rule::unique('customers')->where('company_id', $companyId)->ignore($model)],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'credit_limit' => ['nullable', 'numeric', 'min:0'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'products' => $request->validate([
                'category_id' => ['required', Rule::exists('categories', 'id')->where('company_id', $companyId)],
                'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('company_id', $companyId)],
                'unit_id' => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
                'sku' => ['required', 'string', 'max:100', Rule::unique('products')->where('company_id', $companyId)->ignore($model)],
                'name' => ['required', 'string', 'max:255'],
                'short_description' => ['nullable', 'string', 'max:500'],
                'available_stock' => ['nullable', 'numeric', 'min:0'],
                'active' => ['sometimes', 'boolean'],
            ]),
            'price-tables' => $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('price_tables')->where('company_id', $companyId)->ignore($model)],
                'description' => ['nullable', 'string'],
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
            default => abort(404),
        };
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
            default => abort(404),
        };
    }
}
