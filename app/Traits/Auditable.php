<?php

namespace App\Traits;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    use AuditableTrait;

    /**
     * Get the entity's audits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function audits(): MorphMany
    {
        return $this->morphMany(
            \OwenIt\Auditing\Models\Audit::class,
            'auditable'
        );
    }
} 