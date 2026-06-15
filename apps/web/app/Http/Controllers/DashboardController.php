<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesRepresentative;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $companyId = $request->user()->company_id;

        return view('dashboard', [
            'company' => $request->user()->company,
            'indicators' => [
                'customers' => Customer::query()->where('company_id', $companyId)->where('active', true)->count(),
                'products' => Product::query()->where('company_id', $companyId)->where('active', true)->count(),
                'representatives' => SalesRepresentative::query()->where('company_id', $companyId)->where('active', true)->count(),
                'orders' => Order::query()->where('company_id', $companyId)->count(),
                'orders_today' => Order::query()->where('company_id', $companyId)->whereDate('order_date', today())->count(),
                'orders_month' => Order::query()->where('company_id', $companyId)->whereBetween('order_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ],
            'recentOrders' => Order::query()
                ->with(['customer', 'salesRepresentative.user'])
                ->where('company_id', $companyId)
                ->latest('order_date')
                ->limit(5)
                ->get(),
        ]);
    }
}
