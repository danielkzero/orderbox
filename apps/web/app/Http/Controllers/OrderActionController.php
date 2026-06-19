<?php

namespace App\Http\Controllers;

use App\Mail\OrderDocumentMail;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Services\AuditService;
use App\Services\OperationalAccess;
use App\Services\OrderDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderActionController extends Controller
{
    public function __construct(
        private readonly OperationalAccess $access,
        private readonly OrderDocumentService $documents,
    ) {}

    public function show(Request $request, Order $order): View
    {
        $this->authorize($request, $order);

        return view('orders.document', ['order' => $this->documents->load($order), 'pdfMode' => false]);
    }

    public function pdf(Request $request, Order $order): Response
    {
        $this->authorize($request, $order);

        return response($this->documents->pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$order->order_number.'.pdf"',
        ]);
    }

    public function sharedPdf(Order $order): Response
    {
        return response($this->documents->pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$order->order_number.'.pdf"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function email(Request $request, Order $order, AuditService $audit): RedirectResponse
    {
        $this->authorize($request, $order);
        $order = $this->documents->load($order);
        $recipients = collect([$order->customer->email])
            ->merge($order->customer->contacts->where('active', true)->pluck('email'))
            ->filter()
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return back()->withErrors(['email' => 'O cliente não possui e-mail cadastrado.']);
        }

        Mail::to($recipients->all())->send(new OrderDocumentMail($order, $this->documents->pdf($order)));
        $this->recordDelivery($request, $order, 'Email', $recipients->implode(', '), 'Sent');
        $audit->record($request->user(), 'EmailOrder', $order, null, ['recipients' => $recipients->all()]);

        return back()->with('status', 'Pedido enviado por e-mail.');
    }

    public function whatsapp(Request $request, Order $order, AuditService $audit): RedirectResponse
    {
        $this->authorize($request, $order);
        $order = $this->documents->load($order);
        $contact = $order->customer->contacts
            ->where('active', true)
            ->sortByDesc('primary_contact')
            ->first(fn ($contact): bool => filled($contact->whatsapp));
        $phone = $this->whatsappNumber((string) $contact?->whatsapp);
        $pdfUrl = URL::temporarySignedRoute('orders.pdf.shared', now()->addDays(7), ['order' => $order]);
        $message = "Pedido {$order->order_number}\nPDF: {$pdfUrl}";
        $url = 'https://wa.me/'.($phone ?: '').'?text='.rawurlencode($message);

        $this->recordDelivery($request, $order, 'WhatsApp', $phone ?: null, 'Opened', 'Link temporário válido por 7 dias.');
        $audit->record($request->user(), 'ShareOrderWhatsApp', $order, null, ['recipient' => $phone ?: null]);

        return redirect()->away($url);
    }

    public function history(Request $request, Order $order): View
    {
        $this->authorize($request, $order);

        return view('orders.history', [
            'order' => $order,
            'deliveries' => $order->deliveries()->with('user')->latest()->get(),
        ]);
    }

    public function duplicate(Request $request, Order $order, AuditService $audit): RedirectResponse
    {
        $this->authorize($request, $order);

        $duplicate = DB::transaction(function () use ($request, $order): Order {
            $order->loadMissing('items');
            $duplicate = $order->replicate([
                'client_reference',
                'order_number',
                'status',
                'sent_at',
                'cancelled_at',
                'version',
            ]);
            $duplicate->user_id = $request->user()->id;
            $duplicate->sales_representative_id = $request->user()->role === 'SalesRepresentative'
                ? $this->access->representativeId($request->user())
                : $order->sales_representative_id;
            $duplicate->order_number = $this->nextOrderNumber($order->company_id);
            $duplicate->status = 'Draft';
            $duplicate->source = 'Web';
            $duplicate->sent_at = null;
            $duplicate->cancelled_at = null;
            $duplicate->version = 1;
            $duplicate->save();

            foreach ($order->items as $item) {
                OrderItem::query()->create($item->only([
                    'product_id',
                    'quantity',
                    'unit_price',
                    'discounts',
                    'total_amount',
                ]) + ['order_id' => $duplicate->id]);
            }

            return $duplicate;
        });

        $audit->record($request->user(), 'DuplicateOrder', $duplicate, null, ['source_order_id' => $order->id]);

        return redirect()->route('crud.edit', ['resource' => 'orders', 'id' => $duplicate->id])
            ->with('status', 'Pedido duplicado como rascunho.');
    }

    private function authorize(Request $request, Order $order): void
    {
        abort_unless($order->company_id === $request->user()->company_id, 404);
        $this->access->authorize($request->user(), 'orders', 'view', $order);
    }

    private function recordDelivery(
        Request $request,
        Order $order,
        string $channel,
        ?string $recipient,
        string $status,
        ?string $details = null,
    ): void {
        OrderDelivery::query()->create([
            'company_id' => $order->company_id,
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => $status,
            'details' => $details,
            'sent_at' => now(),
        ]);
    }

    private function nextOrderNumber(int $companyId): string
    {
        $prefix = 'PED-'.now()->format('Ym').'-';
        $lastNumber = Order::query()
            ->where('company_id', $companyId)
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        return $prefix.str_pad((string) ($lastNumber ? ((int) str($lastNumber)->afterLast('-')->toString()) + 1 : 1), 6, '0', STR_PAD_LEFT);
    }

    private function whatsappNumber(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (in_array(strlen($digits), [10, 11], true)) {
            return '55'.$digits;
        }

        return $digits;
    }
}
