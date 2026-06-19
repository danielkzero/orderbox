@php
    $documentColumns = collect($settings->documentColumns());
    $printColumns = collect($settings->printColumns());
    $renderColumns = $pdfMode ? $documentColumns : $documentColumns->merge($printColumns)->unique()->values();
    $printMargin = match ($settings->print_margin) {
        'none' => '0',
        'narrow' => '8mm',
        default => '15mm',
    };
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->order_number }}</title>
    <style>
        @page { size: A4 {{ $pdfMode && $pdfLandscape ? 'landscape' : 'portrait' }}; margin: {{ $pdfMode ? '0' : $printMargin }}; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; padding: 18mm; background: #fff; }
        .toolbar { width: 210mm; margin: 20px auto 0; display: flex; justify-content: space-between; gap: 8px; }
        .toolbar-group { display: flex; gap: 8px; }
        .button { padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; text-decoration: none; cursor: pointer; }
        .button-primary { border-color: #465fff; background: #465fff; color: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #465fff; padding-bottom: 18px; }
        .brand { font-size: 24px; font-weight: 700; color: #465fff; }
        .number { font-size: 18px; font-weight: 700; }
        .muted { color: #6b7280; }
        .grid { display: table; width: 100%; margin-top: 20px; table-layout: fixed; }
        .column { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
        .section-title { margin: 24px 0 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #465fff; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f3f4f6; text-align: left; }
        th, td { padding: 9px; border-bottom: 1px solid #e5e7eb; overflow-wrap: anywhere; word-break: break-word; }
        .right { text-align: right; }
        .totals { width: 280px; margin: 20px 0 0 auto; }
        .totals td { border: 0; padding: 5px; }
        .grand-total { font-size: 16px; font-weight: 700; color: #465fff; }
        .product-image { display: block; object-fit: contain; }
        .product-image-small { width: 34px; height: 34px; }
        .product-image-medium { width: 54px; height: 54px; }
        .product-image-large { width: 78px; height: 78px; }
        dialog { width: min(920px, calc(100% - 32px)); max-height: calc(100vh - 40px); padding: 0; border: 0; border-radius: 14px; box-shadow: 0 24px 70px rgba(15, 23, 42, .28); }
        dialog::backdrop { background: rgba(15, 23, 42, .55); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid #e5e7eb; }
        .modal-body { max-height: calc(100vh - 190px); overflow-y: auto; padding: 22px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 22px; border-top: 1px solid #e5e7eb; }
        .settings-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
        .setting-title { margin: 0 0 10px; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .check { display: flex; align-items: center; gap: 8px; margin: 9px 0; color: #374151; }
        .field { width: 100%; margin-top: 6px; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 7px; background: #fff; }
        .preview { margin-top: 24px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f8fafc; }
        .screen-column-hidden, .screen-block-hidden, .screen-row-hidden { display: none; }
        .pdf-mode { background: #fff; }
        .pdf-mode .page { width: 100%; min-height: 0; margin: 0; padding: 10mm; }
        .pdf-mode table { table-layout: fixed; font-size: 9px; }
        .pdf-mode th, .pdf-mode td { padding: 5px 4px; line-height: 1.25; }
        .pdf-mode .header { display: table; width: 100%; table-layout: fixed; }
        .pdf-mode .header > div { display: table-cell; width: 50%; vertical-align: top; }
        .pdf-mode .header > div:last-child { text-align: right; }
        .pdf-mode .product-image-large { width: 58px; height: 58px; }
        .pdf-mode .product-image-medium { width: 44px; height: 44px; }
        .pdf-mode .product-image-small { width: 30px; height: 30px; }
        @media (max-width: 760px) {
            .toolbar { width: auto; margin: 12px; flex-direction: column; }
            .toolbar-group { flex-wrap: wrap; }
            .page { width: auto; margin: 12px; padding: 20px; }
            .settings-grid { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            .print-hidden { display: none !important; }
            .screen-column-hidden { display: table-cell !important; }
            .screen-block-hidden { display: block !important; }
            .screen-row-hidden { display: table-row !important; }
            .print-image-small .product-image { width: 34px; height: 34px; }
            .print-image-medium .product-image { width: 54px; height: 54px; }
            .print-image-large .product-image { width: 78px; height: 78px; }
        }
    </style>
</head>
<body class="{{ $pdfMode ? 'pdf-mode' : '' }}">
    @unless ($pdfMode)
        <div class="toolbar">
            <div class="toolbar-group">
                <a class="button" href="{{ route('orders.index') }}">Voltar</a>
                <button class="button" onclick="window.print()">Imprimir</button>
                <a class="button" href="{{ route('orders.pdf', $order) }}">Download PDF</a>
                <a class="button" href="{{ route('orders.excel', $order) }}">Download Excel</a>
            </div>
            @if (auth()->user()->isAdministrative())
                <div class="toolbar-group">
                    <button class="button" type="button" onclick="document.getElementById('print-settings').showModal()">Configurar impressão</button>
                    <button class="button button-primary" type="button" onclick="document.getElementById('document-settings').showModal()">Configurar itens e ordem</button>
                </div>
            @endif
        </div>
    @endunless
    <main class="page print-image-{{ $settings->print_image_size ?: $settings->image_size }}">
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
                @if (($pdfMode ? $settings->show_customer_address : ($settings->show_customer_address || $settings->print_customer_address))
                    && ($address = $order->customer->addresses->sortByDesc('default_address')->first()))
                    <span class="{{ ! $settings->show_customer_address ? 'screen-block-hidden' : '' }} {{ ! $settings->print_customer_address ? 'print-hidden' : '' }}">
                        {{ $address->street }}, {{ $address->number }} {{ $address->complement }}<br>
                        {{ $address->district }} — {{ $address->city }}/{{ $address->state }}
                    </span>
                @endif
            </div>
            @if ($pdfMode ? $settings->show_commercial_terms : ($settings->show_commercial_terms || $settings->print_commercial_terms))
                <div class="column {{ ! $settings->show_commercial_terms ? 'screen-block-hidden' : '' }} {{ ! $settings->print_commercial_terms ? 'print-hidden' : '' }}">
                    <div class="section-title">Condições comerciais</div>
                    Representante: {{ $order->salesRepresentative->user->name }}<br>
                    Tabela: {{ $order->priceTable->name }}<br>
                    Forma: {{ $order->payment_method }}<br>
                    Prazo: {{ $order->payment_terms }}
                </div>
            @endif
        </div>

        <div class="section-title">Produtos</div>
        <table>
            <thead>
                <tr>
                    @foreach ($renderColumns as $column)
                        <th class="{{ in_array($column, ['quantity', 'available_stock', 'table_price', 'unit_price', 'total'], true) ? 'right' : '' }} {{ ! $documentColumns->contains($column) ? 'screen-column-hidden' : '' }} {{ ! $printColumns->contains($column) ? 'print-hidden' : '' }}">{{ $columnLabels[$column] ?? $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $index => $item)
                    <tr>
                        @foreach ($renderColumns as $column)
                            @php
                                $columnClass = (! $documentColumns->contains($column) ? 'screen-column-hidden ' : '')
                                    .(! $printColumns->contains($column) ? 'print-hidden' : '');
                            @endphp
                            @if ($column === 'sequence')
                                <td class="{{ $columnClass }}">{{ $index + 1 }}</td>
                            @elseif ($column === 'image')
                                <td class="{{ $columnClass }}">
                                    @if ($item->product->imageSrc())
                                        <img class="product-image product-image-{{ $settings->image_size }}" src="{{ $item->product->imageSrc() }}" alt="">
                                    @else
                                        -
                                    @endif
                                </td>
                            @elseif ($column === 'sku')
                                <td class="{{ $columnClass }}">{{ $item->product->sku }}</td>
                            @elseif ($column === 'name')
                                <td class="{{ $columnClass }}">{{ $item->product->name }}</td>
                            @elseif ($column === 'quantity')
                                <td class="right {{ $columnClass }}">{{ number_format((float) $item->quantity, 3, ',', '.') }}</td>
                            @elseif ($column === 'unit')
                                <td class="{{ $columnClass }}">{{ $item->product->unit?->code ?? '-' }}</td>
                            @elseif ($column === 'available_stock')
                                <td class="right {{ $columnClass }}">{{ number_format((float) ($item->product->available_stock ?? 0), 3, ',', '.') }}</td>
                            @elseif ($column === 'table_price')
                                <td class="right {{ $columnClass }}">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                            @elseif ($column === 'discounts')
                                <td class="{{ $columnClass }}">{{ app(\App\Services\OrderDocumentService::class)->adjustmentSummary($item->discounts) ?: '-' }}</td>
                            @elseif ($column === 'unit_price')
                                <td class="right {{ $columnClass }}">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                            @elseif ($column === 'total')
                                <td class="right {{ $columnClass }}">R$ {{ number_format((float) $item->total_amount, 2, ',', '.') }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            @if ($pdfMode ? $settings->show_total_quantity : ($settings->show_total_quantity || $settings->print_total_quantity))
                <tr class="{{ ! $settings->show_total_quantity ? 'screen-row-hidden' : '' }} {{ ! $settings->print_total_quantity ? 'print-hidden' : '' }}"><td>Quantidade total</td><td class="right">{{ number_format((float) $items->sum('quantity'), 3, ',', '.') }}</td></tr>
            @endif
            @if ($pdfMode ? $settings->show_total_weight : ($settings->show_total_weight || $settings->print_total_weight))
                <tr class="{{ ! $settings->show_total_weight ? 'screen-row-hidden' : '' }} {{ ! $settings->print_total_weight ? 'print-hidden' : '' }}"><td>Peso bruto total</td><td class="right">{{ number_format((float) $items->sum(fn ($item) => (float) $item->quantity * (float) ($item->product->weight_kg ?? 0)), 3, ',', '.') }} kg</td></tr>
            @endif
            @if ($pdfMode ? $settings->show_subtotal : ($settings->show_subtotal || $settings->print_subtotal))
                <tr class="{{ ! $settings->show_subtotal ? 'screen-row-hidden' : '' }} {{ ! $settings->print_subtotal ? 'print-hidden' : '' }}"><td>Subtotal</td><td class="right">R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</td></tr>
            @endif
            @if ($pdfMode ? $settings->show_total : ($settings->show_total || $settings->print_total))
                <tr class="grand-total {{ ! $settings->show_total ? 'screen-row-hidden' : '' }} {{ ! $settings->print_total ? 'print-hidden' : '' }}"><td>Total</td><td class="right">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</td></tr>
            @endif
        </table>

        @if (($pdfMode ? $settings->show_notes : ($settings->show_notes || $settings->print_notes)) && $order->notes)
            <div class="{{ ! $settings->show_notes ? 'screen-block-hidden' : '' }} {{ ! $settings->print_notes ? 'print-hidden' : '' }}">
                <div class="section-title">Observações</div>
                <p>{{ $order->notes }}</p>
            </div>
        @endif
    </main>

    @unless ($pdfMode)
        @if (auth()->user()->isAdministrative())
            <dialog id="document-settings">
                <form method="POST" action="{{ route('orders.document-settings.update', $order) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <strong style="font-size: 17px;">Configurar itens e ordem do pedido</strong>
                            <div class="muted" style="margin-top: 4px;">Escolha colunas, foto, totais e a ordenação usada no PDF, Excel e e-mail.</div>
                        </div>
                        <button class="button" type="button" onclick="document.getElementById('document-settings').close()">Fechar</button>
                    </div>
                    <div class="modal-body">
                        <div class="settings-grid">
                            <section>
                                <h3 class="setting-title">Detalhes do produto</h3>
                                @foreach (['sequence', 'image', 'sku', 'name', 'quantity', 'unit', 'available_stock'] as $column)
                                    <label class="check">
                                        <input type="checkbox" name="columns[]" value="{{ $column }}" data-column-toggle="{{ $column }}" @checked($documentColumns->contains($column))>
                                        {{ $columnLabels[$column] }}
                                    </label>
                                @endforeach
                                <label class="check" style="display:block; margin-top: 16px;">
                                    Tamanho da foto
                                    <select class="field" name="image_size" id="document-image-size">
                                        <option value="small" @selected($settings->image_size === 'small')>Pequena</option>
                                        <option value="medium" @selected($settings->image_size === 'medium')>Média</option>
                                        <option value="large" @selected($settings->image_size === 'large')>Grande</option>
                                    </select>
                                </label>
                            </section>
                            <section>
                                <h3 class="setting-title">Preços e subtotais</h3>
                                @foreach (['table_price', 'discounts', 'unit_price', 'total'] as $column)
                                    <label class="check">
                                        <input type="checkbox" name="columns[]" value="{{ $column }}" data-column-toggle="{{ $column }}" @checked($documentColumns->contains($column))>
                                        {{ $columnLabels[$column] }}
                                    </label>
                                @endforeach
                                <h3 class="setting-title" style="margin-top: 22px;">Informações gerais</h3>
                                @foreach ([
                                    'show_customer_address' => 'Endereço do cliente',
                                    'show_commercial_terms' => 'Condições comerciais',
                                    'show_notes' => 'Observações',
                                ] as $field => $label)
                                    <label class="check">
                                        <input type="hidden" name="{{ $field }}" value="0">
                                        <input type="checkbox" name="{{ $field }}" value="1" @checked($settings->{$field})>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </section>
                            <section>
                                <h3 class="setting-title">Totais do pedido</h3>
                                @foreach ([
                                    'show_total_quantity' => 'Quantidade total',
                                    'show_total_weight' => 'Peso bruto total',
                                    'show_subtotal' => 'Subtotal',
                                    'show_total' => 'Valor total',
                                ] as $field => $label)
                                    <label class="check">
                                        <input type="hidden" name="{{ $field }}" value="0">
                                        <input type="checkbox" name="{{ $field }}" value="1" @checked($settings->{$field})>
                                        {{ $label }}
                                    </label>
                                @endforeach
                                <label class="check" style="display:block; margin-top: 22px;">
                                    Ordem dos itens
                                    <select class="field" name="item_order">
                                        <option value="insertion_asc" @selected($settings->item_order === 'insertion_asc')>Ordem de inserção</option>
                                        <option value="insertion_desc" @selected($settings->item_order === 'insertion_desc')>Inserção decrescente</option>
                                        <option value="product_name" @selected($settings->item_order === 'product_name')>Nome do produto</option>
                                        <option value="sku" @selected($settings->item_order === 'sku')>Código do produto</option>
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="preview">
                            <strong>Pré-visualização</strong>
                            <p class="muted">A amostra responde às colunas e ao tamanho de foto selecionados.</p>
                            @if ($previewItem = $items->first())
                                <div style="overflow-x:auto; margin-top: 12px;">
                                    <table>
                                        <thead>
                                            <tr>
                                                @foreach (\App\Models\OrderDocumentSetting::AVAILABLE_COLUMNS as $column)
                                                    <th data-preview-column="{{ $column }}" @style(['display:none' => ! $documentColumns->contains($column)])>{{ $columnLabels[$column] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @foreach (\App\Models\OrderDocumentSetting::AVAILABLE_COLUMNS as $column)
                                                    <td data-preview-column="{{ $column }}" @style(['display:none' => ! $documentColumns->contains($column)])>
                                                        @if ($column === 'sequence')
                                                            1
                                                        @elseif ($column === 'image')
                                                            @if ($previewItem->product->imageSrc())
                                                                <img id="document-image-preview" class="product-image product-image-{{ $settings->image_size }}" src="{{ $previewItem->product->imageSrc() }}" alt="">
                                                            @else
                                                                -
                                                            @endif
                                                        @elseif ($column === 'sku')
                                                            {{ $previewItem->product->sku }}
                                                        @elseif ($column === 'name')
                                                            {{ $previewItem->product->name }}
                                                        @elseif ($column === 'quantity')
                                                            {{ number_format((float) $previewItem->quantity, 3, ',', '.') }}
                                                        @elseif ($column === 'unit')
                                                            {{ $previewItem->product->unit?->code ?? '-' }}
                                                        @elseif ($column === 'available_stock')
                                                            {{ number_format((float) ($previewItem->product->available_stock ?? 0), 3, ',', '.') }}
                                                        @elseif (in_array($column, ['table_price', 'unit_price'], true))
                                                            R$ {{ number_format((float) $previewItem->unit_price, 2, ',', '.') }}
                                                        @elseif ($column === 'discounts')
                                                            {{ app(\App\Services\OrderDocumentService::class)->adjustmentSummary($previewItem->discounts) ?: '-' }}
                                                        @elseif ($column === 'total')
                                                            R$ {{ number_format((float) $previewItem->total_amount, 2, ',', '.') }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="button" type="button" onclick="document.getElementById('document-settings').close()">Cancelar</button>
                        <button class="button button-primary" type="submit">Salvar modelo</button>
                    </div>
                </form>
            </dialog>
            <dialog id="print-settings">
                <form method="POST" action="{{ route('orders.print-settings.update', $order) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <strong style="font-size: 17px;">Configuração de impressão</strong>
                            <div class="muted" style="margin-top: 4px;">Escolha o que será enviado à impressora pelo navegador.</div>
                        </div>
                        <button class="button" type="button" onclick="document.getElementById('print-settings').close()">Fechar</button>
                    </div>
                    <div class="modal-body">
                        <div class="settings-grid">
                            <section>
                                <h3 class="setting-title">Detalhes do produto</h3>
                                @foreach (['sequence', 'image', 'sku', 'name', 'quantity', 'unit', 'available_stock'] as $column)
                                    <label class="check">
                                        <input type="checkbox" name="print_columns[]" value="{{ $column }}" data-print-column-toggle="{{ $column }}" @checked($printColumns->contains($column))>
                                        {{ $columnLabels[$column] }}
                                    </label>
                                @endforeach
                                <label class="check" style="display:block; margin-top: 16px;">
                                    Tamanho da foto impressa
                                    <select class="field" name="print_image_size" id="print-image-size">
                                        <option value="small" @selected($settings->print_image_size === 'small')>Pequena</option>
                                        <option value="medium" @selected(($settings->print_image_size ?: 'medium') === 'medium')>Média</option>
                                        <option value="large" @selected($settings->print_image_size === 'large')>Grande</option>
                                    </select>
                                </label>
                            </section>
                            <section>
                                <h3 class="setting-title">Preços e subtotais</h3>
                                @foreach (['table_price', 'discounts', 'unit_price', 'total'] as $column)
                                    <label class="check">
                                        <input type="checkbox" name="print_columns[]" value="{{ $column }}" data-print-column-toggle="{{ $column }}" @checked($printColumns->contains($column))>
                                        {{ $columnLabels[$column] }}
                                    </label>
                                @endforeach
                                <h3 class="setting-title" style="margin-top: 22px;">Informações gerais</h3>
                                @foreach ([
                                    'print_customer_address' => 'Endereço do cliente',
                                    'print_commercial_terms' => 'Condições comerciais',
                                    'print_notes' => 'Observações',
                                ] as $field => $label)
                                    <label class="check">
                                        <input type="hidden" name="{{ $field }}" value="0">
                                        <input type="checkbox" name="{{ $field }}" value="1" @checked($settings->{$field})>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </section>
                            <section>
                                <h3 class="setting-title">Totais e página</h3>
                                @foreach ([
                                    'print_total_quantity' => 'Quantidade total',
                                    'print_total_weight' => 'Peso bruto total',
                                    'print_subtotal' => 'Subtotal',
                                    'print_total' => 'Valor total',
                                ] as $field => $label)
                                    <label class="check">
                                        <input type="hidden" name="{{ $field }}" value="0">
                                        <input type="checkbox" name="{{ $field }}" value="1" @checked($settings->{$field})>
                                        {{ $label }}
                                    </label>
                                @endforeach
                                <label class="check" style="display:block; margin-top: 22px;">
                                    Margem da impressão
                                    <select class="field" name="print_margin">
                                        <option value="none" @selected($settings->print_margin === 'none')>Sem margem</option>
                                        <option value="narrow" @selected($settings->print_margin === 'narrow')>Estreita</option>
                                        <option value="standard" @selected(($settings->print_margin ?: 'standard') === 'standard')>Padrão</option>
                                    </select>
                                </label>
                            </section>
                        </div>
                        <div class="preview">
                            <strong>Pré-visualização da impressão</strong>
                            <p class="muted">As colunas selecionadas abaixo são independentes do PDF e do Excel.</p>
                            @if ($previewItem = $items->first())
                                <div style="overflow-x:auto; margin-top: 12px;">
                                    <table>
                                        <thead>
                                            <tr>
                                                @foreach (\App\Models\OrderDocumentSetting::AVAILABLE_COLUMNS as $column)
                                                    <th data-print-preview-column="{{ $column }}" @style(['display:none' => ! $printColumns->contains($column)])>{{ $columnLabels[$column] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @foreach (\App\Models\OrderDocumentSetting::AVAILABLE_COLUMNS as $column)
                                                    <td data-print-preview-column="{{ $column }}" @style(['display:none' => ! $printColumns->contains($column)])>
                                                        @if ($column === 'sequence')
                                                            1
                                                        @elseif ($column === 'image')
                                                            @if ($previewItem->product->imageSrc())
                                                                <img id="print-image-preview" class="product-image product-image-{{ $settings->print_image_size ?: 'medium' }}" src="{{ $previewItem->product->imageSrc() }}" alt="">
                                                            @else
                                                                -
                                                            @endif
                                                        @elseif ($column === 'sku')
                                                            {{ $previewItem->product->sku }}
                                                        @elseif ($column === 'name')
                                                            {{ $previewItem->product->name }}
                                                        @elseif ($column === 'quantity')
                                                            {{ number_format((float) $previewItem->quantity, 3, ',', '.') }}
                                                        @elseif ($column === 'unit')
                                                            {{ $previewItem->product->unit?->code ?? '-' }}
                                                        @elseif ($column === 'available_stock')
                                                            {{ number_format((float) ($previewItem->product->available_stock ?? 0), 3, ',', '.') }}
                                                        @elseif (in_array($column, ['table_price', 'unit_price'], true))
                                                            R$ {{ number_format((float) $previewItem->unit_price, 2, ',', '.') }}
                                                        @elseif ($column === 'discounts')
                                                            {{ app(\App\Services\OrderDocumentService::class)->adjustmentSummary($previewItem->discounts) ?: '-' }}
                                                        @elseif ($column === 'total')
                                                            R$ {{ number_format((float) $previewItem->total_amount, 2, ',', '.') }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="button" type="button" onclick="document.getElementById('print-settings').close()">Cancelar</button>
                        <button class="button button-primary" type="submit">Salvar impressão</button>
                    </div>
                </form>
            </dialog>
            <script>
                document.querySelectorAll('[data-column-toggle]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        document.querySelectorAll(`[data-preview-column="${checkbox.dataset.columnToggle}"]`)
                            .forEach((cell) => cell.style.display = checkbox.checked ? '' : 'none');
                    });
                });

                document.getElementById('document-image-size')?.addEventListener('change', (event) => {
                    const image = document.getElementById('document-image-preview');
                    if (!image) return;
                    image.className = `product-image product-image-${event.target.value}`;
                });

                document.querySelectorAll('[data-print-column-toggle]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        document.querySelectorAll(`[data-print-preview-column="${checkbox.dataset.printColumnToggle}"]`)
                            .forEach((cell) => cell.style.display = checkbox.checked ? '' : 'none');
                    });
                });

                document.getElementById('print-image-size')?.addEventListener('change', (event) => {
                    const image = document.getElementById('print-image-preview');
                    if (!image) return;
                    image.className = `product-image product-image-${event.target.value}`;
                });
            </script>
        @endif
    @endunless
</body>
</html>
