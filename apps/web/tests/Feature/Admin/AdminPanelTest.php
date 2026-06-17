<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
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
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\HydradigitalDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        foreach (['customers', 'products', 'price-tables', 'representatives', 'orders', 'categories', 'brands', 'units', 'regions'] as $path) {
            $this->actingAs($this->admin)->get('/'.$path)->assertOk();
        }
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

        $this->actingAs($this->admin)->post('/crud/customers', [
            'region_id' => Region::query()->where('company_id', $this->admin->company_id)->firstOrFail()->id,
            'corporate_name' => 'Cliente Teste Ltda',
            'trade_name' => 'Cliente Teste',
            'document' => '99999999000199',
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
            'representative_ids' => [SalesRepresentative::query()->where('company_id', $this->admin->company_id)->firstOrFail()->id],
        ])->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('document', '99999999000199')->firstOrFail();
        $this->assertSame(1, CustomerAddress::query()->where('customer_id', $customer->id)->count());
        $this->assertSame(1, CustomerContact::query()->where('customer_id', $customer->id)->count());
        $this->assertSame(1, CustomerRepresentative::query()->where('customer_id', $customer->id)->count());
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
        $region = Region::query()->where('company_id', $this->admin->company_id)->firstOrFail();

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
            'new_price_tables' => [
                ['name' => 'Tabela Produto Teste', 'region_id' => $region->id, 'minimum_quantity' => '5', 'price' => '44.90'],
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
        $createdTable = PriceTable::query()->where('name', 'Tabela Produto Teste')->firstOrFail();
        $this->assertSame($region->id, $createdTable->region_id);
        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'price_table_id' => $createdTable->id,
            'minimum_quantity' => '5.000',
            'price' => '44.90',
        ]);
    }

    public function test_admin_can_manage_price_tables_from_products_datatable(): void
    {
        $region = Region::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $product = Product::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post(route('products.price-tables.store'), [
            'name' => 'Tabela Datatable',
            'region_id' => $region->id,
        ])->assertRedirect();

        $priceTable = PriceTable::query()->where('name', 'Tabela Datatable')->firstOrFail();

        ProductPrice::query()->create([
            'product_id' => $product->id,
            'price_table_id' => $priceTable->id,
            'minimum_quantity' => '3',
            'price' => '77.70',
        ]);

        $this->actingAs($this->admin)->get(route('products.index'))
            ->assertOk()
            ->assertSee('Tabela Datatable')
            ->assertSee('R$ 77,70');

        $this->actingAs($this->admin)->patch(route('products.price-tables.update', $priceTable), [
            'name' => 'Tabela Datatable Editada',
        ])->assertRedirect();

        $this->assertDatabaseHas('price_tables', [
            'id' => $priceTable->id,
            'name' => 'Tabela Datatable Editada',
        ]);
    }

    public function test_admin_can_create_region_and_price_table_products(): void
    {
        $this->actingAs($this->admin)->post('/crud/regions', [
            'name' => 'Vale do Paraiba',
            'state' => 'SP',
            'city' => 'Sao Jose dos Campos',
            'description' => 'Regiao criada pelo teste.',
        ])->assertRedirect(route('regions.index'));

        $region = Region::query()->where('name', 'Vale do Paraiba')->firstOrFail();
        $product = Product::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/price-tables', [
            'region_id' => $region->id,
            'name' => 'Especial Vale',
            'description' => 'Tabela com produto vinculado.',
            'product_prices' => [
                ['product_id' => $product->id, 'minimum_quantity' => '1', 'price' => '99.90'],
                ['product_id' => $product->id, 'minimum_quantity' => '10', 'price' => '89.90'],
            ],
        ])->assertRedirect(route('price-tables.index'));

        $table = PriceTable::query()->where('name', 'Especial Vale')->firstOrFail();
        $this->assertSame($region->id, $table->region_id);
        $this->assertSame(2, ProductPrice::query()->where('price_table_id', $table->id)->count());
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
        ])->assertRedirect(route('representatives.index'));

        $representative = SalesRepresentative::query()->where('code', 'REP-999')->firstOrFail();
        $customer = Customer::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $priceTable = PriceTable::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $product = Product::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/orders', [
            'customer_id' => $customer->id,
            'sales_representative_id' => $representative->id,
            'price_table_id' => $priceTable->id,
            'order_number' => 'PED-TEST-001',
            'status' => 'Sent',
            'order_date' => now()->format('Y-m-d H:i:s'),
            'source' => 'Admin',
            'items' => [
                ['product_id' => $product->id, 'quantity' => '2', 'unit_price' => '15.50', 'discount' => '0'],
                ['product_id' => $product->id, 'quantity' => '1', 'unit_price' => '10.00', 'discount' => '10'],
            ],
            'notes' => 'Pedido criado pelo teste.',
        ])->assertRedirect(route('orders.index'));

        $order = Order::query()->where('order_number', 'PED-TEST-001')->firstOrFail();

        $this->assertSame('41.00', $order->subtotal);
        $this->assertSame('40.00', $order->total_amount);
        $this->assertSame(2, $order->items()->count());

        $this->actingAs($this->admin)->post("/crud/orders/{$order->id}/deactivate")
            ->assertRedirect();

        $this->assertSame('Cancelled', $order->refresh()->status);
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

    public function test_audit_log_page_renders_existing_actions(): void
    {
        AuditLog::query()->create([
            'company_id' => $this->admin->company_id,
            'user_id' => $this->admin->id,
            'action' => 'TemplateInstalled',
            'entity_type' => 'User',
            'entity_id' => $this->admin->id,
            'new_values' => ['template' => 'TailAdmin Laravel'],
        ]);

        $this->actingAs($this->admin)
            ->get('/audit-logs')
            ->assertOk()
            ->assertSee('TemplateInstalled');
    }
}
