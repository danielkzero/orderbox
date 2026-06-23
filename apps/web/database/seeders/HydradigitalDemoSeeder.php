<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\CustomerRepresentative;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTable;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Region;
use App\Models\SalesRepresentative;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class HydradigitalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Hydradigital demo data cannot be seeded in production.');
        }

        DB::transaction(function (): void {
            $admin = User::query()->where('email', 'danikzero@hotmail.com')->first();

            if (! $admin) {
                throw new RuntimeException('Create the administrator danikzero@hotmail.com before seeding demo data.');
            }

            $company = $admin->company;
            $company->update([
                'corporate_name' => 'Hydra Digital Tecnologia Ltda.',
                'trade_name' => 'hydradigital',
                'document' => '12345678000195',
                'email' => 'contato@hydradigital.test',
                'phone' => '(11) 4002-8922',
                'active' => true,
            ]);

            $admin->update(['name' => 'Daniel', 'role' => 'Admin', 'active' => true]);
            (new PaymentSettingsSeeder)->seedCompany($company);

            $regions = $this->seedRegions($company);
            [$manager, $representatives] = $this->seedUsers($company, $regions);
            [$categories, $brands, $units] = $this->seedCatalogReferences($company);
            [$products, $priceTables] = $this->seedProducts($company, $categories, $brands, $units, $regions);
            $representatives->each(fn (SalesRepresentative $representative) => $representative->priceTables()->sync($priceTables->pluck('id')));
            $customers = $this->seedCustomers($company, $representatives, $regions);
            $this->seedOrders($company, $manager, $representatives, $customers, $products, $priceTables);
            $this->seedApiClient($company);
        });
    }

    private function seedRegions(Company $company)
    {
        $regions = collect([
            [
                'name' => 'São Paulo Capital',
                'level' => 1,
                'state' => 'SP',
                'coverage_type' => 'municipalities',
                'municipalities' => [
                    ['ibge_code' => '3504107', 'name' => 'Atibaia', 'state' => 'SP', 'microregion_name' => 'Bragança Paulista', 'mesoregion_name' => 'Macro Metropolitana Paulista'],
                    ['ibge_code' => '3515004', 'name' => 'Embu das Artes', 'state' => 'SP', 'microregion_name' => 'Itapecerica da Serra', 'mesoregion_name' => 'Metropolitana de São Paulo'],
                    ['ibge_code' => '3528502', 'name' => 'Mairiporã', 'state' => 'SP', 'microregion_name' => 'Franco da Rocha', 'mesoregion_name' => 'Metropolitana de São Paulo'],
                    ['ibge_code' => '3550308', 'name' => 'São Paulo', 'state' => 'SP', 'microregion_name' => 'São Paulo', 'mesoregion_name' => 'Metropolitana de São Paulo'],
                    ['ibge_code' => '3550605', 'name' => 'São Roque', 'state' => 'SP', 'microregion_name' => 'Sorocaba', 'mesoregion_name' => 'Macro Metropolitana Paulista'],
                ],
            ],
            [
                'name' => 'São Paulo Interior',
                'level' => 2,
                'state' => 'SP',
                'coverage_type' => 'state_remainder',
                'municipalities' => [],
            ],
        ])->mapWithKeys(function (array $data) use ($company): array {
            $municipalities = $data['municipalities'];
            unset($data['municipalities']);

            $region = Region::query()->updateOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                [
                    'level' => $data['level'],
                    'state' => $data['state'],
                    'city' => null,
                    'coverage_type' => $data['coverage_type'],
                    'description' => 'Regiao comercial demonstrativa.',
                    'active' => true,
                ],
            );

            $region->municipalities()->delete();
            $region->municipalities()->createMany($municipalities);

            return [$region->name => $region];
        });

        Region::query()
            ->where('company_id', $company->id)
            ->whereNotIn('id', $regions->pluck('id'))
            ->update(['active' => false]);

        return $regions;
    }

    private function seedApiClient(Company $company): void
    {
        ApiClient::query()->updateOrCreate(
            ['client_key' => 'obx_hydradigital_mobile'],
            [
                'company_id' => $company->id,
                'name' => 'Hydradigital Mobile Demo',
                'channel' => 'Mobile',
                'secret_hash' => Hash::make('demo-api-secret'),
                'active' => true,
            ],
        );
    }

    private function seedUsers(Company $company, $regions): array
    {
        $manager = User::query()->updateOrCreate(
            ['email' => 'gestor@hydradigital.test'],
            [
                'company_id' => $company->id,
                'name' => 'Marina Costa',
                'password' => Hash::make('password'),
                'role' => 'Manager',
                'active' => true,
                'email_verified_at' => now(),
            ],
        );

        $representativeUsers = collect([
            ['name' => 'João Silva', 'email' => 'joao@hydradigital.test', 'code' => 'REP-001'],
            ['name' => 'Carla Souza', 'email' => 'carla@hydradigital.test', 'code' => 'REP-002'],
        ])->map(function (array $data) use ($company, $regions): SalesRepresentative {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'company_id' => $company->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'SalesRepresentative',
                    'active' => true,
                    'email_verified_at' => now(),
                ],
            );

            return SalesRepresentative::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $data['code']],
                ['user_id' => $user->id, 'region_id' => $regions[$data['code'] === 'REP-001' ? 'São Paulo Capital' : 'São Paulo Interior']->id, 'active' => true],
            );
        });

        return [$manager, $representativeUsers];
    }

    private function seedCatalogReferences(Company $company): array
    {
        $categories = collect([
            ['name' => 'Hidráulica', 'description' => 'Produtos para instalações hidráulicas.'],
            ['name' => 'Elétrica', 'description' => 'Materiais para instalações elétricas.'],
            ['name' => 'Ferramentas', 'description' => 'Ferramentas manuais e acessórios.'],
        ])->mapWithKeys(function (array $data) use ($company): array {
            $category = Category::query()->updateOrCreate(
                ['company_id' => $company->id, 'parent_id' => null, 'name' => $data['name']],
                ['description' => $data['description'], 'active' => true],
            );

            return [$category->name => $category];
        });

        $brands = collect(['Hydra', 'Tigre', 'Tramontina'])->mapWithKeys(function (string $name) use ($company): array {
            $brand = Brand::query()->updateOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                ['description' => "Marca fictícia {$name} para demonstração.", 'active' => true],
            );

            return [$name => $brand];
        });

        $units = collect([
            ['code' => 'UN', 'name' => 'Unidade'],
            ['code' => 'CX', 'name' => 'Caixa'],
            ['code' => 'MT', 'name' => 'Metro'],
        ])->mapWithKeys(function (array $data) use ($company): array {
            $unit = Unit::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $data['code']],
                ['name' => $data['name'], 'active' => true],
            );

            return [$unit->code => $unit];
        });

        return [$categories, $brands, $units];
    }

    private function seedProducts(Company $company, $categories, $brands, $units, $regions): array
    {
        $priceTables = collect([
            ['name' => 'Varejo', 'description' => 'Tabela padrão de varejo.'],
            ['name' => 'Atacado', 'description' => 'Tabela para compras em volume.'],
        ])->mapWithKeys(function (array $data) use ($company, $regions): array {
            $table = PriceTable::query()->updateOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                ['description' => $data['description'], 'active' => true],
            );
            $table->regions()->sync($data['name'] === 'Atacado' ? [$regions['São Paulo Interior']->id] : []);

            return [$table->name => $table];
        });

        $definitions = [
            ['sku' => 'HID-001', 'name' => 'Torneira de Cozinha Hydra', 'category' => 'Hidráulica', 'brand' => 'Hydra', 'unit' => 'UN', 'stock' => 48, 'retail' => 89.90, 'color' => 'Cromado', 'weight' => 0.850],
            ['sku' => 'HID-002', 'name' => 'Registro de Pressão 3/4', 'category' => 'Hidráulica', 'brand' => 'Tigre', 'unit' => 'UN', 'stock' => 120, 'retail' => 34.50, 'color' => 'Bruto', 'weight' => 0.320],
            ['sku' => 'HID-003', 'name' => 'Tubo PVC Soldável 25mm', 'category' => 'Hidráulica', 'brand' => 'Tigre', 'unit' => 'MT', 'stock' => 360, 'retail' => 12.90, 'color' => 'Marrom', 'weight' => 0.180],
            ['sku' => 'ELE-001', 'name' => 'Tomada Dupla 10A', 'category' => 'Elétrica', 'brand' => 'Hydra', 'unit' => 'UN', 'stock' => 95, 'retail' => 18.70, 'color' => 'Branco', 'weight' => 0.120],
            ['sku' => 'ELE-002', 'name' => 'Cabo Flexível 2,5mm', 'category' => 'Elétrica', 'brand' => 'Hydra', 'unit' => 'MT', 'stock' => 850, 'retail' => 4.80, 'color' => 'Azul', 'weight' => 0.045],
            ['sku' => 'FER-001', 'name' => 'Alicate Universal 8"', 'category' => 'Ferramentas', 'brand' => 'Tramontina', 'unit' => 'UN', 'stock' => 32, 'retail' => 54.90, 'color' => 'Laranja', 'weight' => 0.410],
            ['sku' => 'FER-002', 'name' => 'Jogo de Chaves de Fenda', 'category' => 'Ferramentas', 'brand' => 'Tramontina', 'unit' => 'CX', 'stock' => 20, 'retail' => 72.00, 'color' => 'Vermelho', 'weight' => 1.100],
            ['sku' => 'FER-003', 'name' => 'Trena Emborrachada 5m', 'category' => 'Ferramentas', 'brand' => 'Tramontina', 'unit' => 'UN', 'stock' => 64, 'retail' => 29.90, 'color' => 'Amarelo', 'weight' => 0.260],
        ];

        $products = collect($definitions)->mapWithKeys(function (array $data) use ($company, $categories, $brands, $units, $priceTables): array {
            $product = Product::query()->updateOrCreate(
                ['company_id' => $company->id, 'sku' => $data['sku']],
                [
                    'category_id' => $categories[$data['category']]->id,
                    'brand_id' => $brands[$data['brand']]->id,
                    'unit_id' => $units[$data['unit']]->id,
                    'external_id' => 'ERP-'.$data['sku'],
                    'name' => $data['name'],
                    'short_description' => 'Produto fictício para testes do OrderBox.',
                    'description' => 'Item de catálogo usado para validar pedidos, tabelas de preço e disponibilidade comercial.',
                    'color' => $data['color'],
                    'weight_kg' => $data['weight'],
                    'length_cm' => 15,
                    'width_cm' => 10,
                    'height_cm' => 8,
                    'base_price' => $data['retail'],
                    'minimum_quantity' => 1,
                    'quantity_multiple' => null,
                    'allows_fractional_quantity' => in_array($data['unit'], ['KG', 'MT'], true),
                    'active' => true,
                    'available_stock' => $data['stock'],
                    'stock_status' => $data['stock'] <= 30 ? 'LowStock' : 'InStock',
                    'published_at' => now(),
                ],
            );

            ProductPrice::query()->updateOrCreate(
                ['product_id' => $product->id, 'price_table_id' => $priceTables['Varejo']->id],
                ['price' => $data['retail']],
            );
            ProductPrice::query()->updateOrCreate(
                ['product_id' => $product->id, 'price_table_id' => $priceTables['Atacado']->id],
                ['price' => round($data['retail'] * 0.88, 2)],
            );

            return [$product->sku => $product];
        });

        return [$products, $priceTables];
    }

    private function seedCustomers(Company $company, $representatives, $regions)
    {
        $definitions = [
            ['document' => '12ABC34501DE35', 'corporate' => 'Construtora Horizonte Ltda.', 'trade' => 'Horizonte Obras', 'city' => 'São Paulo', 'state' => 'SP'],
            ['document' => '22222222000191', 'corporate' => 'Casa Nova Materiais Ltda.', 'trade' => 'Casa Nova', 'city' => 'Campinas', 'state' => 'SP'],
            ['document' => '33333333000191', 'corporate' => 'Reformas Ideal Ltda.', 'trade' => 'Ideal Reformas', 'city' => 'Santos', 'state' => 'SP'],
            ['document' => '44444444000191', 'corporate' => 'Engenharia Prisma Ltda.', 'trade' => 'Prisma Engenharia', 'city' => 'Sorocaba', 'state' => 'SP'],
            ['document' => '55555555000191', 'corporate' => 'Depósito Central Ltda.', 'trade' => 'Depósito Central', 'city' => 'Jundiaí', 'state' => 'SP'],
            ['document' => '66666666000191', 'corporate' => 'Comercial Avenida Ltda.', 'trade' => 'Comercial Avenida', 'city' => 'Guarulhos', 'state' => 'SP'],
        ];

        return collect($definitions)->mapWithKeys(function (array $data, int $index) use ($company, $representatives, $regions): array {
            $customer = Customer::query()->updateOrCreate(
                ['company_id' => $company->id, 'corporate_name' => $data['corporate']],
                [
                    'document' => $data['document'],
                    'client_reference' => (string) Str::uuid(),
                    'region_id' => $data['city'] === 'São Paulo'
                        ? $regions['São Paulo Capital']->id
                        : $regions['São Paulo Interior']->id,
                    'corporate_name' => $data['corporate'],
                    'trade_name' => $data['trade'],
                    'email' => 'compras'.($index + 1).'@cliente.test',
                    'phone' => '(11) 3000-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'credit_limit' => 10000 + ($index * 5000),
                    'active' => true,
                    'version' => 1,
                ],
            );

            CustomerAddress::query()->updateOrCreate(
                ['customer_id' => $customer->id, 'type' => 'Headquarters'],
                [
                    'zip_code' => '01001-000',
                    'street' => 'Rua de Demonstração',
                    'number' => (string) (100 + $index),
                    'district' => 'Centro',
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'municipality_ibge_code' => match ($data['city']) {
                        'São Paulo' => '3550308',
                        'Campinas' => '3509502',
                        'Santos' => '3548500',
                        default => null,
                    },
                    'country' => 'Brasil',
                    'default_address' => true,
                ],
            );

            CustomerContact::query()->updateOrCreate(
                ['customer_id' => $customer->id, 'email' => 'compras'.($index + 1).'@cliente.test'],
                [
                    'name' => 'Contato de Compras '.($index + 1),
                    'position' => 'Comprador',
                    'department' => 'Purchasing',
                    'mobile' => '(11) 99000-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'whatsapp' => '(11) 99000-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'primary_contact' => true,
                    'active' => true,
                ],
            );

            CustomerRepresentative::query()->updateOrCreate(
                ['customer_id' => $customer->id, 'sales_representative_id' => $representatives[$index % $representatives->count()]->id],
                ['is_primary' => true],
            );

            return [$customer->document => $customer];
        });
    }

    private function seedOrders(Company $company, User $manager, $representatives, $customers, $products, $priceTables): void
    {
        $customerValues = $customers->values();
        $productValues = $products->values();

        foreach (range(1, 4) as $index) {
            $representative = $representatives[($index - 1) % $representatives->count()];
            $status = $index === 1 ? 'Draft' : 'Sent';
            $firstProduct = $productValues[($index - 1) % $productValues->count()];
            $secondProduct = $productValues[$index % $productValues->count()];
            $firstPrice = (float) $firstProduct->prices()->where('price_table_id', $priceTables['Varejo']->id)->value('price');
            $secondPrice = (float) $secondProduct->prices()->where('price_table_id', $priceTables['Varejo']->id)->value('price');
            $subtotal = round(($firstPrice * 2) + ($secondPrice * 3), 2);

            $order = Order::query()->updateOrCreate(
                ['company_id' => $company->id, 'order_number' => 'PED-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT)],
                [
                    'customer_id' => $customerValues[$index - 1]->id,
                    'sales_representative_id' => $representative->id,
                    'user_id' => $manager->id,
                    'price_table_id' => $priceTables['Varejo']->id,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'discounts' => null,
                    'total_amount' => $subtotal,
                    'notes' => 'Pedido fictício para validação do fluxo comercial.',
                    'source' => $index % 2 === 0 ? 'Mobile' : 'Admin',
                    'payment_method' => 'boleto',
                    'payment_terms' => '15/30/45',
                    'order_date' => now()->subDays(5 - $index),
                    'sent_at' => $status === 'Sent' ? now()->subDays(5 - $index) : null,
                    'version' => 1,
                ],
            );

            OrderItem::query()->updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $firstProduct->id],
                ['quantity' => 2, 'unit_price' => $firstPrice, 'total_amount' => round($firstPrice * 2, 2)],
            );
            OrderItem::query()->updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $secondProduct->id],
                ['quantity' => 3, 'unit_price' => $secondPrice, 'total_amount' => round($secondPrice * 3, 2)],
            );
        }
    }
}
