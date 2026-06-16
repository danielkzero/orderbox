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
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Campos no padrao TailAdmin, com foco no cadastro rapido e consistente.</p>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-6 p-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            @if ($resource === 'customers')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="corporate_name" value="Razao social" />
                        <x-text-input id="corporate_name" name="corporate_name" class="mt-1 block w-full" :value="old('corporate_name', $model->corporate_name)" required />
                    </div>
                    <div>
                        <x-input-label for="trade_name" value="Nome fantasia" />
                        <x-text-input id="trade_name" name="trade_name" class="mt-1 block w-full" :value="old('trade_name', $model->trade_name)" />
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
                        <x-input-label for="credit_limit" value="Limite de credito" />
                        <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('credit_limit', $model->credit_limit)" />
                    </div>
                </div>
            @elseif ($resource === 'products')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="sku" value="SKU" />
                        <x-text-input id="sku" name="sku" class="mt-1 block w-full" :value="old('sku', $model->sku)" required />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="category_id" value="Categoria" />
                        <select id="category_id" name="category_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $model->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="brand_id" value="Marca" />
                        <select id="brand_id" name="brand_id" class="{{ $inputClass }}">
                            <option value="">Sem marca</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((int) old('brand_id', $model->brand_id) === $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="unit_id" value="Unidade" />
                        <select id="unit_id" name="unit_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected((int) old('unit_id', $model->unit_id) === $unit->id)>{{ $unit->code }} - {{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="available_stock" value="Estoque disponivel" />
                        <x-text-input id="available_stock" name="available_stock" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="old('available_stock', $model->available_stock)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="short_description" value="Descricao curta" />
                        <textarea id="short_description" name="short_description" rows="3" class="{{ Str::replaceFirst('h-11', 'min-h-28', $inputClass) }}">{{ old('short_description', $model->short_description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'price-tables')
                <div class="space-y-5">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div>
                        <x-input-label for="description" value="Descricao" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
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
                        <x-input-label for="description" value="Descricao" />
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
                        <x-input-label for="description" value="Descricao" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'units')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="code" value="Codigo" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" required />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descricao" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'representatives')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="user_id" value="Usuario" />
                        <select id="user_id" name="user_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) old('user_id', $model->user_id) === $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="code" value="Codigo do representante" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" required />
                    </div>
                </div>
            @elseif ($resource === 'orders')
                @php
                    $firstItem = $model->exists ? $model->items()->first() : null;
                @endphp
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="order_number" value="Numero do pedido" />
                        <x-text-input id="order_number" name="order_number" class="mt-1 block w-full" :value="old('order_number', $model->order_number ?: 'PED-'.now()->format('YmdHis'))" required />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="{{ $inputClass }}" required>
                            @foreach (['Draft' => 'Rascunho', 'Sent' => 'Enviado', 'Approved' => 'Aprovado', 'Cancelled' => 'Cancelado'] as $value => $label)
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
                        <x-input-label for="price_table_id" value="Tabela de preco" />
                        <select id="price_table_id" name="price_table_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($priceTables as $priceTable)
                                <option value="{{ $priceTable->id }}" @selected((int) old('price_table_id', $model->price_table_id) === $priceTable->id)>{{ $priceTable->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="order_date" value="Data do pedido" />
                        <x-text-input id="order_date" name="order_date" type="datetime-local" class="mt-1 block w-full" :value="old('order_date', optional($model->order_date)->format('Y-m-d\\TH:i') ?: now()->format('Y-m-d\\TH:i'))" required />
                    </div>
                    <div>
                        <x-input-label for="source" value="Origem" />
                        <select id="source" name="source" class="{{ $inputClass }}" required>
                            @foreach (['Admin', 'Mobile'] as $source)
                                <option value="{{ $source }}" @selected(old('source', $model->source ?: 'Admin') === $source)>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="product_id" value="Produto" />
                        <select id="product_id" name="product_id" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((int) old('product_id', $firstItem?->product_id) === $product->id)>{{ $product->sku }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="quantity" value="Quantidade" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.001" min="0.001" class="mt-1 block w-full" :value="old('quantity', $firstItem?->quantity ?: 1)" required />
                    </div>
                    <div>
                        <x-input-label for="unit_price" value="Preco unitario" />
                        <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_price', $firstItem?->unit_price ?: 0)" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="notes" value="Observacoes" />
                        <textarea id="notes" name="notes" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('notes', $model->notes) }}</textarea>
                    </div>
                </div>
            @endif

            @if ($editing && $resource !== 'orders')
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-800">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" @checked(old('active', $model->active))>
                    Registro ativo
                </label>
            @endif

            <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route($config['index']) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancelar</a>
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Salvar</button>
            </div>
        </form>
    </x-panel>
</x-app-layout>
