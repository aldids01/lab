<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestList extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'range' => 'array',
    ];
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
