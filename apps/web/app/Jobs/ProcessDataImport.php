<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Services\Import\DataImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessDataImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1200;

    public function __construct(public int $importBatchId) {}

    public function handle(DataImportService $imports): void
    {
        $batch = ImportBatch::query()->find($this->importBatchId);

        if (! $batch || in_array($batch->status, ['completed', 'failed'], true)) {
            return;
        }

        $imports->process($batch);
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("import-batch-{$this->importBatchId}"))
                ->dontRelease()
                ->expireAfter(1300),
        ];
    }

    public function failed(Throwable $exception): void
    {
        $batch = ImportBatch::query()->find($this->importBatchId);

        if (! $batch) {
            return;
        }

        if ($batch->storage_path) {
            Storage::delete($batch->storage_path);
        }

        $batch->update([
            'status' => 'failed',
            'storage_path' => null,
            'failed_rows' => 1,
            'errors' => ['A fila não conseguiu concluir a importação. Tente novamente.'],
            'completed_at' => now(),
        ]);
    }
}
