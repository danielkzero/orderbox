<p>Olá,</p>

<p>Segue em anexo o pedido <strong>{{ $order->order_number }}</strong>, no valor total de
R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}.</p>

<p>Atenciosamente,<br>{{ $order->company->trade_name ?: $order->company->corporate_name }}</p>
