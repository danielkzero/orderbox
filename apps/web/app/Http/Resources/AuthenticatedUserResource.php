<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'two_factor_enabled' => $this->two_factor_enabled,
            'company' => [
                'id' => $this->company->id,
                'trade_name' => $this->company->trade_name,
                'corporate_name' => $this->company->corporate_name,
            ],
        ];
    }
}
