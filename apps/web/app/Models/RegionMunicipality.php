<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionMunicipality extends Model
{
    protected $fillable = ['ibge_code', 'name', 'state', 'microregion_name', 'mesoregion_name'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
