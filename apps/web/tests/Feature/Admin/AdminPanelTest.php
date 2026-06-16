<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Models\ApiClient;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
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
            'representative_ids' => [SalesRepresentative::query()->where('company_id', $this->admin->company_id)->firstOrFail()->id],
        ])->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('document', '99999999000199')->firstOrFail();
        $this->assertSame(1, CustomerRepresentative::query()->where('customer_id', $customer->id)->count());
        $this->actingAs($this->admin)->post("/crud/customers/{$customer->id}/deactivate")
            ->assertRedirect();

        $this->assertFalse($customer->refresh()->active);
    }

    public function test_admin_can_create_product_from_references(): void
    {
        $category = Category::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $brand = Brand::query()->where('company_id', $this->admin->company_id)->firstOrFail();
        $unit = Unit::query()->where('company_id', $this->admin->company_id)->firstOrFail();

        $this->actingAs($this->admin)->post('/crud/products', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'sku' => 'TEST-001',
            'name' => 'Produto Teste',
            'short_description' => 'Produto criado pelo teste.',
            'available_stock' => '12.5',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $this->admin->company_id,
            'sku' => 'TEST-001',
            'name' => 'Produto Teste',
        ]);
        $this->assertSame('Produto Teste', Product::query()->where('sku', 'TEST-001')->firstOrFail()->name);
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
