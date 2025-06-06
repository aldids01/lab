<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scanning extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }
}
