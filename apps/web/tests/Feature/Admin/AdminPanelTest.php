<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Mail\OrderDocumentMail;
use App\Models\ApiClient;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\CustomerRepresentative;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Models\User;
use App\Services\CommercialRegionResolver;
use Database\Seeders\HydradigitalDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsureAuthenticationSessionIsActive::class);

        $company = Company::factory()->create();
        $this->admin = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'danikzero@hotmail.com',
            'role' => 'Admin',
        ]);
        $this->seed(HydradigitalDemoSeeder::class);
    }

    public function test_operational_modules_are_rendered_for_the_authenticated_company(): void
    {
        foreach (['customers', 'products', 'representatives', 'orders', 'payment-methods', 'payment-terms', 'categories', 'brands', 'units', 'regions'] as $path) {
            $this->actingAs($this->admin)->get('/'.$path)->assertOk();
        }

        $this->actingAs($this->admin)->get('/price-tables')->assertNotFound();
    }

    public function test_documentation_pages_are_rendered(): void
    {
        $this->actingAs($this->admin)->get('/manual')
            ->assertOk()
            ->assertSee('Manual de uso');

        $this->actingAs($this->admin)->get('/api-guide')
            ->assertOk()
            ->assertSee('X-OrderBox-Client-Key');
    }

    public function test_product_language_is_consistent_across_public_and_admin_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Uma experiência simples para vender, acompanhar e crescer.')
            ->assertSee('Sua operação em tempo real');

        $this->actingAs($this->admin)->get('/crud/categories/create')
            ->assertOk()
            ->assertSee('Revise as informações abaixo e salve quando estiver tudo certo.');

        $this->actingAs($this->admin)->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Consulte e gerencie as informações da sua empresa.');
    }

    public function test_navigation_prioritizes_daily_work_and_uses_contextual_tabs(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Clientes')
            ->assertSee('Produtos')
            ->assertSee('Pedidos')
            ->assertSee('Configurações')
            ->assertSee('Ajuda')
            ->assertDontSee('CADASTROS')
            ->assertDontSee('ADMINISTRACAO');

        $this->actingAs($this->admin)->get(route('products.index'))
            ->assertOk()
            ->assertSee('Navegação do módulo')
            ->assertSee('Categorias')
            ->assertSee('Marcas')
            ->assertSee('Unidades');

        $this->actingAs($this->admin)->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Representantes')
            ->assertSee('Regiões');

        $this->actingAs($this->admin)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Formas de pagamento')
            ->assertSee('Prazos');

        $this->actingAs($this->admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Meu perfil')
            ->assertSee('Segurança')
            ->assertSee('Usuários')
            ->assertSee('Integrações')
            ->assertSee('Auditoria');
    }

    public function test_feedback_and_confirmation_components_are_rendered_consistently(): void
    {
        $this->actingAs($this->admin)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="', false)
            ->assertSee('data-confirm-level="double"', false)
            ->assertSee('confirmationDialog()', false)
            ->assertDontSee('return confirm(', false);

        $this->actingAs($this->admin)
            ->withSession(['status' => 'Operação de teste concluída.'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('notificationCenter', false);

        $this->actingAs($this->admin)->get(route('products.index'))
            ->assertOk()
            ->assertSee('role="tooltip"', false)
            ->assertSee('x-teleport="body"', false)
            ->assertSee('fixed z-[100002]', false)
            ->assertSee("transform = 'translate(-50%, -100%)'", false)
            ->assertSee('Adicionar tabela de preço');
    }

    public function test_reusable_ui_feedback_components_compile(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-alert variant="info" title="Aviso">Mensagem</x-alert>
            <x-spinner />
            <x-tooltip text="Ajuda"><button type="button">?</button></x-tooltip>
            <x-popover title="Detalhes" trigger="Abrir">Conteúdo</x-popover>
            <x-progress-bar :value="45" label="Progresso" />
            <x-ribbon>Novo</x-ribbon>
            <x-list><x-list-item title="Item" description="Descrição" /></x-list>
        BLADE);

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('role="tooltip"', $html);
        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('Novo', $html);
        $this->assertStringContainsString('Descrição', $html);
    }

    public function test_dashboard_filters_period_and_exports_orders(): void
    {
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $this->actingAs($this->admin)->get(route('dashboard', [
            'preset' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => 'weekly',
        ]))
            ->assertOk()
            ->assertSee('Dashboard de vendas')
            ->assertSee('Exportar')
            ->assertSee('Ver todos')
            ->assertSee('Semanal');

        $response = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'preset' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Pedido;Cliente;Representante;Status;Origem;Data;Subtotal;Total', $response->streamedContent());
    }

    public function test_admin_can_create_api_client_and_receive_plain_secret_once(): void
    {
        $this->actingAs($this->admin)->post('/api-clients', [
            'name' => 'Ionic Mobile',
            'channel' => 'Mobile',
        ])->assertRedirect(route('api-clients.index'));

        $client = ApiClient::query()->where('name', 'Ionic Mobile')->firstOrFail();

        $this->assertSame($this->admin->company_id, $client->company_id);
        $this->assertNotEmpty($client->client_key);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CreateApiClient', 'entity_id' => $client->id]);
    }

    public function test_admin_can_create_update_and_deactivate_catalog_records(): void
    {
        $this->actingAs($this->admin)->post('/crud/categories', [
            'name' => 'Nova Categoria',
            'description' => 'Cadastro criado pelo teste.',
        ])->assertRedirect(route('categories.index'));

        $category = Category::query()->where('name', 'Nova Categoria')->firstOrFail();
        $this->actingAs($this->admin)->put("/crud/categories/{$category->id}", [
            'name' => 'Categoria Revisada',
            'description' => 'Cadastro atualizado pelo teste.',
            'active' => '1',
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Categoria Revisada',
        ]);

        $customerRegion = Region::query()
            ->where('company_id', $this->admin->company_id)
            ->where('name', 'São Paulo Capital')
            ->firstOrFail();
        $customerPriceTable = PriceTable::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $customerRepresentative = SalesRepresentative::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/customers', [
            'corporate_name' => 'Cliente Teste Ltda',
            'trade_name' => 'Cliente Teste',
            'document' => 'AB.CD1.234/0001-47',
            'email' => 'cliente.teste@example.test',
            'credit_limit' => '1500',
            'addresses' => [
                [
                    'type' => 'Entrega',
                    'zip_code' => '01001-000',
                    'street' => 'Praça da Sé',
                    'number' => '100',
                    'complement' => 'Sala 10',
                    'district' => 'Sé',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'municipality_ibge_code' => '3550308',
                    'country' => 'Brasil',
                    'default_address' => '1',
                ],
            ],
            'contacts' => [
                [
                    'name' => 'Contato Cliente',
                    'position' => 'Comprador',
                    'department' => 'Compras',
                    'email' => 'contato.cliente@example.test',
                    'phone' => '(11) 3000-1000',
                    'mobile' => '(11) 99000-1000',
                    'whatsapp' => '(11) 99000-1000',
                    'primary_contact' => '1',
                    'active' => '1',
                ],
            ],
            'representative_ids' => [$customerRepresentative->id],
            'price_table_ids' => [$customerPriceTable->id],
        ])->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('document', 'ABCD1234000147')->firstOrFail();
        $this->assertSame('ABCD1234000147', $customer->document);
        $this->assertSame($customerRegion->id, $customer->region_id);
        $this->assertSame(1, CustomerAddress::query()->where('customer_id', $customer->id)->count());
        $this->assertSame(1, CustomerContact::query()->where('customer_id', $customer->id)->count());
        $this->assertSame(1, CustomerRepresentative::query()->where('customer_id', $customer->id)->count());
        $this->assertDatabaseHas('customer_price_table', [
            'customer_id' => $customer->id,
            'price_table_id' => $customerPriceTable->id,
        ]);
        $this->actingAs($this->admin)->post("/crud/customers/{$customer->id}/deactivate")
            ->assertRedirect();

        $this->assertFalse($customer->refresh()->active);
    }

    public function test_admin_can_create_product_from_references(): void
    {
        Storage::fake('public');
        $category = Category::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $brand = Brand::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $unit = Unit::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $priceTable = PriceTable::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/products', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'sku' => 'TEST-001',
            'barcode' => '7891000000010',
            'name' => 'Produto Teste',
            'short_description' => 'Produto criado pelo teste.',
            'description' => 'Descrição completa do produto de teste.',
            'color' => 'Azul',
            'weight_kg' => '1.250',
            'length_cm' => '20',
            'width_cm' => '10',
            'height_cm' => '8',
            'base_price' => '49.90',
            'available_stock' => '12.5',
            'stock_status' => 'LowStock',
            'active' => '1',
            'image' => UploadedFile::fake()->image('produto-teste.jpg', 800, 400),
            'table_prices' => [
                ['price_table_id' => $priceTable->id, 'minimum_quantity' => '1', 'price' => '47.90'],
            ],
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $this->admin->company_id,
            'sku' => 'TEST-001',
            'name' => 'Produto Teste',
            'barcode' => '7891000000010',
            'base_price' => '49.90',
            'stock_status' => 'LowStock',
        ]);
        $product = Product::query()->where('sku', 'TEST-001')->firstOrFail();
        $this->assertSame('Produto Teste', $product->name);
        $this->assertTrue($product->active);
        $this->assertNotNull($product->published_at);
        $this->assertStringStartsWith('storage/products/', $product->image_url);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $product->image_url));
        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'price_table_id' => $priceTable->id,
            'minimum_quantity' => '1.000',
            'price' => '47.90',
        ]);
    }

    public function test_admin_can_manage_payment_methods_and_terms(): void
    {
        $this->actingAs($this->admin)->post('/crud/payment-methods', [
            'name' => 'Transferência bancária',
            'code' => 'Transferência Bancária',
            'description' => 'Pagamento por transferência.',
            'sort_order' => 50,
        ])->assertRedirect(route('payment-methods.index'));

        $method = PaymentMethod::query()->where('company_id', $this->admin->company_id)->where('code', 'transferencia-bancaria')->firstOrFail();
        $this->assertSame('Transferência bancária', $method->name);

        $this->actingAs($this->admin)->post('/crud/payment-terms', [
            'name' => '30/60/90 dias',
            'code' => '30/60/90',
            'installment_days_input' => '30 / 60 / 90',
            'minimum_order_amount' => '500.00',
            'description' => 'Três parcelas mensais.',
            'sort_order' => 100,
        ])->assertRedirect(route('payment-terms.index'));

        $term = PaymentTerm::query()->where('company_id', $this->admin->company_id)->where('code', '30/60/90')->firstOrFail();
        $this->assertSame([30, 60, 90], $term->installment_days);
        $this->assertSame('500.00', $term->minimum_order_amount);
        $this->assertSame('30/60/90 dias', $term->installmentSummary());

        $this->actingAs($this->admin)->get('/crud/orders/create')
            ->assertOk()
            ->assertSee('Transferência bancária')
            ->assertSee('paymentTerms:', false)
            ->assertSee('selectedPaymentTermCode:', false)
            ->assertSee('Faltam ${money(paymentTermShortfall(term))} para habilitar este prazo', false)
            ->assertSee('Bloqueado')
            ->assertSee('Disponível')
            ->assertSee('minimum_order_amount', false);
    }

    public function test_admin_can_create_and_rename_price_tables_from_product_header(): void
    {
        $this->actingAs($this->admin)->post(route('products.price-tables.store'), [
            'name' => 'Tabela Datatable',
        ])->assertRedirect(route('products.index'));

        $priceTable = PriceTable::query()->where('name', 'Tabela Datatable')->firstOrFail();

        $this->actingAs($this->admin)->get(route('products.index'))
            ->assertOk()
            ->assertSee('Tabela Datatable')
            ->assertSee('Adicionar tabela de preço');

        $this->actingAs($this->admin)->patch(route('products.price-tables.update', $priceTable), [
            'name' => 'Tabela Datatable Editada',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('price_tables', [
            'id' => $priceTable->id,
            'name' => 'Tabela Datatable Editada',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Create',
            'entity_type' => 'PriceTable',
            'entity_id' => $priceTable->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Update',
            'entity_type' => 'PriceTable',
            'entity_id' => $priceTable->id,
        ]);
    }

    public function test_admin_can_link_a_price_table_created_in_products_to_a_region(): void
    {
        $this->actingAs($this->admin)->post(route('products.price-tables.store'), [
            'name' => 'Especial Vale',
        ])->assertRedirect(route('products.index'));
        $table = PriceTable::query()->where('name', 'Especial Vale')->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/regions', [
            'name' => 'Vale do Paraiba',
            'level' => 1,
            'state' => 'SP',
            'coverage_type' => 'municipalities',
            'municipalities' => [
                [
                    'ibge_code' => '3549904',
                    'name' => 'São José dos Campos',
                    'state' => 'SP',
                    'microregion_name' => 'São José dos Campos',
                    'mesoregion_name' => 'Vale do Paraíba Paulista',
                ],
            ],
            'price_table_ids' => [$table->id],
            'description' => 'Regiao criada pelo teste.',
        ])->assertRedirect(route('regions.index'));

        $region = Region::query()->where('name', 'Vale do Paraiba')->firstOrFail();
        $this->assertSame($region->id, $table->refresh()->region_id);
        $this->assertDatabaseHas('region_municipalities', [
            'region_id' => $region->id,
            'ibge_code' => '3549904',
        ]);
        $this->assertSame(0, ProductPrice::query()->where('price_table_id', $table->id)->count());
    }

    public function test_commercial_region_uses_ibge_city_before_state_remainder(): void
    {
        $resolver = app(CommercialRegionResolver::class);
        $capital = Region::query()->where('company_id', $this->admin->company_id)->where('name', 'São Paulo Capital')->firstOrFail();
        $interior = Region::query()->where('company_id', $this->admin->company_id)->where('name', 'São Paulo Interior')->firstOrFail();

        $this->assertSame($capital->id, $resolver->resolve($this->admin->company_id, 'SP', 'São Paulo', '3550308')?->id);
        $this->assertSame($interior->id, $resolver->resolve($this->admin->company_id, 'SP', 'Campinas', '3509502')?->id);
    }

    public function test_admin_can_create_representative_and_order(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->admin->company_id,
            'email' => 'representante.novo@hydradigital.test',
            'role' => 'SalesRepresentative',
        ]);

        $this->actingAs($this->admin)->post('/crud/representatives', [
            'user_id' => $user->id,
            'region_id' => Region::query()->where('company_id', $this->admin->company_id)->firstOrFail()->id,
            'code' => 'REP-999',
            'price_table_ids' => [PriceTable::query()->where('company_id', $this->admin->company_id)->firstOrFail()->id],
        ])->assertRedirect(route('representatives.index'));

        $representative = SalesRepresentative::query()->where('code', 'REP-999')->firstOrFail();
        $this->assertCount(1, $representative->priceTables);
        $customer = Customer::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $priceTable = $customer->applicablePriceTables()->first();
        $products = Product::query()->where('company_id', $this->admin->company_id)->limit(2)->get();
        $firstProduct = $products->first();
        $secondProduct = $products->last();
        ProductPrice::query()->updateOrCreate(
            ['product_id' => $firstProduct->id, 'price_table_id' => $priceTable->id, 'minimum_quantity' => 1],
            ['price' => '15.50'],
        );
        ProductPrice::query()->updateOrCreate(
            ['product_id' => $secondProduct->id, 'price_table_id' => $priceTable->id, 'minimum_quantity' => 1],
            ['price' => '10.00'],
        );

        $orderPayload = [
            'customer_id' => $customer->id,
            'sales_representative_id' => $representative->id,
            'price_table_id' => $priceTable->id,
            'order_date' => now()->format('Y-m-d H:i:s'),
            'payment_method' => 'boleto',
            'payment_terms' => '15/30/45',
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => '2', 'unit_price' => '999.99', 'discount' => '0'],
                ['product_id' => $secondProduct->id, 'quantity' => '1', 'unit_price' => '999.99', 'discount' => '10'],
            ],
            'notes' => 'Pedido criado pelo teste.',
        ];

        $this->actingAs($this->admin)->post('/crud/orders', $orderPayload)
            ->assertRedirect(route('orders.index'))
            ->assertSessionHasNoErrors();

        $order = Order::query()->where('company_id', $this->admin->company_id)->latest('id')->firstOrFail();

        $this->assertSame('Draft', $order->status);
        $this->assertStringStartsWith('PED-', $order->order_number);
        $this->assertSame('41.00', $order->subtotal);
        $this->assertSame('40.00', $order->total_amount);
        $this->assertSame('Web', $order->source);
        $this->assertSame('boleto', $order->payment_method);
        $this->assertSame('15/30/45', $order->payment_terms);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame('10.00', $order->items()->where('product_id', $secondProduct->id)->firstOrFail()->unit_price);

        PaymentTerm::query()
            ->where('company_id', $this->admin->company_id)
            ->where('code', '15/30/45')
            ->update(['minimum_order_amount' => 50]);

        $this->actingAs($this->admin)
            ->from('/crud/orders/create')
            ->post('/crud/orders', $orderPayload)
            ->assertRedirect('/crud/orders/create')
            ->assertSessionHasErrors([
                'payment_terms' => 'O prazo 15/30/45 dias exige pedido mínimo de R$ 50,00.',
            ]);

        $this->assertSame(1, Order::query()->where('company_id', $this->admin->company_id)->where('notes', 'Pedido criado pelo teste.')->count());

        $this->actingAs($this->admin)
            ->get(route('crud.edit', ['resource' => 'orders', 'id' => $order->id]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($customer->trade_name ?: $customer->corporate_name)
            ->assertSee($priceTable->name)
            ->assertSee('Busque por código, nome ou e-mail...')
            ->assertSee('representativeMatches()', false)
            ->assertDontSee('<select id="sales_representative_id"', false)
            ->assertDontSee('>Origem<', false)
            ->assertDontSee('name="source"', false);

        $this->actingAs($this->admin)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($customer->trade_name ?: $customer->corporate_name);
        $this->actingAs($this->admin)->get(route('orders.pdf', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        Mail::fake();
        $this->actingAs($this->admin)->post(route('orders.email', $order))->assertRedirect();
        Mail::assertSent(OrderDocumentMail::class);
        $this->assertDatabaseHas('order_deliveries', ['order_id' => $order->id, 'channel' => 'Email', 'status' => 'Sent']);

        $this->actingAs($this->admin)->post(route('orders.whatsapp', $order))
            ->assertRedirectContains('https://wa.me/');
        $this->assertDatabaseHas('order_deliveries', ['order_id' => $order->id, 'channel' => 'WhatsApp', 'status' => 'Opened']);
        $this->actingAs($this->admin)->get(route('orders.history', $order))
            ->assertOk()
            ->assertSee('Histórico de envios')
            ->assertSee('Compartilhamento aberto');

        $this->actingAs($this->admin)->post(route('orders.duplicate', $order))->assertRedirect();
        $duplicate = Order::query()->where('company_id', $this->admin->company_id)->whereKeyNot($order->id)->latest('id')->firstOrFail();
        $this->assertSame('Draft', $duplicate->status);
        $this->assertSame($order->items()->count(), $duplicate->items()->count());

        $this->actingAs($this->admin)->post(route('orders.cancel', $duplicate))->assertRedirect();
        $this->assertSame('Cancelled', $duplicate->refresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CancelOrder',
            'entity_type' => 'Order',
            'entity_id' => $duplicate->id,
            'entity_label' => $duplicate->order_number,
        ]);

        $this->actingAs($this->admin)->post(route('orders.send', $order))->assertRedirect();
        $this->assertSame('Sent', $order->refresh()->status);
        $this->actingAs($this->admin)->post(route('orders.cancel', $order))->assertUnprocessable();

        $this->actingAs($this->admin)
            ->get('/audit-logs?search='.urlencode($duplicate->order_number))
            ->assertOk()
            ->assertSee('Pedido cancelado')
            ->assertSee('Pedido')
            ->assertSee($duplicate->order_number.' (#'.$duplicate->id.')');
    }

    public function test_admin_can_create_a_user_and_the_action_is_audited(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'Novo Gestor',
            'email' => 'novo.gestor@hydradigital.test',
            'role' => 'Manager',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'novo.gestor@hydradigital.test')->firstOrFail();
        $this->assertSame($this->admin->company_id, $user->company_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'Create', 'entity_id' => $user->id]);
    }

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $this->actingAs($this->admin)
            ->withSession(['two_factor_setup_secret' => $secret])
            ->get('/security')
            ->assertOk()
            ->assertSee('Chave manual')
            ->assertSee('<svg', false);

        $this->actingAs($this->admin)
            ->withSession(['two_factor_setup_secret' => $secret])
            ->post('/security/2fa', ['code' => $google2fa->getCurrentOtp($secret)])
            ->assertRedirect();

        $this->assertTrue($this->admin->refresh()->two_factor_enabled);
        $this->assertSame(1, AuditLog::query()->where('action', 'Enable2FA')->count());
    }

    public function test_non_admin_cannot_manage_users_or_view_audit_logs(): void
    {
        $manager = User::query()->where('role', 'Manager')->firstOrFail();

        $this->actingAs($manager)->get('/users')->assertForbidden();
        $this->actingAs($manager)->get('/audit-logs')->assertOk();
    }

    public function test_sales_representative_is_restricted_to_operational_scope(): void
    {
        $representativeUser = User::query()->where('role', 'SalesRepresentative')->firstOrFail();
        $representative = $representativeUser->salesRepresentative;
        $visiblePriceTable = PriceTable::query()->where('company_id', $representativeUser->company_id)->firstOrFail();
        $representative->priceTables()->sync([$visiblePriceTable->id]);

        $this->actingAs($representativeUser)->get('/products')->assertOk();
        $this->actingAs($representativeUser)->get('/price-tables')->assertNotFound();
        $this->actingAs($representativeUser)
            ->post(route('products.price-tables.store'), ['name' => 'Não permitida'])
            ->assertForbidden();
        $this->actingAs($representativeUser)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Meu perfil')
            ->assertSee('Segurança')
            ->assertDontSee('Integrações')
            ->assertDontSee('Auditoria');
        $this->actingAs($representativeUser)->get('/categories')->assertForbidden();
        $this->actingAs($representativeUser)->get('/regions')->assertForbidden();
        $this->actingAs($representativeUser)->get('/payment-methods')->assertForbidden();
        $this->actingAs($representativeUser)->get('/payment-terms')->assertForbidden();
        $this->actingAs($representativeUser)->get('/crud/products/create')->assertForbidden();
        $this->actingAs($representativeUser)->get('/crud/orders/create')
            ->assertOk()
            ->assertSee($representative->code.' - '.$representativeUser->name)
            ->assertSee($visiblePriceTable->name)
            ->assertDontSee('Busque por código, nome ou e-mail...')
            ->assertDontSee('name="source"', false);

        $this->actingAs($representativeUser)->get('/crud/customers/create')
            ->assertOk()
            ->assertDontSee('name="credit_limit"', false)
            ->assertDontSee('name="price_table_ids[]"', false)
            ->assertDontSee('name="representative_ids[]"', false)
            ->assertSee('Você será definido automaticamente como representante principal');

        $this->actingAs($representativeUser)->post('/crud/customers', [
            'corporate_name' => 'Cliente criado pelo representante',
            'document' => '52998224725',
            'credit_limit' => 999999,
            'price_table_ids' => [$visiblePriceTable->id],
            'representative_ids' => [$representative->id],
        ])->assertSessionHasErrors(['credit_limit', 'price_table_ids', 'representative_ids']);

        $this->actingAs($representativeUser)->post('/crud/customers', [
            'corporate_name' => 'Cliente criado pelo representante',
            'document' => '52998224725',
        ])->assertRedirect(route('customers.index'));

        $createdCustomer = Customer::query()->where('corporate_name', 'Cliente criado pelo representante')->firstOrFail();
        $this->assertNull($createdCustomer->credit_limit);
        $this->assertTrue($createdCustomer->priceTables->isEmpty());
        $this->assertDatabaseHas('customer_representatives', [
            'customer_id' => $createdCustomer->id,
            'sales_representative_id' => $representative->id,
            'is_primary' => true,
        ]);

        $outsideCustomer = Customer::query()->create([
            'company_id' => $representativeUser->company_id,
            'corporate_name' => 'Cliente fora da carteira',
            'document' => '39053344705',
            'active' => true,
            'version' => 1,
        ]);

        $this->actingAs($representativeUser)
            ->get('/customers')
            ->assertOk()
            ->assertDontSee('Cliente fora da carteira');

        $this->actingAs($representativeUser)
            ->get("/crud/customers/{$outsideCustomer->id}/edit")
            ->assertNotFound();

        $assignedCustomer = Customer::query()
            ->whereHas('representatives', fn ($query) => $query->where('sales_representative_id', $representative->id))
            ->firstOrFail();

        $this->actingAs($representativeUser)
            ->get("/crud/customers/{$assignedCustomer->id}/edit")
            ->assertOk();
    }

    public function test_cross_company_resources_are_not_exposed(): void
    {
        $otherCompany = Company::factory()->create();
        $otherCategory = Category::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Categoria externa',
            'active' => true,
        ]);
        $otherPriceTable = PriceTable::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Tabela externa',
            'active' => true,
        ]);
        $otherPaymentMethod = PaymentMethod::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->admin)
            ->get("/crud/categories/{$otherCategory->id}/edit")
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->put("/crud/categories/{$otherCategory->id}", ['name' => 'Tentativa'])
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->patch(route('products.price-tables.update', $otherPriceTable), ['name' => 'Tentativa'])
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->put("/crud/payment-methods/{$otherPaymentMethod->id}", [
                'name' => 'Tentativa externa',
                'code' => 'tentativa-externa',
            ])
            ->assertNotFound();
    }

    public function test_branded_error_pages_are_available(): void
    {
        $this->get('/pagina-inexistente')
            ->assertNotFound()
            ->assertSee('OrderBox')
            ->assertSee('Página não encontrada');

        $representative = User::query()->where('role', 'SalesRepresentative')->firstOrFail();
        $this->actingAs($representative)
            ->get('/regions')
            ->assertForbidden()
            ->assertSee('Permissão insuficiente');
    }

    public function test_location_gateway_proxies_and_caches_external_services(): void
    {
        Http::fake([
            'servicodados.ibge.gov.br/*' => Http::response([
                ['id' => 35, 'sigla' => 'SP', 'nome' => 'São Paulo'],
            ]),
            'viacep.com.br/*' => Http::response([
                'cep' => '01001-000',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308',
            ]),
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('locations.states'))
            ->assertOk()
            ->assertJsonPath('0.sigla', 'SP');

        $this->actingAs($this->admin)
            ->getJson(route('locations.zip-codes', '01001000'))
            ->assertOk()
            ->assertJsonPath('ibge', '3550308');
    }

    public function test_audit_log_page_renders_existing_actions(): void
    {
        AuditLog::query()->create([
            'company_id' => $this->admin->company_id,
            'user_id' => $this->admin->id,
            'action' => 'AccessPolicyReviewed',
            'entity_type' => 'User',
            'entity_id' => $this->admin->id,
            'entity_label' => $this->admin->email,
            'new_values' => ['policy' => 'AdministrativeAccess'],
        ]);

        $this->actingAs($this->admin)
            ->get('/audit-logs')
            ->assertOk()
            ->assertSee('AccessPolicyReviewed')
            ->assertSee($this->admin->email.' (#'.$this->admin->id.')');
    }
}
