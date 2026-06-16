@php
    $editing = $model->exists;
    $title = ($editing ? 'Editar ' : 'Novo ') . $config['label'];
    $action = $editing ? route('crud.update', [$resource, $model->id]) : route('crud.store', $resource);
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
@endphp

<x-app-layout>
    <x-page-header :title="$title" description="Preencha os dados principais e salve para atualizar o cadastro operacional.">
        <x-slot name="actions">
            <a href="{{ route($config['index']) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Voltar
            </a>
        </x-slot>
    </x-page-header>

    <x-panel class="max-w-4xl">
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
                        <textarea id="short_description" name="short_description" rows="3" class="{{ $inputClass }}">{{ old('short_description', $model->short_description) }}</textarea>
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
                        <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $model->description) }}</textarea>
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
                        <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $model->description) }}</textarea>
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
                        <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $model->description) }}</textarea>
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
                        <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @endif

            @if ($editing)
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-800">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" @checked(old('active', $model->active))>
                    Registro ativo
                </label>
            @endif

            <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
                <a href="{{ route($config['index']) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</a>
            </div>
        </form>
    </x-panel>
</x-app-layout>
