<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

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
        return Pdf::loadView('orders.document', [
            'order' => $this->load($order),
            'pdfMode' => true,
        ])->setPaper('a4')->output();
    }
}
