<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.api-clients.index', [
            'clients' => ApiClient::query()
                ->where('company_id', $request->user()->company_id)
                ->latest()
                ->paginate(15),
            'plainSecret' => session('plain_api_secret'),
        ]);
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'in:Mobile,Integration'],
        ]);
        $secret = Str::password(40, symbols: false);
        $client = ApiClient::query()->create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'channel' => $data['channel'],
            'client_key' => 'obx_'.Str::random(32),
            'secret_hash' => Hash::make($secret),
            'active' => true,
        ]);
        $audit->record($request->user(), 'CreateApiClient', $client, null, $client->only(['name', 'client_key', 'channel', 'active']));

        return redirect()->route('api-clients.index')->with('plain_api_secret', $secret)->with('status', 'Cliente de API criado. Copie o segredo agora.');
    }

    public function regenerate(Request $request, ApiClient $apiClient, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request, $apiClient);
        $secret = Str::password(40, symbols: false);
        $apiClient->update(['secret_hash' => Hash::make($secret), 'active' => true]);
        $audit->record($request->user(), 'RegenerateApiClientSecret', $apiClient);

        return redirect()->route('api-clients.index')->with('plain_api_secret', $secret)->with('status', 'Segredo regenerado. Copie o novo valor agora.');
    }

    public function deactivate(Request $request, ApiClient $apiClient, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request, $apiClient);
        $apiClient->update(['active' => false]);
        $audit->record($request->user(), 'DeactivateApiClient', $apiClient, ['active' => true], ['active' => false]);

        return back()->with('status', 'Cliente de API bloqueado.');
    }

    private function authorizeAdmin(Request $request, ?ApiClient $client = null): void
    {
        abort_unless($request->user()->role === 'Admin', 403);
        abort_if($client && $client->company_id !== $request->user()->company_id, 404);
    }
}
