<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    public function customers(Request $request): View
    {
        return $this->module($request, Customer::query()->with('addresses'), 'Clientes', 'customers', [
            'Nome' => fn (Customer $item) => $item->trade_name ?: $item->corporate_name,
            'Documento' => 'document',
            'Cidade' => fn (Customer $item) => $item->addresses->first()?->city ?? '-',
            'Limite' => fn (Customer $item) => 'R$ '.number_format((float) $item->credit_limit, 2, ',', '.'),
            'Status' => fn (Customer $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Customer $item) => view('admin.modules.actions', ['resource' => 'customers', 'item' => $item]),
        ]);
    }

    public function products(Request $request): View
    {
        return $this->module($request, Product::query()->with(['category', 'brand', 'unit', 'prices']), 'Produtos', 'products', [
            'SKU' => 'sku',
            'Produto' => 'name',
            'Categoria' => fn (Product $item) => $item->category->name,
            'Marca' => fn (Product $item) => $item->brand?->name ?? '-',
            'Estoque' => fn (Product $item) => $item->available_stock.' '.$item->unit->code,
            'Status' => fn (Product $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Product $item) => view('admin.modules.actions', ['resource' => 'products', 'item' => $item]),
        ]);
    }

    public function priceTables(Request $request): View
    {
        return $this->module($request, PriceTable::query()->withCount('prices'), 'Tabelas de preço', 'price_tables', [
            'Nome' => 'name',
            'Descrição' => 'description',
            'Faixas de preço' => 'prices_count',
            'Status' => fn (PriceTable $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (PriceTable $item) => view('admin.modules.actions', ['resource' => 'price-tables', 'item' => $item]),
        ]);
    }

    public function representatives(Request $request): View
    {
        return $this->module($request, SalesRepresentative::query()->with('user')->withCount('customers'), 'Representantes', 'sales_representatives', [
            'Código' => 'code',
            'Nome' => fn (SalesRepresentative $item) => $item->user->name,
            'E-mail' => fn (SalesRepresentative $item) => $item->user->email,
            'Clientes' => 'customers_count',
            'Status' => fn (SalesRepresentative $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (SalesRepresentative $item) => view('admin.modules.actions', ['resource' => 'representatives', 'item' => $item]),
        ]);
    }

    public function orders(Request $request): View
    {
        return $this->module($request, Order::query()->with(['customer', 'salesRepresentative.user'])->latest('order_date'), 'Pedidos', 'orders', [
            'Número' => 'order_number',
            'Cliente' => fn (Order $item) => $item->customer->trade_name ?: $item->customer->corporate_name,
            'Representante' => fn (Order $item) => $item->salesRepresentative->user->name,
            'Origem' => 'source',
            'Status' => fn (Order $item) => view('components.status-badge', ['active' => $item->status !== 'Cancelled', 'label' => $item->status]),
            'Total' => fn (Order $item) => 'R$ '.number_format((float) $item->total_amount, 2, ',', '.'),
            'Ações' => fn (Order $item) => view('admin.modules.actions', ['resource' => 'orders', 'item' => $item]),
        ]);
    }

    public function categories(Request $request): View
    {
        return $this->module($request, Category::query()->with('parent')->withCount('products'), 'Categorias', 'categories', [
            'Nome' => 'name',
            'Categoria pai' => fn (Category $item) => $item->parent?->name ?? '-',
            'Produtos' => 'products_count',
            'Status' => fn (Category $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Category $item) => view('admin.modules.actions', ['resource' => 'categories', 'item' => $item]),
        ]);
    }

    public function brands(Request $request): View
    {
        return $this->module($request, Brand::query()->withCount('products'), 'Marcas', 'brands', [
            'Nome' => 'name',
            'Descrição' => 'description',
            'Produtos' => 'products_count',
            'Status' => fn (Brand $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Brand $item) => view('admin.modules.actions', ['resource' => 'brands', 'item' => $item]),
        ]);
    }

    public function units(Request $request): View
    {
        return $this->module($request, Unit::query()->withCount('products'), 'Unidades', 'units', [
            'Código' => 'code',
            'Nome' => 'name',
            'Produtos' => 'products_count',
            'Status' => fn (Unit $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Unit $item) => view('admin.modules.actions', ['resource' => 'units', 'item' => $item]),
        ]);
    }

    public function auditLogs(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['Admin', 'Manager'], true), 403);

        return $this->module($request, AuditLog::query()->with('user')->latest('created_at'), 'Auditoria', 'audit_logs', [
            'Data' => fn (AuditLog $item) => $item->created_at->format('d/m/Y H:i:s'),
            'Usuário' => fn (AuditLog $item) => $item->user->name,
            'Ação' => 'action',
            'Entidade' => 'entity_type',
            'Registro' => 'entity_id',
            'IP' => 'ip_address',
        ]);
    }

    private function module(Request $request, Builder $query, string $title, string $table, array $columns): View
    {
        $companyId = $request->user()->company_id;
        $search = trim($request->string('search')->toString());

        $query->where($table.'.company_id', $companyId);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search, $table): void {
                foreach (['name', 'trade_name', 'corporate_name', 'document', 'sku', 'code', 'order_number'] as $column) {
                    if (\Schema::hasColumn($table, $column)) {
                        $query->orWhere($table.'.'.$column, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        return view('admin.modules.index', [
            'title' => $title,
            'description' => 'Dados reais da empresa autenticada.',
            'items' => $query->paginate(15)->withQueryString(),
            'columns' => $columns,
            'resource' => [
                'customers' => 'customers',
                'products' => 'products',
                'price_tables' => 'price-tables',
                'sales_representatives' => 'representatives',
                'orders' => 'orders',
                'categories' => 'categories',
                'brands' => 'brands',
                'units' => 'units',
            ][$table] ?? null,
            'search' => $search,
        ]);
    }
}
