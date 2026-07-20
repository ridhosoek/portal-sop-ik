<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'active'])]
class Department extends Model
{
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function organizationStructures(): HasMany
    {
        return $this->hasMany(OrganizationStructure::class);
    }
}
