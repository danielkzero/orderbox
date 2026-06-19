<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDocumentSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderDocumentService
{
    public function load(Order $order): Order
    {
        return $order->loadMissing([
            'company',
            'customer.addresses',
            'customer.contacts',
            'salesRepresentative.user',
            'priceTable',
            'items.product.unit',
        ]);
    }

    public function pdf(Order $order): string
    {
        $order = $this->load($order);
        $settings = $this->settings($order);
        $landscape = $this->usesLandscape($settings);

        return Pdf::loadView('orders.document', [
            'order' => $order,
            'settings' => $settings,
            'items' => $this->items($order),
            'columnLabels' => $this->columnLabels(),
            'pdfLandscape' => $landscape,
            'pdfMode' => true,
        ])->setPaper('a4', $landscape ? 'landscape' : 'portrait')->output();
    }

    public function usesLandscape(OrderDocumentSetting $settings): bool
    {
        return collect($settings->columns)->count() > 7;
    }

    public function settings(Order $order): OrderDocumentSetting
    {
        return OrderDocumentSetting::query()->where('company_id', $order->company_id)->first()
            ?? OrderDocumentSetting::defaults($order->company_id);
    }

    public function items(Order $order): Collection
    {
        $items = $this->load($order)->items;

        return match ($this->settings($order)->item_order) {
            'insertion_desc' => $items->sortByDesc('id')->values(),
            'product_name' => $items->sortBy(fn ($item) => $item->product->name)->values(),
            'sku' => $items->sortBy(fn ($item) => $item->product->sku)->values(),
            default => $items->sortBy('id')->values(),
        };
    }

    public function columnLabels(): array
    {
        return [
            'sequence' => '#',
            'image' => 'Foto',
            'sku' => 'Código',
            'name' => 'Produto',
            'quantity' => 'Quantidade',
            'unit' => 'Unidade',
            'available_stock' => 'Estoque',
            'table_price' => 'Preço de tabela',
            'discounts' => 'Descontos e acréscimos',
            'unit_price' => 'Preço líquido',
            'total' => 'Subtotal',
        ];
    }

    public function excel(Order $order): string
    {
        $order = $this->load($order);
        $settings = $this->settings($order);
        $columns = collect($settings->columns)->reject(fn (string $column): bool => $column === 'image')->values();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pedido');
        $sheet->setCellValue('A1', 'Pedido '.$order->order_number);
        $sheet->setCellValue('A2', $order->customer->trade_name ?: $order->customer->corporate_name);
        $sheet->mergeCells('A1:'.Coordinate::stringFromColumnIndex(max(1, $columns->count())).'1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        foreach ($columns as $index => $column) {
            $cell = Coordinate::stringFromColumnIndex($index + 1).'4';
            $sheet->setCellValue($cell, $this->columnLabels()[$column]);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(max(1, $columns->count()));
        $sheet->getStyle("A4:{$lastColumn}4")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lastColumn}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('465FFF');

        foreach ($this->items($order) as $index => $item) {
            foreach ($columns as $columnIndex => $column) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex + 1).($index + 5),
                    $this->excelValue($column, $item, $index),
                );
            }
        }

        $totalRow = $this->items($order)->count() + 6;
        $sheet->setCellValue('A'.$totalRow, 'Total do pedido');
        $sheet->setCellValue('B'.$totalRow, (float) $order->total_amount);
        $sheet->getStyle("A{$totalRow}:B{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("B5:{$lastColumn}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        foreach (range(1, max(1, $columns->count())) as $columnIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
        }

        $temporary = tempnam(sys_get_temp_dir(), 'orderbox-xlsx-');
        (new Xlsx($spreadsheet))->save($temporary);
        $contents = file_get_contents($temporary);
        unlink($temporary);
        $spreadsheet->disconnectWorksheets();

        return $contents;
    }

    private function excelValue(string $column, $item, int $index): string|float|int
    {
        return match ($column) {
            'sequence' => $index + 1,
            'sku' => $item->product->sku,
            'name' => $item->product->name,
            'quantity' => (float) $item->quantity,
            'unit' => $item->product->unit?->code ?? '',
            'available_stock' => (float) ($item->product->available_stock ?? 0),
            'table_price' => (float) $item->unit_price,
            'discounts' => $this->adjustmentSummary($item->discounts),
            'unit_price' => (float) $item->unit_price,
            'total' => (float) $item->total_amount,
            default => '',
        };
    }

    public function adjustmentSummary(?array $adjustments): string
    {
        return collect($adjustments)->map(function (array $adjustment): string {
            $prefix = ($adjustment['type'] ?? 'discount') === 'surcharge' ? 'Acréscimo' : 'Desconto';
            $value = ($adjustment['mode'] ?? 'percentage') === 'fixed'
                ? 'R$ '.number_format((float) ($adjustment['value'] ?? 0), 2, ',', '.')
                : number_format((float) ($adjustment['value'] ?? 0), 2, ',', '.').'%';

            return $prefix.' '.$value;
        })->implode(' + ');
    }
}
