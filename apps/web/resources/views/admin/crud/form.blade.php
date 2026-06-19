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
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revise as informações abaixo e salve quando estiver tudo certo.</p>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-6 p-6" @if ($resource === 'products') enctype="multipart/form-data" @endif>
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            @if ($resource === 'customers')
                @php
                    $addressRows = collect(old('addresses', $model->exists ? $model->addresses->map(fn ($address) => [
                        'id' => $address->id,
                        'type' => $address->type,
                        'zip_code' => $address->zip_code,
                        'street' => $address->street,
                        'number' => $address->number,
                        'complement' => $address->complement,
                        'district' => $address->district,
                        'city' => $address->city,
                        'state' => $address->state,
                        'municipality_ibge_code' => $address->municipality_ibge_code,
                        'country' => $address->country,
                        'default_address' => (bool) $address->default_address,
                    ])->values()->all() : []))->values();
                    $contactRows = collect(old('contacts', $model->exists ? $model->contacts->map(fn ($contact) => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'position' => $contact->position,
                        'department' => $contact->department,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'mobile' => $contact->mobile,
                        'whatsapp' => $contact->whatsapp,
                        'primary_contact' => (bool) $contact->primary_contact,
                        'active' => (bool) $contact->active,
                    ])->values()->all() : []))->values();
                    $linkedRepresentatives = collect(old('representative_ids', $model->exists ? $model->representatives->pluck('sales_representative_id')->all() : []))->map(fn ($id) => (int) $id);
                    $primaryRepresentative = (int) old('primary_representative_id', $model->exists ? $model->representatives->firstWhere('is_primary', true)?->sales_representative_id : null);
                    $linkedPriceTables = collect(old('price_table_ids', $model->exists ? $model->priceTables->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
                    $representativeOptions = $representatives->map(fn ($representative) => [
                        'id' => $representative->id,
                        'label' => $representative->code.' - '.$representative->user->name,
                        'search' => strtolower($representative->code.' '.$representative->user->name.' '.$representative->user->email),
                    ])->values();
                    $priceTableOptions = $priceTables->map(fn ($priceTable) => [
                        'id' => $priceTable->id,
                        'label' => $priceTable->name.($priceTable->region ? ' · '.$priceTable->region->name : ' · Todas as regiões'),
                    ])->values();
                @endphp
                <div class="space-y-6" x-data="{
                    addresses: @js($addressRows),
                    contacts: @js($contactRows),
                    representatives: @js($representativeOptions),
                    selectedRepresentativeIds: @js($linkedRepresentatives->values()),
                    representativeSearch: '',
                    priceTables: @js($priceTableOptions),
                    selectedPriceTableIds: @js($linkedPriceTables->values()),
                    priceTableSearch: '',
                    addAddress() {
                        this.addresses.push({ type: 'Entrega', zip_code: '', street: '', number: '', complement: '', district: '', city: '', state: '', municipality_ibge_code: '', country: 'Brasil', default_address: this.addresses.length === 0 });
                    },
                    removeAddress(index) {
                        this.addresses.splice(index, 1);
                    },
                    addContact() {
                        this.contacts.push({ name: '', position: '', department: '', email: '', phone: '', mobile: '', whatsapp: '', primary_contact: this.contacts.length === 0, active: true });
                    },
                    removeContact(index) {
                        this.contacts.splice(index, 1);
                    },
                    async fetchZip(row) {
                        const zip = String(row.zip_code || '').replace(/\D/g, '');
                        if (zip.length !== 8) return;

                        const response = await fetch(`{{ url('/locations/zip-codes') }}/${zip}`, { headers: { Accept: 'application/json' } });
                        if (! response.ok) return;
                        const data = await response.json();
                        if (data.erro) return;

                        row.zip_code = data.cep || row.zip_code;
                        row.street = data.logradouro || row.street;
                        row.district = data.bairro || row.district;
                        row.city = data.localidade || row.city;
                        row.state = data.uf || row.state;
                        row.municipality_ibge_code = data.ibge || row.municipality_ibge_code;
                        row.country = 'Brasil';
                    },
                    representativeMatches() {
                        const term = this.representativeSearch.toLowerCase();
                        return this.representatives.filter((representative) => ! this.selectedRepresentativeIds.includes(representative.id) && representative.search.includes(term)).slice(0, 8);
                    },
                    selectedRepresentatives() {
                        return this.representatives.filter((representative) => this.selectedRepresentativeIds.includes(representative.id));
                    },
                    addRepresentative(id) {
                        if (! this.selectedRepresentativeIds.includes(id)) this.selectedRepresentativeIds.push(id);
                        this.representativeSearch = '';
                    },
                    removeRepresentative(id) {
                        this.selectedRepresentativeIds = this.selectedRepresentativeIds.filter((selectedId) => selectedId !== id);
                    },
                    priceTableMatches() {
                        const term = this.priceTableSearch.toLowerCase();
                        return this.priceTables.filter((table) => ! this.selectedPriceTableIds.includes(table.id) && table.label.toLowerCase().includes(term)).slice(0, 8);
                    },
                    selectedPriceTables() {
                        return this.priceTables.filter((table) => this.selectedPriceTableIds.includes(table.id));
                    },
                    addPriceTable(id) {
                        if (! this.selectedPriceTableIds.includes(id)) this.selectedPriceTableIds.push(id);
                        this.priceTableSearch = '';
                    },
                    removePriceTable(id) {
                        this.selectedPriceTableIds = this.selectedPriceTableIds.filter((selectedId) => selectedId !== id);
                    }
                }">
                    @if ($model->exists)
                        <input type="hidden" name="version" value="{{ $model->version }}">
                    @endif
                    <datalist id="customer-address-types">
                        @foreach ($addressTypes as $type)
                            <option value="{{ $type }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="customer-contact-positions">
                        @foreach ($contactPositions as $position)
                            <option value="{{ $position }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="customer-contact-departments">
                        @foreach ($contactDepartments as $department)
                            <option value="{{ $department }}"></option>
                        @endforeach
                    </datalist>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Dados principais</h3>
                        </div>
                        <div class="grid gap-5 p-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="corporate_name" value="Razão social" />
                                <x-text-input id="corporate_name" name="corporate_name" class="mt-1 block w-full" :value="old('corporate_name', $model->corporate_name)" required />
                            </div>
                            <div>
                                <x-input-label for="trade_name" value="Nome fantasia" />
                                <x-text-input id="trade_name" name="trade_name" class="mt-1 block w-full" :value="old('trade_name', $model->trade_name)" />
                            </div>
                            <div>
                                <x-input-label for="document" value="CPF ou CNPJ" />
                                <x-text-input
                                    id="document"
                                    name="document"
                                    class="mt-1 block w-full uppercase"
                                    :value="old('document', $model->document)"
                                    maxlength="20"
                                    placeholder="12.ABC.345/01DE-35"
                                    oninput="this.value = this.value.toUpperCase()"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Aceita CPF, CNPJ numérico e CNPJ alfanumérico. Pontuação é opcional.</p>
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
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white/90">Endereços</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use o CEP para preencher rua, bairro, cidade e UF. Número e complemento ficam manuais.</p>
                            </div>
                            <button type="button" @click="addAddress()" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                + Endereço
                            </button>
                        </div>

                        <div class="space-y-4 p-5">
                            <template x-for="(address, index) in addresses" :key="index">
                                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                    <input type="hidden" :name="`addresses[${index}][id]`" :value="address.id || ''">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 dark:text-white/90">Endereço <span x-text="index + 1"></span></h4>
                                        <button type="button" @click="removeAddress(index)" class="text-sm font-medium text-error-600">Remover</button>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-4">
                                        <div>
                                            <x-input-label value="Tipo" />
                                            <input list="customer-address-types" :name="`addresses[${index}][type]`" x-model="address.type" class="{{ $inputClass }}" placeholder="Entrega, cobrança..." required>
                                        </div>
                                        <div>
                                            <x-input-label value="CEP" />
                                            <input :name="`addresses[${index}][zip_code]`" x-model="address.zip_code" @blur="fetchZip(address)" class="{{ $inputClass }}" placeholder="00000-000" required>
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label value="Rua" />
                                            <input :name="`addresses[${index}][street]`" x-model="address.street" class="{{ $inputClass }}" required>
                                        </div>
                                        <div>
                                            <x-input-label value="Número" />
                                            <input :name="`addresses[${index}][number]`" x-model="address.number" class="{{ $inputClass }}" required>
                                        </div>
                                        <div>
                                            <x-input-label value="Complemento" />
                                            <input :name="`addresses[${index}][complement]`" x-model="address.complement" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="Bairro" />
                                            <input :name="`addresses[${index}][district]`" x-model="address.district" class="{{ $inputClass }}" required>
                                        </div>
                                        <div>
                                            <x-input-label value="Cidade" />
                                            <input :name="`addresses[${index}][city]`" x-model="address.city" class="{{ $inputClass }}" required>
                                            <input type="hidden" :name="`addresses[${index}][municipality_ibge_code]`" x-model="address.municipality_ibge_code">
                                        </div>
                                        <div>
                                            <x-input-label value="UF" />
                                            <input :name="`addresses[${index}][state]`" x-model="address.state" maxlength="2" class="{{ $inputClass }} uppercase" required>
                                        </div>
                                        <div>
                                            <x-input-label value="País" />
                                            <input :name="`addresses[${index}][country]`" x-model="address.country" class="{{ $inputClass }}">
                                        </div>
                                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300 md:col-span-2">
                                            <input type="hidden" :name="`addresses[${index}][default_address]`" value="0">
                                            <input type="checkbox" :name="`addresses[${index}][default_address]`" value="1" x-model="address.default_address" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            Endereço padrão
                                        </label>
                                    </div>
                                </div>
                            </template>

                            <div x-show="addresses.length === 0" class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Nenhum endereço adicionado.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white/90">Contatos</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cargos e departamentos sugerem valores já usados nos clientes da empresa.</p>
                            </div>
                            <button type="button" @click="addContact()" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                                + Contato
                            </button>
                        </div>

                        <div class="space-y-4 p-5">
                            <template x-for="(contact, index) in contacts" :key="index">
                                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                    <input type="hidden" :name="`contacts[${index}][id]`" :value="contact.id || ''">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 dark:text-white/90">Contato <span x-text="index + 1"></span></h4>
                                        <button type="button" @click="removeContact(index)" class="text-sm font-medium text-error-600">Remover</button>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div>
                                            <x-input-label value="Nome" />
                                            <input :name="`contacts[${index}][name]`" x-model="contact.name" class="{{ $inputClass }}" required>
                                        </div>
                                        <div>
                                            <x-input-label value="Cargo" />
                                            <input list="customer-contact-positions" :name="`contacts[${index}][position]`" x-model="contact.position" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="Departamento" />
                                            <input list="customer-contact-departments" :name="`contacts[${index}][department]`" x-model="contact.department" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="E-mail" />
                                            <input type="email" :name="`contacts[${index}][email]`" x-model="contact.email" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="Telefone" />
                                            <input :name="`contacts[${index}][phone]`" x-model="contact.phone" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="Celular" />
                                            <input :name="`contacts[${index}][mobile]`" x-model="contact.mobile" class="{{ $inputClass }}">
                                        </div>
                                        <div>
                                            <x-input-label value="WhatsApp" />
                                            <input :name="`contacts[${index}][whatsapp]`" x-model="contact.whatsapp" class="{{ $inputClass }}">
                                        </div>
                                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                            <input type="hidden" :name="`contacts[${index}][primary_contact]`" value="0">
                                            <input type="checkbox" :name="`contacts[${index}][primary_contact]`" value="1" x-model="contact.primary_contact" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            Contato principal
                                        </label>
                                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                            <input type="hidden" :name="`contacts[${index}][active]`" value="0">
                                            <input type="checkbox" :name="`contacts[${index}][active]`" value="1" x-model="contact.active" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            Contato ativo
                                        </label>
                                    </div>
                                </div>
                            </template>

                            <div x-show="contacts.length === 0" class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Nenhum contato adicionado.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Representantes</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Busque e adicione somente os representantes necessários para este cliente.</p>
                        </div>
                        <div class="space-y-5 p-5">
                            <template x-for="id in selectedRepresentativeIds" :key="`representative-${id}`">
                                <input type="hidden" name="representative_ids[]" :value="id">
                            </template>

                            <div class="relative">
                                <x-input-label value="Buscar representante" />
                                <input x-model="representativeSearch" class="{{ $inputClass }}" placeholder="Digite código, nome ou e-mail do representante...">
                                <div x-show="representativeSearch.length > 0" x-cloak class="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                    <template x-for="representative in representativeMatches()" :key="representative.id">
                                        <button type="button" @click="addRepresentative(representative.id)" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="representative.label"></span>
                                            <span class="text-brand-500">Adicionar</span>
                                        </button>
                                    </template>
                                    <div x-show="representativeMatches().length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Nenhum representante encontrado.</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <template x-for="representative in selectedRepresentatives()" :key="`selected-representative-${representative.id}`">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <span x-text="representative.label"></span>
                                        <button type="button" @click="removeRepresentative(representative.id)" class="text-brand-500 hover:text-brand-700">x</button>
                                    </span>
                                </template>
                                <span x-show="selectedRepresentativeIds.length === 0" class="text-sm text-gray-500 dark:text-gray-400">Nenhum representante vinculado.</span>
                            </div>

                            <div>
                                <x-input-label for="primary_representative_id" value="Representante principal" />
                                <select id="primary_representative_id" name="primary_representative_id" class="{{ $inputClass }}">
                                    <option value="">Selecione</option>
                                    <template x-for="representative in selectedRepresentatives()" :key="`primary-representative-${representative.id}`">
                                        <option :value="representative.id" x-text="representative.label" :selected="representative.id === {{ $primaryRepresentative ?: 'null' }}"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Tabelas habilitadas ao cliente</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estas tabelas têm prioridade maior que as tabelas habilitadas pela região do endereço padrão.</p>
                        </div>
                        <div class="space-y-5 p-5">
                            <template x-for="id in selectedPriceTableIds" :key="`price-table-${id}`">
                                <input type="hidden" name="price_table_ids[]" :value="id">
                            </template>

                            <div class="relative">
                                <x-input-label value="Buscar tabela de preço" />
                                <input x-model="priceTableSearch" class="{{ $inputClass }}" placeholder="Digite o nome da tabela de preço...">
                                <div x-show="priceTableSearch.length > 0" x-cloak class="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                    <template x-for="table in priceTableMatches()" :key="table.id">
                                        <button type="button" @click="addPriceTable(table.id)" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="table.label"></span>
                                            <span class="text-brand-500">Adicionar</span>
                                        </button>
                                    </template>
                                    <div x-show="priceTableMatches().length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Nenhuma tabela encontrada.</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <template x-for="table in selectedPriceTables()" :key="`selected-price-table-${table.id}`">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <span x-text="table.label"></span>
                                        <button type="button" @click="removePriceTable(table.id)" class="text-brand-500 hover:text-brand-700">x</button>
                                    </span>
                                </template>
                                <span x-show="selectedPriceTableIds.length === 0" class="text-sm text-gray-500 dark:text-gray-400">Nenhuma tabela direta habilitada.</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($resource === 'products')
                @php
                    $currentProductPrices = $model->exists
                        ? $model->prices->sortBy('minimum_quantity')->groupBy('price_table_id')
                        : collect();
                    $tablePriceRows = collect(old('table_prices', $priceTables->values()->map(function ($priceTable) use ($currentProductPrices) {
                        $price = $currentProductPrices->get($priceTable->id)?->first();

                        return [
                            'price_table_id' => $priceTable->id,
                            'minimum_quantity' => $price?->minimum_quantity ?? 1,
                            'price' => $price?->price,
                        ];
                    })->all()));
                @endphp
                <div class="space-y-6" x-data="{
                    imageUrl: @js(old('image_url', str_starts_with((string) $model->image_url, 'http') ? $model->image_url : null)),
                    previewUrl: @js(old('image_url', $model->imageSrc())),
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
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white/90">Preços de tabela</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Edite os preços do produto nas tabelas existentes. Novas tabelas e renomeações são gerenciadas no cabeçalho da lista de Produtos.</p>
                            </div>
                        </div>

                        <div class="space-y-5 p-5">
                            @if ($priceTables->isNotEmpty())
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($priceTables as $index => $priceTable)
                                        @php
                                            $row = $tablePriceRows->firstWhere('price_table_id', $priceTable->id) ?? [
                                                'price_table_id' => $priceTable->id,
                                                'minimum_quantity' => 1,
                                                'price' => null,
                                            ];
                                        @endphp
                                        <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                            <div class="mb-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-medium text-gray-800 dark:text-white/90">{{ $priceTable->name }}</p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $priceTable->region?->name ?? 'Todas as regiões' }}</p>
                                                </div>
                                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Tabela</span>
                                            </div>
                                            <input type="hidden" name="table_prices[{{ $index }}][price_table_id]" value="{{ $priceTable->id }}">
                                            <div class="grid gap-3 sm:grid-cols-[120px_1fr]">
                                                <div>
                                                    <x-input-label value="Qtd. mínima" />
                                                    <input type="number" step="0.001" min="0.001" name="table_prices[{{ $index }}][minimum_quantity]" value="{{ old("table_prices.$index.minimum_quantity", $row['minimum_quantity'] ?? 1) }}" class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <x-input-label value="Preço" />
                                                    <div class="mt-1 flex">
                                                        <span class="inline-flex h-11 items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">R$</span>
                                                        <input type="number" step="0.01" min="0" name="table_prices[{{ $index }}][price]" value="{{ old("table_prices.$index.price", $row['price'] ?? '') }}" class="{{ Str::replaceFirst('rounded-lg', 'rounded-r-lg', $inputClass) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Nenhuma tabela de preço cadastrada. Use o botão + ao lado de Preço na lista de Produtos.
                                </div>
                            @endif
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
                @php
                    $regionMunicipalities = collect(old('municipalities', $model->exists ? $model->municipalities->map(fn ($municipality) => [
                        'ibge_code' => $municipality->ibge_code,
                        'name' => $municipality->name,
                        'state' => $municipality->state,
                        'microregion_name' => $municipality->microregion_name,
                        'mesoregion_name' => $municipality->mesoregion_name,
                    ])->values()->all() : []))->values();
                    $selectedRegionPriceTables = collect(old(
                        'price_table_ids',
                        $model->exists ? $model->priceTables->pluck('id')->all() : [],
                    ))->map(fn ($id) => (int) $id);
                @endphp
                <div class="space-y-6" x-data="{
                    states: [],
                    municipalities: [],
                    selectedMunicipalities: @js($regionMunicipalities),
                    selectedState: @js(old('state', $model->state)),
                    coverageType: @js(old('coverage_type', $model->coverage_type ?: 'municipalities')),
                    municipalitySearch: '',
                    loadingLocations: false,
                    async init() {
                        await this.loadStates();
                        if (this.selectedState) await this.loadMunicipalities();
                    },
                    async loadStates() {
                        const response = await fetch('{{ route('locations.states') }}', { headers: { Accept: 'application/json' } });
                        if (! response.ok) return;
                        this.states = await response.json();
                    },
                    async loadMunicipalities() {
                        if (! this.selectedState) return;
                        this.loadingLocations = true;
                        this.municipalitySearch = '';
                        const response = await fetch(`{{ url('/locations/states') }}/${this.selectedState}/municipalities`, { headers: { Accept: 'application/json' } });
                        if (! response.ok) {
                            this.loadingLocations = false;
                            return;
                        }
                        const data = await response.json();
                        this.municipalities = data.map((municipality) => ({
                            ibge_code: String(municipality.id),
                            name: municipality.nome,
                            state: this.selectedState,
                            microregion_name: municipality.microrregiao?.nome || null,
                            mesoregion_name: municipality.microrregiao?.mesorregiao?.nome || null,
                        }));
                        this.selectedMunicipalities = this.selectedMunicipalities.filter((municipality) => municipality.state === this.selectedState);
                        this.loadingLocations = false;
                    },
                    municipalityMatches() {
                        const term = this.municipalitySearch.toLowerCase();
                        if (! term) return [];
                        return this.municipalities
                            .filter((municipality) => ! this.selectedMunicipalities.some((selected) => selected.ibge_code === municipality.ibge_code))
                            .filter((municipality) => `${municipality.name} ${municipality.microregion_name || ''} ${municipality.mesoregion_name || ''}`.toLowerCase().includes(term))
                            .slice(0, 12);
                    },
                    addMunicipality(municipality) {
                        this.selectedMunicipalities.push(municipality);
                        this.municipalitySearch = '';
                    },
                    removeMunicipality(code) {
                        this.selectedMunicipalities = this.selectedMunicipalities.filter((municipality) => municipality.ibge_code !== code);
                    }
                }">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="name" value="Nome da região comercial" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" placeholder="São Paulo Capital" required />
                        </div>
                        <div>
                            <x-input-label for="level" value="Nível de prioridade" />
                            <x-text-input id="level" name="level" type="number" min="1" max="99" class="mt-1 block w-full" :value="old('level', $model->level ?: 1)" required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Níveis menores são avaliados primeiro.</p>
                        </div>
                        <div>
                            <x-input-label for="state" value="UF" />
                            <select id="state" name="state" x-model="selectedState" @change="loadMunicipalities()" class="{{ $inputClass }}" required>
                                <option value="">Selecione a UF</option>
                                <template x-for="state in states" :key="state.id">
                                    <option :value="state.sigla" x-text="`${state.nome} - ${state.sigla}`"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="coverage_type" value="Abrangência" />
                            <select id="coverage_type" name="coverage_type" x-model="coverageType" class="{{ $inputClass }}" required>
                                <option value="municipalities">Municípios selecionados</option>
                                <option value="state_remainder">Todos os municípios restantes da UF</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="description" value="Descrição" />
                            <textarea id="description" name="description" rows="3" class="{{ Str::replaceFirst('h-11', 'min-h-24', $inputClass) }}">{{ old('description', $model->description) }}</textarea>
                        </div>
                    </div>

                    <div x-show="coverageType === 'municipalities'" x-cloak class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Municípios da região</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Dados oficiais carregados da API de Localidades do IBGE.</p>
                        </div>
                        <div class="space-y-4 p-5">
                            <template x-for="(municipality, index) in selectedMunicipalities" :key="municipality.ibge_code">
                                <div>
                                    <input type="hidden" :name="`municipalities[${index}][ibge_code]`" :value="municipality.ibge_code">
                                    <input type="hidden" :name="`municipalities[${index}][name]`" :value="municipality.name">
                                    <input type="hidden" :name="`municipalities[${index}][state]`" :value="municipality.state">
                                    <input type="hidden" :name="`municipalities[${index}][microregion_name]`" :value="municipality.microregion_name">
                                    <input type="hidden" :name="`municipalities[${index}][mesoregion_name]`" :value="municipality.mesoregion_name">
                                </div>
                            </template>

                            <div class="relative">
                                <x-input-label value="Buscar município, microrregião ou mesorregião" />
                                <input x-model="municipalitySearch" :disabled="! selectedState || loadingLocations" class="{{ $inputClass }}" placeholder="Ex.: Embu das Artes, Sorocaba...">
                                <div x-show="municipalityMatches().length > 0" x-cloak class="absolute z-30 mt-2 max-h-80 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                    <template x-for="municipality in municipalityMatches()" :key="municipality.ibge_code">
                                        <button type="button" @click="addMunicipality(municipality)" class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                            <span>
                                                <strong class="block text-gray-800 dark:text-white/90" x-text="`${municipality.name} - ${municipality.state}`"></strong>
                                                <span class="text-xs text-gray-500" x-text="`${municipality.microregion_name || 'Sem microrregião'} · ${municipality.mesoregion_name || 'Sem mesorregião'} · IBGE ${municipality.ibge_code}`"></span>
                                            </span>
                                            <span class="text-brand-500">Adicionar</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <template x-for="municipality in selectedMunicipalities" :key="`selected-${municipality.ibge_code}`">
                                    <div class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                                        <span>
                                            <strong class="block text-sm text-gray-800 dark:text-white/90" x-text="`${municipality.name} - ${municipality.state}`"></strong>
                                            <span class="text-xs text-gray-500" x-text="`IBGE ${municipality.ibge_code}${municipality.microregion_name ? ' · ' + municipality.microregion_name : ''}`"></span>
                                        </span>
                                        <button type="button" @click="removeMunicipality(municipality.ibge_code)" class="text-sm font-medium text-error-600">Remover</button>
                                    </div>
                                </template>
                            </div>
                            <p x-show="selectedMunicipalities.length === 0" class="text-sm text-gray-500 dark:text-gray-400">Nenhum município selecionado.</p>
                        </div>
                    </div>

                    <div x-show="coverageType === 'state_remainder'" x-cloak class="rounded-2xl border border-brand-200 bg-brand-50 p-5 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
                        Esta região receberá automaticamente todos os municípios da UF que não estiverem vinculados a uma região mais específica.
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-800 dark:text-white/90">Tabelas de preço da região</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este é o único local de manutenção do vínculo entre região e tabela de preço.</p>
                        </div>
                        <div class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-3">
                            @forelse ($priceTables as $priceTable)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 hover:border-brand-300 dark:border-gray-800 dark:hover:border-brand-500">
                                    <input
                                        type="checkbox"
                                        name="price_table_ids[]"
                                        value="{{ $priceTable->id }}"
                                        class="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                        @checked($selectedRegionPriceTables->contains($priceTable->id))
                                    >
                                    <span>
                                        <strong class="block text-sm text-gray-800 dark:text-white/90">{{ $priceTable->name }}</strong>
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $priceTable->description ?: 'Sem descrição' }}</span>
                                        @if ($priceTable->region_id && $priceTable->region_id !== $model->id)
                                            <span class="mt-1 block text-xs text-warning-600">Será movida da região atual.</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma tabela ativa cadastrada.</p>
                            @endforelse
                        </div>
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
            @elseif ($resource === 'payment-methods')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" placeholder="Ex.: Boleto" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Código" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" placeholder="Ex.: boleto" required />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Identificador enviado ao pedido e às integrações.</p>
                    </div>
                    <div>
                        <x-input-label for="sort_order" value="Ordem de exibição" />
                        <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535" class="mt-1 block w-full" :value="old('sort_order', $model->sort_order ?? 0)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}" placeholder="Informe quando esta forma deve ser utilizada.">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'payment-terms')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $model->name)" placeholder="Ex.: 15/30/45 dias" required />
                    </div>
                    <div>
                        <x-input-label for="code" value="Código" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $model->code)" placeholder="Ex.: 15/30/45" required />
                    </div>
                    <div>
                        <x-input-label for="installment_days_input" value="Dias das parcelas" />
                        <x-text-input
                            id="installment_days_input"
                            name="installment_days_input"
                            class="mt-1 block w-full"
                            :value="old('installment_days_input', $model->exists ? collect($model->installment_days)->implode('/') : '')"
                            placeholder="Ex.: 15/30/45"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use zero para pagamento à vista. Separe os dias por barra, vírgula ou espaço.</p>
                    </div>
                    <div>
                        <x-input-label for="sort_order" value="Ordem de exibição" />
                        <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535" class="mt-1 block w-full" :value="old('sort_order', $model->sort_order ?? 0)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}" placeholder="Informe as condições comerciais deste prazo.">{{ old('description', $model->description) }}</textarea>
                    </div>
                </div>
            @elseif ($resource === 'orders')
                @php
                    $loggedRepresentative = auth()->user()->role === 'SalesRepresentative'
                        ? auth()->user()->salesRepresentative
                        : null;
                    $selectedCustomerId = (int) old('customer_id', $model->customer_id);
                    $selectedPriceTableId = (int) old('price_table_id', $model->price_table_id);
                    $selectedRepresentativeId = (int) old('sales_representative_id', $loggedRepresentative?->id ?: $model->sales_representative_id);
                    $customerOptions = $customers->map(function ($customer) use ($applicablePriceTables) {
                        return [
                            'id' => $customer->id,
                            'label' => $customer->trade_name ?: $customer->corporate_name,
                            'document' => $customer->document,
                            'search' => strtolower(($customer->trade_name ?: $customer->corporate_name).' '.$customer->corporate_name.' '.$customer->document),
                            'price_tables' => $applicablePriceTables->get($customer->id, collect())->map(fn ($table) => [
                                'id' => $table->id,
                                'name' => $table->name,
                                'region' => $table->region?->name,
                            ])->values(),
                        ];
                    })->values();
                    $representativeOptions = $representatives->map(fn ($representative) => [
                        'id' => $representative->id,
                        'label' => $representative->code.' - '.$representative->user->name,
                        'email' => $representative->user->email,
                        'search' => strtolower($representative->code.' '.$representative->user->name.' '.$representative->user->email),
                    ])->values();
                    $productOptions = $products->map(fn ($product) => [
                        'id' => $product->id,
                        'label' => $product->sku.' - '.$product->name,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'unit' => $product->unit?->code,
                        'stock' => $product->available_stock,
                        'image' => $product->imageSrc(),
                        'default_price' => $product->displayPrice(),
                        'prices' => $product->prices
                            ->sortByDesc('minimum_quantity')
                            ->groupBy('price_table_id')
                            ->map(fn ($prices) => (float) $prices->first()->price),
                        'search' => strtolower($product->sku.' '.$product->name.' '.$product->barcode),
                    ])->values();
                    $orderRows = collect(old('items', $model->exists ? $model->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'product_search' => $item->product?->sku.' - '.$item->product?->name,
                        'adjustments' => collect($item->discounts ?? [])->map(function ($discount) {
                            $legacyType = $discount['type'] ?? 'percentage';

                            return [
                                'name' => $discount['name'] ?? 'Desconto comercial',
                                'type' => in_array($legacyType, ['discount', 'surcharge'], true) ? $legacyType : 'discount',
                                'mode' => $discount['mode'] ?? ($legacyType === 'fixed' ? 'fixed' : 'percentage'),
                                'value' => $discount['value'] ?? 0,
                            ];
                        })->values()->all(),
                    ])->values()->all() : []));
                    if ($orderRows->isEmpty()) {
                        $orderRows = collect([['product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'product_search' => '', 'adjustments' => []]]);
                    }
                @endphp
                <div class="space-y-6" x-data="{
                    customers: @js($customerOptions),
                    representatives: @js($representativeOptions),
                    products: @js($productOptions),
                    selectedCustomerId: @js($selectedCustomerId ?: null),
                    selectedPriceTableId: @js($selectedPriceTableId ?: null),
                    selectedRepresentativeId: @js($selectedRepresentativeId ?: null),
                    loggedRepresentativeId: @js($loggedRepresentative?->id),
                    customerSearch: '',
                    representativeSearch: '',
                    representativeDropdownOpen: false,
                    tableModalOpen: false,
                    bulkModalOpen: false,
                    adjustmentModalOpen: false,
                    activeItemIndex: null,
                    itemMenuIndex: null,
                    productSearch: '',
                    bulkAdjustment: { name: 'Desconto em massa', type: 'discount', mode: 'percentage', value: '' },
                    items: @js($orderRows->values()),
                    init() {
                        const customer = this.selectedCustomer();
                        if (customer) {
                            this.customerSearch = `${customer.label} - ${customer.document}`;
                            if (! this.selectedPriceTableId && customer.price_tables.length === 1) {
                                this.selectedPriceTableId = customer.price_tables[0].id;
                            }
                        }
                        const representative = this.selectedRepresentative();
                        if (representative) {
                            this.representativeSearch = representative.label;
                        }
                        this.items = this.items.map((item) => this.hydrateItem(item));
                        this.refreshItemPrices();
                    },
                    addItem() { this.items.push({ product_id: '', quantity: 1, unit_price: 0, product_search: '', adjustments: [] }) },
                    removeItem(index) { this.items.splice(index, 1) },
                    selectedCustomer() { return this.customers.find((customer) => customer.id === Number(this.selectedCustomerId)); },
                    selectedPriceTable() {
                        const customer = this.selectedCustomer();
                        return customer ? customer.price_tables.find((table) => table.id === Number(this.selectedPriceTableId)) : null;
                    },
                    customerMatches() {
                        const term = this.customerSearch.toLowerCase();
                        if (! term) return [];
                        return this.customers.filter((customer) => customer.search.includes(term)).slice(0, 8);
                    },
                    representativeMatches() {
                        const term = this.representativeSearch.toLowerCase().trim();
                        if (! term) return [];
                        return this.representatives
                            .filter((representative) => representative.search.includes(term))
                            .slice(0, 8);
                    },
                    selectedRepresentative() {
                        return this.representatives.find((representative) => representative.id === Number(this.selectedRepresentativeId));
                    },
                    selectRepresentative(representative) {
                        this.selectedRepresentativeId = representative.id;
                        this.representativeSearch = representative.label;
                        this.representativeDropdownOpen = false;
                    },
                    selectCustomer(customer) {
                        this.selectedCustomerId = customer.id;
                        this.customerSearch = `${customer.label} - ${customer.document}`;
                        this.selectedPriceTableId = null;
                        if (customer.price_tables.length === 1) {
                            this.selectedPriceTableId = customer.price_tables[0].id;
                        } else if (customer.price_tables.length > 1) {
                            this.tableModalOpen = true;
                        }
                        this.refreshItemPrices();
                    },
                    choosePriceTable(tableId) {
                        this.selectedPriceTableId = tableId;
                        this.tableModalOpen = false;
                        this.refreshItemPrices();
                    },
                    hydrateItem(item) {
                        const product = this.products.find((product) => product.id === Number(item.product_id));
                        return {
                            product_id: item.product_id || '',
                            product_search: item.product_search || (product ? product.label : ''),
                            quantity: item.quantity || 1,
                            unit_price: item.unit_price || 0,
                            adjustments: item.adjustments || [],
                        };
                    },
                    productMatches(term) {
                        const search = String(term || '').toLowerCase();
                        if (! search) return [];
                        return this.products.filter((product) => product.search.includes(search)).slice(0, 8);
                    },
                    selectProduct(index, product) {
                        this.items[index].product_id = product.id;
                        this.items[index].product_search = product.label;
                        this.items[index].unit_price = this.productPrice(product);
                    },
                    selectedProduct(item) { return this.products.find((product) => product.id === Number(item.product_id)); },
                    productPrice(product) {
                        if (! product) return 0;
                        return Number(product.prices[this.selectedPriceTableId] ?? product.default_price ?? 0);
                    },
                    refreshItemPrices() {
                        this.items.forEach((item) => {
                            const product = this.selectedProduct(item);
                            if (product) item.unit_price = this.productPrice(product);
                        });
                    },
                    addAdjustment(item, type = 'discount') {
                        if (! item) return;
                        item.adjustments.push({ name: type === 'surcharge' ? 'Acréscimo comercial' : 'Desconto comercial', type, mode: 'percentage', value: '' });
                    },
                    removeAdjustment(item, index) { item.adjustments.splice(index, 1); },
                    openAdjustmentModal(index, type = null) {
                        this.activeItemIndex = index;
                        this.itemMenuIndex = null;
                        if (type) this.addAdjustment(this.items[index], type);
                        this.adjustmentModalOpen = true;
                    },
                    activeItem() {
                        return this.activeItemIndex === null ? null : this.items[this.activeItemIndex];
                    },
                    adjustmentSummary(item) {
                        if (! item.adjustments || item.adjustments.length === 0) return 'Sem descontos ou acréscimos';
                        return item.adjustments.map((adjustment) => {
                            const prefix = adjustment.type === 'surcharge' ? 'Acréscimo' : 'Desconto';
                            const value = adjustment.mode === 'percentage' ? `${Number(adjustment.value || 0).toLocaleString('pt-BR')}%` : this.money(Number(adjustment.value || 0));
                            return `${prefix}: ${value}`;
                        }).join(' · ');
                    },
                    applyBulkAdjustment() {
                        const value = Number(this.bulkAdjustment.value || 0);
                        if (value <= 0) return;
                        this.items.forEach((item) => {
                            item.adjustments.push({ ...this.bulkAdjustment, value });
                        });
                        this.bulkAdjustment.value = '';
                        this.bulkModalOpen = false;
                    },
                    applyAdjustments(amount, adjustments) {
                        return (adjustments || []).reduce((total, adjustment) => {
                            const value = Number(adjustment.value || 0);
                            if (value <= 0) return total;
                            const adjustmentAmount = adjustment.mode === 'percentage' ? total * (value / 100) : value;
                            return Math.max(0, adjustment.type === 'surcharge' ? total + adjustmentAmount : total - adjustmentAmount);
                        }, amount);
                    },
                    lineTotal(item) {
                        const subtotal = Number(item.quantity || 0) * Number(item.unit_price || 0);
                        return this.applyAdjustments(subtotal, item.adjustments);
                    },
                    subtotal() { return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0) },
                    total() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0) },
                    money(value) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0) }
                }">
                    <input type="hidden" name="customer_id" :value="selectedCustomerId || ''">
                    <input type="hidden" name="price_table_id" :value="selectedPriceTableId || ''">
                    @if ($model->exists)
                        <input type="hidden" name="version" value="{{ $model->version }}">
                    @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label value="Número do pedido" />
                        <div class="mt-1 flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                            {{ $model->order_number ?: 'Gerado automaticamente ao salvar' }}
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Status" />
                        <div class="mt-1 flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                            {{ $model->exists ? 'Rascunho' : 'O pedido será criado como rascunho' }}
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Buscar cliente" />
                        <div class="relative">
                            <input x-model="customerSearch" class="{{ $inputClass }}" placeholder="Digite nome, fantasia ou documento do cliente..." required>
                            <div x-show="customerMatches().length > 0" x-cloak class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                <template x-for="customer in customerMatches()" :key="customer.id">
                                    <button type="button" @click="selectCustomer(customer)" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                        <span><strong class="block text-gray-800 dark:text-white/90" x-text="customer.label"></strong><span class="text-gray-500" x-text="customer.document"></span></span>
                                        <span class="text-brand-500">Selecionar</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <x-input-label for="representative_search" value="Representante" />
                        @if ($loggedRepresentative)
                            <input type="hidden" name="sales_representative_id" value="{{ $loggedRepresentative->id }}">
                            <input id="representative_search" class="{{ $inputClass }}" value="{{ $loggedRepresentative->code }} - {{ $loggedRepresentative->user->name }}" readonly>
                        @else
                            <input type="hidden" name="sales_representative_id" :value="selectedRepresentativeId || ''">
                            <div class="relative" @click.outside="representativeDropdownOpen = false">
                                <input
                                    id="representative_search"
                                    x-model="representativeSearch"
                                    @focus="representativeDropdownOpen = true"
                                    @input="selectedRepresentativeId = selectedRepresentative()?.label === representativeSearch ? selectedRepresentativeId : null"
                                    @input.debounce.150ms="representativeDropdownOpen = true"
                                    class="{{ $inputClass }}"
                                    placeholder="Busque por código, nome ou e-mail..."
                                    autocomplete="off"
                                    required
                                >
                                <div x-show="representativeDropdownOpen && representativeMatches().length > 0" x-cloak class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                    <template x-for="representative in representativeMatches()" :key="representative.id">
                                        <button type="button" @click="selectRepresentative(representative)" class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                            <span class="min-w-0">
                                                <strong class="block truncate text-gray-800 dark:text-white/90" x-text="representative.label"></strong>
                                                <span class="block truncate text-xs text-gray-500" x-text="representative.email"></span>
                                            </span>
                                            <span class="shrink-0 text-brand-500">Selecionar</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div>
                        <x-input-label value="Tabela de preço" />
                        <button type="button" @click="selectedCustomer() && selectedCustomer().price_tables.length > 1 ? tableModalOpen = true : null" class="{{ $inputClass }} text-left" :class="! selectedPriceTable() ? 'text-gray-400' : ''">
                            <span x-text="selectedPriceTable() ? selectedPriceTable().name : 'Selecione um cliente para carregar as tabelas'"></span>
                        </button>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Quando houver uma única tabela habilitada, ela será selecionada automaticamente.</p>
                    </div>
                    <div>
                        <x-input-label for="order_date" value="Data do pedido" />
                        <x-text-input id="order_date" name="order_date" data-datepicker class="mt-1 block w-full" :value="old('order_date', optional($model->order_date)->format('Y-m-d') ?: now()->format('Y-m-d'))" required />
                    </div>
                    <div>
                        <x-input-label for="payment_method" value="Forma de pagamento" />
                        <select id="payment_method" name="payment_method" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->code }}" @selected(old('payment_method', $model->payment_method) === $paymentMethod->code)>{{ $paymentMethod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="payment_terms" value="Prazo" />
                        <select id="payment_terms" name="payment_terms" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($paymentTerms as $paymentTerm)
                                <option value="{{ $paymentTerm->code }}" @selected(old('payment_terms', $model->payment_terms) === $paymentTerm->code)>{{ $paymentTerm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="notes" value="Observações" />
                        <textarea id="notes" name="notes" rows="4" class="{{ Str::replaceFirst('h-11', 'min-h-32', $inputClass) }}">{{ old('notes', $model->notes) }}</textarea>
                    </div>
                </div>
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white/90">Produtos do pedido</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Busque produtos, confira imagem e preço da tabela selecionada.</p>
                            </div>
                            <button type="button" @click="bulkModalOpen = true" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                Desconto em massa
                            </button>
                        </div>
                        <div class="space-y-4 p-5">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                                    <template x-for="(adjustment, adjustmentIndex) in item.adjustments" :key="`hidden-adjustment-${index}-${adjustmentIndex}`">
                                        <div>
                                            <input type="hidden" :name="`items[${index}][adjustments][${adjustmentIndex}][name]`" x-model="adjustment.name">
                                            <input type="hidden" :name="`items[${index}][adjustments][${adjustmentIndex}][type]`" x-model="adjustment.type">
                                            <input type="hidden" :name="`items[${index}][adjustments][${adjustmentIndex}][mode]`" x-model="adjustment.mode">
                                            <input type="hidden" :name="`items[${index}][adjustments][${adjustmentIndex}][value]`" x-model="adjustment.value">
                                        </div>
                                    </template>

                                    <div class="grid gap-4 xl:grid-cols-[minmax(380px,1fr)_120px_120px_150px_160px_44px] xl:items-start">
                                        <div class="relative">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                                    <template x-if="selectedProduct(item)?.image">
                                                        <img :src="selectedProduct(item).image" class="size-full object-cover" alt="">
                                                    </template>
                                                    <span x-show="! selectedProduct(item)?.image" class="px-1 text-center text-[11px] text-gray-400">Sem imagem</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                                    <input x-model="item.product_search" class="{{ $inputClass }}" placeholder="Buscar SKU, nome ou código de barras..." required>
                                                    <div x-show="productMatches(item.product_search).length > 0" x-cloak class="absolute z-[9999] mt-2 max-h-80 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                                        <template x-for="product in productMatches(item.product_search)" :key="product.id">
                                                            <button type="button" @click="selectProduct(index, product)" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                                                <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                                                    <img x-show="product.image" :src="product.image" class="size-full object-cover" alt="">
                                                                    <span x-show="! product.image" class="px-1 text-center text-xs text-gray-400">Sem imagem</span>
                                                                </span>
                                                                <span>
                                                                    <strong class="block text-gray-800 dark:text-white/90" x-text="product.name"></strong>
                                                                    <span class="text-gray-500" x-text="`${product.sku}${product.unit ? ' · ' + product.unit : ''}${product.barcode ? ' · ' + product.barcode : ''}`"></span>
                                                                </span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 grid gap-1 text-xs text-gray-500 dark:text-gray-400 sm:grid-cols-3">
                                                <span>Unidade: <strong x-text="selectedProduct(item)?.unit || '-'"></strong></span>
                                                <span>Código: <strong x-text="selectedProduct(item)?.barcode || selectedProduct(item)?.sku || '-'"></strong></span>
                                                <span>Estoque: <strong x-text="selectedProduct(item)?.stock ?? '-'"></strong></span>
                                            </div>
                                        </div>

                                        <div>
                                            <x-input-label value="Qtd." />
                                            <input type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`" x-model="item.quantity" class="{{ $inputClass }}" required>
                                        </div>

                                        <div>
                                            <x-input-label value="Preço" />
                                            <input type="hidden" :name="`items[${index}][unit_price]`" x-model="item.unit_price">
                                            <div class="flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-800 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90" x-text="money(item.unit_price)"></div>
                                        </div>

                                        <div>
                                            <x-input-label value="Subtotal" />
                                            <div class="flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-800 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90" x-text="money(Number(item.quantity || 0) * Number(item.unit_price || 0))"></div>
                                        </div>

                                        <div>
                                            <x-input-label value="Total" />
                                            <div class="flex h-11 items-center rounded-lg bg-brand-50 px-3 text-sm font-semibold text-gray-800 dark:bg-brand-500/10 dark:text-white/90" x-text="money(lineTotal(item))"></div>
                                        </div>

                                        <div class="relative flex justify-end pt-6">
                                            <button type="button" @click="itemMenuIndex = itemMenuIndex === index ? null : index" class="flex size-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]" aria-label="Ações do item">
                                                ⋮
                                            </button>
                                            <div x-show="itemMenuIndex === index" x-cloak @click.outside="itemMenuIndex = null" class="absolute right-0 top-12 z-30 w-56 rounded-xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                                                <button type="button" @click="openAdjustmentModal(index, 'discount')" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Adicionar desconto</button>
                                                <button type="button" @click="openAdjustmentModal(index, 'surcharge')" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Adicionar acréscimo</button>
                                                <button type="button" @click="openAdjustmentModal(index)" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Editar descontos</button>
                                                <button type="button" @click="removeItem(index); itemMenuIndex = null" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10">Remover item</button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="openAdjustmentModal(index)" class="mt-3 block max-w-full truncate text-left text-xs text-gray-500 hover:text-brand-600 dark:text-gray-400">
                                        <span x-text="adjustmentSummary(item)"></span>
                                    </button>
                                </div>
                            </template>
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
                    <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/40 p-4">
                        <div class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                                <div>
                                    <h3 class="font-semibold text-gray-800 dark:text-white/90">Desconto em massa</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aplique um desconto ou acréscimo a todos os itens do pedido.</p>
                                </div>
                                <button type="button" @click="bulkModalOpen = false" class="text-gray-500">Fechar</button>
                            </div>
                            <div class="grid gap-4 p-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label value="Descrição" />
                                    <input x-model="bulkAdjustment.name" class="{{ $inputClass }}" placeholder="Desconto em massa">
                                </div>
                                <div>
                                    <x-input-label value="Tipo" />
                                    <select x-model="bulkAdjustment.type" class="{{ $inputClass }}">
                                        <option value="discount">Desconto</option>
                                        <option value="surcharge">Acréscimo</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label value="Formato" />
                                    <select x-model="bulkAdjustment.mode" class="{{ $inputClass }}">
                                        <option value="percentage">Percentual</option>
                                        <option value="fixed">Valor fixo</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label value="Valor" />
                                    <input type="number" min="0" step="0.01" x-model="bulkAdjustment.value" class="{{ $inputClass }}" placeholder="Informe o valor">
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                                <button type="button" @click="bulkModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancelar</button>
                                <button type="button" @click="applyBulkAdjustment()" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Aplicar</button>
                            </div>
                        </div>
                    </div>
                    <div x-show="adjustmentModalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/40 p-4">
                        <div class="w-full max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                                <div>
                                    <h3 class="font-semibold text-gray-800 dark:text-white/90">Descontos e acréscimos do item</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="activeItem()?.product_search || 'Selecione um produto'"></p>
                                </div>
                                <button type="button" @click="adjustmentModalOpen = false; activeItemIndex = null" class="text-gray-500">Fechar</button>
                            </div>
                            <div class="space-y-3 p-5" x-show="activeItem()">
                                <template x-for="(adjustment, adjustmentIndex) in activeItem().adjustments" :key="adjustmentIndex">
                                    <div class="grid gap-2 rounded-xl border border-gray-100 p-3 dark:border-gray-800 lg:grid-cols-[minmax(180px,1fr)_140px_130px_120px_auto]">
                                        <input x-model="adjustment.name" class="{{ $inputClass }}" placeholder="Descrição">
                                        <select x-model="adjustment.type" class="{{ $inputClass }}">
                                            <option value="discount">Desconto</option>
                                            <option value="surcharge">Acréscimo</option>
                                        </select>
                                        <select x-model="adjustment.mode" class="{{ $inputClass }}">
                                            <option value="percentage">Percentual</option>
                                            <option value="fixed">Valor fixo</option>
                                        </select>
                                        <input type="number" min="0" step="0.01" x-model="adjustment.value" class="{{ $inputClass }}" placeholder="Valor">
                                        <button type="button" @click="removeAdjustment(activeItem(), adjustmentIndex)" class="text-sm font-medium text-error-600">Remover</button>
                                    </div>
                                </template>
                                <p x-show="activeItem().adjustments.length === 0" class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Nenhum desconto ou acréscimo aplicado neste item.
                                </p>
                            </div>
                            <div class="flex flex-wrap justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="addAdjustment(activeItem(), 'discount')" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Adicionar desconto</button>
                                    <button type="button" @click="addAdjustment(activeItem(), 'surcharge')" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Adicionar acréscimo</button>
                                </div>
                                <button type="button" @click="adjustmentModalOpen = false; activeItemIndex = null" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">Concluir</button>
                            </div>
                        </div>
                    </div>
                    <div x-show="tableModalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/40 p-4">
                        <div class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                                <div>
                                    <h3 class="font-semibold text-gray-800 dark:text-white/90">Escolha a tabela de preço</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este cliente possui mais de uma tabela habilitada.</p>
                                </div>
                                <button type="button" @click="tableModalOpen = false" class="text-gray-500">Fechar</button>
                            </div>
                            <div class="space-y-3 p-5">
                                <template x-for="table in selectedCustomer()?.price_tables || []" :key="table.id">
                                    <button type="button" @click="choosePriceTable(table.id)" class="flex w-full items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-left hover:border-brand-300 hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-brand-500/10">
                                        <span><strong class="block text-gray-800 dark:text-white/90" x-text="table.name"></strong><span class="text-sm text-gray-500" x-text="table.region || 'Todas as regiões'"></span></span>
                                        <span class="text-brand-500">Usar tabela</span>
                                    </button>
                                </template>
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
