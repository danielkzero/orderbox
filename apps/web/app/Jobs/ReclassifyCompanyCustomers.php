<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CommercialRegionResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReclassifyCompanyCustomers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $companyId,
        public readonly int $actorId,
    ) {}

    public function handle(CommercialRegionResolver $resolver, AuditService $audit): void
    {
        $actor = User::query()->where('company_id', $this->companyId)->findOrFail($this->actorId);

        Customer::query()
            ->with('addresses')
            ->where('company_id', $this->companyId)
            ->eachById(function (Customer $customer) use ($resolver, $audit, $actor): void {
                $defaultAddress = $customer->addresses->firstWhere('default_address', true) ?? $customer->addresses->first();
                $regionId = $defaultAddress
                    ? $resolver->resolve(
                        $this->companyId,
                        $defaultAddress->state,
                        $defaultAddress->city,
                        $defaultAddress->municipality_ibge_code,
                    )?->id
                    : null;

                if ($customer->region_id !== $regionId) {
                    $oldRegionId = $customer->region_id;
                    $customer->update(['region_id' => $regionId]);
                    $audit->record(
                        $actor,
                        'ReclassifyCustomerRegion',
                        $customer,
                        ['region_id' => $oldRegionId],
                        ['region_id' => $regionId],
                    );
                }
            });
    }
}
