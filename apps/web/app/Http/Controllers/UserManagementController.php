<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use App\Services\Authentication\AuthenticationSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.users.index', [
            'users' => User::query()
                ->where('company_id', $request->user()->company_id)
                ->withCount(['orders'])
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validateUser($request);
        $data['company_id'] = $request->user()->company_id;
        $data['active'] = true;
        $data['email_verified_at'] = now();
        $user = User::query()->create($data);
        $audit->record($request->user(), 'Create', $user, null, $user->only(['name', 'email', 'role', 'active']));

        return redirect()->route('users.index')->with('status', 'Usuário criado.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeAdmin($request, $user);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request, $user);
        $data = $this->validateUser($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $oldValues = $user->only(['name', 'email', 'role', 'active']);
        $user->update($data);
        $audit->record($request->user(), 'Update', $user, $oldValues, $user->only(['name', 'email', 'role', 'active']));

        return redirect()->route('users.index')->with('status', 'Usuário atualizado.');
    }

    public function deactivate(Request $request, User $user, AuthenticationSessionService $sessions, AuditService $audit): RedirectResponse
    {
        $this->authorizeAdmin($request, $user);
        abort_if($user->is($request->user()), 422, 'Você não pode inativar sua própria conta.');
        $user->update(['active' => false]);
        $sessions->revokeAll($user);
        $audit->record($request->user(), 'Deactivate', $user, ['active' => true], ['active' => false]);

        return back()->with('status', 'Usuário inativado e sessões revogadas.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['Admin', 'Manager', 'SalesRepresentative'])],
            'active' => ['sometimes', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function authorizeAdmin(Request $request, ?User $target = null): void
    {
        abort_unless($request->user()->role === 'Admin', 403);
        abort_if($target && $target->company_id !== $request->user()->company_id, 404);
    }
}
