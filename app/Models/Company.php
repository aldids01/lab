<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model implements HasAvatar, HasCurrentTenantLabel
{
    protected $guarded = [];
    public function members():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id');
    }
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->company_logo ? Storage::url($this->company_logo) : null ;
    }
    public function getCurrentTenantLabel(): string
    {
        return 'Active Company';
    }
//    public function roles(): HasMany
//    {
//        return $this->hasMany(\Spatie\Permission\Models\Role::class);
//    }
    public function testLists(): HasMany
    {
        return $this->hasMany(TestList::class);
    }
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
