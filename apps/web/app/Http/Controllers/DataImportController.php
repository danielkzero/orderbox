<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDataImportRequest;
use App\Models\ImportBatch;
use App\Services\AuditService;
use App\Services\Import\DataImportService;
use App\Services\Import\DataImportTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataImportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('admin.imports.index', [
            'types' => DataImportTemplateService::TYPES,
            'imports' => ImportBatch::query()
                ->where('company_id', $request->user()->company_id)
                ->with('user')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function template(Request $request, string $type, DataImportTemplateService $templates): BinaryFileResponse
    {
        $this->authorizeAccess($request);
        $path = $templates->create($type);

        return response()->download($path, "orderbox-importacao-{$type}.xlsx")->deleteFileAfterSend();
    }

    public function store(StoreDataImportRequest $request, DataImportService $imports, AuditService $audit): RedirectResponse
    {
        $batch = $imports->queue(
            $request->user(),
            $request->string('type')->toString(),
            $request->file('file'),
        );

        $audit->record($request->user(), 'ImportData', $batch, null, $batch->only([
            'type',
            'original_filename',
            'status',
        ]));

        return redirect()->route('imports.index')->with(
            'status',
            'Importação adicionada à fila. O progresso será atualizado no histórico.',
        );
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['Admin', 'Manager'], true), 403);
    }
}
