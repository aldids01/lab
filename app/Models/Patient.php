<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    public function laboratories(): HasMany
    {
        return $this->hasMany(Laboratory::class);
    }
    public function scannings(): HasMany
    {
        return $this->hasMany(Scanning::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

}
