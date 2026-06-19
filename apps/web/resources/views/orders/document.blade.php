<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; padding: 18mm; background: #fff; }
        .toolbar { width: 210mm; margin: 20px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        .button { padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; text-decoration: none; cursor: pointer; }
        .button-primary { border-color: #465fff; background: #465fff; color: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #465fff; padding-bottom: 18px; }
        .brand { font-size: 24px; font-weight: 700; color: #465fff; }
        .number { font-size: 18px; font-weight: 700; }
        .muted { color: #6b7280; }
        .grid { display: table; width: 100%; margin-top: 20px; table-layout: fixed; }
        .column { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
        .section-title { margin: 24px 0 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #465fff; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; }
        th, td { padding: 9px; border-bottom: 1px solid #e5e7eb; }
        .right { text-align: right; }
        .totals { width: 280px; margin: 20px 0 0 auto; }
        .totals td { border: 0; padding: 5px; }
        .grand-total { font-size: 16px; font-weight: 700; color: #465fff; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @unless ($pdfMode)
        <div class="toolbar">
            <a class="button" href="{{ route('orders.index') }}">Voltar</a>
            <a class="button" href="{{ route('orders.pdf', $order) }}" target="_blank">Abrir PDF</a>
            <button class="button button-primary" onclick="window.print()">Imprimir</button>
        </div>
    @endunless
    <main class="page">
        <header class="header">
            <div>
                <div class="brand">OrderBox</div>
                <div>{{ $order->company->trade_name ?: $order->company->corporate_name }}</div>
            </div>
            <div class="right">
                <div class="muted">PEDIDO</div>
                <div class="number">{{ $order->order_number }}</div>
                <div>{{ $order->order_date->format('d/m/Y') }}</div>
            </div>
        </header>

        <div class="grid">
            <div class="column">
                <div class="section-title">Cliente</div>
                <strong>{{ $order->customer->trade_name ?: $order->customer->corporate_name }}</strong><br>
                {{ $order->customer->corporate_name }}<br>
                {{ $order->customer->document }}<br>
                @if ($address = $order->customer->addresses->sortByDesc('default_address')->first())
                    {{ $address->street }}, {{ $address->number }} {{ $address->complement }}<br>
                    {{ $address->district }} — {{ $address->city }}/{{ $address->state }}
                @endif
            </div>
            <div class="column">
                <div class="section-title">Condições comerciais</div>
                Representante: {{ $order->salesRepresentative->user->name }}<br>
                Tabela: {{ $order->priceTable->name }}<br>
                Forma: {{ $order->payment_method }}<br>
                Prazo: {{ $order->payment_terms }}
            </div>
        </div>

        <div class="section-title">Produtos</div>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="right">Quantidade</th>
                    <th class="right">Preço unitário</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td><strong>{{ $item->product->sku }}</strong><br>{{ $item->product->name }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 3, ',', '.') }} {{ $item->product->unit?->code }}</td>
                        <td class="right">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td class="right">R$ {{ number_format((float) $item->total_amount, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td class="right">R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</td></tr>
            <tr class="grand-total"><td>Total</td><td class="right">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</td></tr>
        </table>

        @if ($order->notes)
            <div class="section-title">Observações</div>
            <p>{{ $order->notes }}</p>
        @endif
    </main>
</body>
</html>
