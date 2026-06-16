<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('image_url', 'like', '%/storage/products/%')
            ->orderBy('id')
            ->select(['id', 'image_url'])
            ->chunk(100, function ($products): void {
                foreach ($products as $product) {
                    $path = parse_url($product->image_url, PHP_URL_PATH);

                    if (! $path || ! str_contains($path, '/storage/products/')) {
                        continue;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['image_url' => ltrim($path, '/')]);
                }
            });
    }

    public function down(): void
    {
        // Caminhos relativos funcionam em qualquer domínio; não há reversão segura para a URL absoluta anterior.
    }
};
