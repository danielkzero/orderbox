<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesRepresentative;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $period = $this->period($request);
        $previousPeriod = $this->previousPeriod($period['start'], $period['end']);
        $groupBy = in_array($request->string('group_by')->toString(), ['daily', 'weekly', 'monthly'], true)
            ? $request->string('group_by')->toString()
            : 'daily';

        $ordersInPeriod = Order::query()
            ->where('company_id', $companyId)
            ->whereBetween('order_date', [$period['start']->startOfDay(), $period['end']->endOfDay()]);

        $previousOrders = Order::query()
            ->where('company_id', $companyId)
            ->whereBetween('order_date', [$previousPeriod['start']->startOfDay(), $previousPeriod['end']->endOfDay()]);

        $totalRevenue = (float) (clone $ordersInPeriod)->sum('total_amount');
        $ordersCount = (clone $ordersInPeriod)->count();
        $cancelledCount = (clone $ordersInPeriod)->where('status', 'Cancelled')->count();
        $previousRevenue = (float) (clone $previousOrders)->sum('total_amount');
        $previousOrdersCount = (clone $previousOrders)->count();

        return view('dashboard', [
            'company' => $request->user()->company,
            'period' => $period,
            'groupBy' => $groupBy,
            'preset' => $request->string('preset')->toString() ?: '7d',
            'periodShortcuts' => $this->periodShortcuts(),
            'indicators' => [
                'revenue' => $totalRevenue,
                'revenue_change' => $this->percentageChange($totalRevenue, $previousRevenue),
                'orders' => $ordersCount,
                'orders_change' => $this->percentageChange($ordersCount, $previousOrdersCount),
                'average_ticket' => $ordersCount > 0 ? $totalRevenue / $ordersCount : 0,
                'cancel_rate' => $ordersCount > 0 ? ($cancelledCount / $ordersCount) * 100 : 0,
                'customers' => Customer::query()->where('company_id', $companyId)->where('active', true)->count(),
                'products' => Product::query()->where('company_id', $companyId)->where('active', true)->count(),
                'representatives' => SalesRepresentative::query()->where('company_id', $companyId)->where('active', true)->count(),
            ],
            'channelStats' => $this->channelStats($companyId, $period['start'], $period['end']),
            'revenueStats' => $this->revenueStats($companyId, $period['start'], $period['end'], $groupBy),
            'recentOrders' => Order::query()
                ->with(['customer', 'salesRepresentative.user'])
                ->where('company_id', $companyId)
                ->whereBetween('order_date', [$period['start']->startOfDay(), $period['end']->endOfDay()])
                ->latest('order_date')
                ->limit(5)
                ->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $companyId = $request->user()->company_id;
        $period = $this->period($request);
        $filename = 'dashboard-vendas-'.$period['start']->format('Y-m-d').'-'.$period['end']->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($companyId, $period): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Pedido', 'Cliente', 'Representante', 'Status', 'Origem', 'Data', 'Subtotal', 'Total'], ';');

            Order::query()
                ->with(['customer', 'salesRepresentative.user'])
                ->where('company_id', $companyId)
                ->whereBetween('order_date', [$period['start']->startOfDay(), $period['end']->endOfDay()])
                ->orderBy('order_date')
                ->each(function (Order $order) use ($handle): void {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->customer?->trade_name ?: $order->customer?->corporate_name,
                        $order->salesRepresentative?->user?->name,
                        $order->status,
                        $order->source === 'Mobile' ? 'APP' : $order->source,
                        $order->order_date?->format('d/m/Y H:i'),
                        number_format((float) $order->subtotal, 2, ',', '.'),
                        number_format((float) $order->total_amount, 2, ',', '.'),
                    ], ';');
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function period(Request $request): array
    {
        $today = CarbonImmutable::today();
        $preset = $request->string('preset')->toString();

        if ($preset && $preset !== 'custom') {
            return match ($preset) {
                'today' => ['start' => $today, 'end' => $today],
                '30d' => ['start' => $today->subDays(29), 'end' => $today],
                'month' => ['start' => $today->startOfMonth(), 'end' => $today->endOfMonth()],
                default => ['start' => $today->subDays(6), 'end' => $today],
            };
        }

        $start = $request->date('start_date') ? CarbonImmutable::parse($request->date('start_date')) : $today->subDays(6);
        $end = $request->date('end_date') ? CarbonImmutable::parse($request->date('end_date')) : $today;

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return ['start' => $start, 'end' => $end];
    }

    private function previousPeriod(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $days = $start->diffInDays($end) + 1;

        return [
            'start' => $start->subDays($days),
            'end' => $start->subDay(),
        ];
    }

    private function periodShortcuts(): array
    {
        return [
            'today' => 'Hoje',
            '7d' => '7 dias',
            '30d' => '30 dias',
            'month' => 'Mês atual',
        ];
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function channelStats(int $companyId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $total = Order::query()
            ->where('company_id', $companyId)
            ->whereBetween('order_date', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        return collect([
            ['label' => 'Web', 'sources' => ['Admin']],
            ['label' => 'APP', 'sources' => ['Mobile']],
            ['label' => 'API', 'sources' => ['API', 'Api']],
        ])
            ->map(function (array $channel) use ($companyId, $start, $end, $total): array {
                $count = Order::query()
                    ->where('company_id', $companyId)
                    ->whereIn('source', $channel['sources'])
                    ->whereBetween('order_date', [$start->startOfDay(), $end->endOfDay()])
                    ->count();

                return [
                    'label' => $channel['label'],
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function revenueStats(int $companyId, CarbonImmutable $start, CarbonImmutable $end, string $groupBy): array
    {
        $orders = Order::query()
            ->where('company_id', $companyId)
            ->whereBetween('order_date', [$start->startOfDay(), $end->endOfDay()])
            ->orderBy('order_date')
            ->get(['order_date', 'total_amount']);

        $grouped = $orders->groupBy(function (Order $order) use ($groupBy): string {
            return match ($groupBy) {
                'weekly' => $order->order_date->startOfWeek()->format('d/m'),
                'monthly' => $order->order_date->format('m/Y'),
                default => $order->order_date->format('d/m'),
            };
        })->map(fn ($orders): float => (float) $orders->sum('total_amount'));

        $max = max((float) $grouped->max(), 1);

        return $grouped->map(fn (float $total, string $label): array => [
            'label' => $label,
            'total' => $total,
            'height' => max(10, (int) round(($total / $max) * 140)),
        ])->values()->all();
    }
}
