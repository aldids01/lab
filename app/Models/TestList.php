<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    public function tests(): HasMany
    {
        return $this->hasMany(TestList::class, 'test_list_id');
    }
}
