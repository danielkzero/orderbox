<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use App\Models\AuditLog;
use App\Models\Company;
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
        foreach (['customers', 'products', 'price-tables', 'representatives', 'orders', 'categories', 'brands', 'units'] as $path) {
            $this->actingAs($this->admin)->get('/'.$path)->assertOk();
        }
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
