<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Services\OperationalAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    public function __construct(private readonly OperationalAccess $access) {}

    public function customers(Request $request): View
    {
        $this->access->authorize($request->user(), 'customers', 'view');

        return $this->module($request, $this->access->scopeCustomers(Customer::query(), $request->user())->with(['addresses', 'region']), 'Clientes', 'customers', [
            'Nome' => fn (Customer $item) => $item->trade_name ?: $item->corporate_name,
            'Documento' => 'document',
            'Cidade' => fn (Customer $item) => $item->addresses->first()?->city ?? '-',
            'Região' => fn (Customer $item) => $item->region?->name ?? '-',
            'Limite' => fn (Customer $item) => 'R$ '.number_format((float) $item->credit_limit, 2, ',', '.'),
            'Status' => fn (Customer $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Customer $item) => view('admin.modules.actions', ['resource' => 'customers', 'item' => $item]),
        ]);
    }

    public function products(Request $request): View
    {
        $this->access->authorize($request->user(), 'products', 'view');
        $companyId = $request->user()->company_id;
        $search = trim($request->string('search')->toString());
        $categoryId = $request->integer('category_id') ?: null;
        $brandId = $request->integer('brand_id') ?: null;
        $stockStatus = $request->string('stock_status')->toString();

        $query = Product::query()
            ->with(['category', 'brand', 'unit', 'prices'])
            ->where('company_id', $companyId)
            ->latest();

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%');
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if (in_array($stockStatus, ['InStock', 'LowStock', 'OutOfStock'], true)) {
            $query->where('stock_status', $stockStatus);
        }

        return view('admin.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => Category::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'brands' => Brand::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'priceTables' => PriceTable::query()
                ->where('company_id', $companyId)
                ->where('active', true)
                ->orderBy('id')
                ->get(),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'stock_status' => $stockStatus,
            ],
        ]);
    }

    public function representatives(Request $request): View
    {
        $this->access->authorize($request->user(), 'representatives', 'view');

        return $this->module($request, SalesRepresentative::query()->with(['user', 'region'])->withCount('customers'), 'Representantes', 'sales_representatives', [
            'Código' => 'code',
            'Nome' => fn (SalesRepresentative $item) => $item->user->name,
            'Região' => fn (SalesRepresentative $item) => $item->region?->name ?? '-',
            'E-mail' => fn (SalesRepresentative $item) => $item->user->email,
            'Clientes' => 'customers_count',
            'Status' => fn (SalesRepresentative $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (SalesRepresentative $item) => view('admin.modules.actions', ['resource' => 'representatives', 'item' => $item]),
        ]);
    }

    public function orders(Request $request): View
    {
        $this->access->authorize($request->user(), 'orders', 'view');

        return $this->module($request, $this->access->scopeOrders(Order::query(), $request->user())->with(['customer', 'salesRepresentative.user'])->latest('order_date'), 'Pedidos', 'orders', [
            'Número' => 'order_number',
            'Cliente' => fn (Order $item) => $item->customer->trade_name ?: $item->customer->corporate_name,
            'Representante' => fn (Order $item) => $item->salesRepresentative->user->name,
            'Origem' => fn (Order $item) => match ($item->source) {
                'Mobile', 'App' => 'APP',
                'Admin', 'Web' => 'Web',
                default => $item->source,
            },
            'Status' => fn (Order $item) => view('components.status-badge', ['active' => $item->status !== 'Cancelled', 'label' => $item->status]),
            'Total' => fn (Order $item) => 'R$ '.number_format((float) $item->total_amount, 2, ',', '.'),
            'Ações' => fn (Order $item) => view('admin.modules.actions', ['resource' => 'orders', 'item' => $item]),
        ]);
    }

    public function paymentMethods(Request $request): View
    {
        $this->access->authorize($request->user(), 'payment-methods', 'view');

        return $this->module($request, PaymentMethod::query(), 'Formas de pagamento', 'payment_methods', [
            'Código' => 'code',
            'Nome' => 'name',
            'Ordem' => 'sort_order',
            'Status' => fn (PaymentMethod $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (PaymentMethod $item) => view('admin.modules.actions', ['resource' => 'payment-methods', 'item' => $item]),
        ]);
    }

    public function paymentTerms(Request $request): View
    {
        $this->access->authorize($request->user(), 'payment-terms', 'view');

        return $this->module($request, PaymentTerm::query(), 'Prazos de pagamento', 'payment_terms', [
            'Código' => 'code',
            'Nome' => 'name',
            'Parcelas' => fn (PaymentTerm $item) => $item->installmentSummary(),
            'Pedido mínimo' => fn (PaymentTerm $item) => 'R$ '.number_format((float) $item->minimum_order_amount, 2, ',', '.'),
            'Ordem' => 'sort_order',
            'Status' => fn (PaymentTerm $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (PaymentTerm $item) => view('admin.modules.actions', ['resource' => 'payment-terms', 'item' => $item]),
        ]);
    }

    public function categories(Request $request): View
    {
        $this->access->authorize($request->user(), 'categories', 'view');

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
        $this->access->authorize($request->user(), 'brands', 'view');

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
        $this->access->authorize($request->user(), 'units', 'view');

        return $this->module($request, Unit::query()->withCount('products'), 'Unidades', 'units', [
            'Código' => 'code',
            'Nome' => 'name',
            'Produtos' => 'products_count',
            'Status' => fn (Unit $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Unit $item) => view('admin.modules.actions', ['resource' => 'units', 'item' => $item]),
        ]);
    }

    public function regions(Request $request): View
    {
        $this->access->authorize($request->user(), 'regions', 'view');

        return $this->module($request, Region::query()->withCount(['municipalities', 'customers', 'representatives', 'priceTables']), 'Regiões', 'regions', [
            'Nome' => 'name',
            'Nível' => 'level',
            'UF' => fn (Region $item) => $item->state ?? '-',
            'Abrangência' => fn (Region $item) => $item->coverage_type === 'state_remainder'
                ? 'Demais municípios da UF'
                : $item->municipalities_count.' município(s)',
            'Clientes' => 'customers_count',
            'Representantes' => 'representatives_count',
            'Tabelas' => 'price_tables_count',
            'Status' => fn (Region $item) => view('components.status-badge', ['active' => $item->active]),
            'Ações' => fn (Region $item) => view('admin.modules.actions', ['resource' => 'regions', 'item' => $item]),
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
        $status = $request->string('status')->toString();
        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $query->where($table.'.company_id', $companyId);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search, $table): void {
                foreach (['name', 'trade_name', 'corporate_name', 'document', 'sku', 'code', 'order_number', 'city', 'state'] as $column) {
                    if (\Schema::hasColumn($table, $column)) {
                        $query->orWhere($table.'.'.$column, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        if ($status !== '') {
            if (\Schema::hasColumn($table, 'active') && in_array($status, ['active', 'inactive'], true)) {
                $query->where($table.'.active', $status === 'active');
            } elseif ($table === 'orders' && in_array($status, ['Draft', 'Sent', 'Cancelled'], true)) {
                $query->where($table.'.status', $status);
            }
        }

        $sortable = collect(['name', 'trade_name', 'corporate_name', 'code', 'order_number', 'created_at', 'updated_at', 'order_date'])
            ->first(fn (string $column): bool => $column === $sort && \Schema::hasColumn($table, $column));
        if ($sortable) {
            $query->reorder($table.'.'.$sortable, $direction)->orderBy($table.'.id');
        }

        return view('admin.modules.index', [
            'title' => $title,
            'description' => 'Consulte e gerencie as informações da sua empresa.',
            'items' => $query->paginate(15)->withQueryString(),
            'columns' => $columns,
            'resource' => [
                'customers' => 'customers',
                'products' => 'products',
                'sales_representatives' => 'representatives',
                'orders' => 'orders',
                'payment_methods' => 'payment-methods',
                'payment_terms' => 'payment-terms',
                'regions' => 'regions',
                'categories' => 'categories',
                'brands' => 'brands',
                'units' => 'units',
            ][$table] ?? null,
            'search' => $search,
            'filters' => [
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
                'has_active' => \Schema::hasColumn($table, 'active'),
                'is_orders' => $table === 'orders',
            ],
        ]);
    }
}
