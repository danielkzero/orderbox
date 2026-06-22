<?php

namespace App\Http\Controllers;

use App\Models\PriceTable;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductPriceTableController extends Controller
{
    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        abort_unless($request->user()->isAdministrative(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('price_tables')->where('company_id', $request->user()->company_id),
            ],
        ]);

        $priceTable = PriceTable::query()->create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'active' => true,
        ]);

        $audit->record($request->user(), 'Create', $priceTable, null, $priceTable->toArray());

        return redirect()->route('products.index')->with('status', 'Tabela de preço criada.');
    }

    public function update(Request $request, PriceTable $priceTable, AuditService $audit): RedirectResponse
    {
        abort_unless($request->user()->isAdministrative(), 403);
        abort_unless($priceTable->company_id === $request->user()->company_id, 404);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('price_tables')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($priceTable),
            ],
        ]);
        $oldValues = $priceTable->toArray();

        $priceTable->update(['name' => $data['name']]);
        $audit->record($request->user(), 'Update', $priceTable, $oldValues, $priceTable->fresh()->toArray());

        return redirect()->route('products.index')->with('status', 'Nome da tabela de preço atualizado.');
    }

    public function deactivate(Request $request, PriceTable $priceTable, AuditService $audit): RedirectResponse
    {
        abort_unless($request->user()->isAdministrative(), 403);
        abort_unless($priceTable->company_id === $request->user()->company_id, 404);

        if (! $priceTable->active) {
            return redirect()->route('products.index')->with('status', 'A tabela de preço já está inativa.');
        }

        $oldValues = $priceTable->toArray();
        $priceTable->update(['active' => false]);
        $audit->record($request->user(), 'Deactivate', $priceTable, $oldValues, $priceTable->fresh()->toArray());

        return redirect()->route('products.index')->with('status', 'Tabela de preço inativada.');
    }
}
