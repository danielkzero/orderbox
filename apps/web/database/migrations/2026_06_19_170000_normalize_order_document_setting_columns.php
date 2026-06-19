<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('order_document_settings')
            ->orderBy('id')
            ->eachById(function (object $setting): void {
                $updates = [];

                foreach (['columns', 'print_columns'] as $field) {
                    $normalized = $this->normalize($setting->{$field});

                    if ($normalized !== null) {
                        $updates[$field] = json_encode($normalized);
                    }
                }

                if ($updates !== []) {
                    DB::table('order_document_settings')->where('id', $setting->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        //
    }

    private function normalize(mixed $value): ?array
    {
        for ($attempt = 0; $attempt < 3 && is_string($value); $attempt++) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            $value = $decoded;
        }

        return is_array($value) ? array_values($value) : null;
    }
};
