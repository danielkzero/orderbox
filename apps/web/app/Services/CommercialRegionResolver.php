<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Str;

class CommercialRegionResolver
{
    public function resolve(
        int $companyId,
        string $state,
        ?string $city = null,
        ?string $ibgeCode = null,
    ): ?Region {
        $state = strtoupper($state);
        $explicitRegions = Region::query()
            ->with('municipalities')
            ->where('company_id', $companyId)
            ->where('active', true)
            ->where('state', $state)
            ->where('coverage_type', 'municipalities')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $normalizedCity = filled($city) ? Str::lower(Str::ascii($city)) : null;

        $explicitRegion = $explicitRegions->first(function (Region $region) use ($ibgeCode, $normalizedCity): bool {
            $matchesMunicipality = $region->municipalities->contains(function ($municipality) use ($ibgeCode, $normalizedCity): bool {
                if (filled($ibgeCode) && $municipality->ibge_code === (string) $ibgeCode) {
                    return true;
                }

                return $normalizedCity
                    && Str::lower(Str::ascii($municipality->name)) === $normalizedCity;
            });

            if ($matchesMunicipality) {
                return true;
            }

            return $normalizedCity
                && filled($region->city)
                && Str::lower(Str::ascii($region->city)) === $normalizedCity;
        });

        if ($explicitRegion) {
            return $explicitRegion;
        }

        return Region::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->where('state', $state)
            ->where('coverage_type', 'state_remainder')
            ->orderBy('level')
            ->orderBy('name')
            ->first();
    }
}
