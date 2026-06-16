@php
    $editing = $model->exists;
    $title = ($editing ? 'Editar ' : 'Novo ') . $config['label'];
    $action = $editing ? route('crud.update', [$resource, $model->id]) : route('crud.store', $resource);
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
@endphp

<x-app-layout>
    <x-page-header :title="$title" description="Preencha os dados principais e salve para atualizar o cadastro operacional.">
        <x-slot name="actions">
            <a href="{{ route($config['index']) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Voltar
            </a>
        </x-slot>
    </x-page-header>

    <x-panel>
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Campos no padrão TailAdmin, com foco no cadastro rápido e consistente.</p>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-6 p-6" @if ($resource === 'products') enctype="multipart/form-data" @endif>
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            @if ($resource === 'customers')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="corporate_name" value="Razão social" />
                        <x-text-input id="corporate_name" name="corporate_name" class="mt-1 block w-full" :value="old('corporate_name', $model->corporate_name)" required />
                    </div>
                    <div>
                        <x-input-label for="trade_name" value="Nome fantasia" />
                        <x-text-input id="trade_name" name="trade_name" class="mt-1 block w-full" :value="old('trade_name', $model->trade_name)" />
                    </div>
                    <div>
                        <x-input-label for="region_id" value="Região" />
                        <select id="region_id" name="region_id" class="{{ $inputClass }}">
                            <option value="">Sem região</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" @selected((int) old('region_id', $model->region_id) === $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="document" value="Documento" />
                        <x-text-input id="document" name="document" class="mt-1 block w-full" :value="old('document', $model->document)" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="E-mail" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $model->email)" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Telefone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $model->phone)" />
                    </div>
                    <div>
                        <x-input-label for="credit_limit" value="Limite de crédito" />
                        <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('credit_limit', $model->credit_limit)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Representantes vinculados" />
                        <div class="grid gap-3 rounded-2xl border border-gray-200 p-4 dark:border-gray-800 sm:grid-cols-2 lg:grid-cols-3">
                            @php
                                $linkedRepresentatives = collect(old('representative_ids', $model->exists ? $model->representatives->pluck('sales_representative_id')->all() : []))->map(fn ($id) => (int) $id);
                                $primaryRepresentative = (int) old('primary_representative_id', $model->exists ? $model->representatives->firstWhere('is_primary', true)?->sales_representative_id : null);
                            @endphp
                            @foreach ($representatives as $representative)
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                    <input type="checkbox" name="representative_ids[]" value="{{ $representative->id }}" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" @checked($linkedRepresentatives->contains($representative->id))>
                                    <span>{{ $representative->code }} - {{ $representative->user->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="primary_representative_id" value="Representante principal" />
                        <select id="primary_representative_id" name="primary_representative_id" class="{{ $inputClass }}">
                            <option value="">Selecione</option>
                            @foreach ($representatives as $representative)
                                <option value="{{ $representative->id }}" @selected($primaryRepresentative === $representative->id)>{{ $representative->code }} - {{ $representative->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @elseif ($resource === 'products')
                <div class="space-y-6" x-data="{
                    imageUrl: @js(old('image_url', $model->image_url)),
                    previewUrl: @js(old('image_url', $model->image_url)),
                    isDragging: false,
                    setFile(file) {
                        if (!file) return;
                        this.previewUrl = URL.createObjectURL(file);
                    }
                }">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Descrição do produto</h3>
                        </div>
                        <div class="grid gap-5 p-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="name" value="Nome do produto" />
                                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                            </div>
                            <div>
                                <x-input-label for="sku" value="SKU" />
                                <x-text-input id="sku" name="sku" class="mt-1 block w-full" :value="old('sku', $model->sku)" required />
                            </div>
                            <div>
                                <x-input-label for="barcode" value="Código de barras" />
                                <x-text-input id="barcode" name="barcode" class="mt-1 block w-full" :value="old('barcode', $model->barcode)" />
                            </div>
                            <div>
                                <x-input-label for="category_id" value="Categoria" />
                                <select id="category_id" name="category_id" class="{{ $inputClass }}" required>
                                    <option value="">Selecione a categoria</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $model->category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="brand_id" value="Marca" />
                                <select id="brand_id" name="brand_id" class="{{ $inputClass }}">
                                    <option value="">Selecione a marca</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected((int) old('brand_id', $model->brand_id) === $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="color" value="Cor" />
                                <x-text-input id="color" name="color" class="mt-1 block w-full" :value="old('color', $model->color)" placeholder="Preto, branco, azul..." />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="short_description" value="Descrição curta" />
                                <textarea id="short_description" name="short_description" rows="3" class="{{ Str::replaceFirst('h-11', 'min-h-28', $inputClass) }}">{{ old('short_description', $model->short_description) }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="description" value="Descrição completa" />
                                <textarea id="description" name="description" rows="5" class="{{ Str::replaceFirst('h-11', 'min-h-40', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Preço e disponibilidade</h3>
                        </div>
                        <div class="grid gap-5 p-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="base_price" value="Preço base" />
                                <x-text-input id="base_price" name="base_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('base_price', $model->base_price)" />
                            </div>
                            <div>
                                <x-input-label for="unit_id" value="Unidade" />
                                <select id="unit_id" name="unit_id" class="{{ $inputClass }}" required>
                                    <option value="">Selecione a unidade</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected((int) old('unit_id', $model->unit_id) === $unit->id)>{{ $unit->code }} - {{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="available_stock" value="Quantidade em estoque" />
                                <x-text-input id="available_stock" name="available_stock" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="old('available_stock', $model->available_stock)" />
                            </div>
                            <div>
                                <x-input-label for="stock_status" value="Status de disponibilidade" />
                                <select id="stock_status" name="stock_status" class="{{ $inputClass }}" required>
                                    <option value="InStock" @selected(old('stock_status', $model->stock_status ?: 'InStock') === 'InStock')>Em estoque</option>
                                    <option value="LowStock" @selected(old('stock_status', $model->stock_status) === 'LowStock')>Estoque baixo</option>
                                    <option value="OutOfStock" @selected(old('stock_status', $model->stock_status) === 'OutOfStock')>Sem estoque</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Dimensões e logística</h3>
                        </div>
                        <div class="grid gap-5 p-5 md:grid-cols-4">
                            <div>
                                <x-input-label for="weight_kg" value="Peso (kg)" />
                                <x-text-input id="weight_kg" name="weight_kg" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="old('weight_kg', $model->weight_kg)" />
                            </div>
                            <div>
                                <x-input-label for="length_cm" value="Comprimento (cm)" />
                                <x-text-input id="length_cm" name="length_cm" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('length_cm', $model->length_cm)" />
                            </div>
                            <div>
                                <x-input-label for="width_cm" value="Largura (cm)" />
                                <x-text-input id="width_cm" name="width_cm" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('width_cm', $model->width_cm)" />
                            </div>
                            <div>
                                <x-input-label for="height_cm" value="Altura (cm)" />
                                <x-text-input id="height_cm" name="height_cm" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('height_cm', $model->height_cm)" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Imagem do produto</h3>
                        </div>
                        <div class="grid gap-5 p-5 lg:grid-cols-[360px_1fr]">
                            <label
                                for="image"
                                class="group flex min-h-60 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-5 text-center transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-500/60 dark:hover:bg-brand-500/10"
                                :class="{ 'border-brand-400 bg-brand-50 dark:border-brand-500 dark:bg-brand-500/10': isDragging }"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="isDragging = false; const file = $event.dataTransfer.files[0]; if (file) { $refs.imageInput.files = $event.dataTransfer.files; setFile(file); }"
                            >
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Prévia do produto" class="max-h-52 w-full rounded-xl object-cover">
                                </template>
                                <template x-if="!previewUrl">
                                    <div>
                                        <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-white text-gray-500 shadow-theme-xs dark:bg-gray-800 dark:text-gray-400">
                                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M12 16V4m0 0-4 4m4-4 4 4" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M20 16.5V19a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2.5" stroke-linecap="round" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clique para enviar ou arraste a imagem</p>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG, WEBP ou GIF até 2 MB</p>
                                    </div>
                                </template>
                                <input id="image" name="image" type="file" accept="image/*" class="sr-only" x-ref="imageInput" @change="setFile($event.target.files[0])">
                            </label>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="image_url" value="URL externa da imagem" />
                                    <x-text-input id="image_url" name="image_url" x-model="imageUrl" @input="previewUrl = imageUrl" type="url" class="mt-1 block w-full" :value="old('image_url', $model->image_url)" placeholder="https://exemplo.com/produto.jpg" />
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">A dropzone salva a imagem no armazenamento público do Laravel. A URL externa é opcional e pode ser usada quando a imagem já estiver hospedada.</p>
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                <x-input-error :messages="$errors->get('image_url')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="active" value="0">
                </div>
            @elseif ($resource === 'price-tables')
                @php
                    $priceRows = collect(old('product_prices', $model->exists ? $model->prices->map(fn ($price) => [
                        'product_id' => $price->product_id,
                        'minimum_quantity' => $price->minimum_quantity,
                        'price' => $price->price,
                    ])->values()->all() : []));
                    if ($priceRows->isEmpty()) {
                        $priceRows = collect([['product_id' => '', 'minimum_quantity' => 1, 'price' => '']]);
                    }
                @endphp
                <div class="space-y-5" x-data="{ rows: @js($priceRows->values()) }">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="region_id" value="Região" />
                        <select id="region_id" name="region_id" class="{{ $inputClass }}">
                            <option value="">Sem região específica</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" @selected((int) old('region_id', $model->region_id) === $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white/90">Produtos e preços</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cadastre uma ou mais faixas por produto.</p>
                            </div>
                            <button type="button" @click="rows.push({ product_id: '', minimum_quantity: 1, price: '' })" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">Adicionar preço</button>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="grid gap-4 p-5 md:grid-cols-[1fr_160px_180px_80px]">
                                    <div>
                                        <x-input-label value="Produto" />
                                        <select :name="`product_prices[${index}][product_id]`" x-model="row.product_id" class="{{ $inputClass }}">
                                            <option value="">Selecione</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label value="Qtd. mínima" />
                                        <input type="number" step="0.001" min="0.001" :name="`product_prices[${index}][minimum_quantity]`" x-model="row.minimum_quantity" class="{{ $inputClass }}">
                                    </div>
                                    <div>
                                        <x-input-label value="Preço" />
                                        <input type="number" step="0.01" min="0" :name="`product_prices[${index}][price]`" x-model="row.price" class="{{ $inputClass }}">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="rows.splice(index, 1)" class="rounded-lg border border-error-200 px-3 py-2.5 text-sm font-medium text-error-600">Remover</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            @elseif ($resource === 'categories')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="parent_id" value="Categoria pai" />
                        <select id="parent_id" name="parent_id" class="{{ $inputClass }}">
                            <option value="">Sem categoria pai</option>
                            @foreach ($categories->where('id', '!=', $model->id) as $category)
                                <option value="{{ $category->id }}" @selected((int) old('parent_id', $model->parent_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'brands')
                <div class="space-y-5">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'units')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="code" value="Código" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" required />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'regions')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="state" value="UF" />
                        <x-text-input id="state" name="state" maxlength="2" class="mt-1 block w-full uppercase" :value="old('state', $model->state)" />
                    </div>
                    <div>
                        <x-input-label for="city" value="Cidade" />
                        <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $model->city)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'representatives')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="user_id" value="Usuário" />
                        <select id="user_id" name="user_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) old('user_id', $model->user_id) === $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="code" value="Código do representante" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="region_id" value="Região" />
                        <select id="region_id" name="region_id" class="{{ $inputClass }}">
                            <option value="">Sem região</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" @selected((int) old('region_id', $model->region_id) === $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @elseif ($resource === 'orders')
                @php
                    $orderRows = collect(old('items', $model->exists ? $model->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => collect($item->discounts)->first()['value'] ?? 0,
                    ])->values()->all() : []));
                    if ($orderRows->isEmpty()) {
                        $orderRows = collect([['product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0]]);
                    }
                @endphp
                <div class="space-y-6" x-data="{
                    items: @js($orderRows->values()),
                    addItem() { this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount: 0 }) },
                    removeItem(index) { this.items.splice(index, 1) },
                    lineTotal(item) {
                        const subtotal = Number(item.quantity || 0) * Number(item.unit_price || 0);
                        return Math.max(0, subtotal - (subtotal * (Number(item.discount || 0) / 100)));
                    },
                    subtotal() { return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0) },
                    total() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0) },
                    money(value) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0) }
                }">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="order_number" value="Número do pedido" />
                        <x-text-input id="order_number" name="order_number" class="mt-1 block w-full" :value="old('order_number', $model->order_number ?: 'PED-'.now()->format('YmdHis'))" required />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="{{ $inputClass }}" required>
                            @foreach (['Draft' => 'Rascunho', 'Sent' => 'Enviado', 'Cancelled' => 'Cancelado'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $model->status ?: 'Draft') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="customer_id" value="Cliente" />
                        <select id="customer_id" name="customer_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((int) old('customer_id', $model->customer_id) === $customer->id)>{{ $customer->trade_name ?: $customer->corporate_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="sales_representative_id" value="Representante" />
                        <select id="sales_representative_id" name="sales_representative_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($representatives as $representative)
                                <option value="{{ $representative->id }}" @selected((int) old('sales_representative_id', $model->sales_representative_id) === $representative->id)>{{ $representative->code }} - {{ $representative->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="price_table_id" value="Tabela de preço" />
                        <select id="price_table_id" name="price_table_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($priceTables as $priceTable)
                                <option value="{{ $priceTable->id }}" @selected((int) old('price_table_id', $model->price_table_id) === $priceTable->id)>{{ $priceTable->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="order_date" value="Data do pedido" />
                        <x-text-input id="order_date" name="order_date" data-datepicker class="mt-1 block w-full" :value="old('order_date', optional($model->order_date)->format('Y-m-d') ?: now()->format('Y-m-d'))" required />
                    </div>
                    <div>
                        <x-input-label for="source" value="Origem" />
                        <select id="source" name="source" class="{{ $inputClass }}" required>
                            @foreach (['Admin' => 'Admin', 'Mobile' => 'APP'] as $source => $label)
                                <option value="{{ $source }}" @selected(old('source', $model->source ?: 'Admin') === $source)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="notes" value="Observações" />
                        <textarea id="notes" name="notes" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('notes', $model->notes) }}</textarea>
                    </div>
                </div>
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                                <thead class="bg-gray-50 text-left text-theme-xs font-medium text-gray-500 dark:bg-white/[0.02]">
                                    <tr>
                                        <th class="px-5 py-4">S. No.</th>
                                        <th class="px-5 py-4">Produto</th>
                                        <th class="px-5 py-4">Quantidade</th>
                                        <th class="px-5 py-4">Preço unitário</th>
                                        <th class="px-5 py-4">Desconto</th>
                                        <th class="px-5 py-4">Total</th>
                                        <th class="px-5 py-4"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-5 py-4" x-text="index + 1"></td>
                                            <td class="min-w-[280px] px-5 py-4">
                                                <select :name="`items[${index}][product_id]`" x-model="item.product_id" class="{{ $inputClass }}" required>
                                                    <option value="">Selecione</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-5 py-4"><input type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`" x-model="item.quantity" class="{{ $inputClass }} min-w-28" required></td>
                                            <td class="px-5 py-4"><input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="{{ $inputClass }} min-w-32" required></td>
                                            <td class="px-5 py-4"><input type="number" step="0.01" min="0" max="100" :name="`items[${index}][discount]`" x-model="item.discount" class="{{ $inputClass }} min-w-28"></td>
                                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90" x-text="money(lineTotal(item))"></td>
                                            <td class="px-5 py-4 text-right"><button type="button" @click="removeItem(index)" class="font-medium text-error-600">Remover</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="grid gap-4 lg:grid-cols-[1fr_260px] lg:items-end">
                            <div>
                                <button type="button" @click="addItem()" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Adicionar produto</button>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Adicione quantos itens forem necessários. Os totais são recalculados pelo servidor ao salvar.</p>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span>Sub Total</span><strong x-text="money(subtotal())"></strong></div>
                                <div class="flex justify-between text-gray-500"><span>Descontos</span><span x-text="money(subtotal() - total())"></span></div>
                                <div class="flex justify-between text-lg font-semibold text-gray-800 dark:text-white/90"><span>Total</span><span x-text="money(total())"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($editing && ! in_array($resource, ['orders', 'products'], true))
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-800">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" @checked(old('active', $model->active))>
                    Registro ativo
                </label>
            @endif

            <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route($config['index']) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancelar</a>
                @if ($resource === 'products')
                    <button name="active" value="0" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Rascunho</button>
                    <button name="active" value="1" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Publicar produto</button>
                @else
                    <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Salvar</button>
                @endif
            </div>
        </form>
    </x-panel>
</x-app-layout>
