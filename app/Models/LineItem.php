<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LineItem extends Model
{
    protected $guarded = [];
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
    public function scanning(): BelongsTo
    {
        return $this->belongsTo(Scanning::class);
    }
    public function testLists(): HasMany
    {
        return $this->hasMany(TestList::class);
    }
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function billing():BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
    public function patient():BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
