<?php

namespace App\Models;

use App\Enums\TagRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentTag extends Pivot
{
    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $casts = [
        'role' => TagRole::class,
    ];
}
