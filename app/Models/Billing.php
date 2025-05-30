<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function payments():HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function lineItems():HasMany
    {
        return $this->hasMany(LineItem::class);
    }
    public function laboratories(): HasMany
    {
        return $this->hasMany(Laboratory::class);
    }
    public function scannings(): HasMany
    {
        return $this->hasMany(Scanning::class);
    }

}
