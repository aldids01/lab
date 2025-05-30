<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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

    public function testList(): BelongsTo
    {
        return $this->belongsTo(TestList::class, 'test_list_id');
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

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
    protected $casts = [
        'results' => 'array',
    ];

    protected static function booted(): void
    {
        static::updated(function (LineItem $lineItem) {
            // Only proceed if the 'status' attribute has changed to 'Delivered'
            if (!$lineItem->isDirty('status') || $lineItem->status !== 'Delivered') {
                return; // Exit if status didn't change to 'Delivered'
            }

            // --- Handle LabRequest ---
            if ($lineItem->laboratory) {
                $labRequest = $lineItem->laboratory;

                // Set LabRequest to 'Processing' if not already 'Delivered'
                if ($labRequest->status !== 'Delivered') {
                    $labRequest->status = 'Processing';
                    $labRequest->save();
                }

                // Check if ALL associated LineItems are 'Delivered'
                // Assuming the relationship method in LabRequest is named 'lineItems'
                if ($labRequest->lineItems instanceof EloquentCollection) {
                    $allLabItemsDelivered = $labRequest->lineItems->every(function ($item) {
                        return $item->status === 'Delivered';
                    });

                    if ($allLabItemsDelivered && $labRequest->status !== 'Delivered') {
                        $labRequest->status = 'Delivered';
                        $labRequest->save();
                    } else if (!$allLabItemsDelivered) {
                        Log::info("LabRequest {$labRequest->id} still has pending line items. Not yet Delivered.");
                    }
                } else {
                    Log::error("LabRequest->lineItems relationship for LabRequest ID: {$labRequest->id} is not an Eloquent Collection. Type: " . gettype($labRequest->lineItems));
                }
            } else {
                Log::info("LineItem {$lineItem->id} has no associated LabRequest.");
            }

            // --- Handle ScanRequest ---
            if ($lineItem->scanning) { // Ensure consistent casing if it's 'scanRequest' in the model
                $scanRequest = $lineItem->scanning;

                // Set ScanRequest to 'Processing' if not already 'Delivered'
                if ($scanRequest->status !== 'Delivered') {
                    $scanRequest->status = 'Processing';
                    $scanRequest->save();
                }

                if ($scanRequest->lineItems instanceof EloquentCollection) {
                    $allScanItemsDelivered = $scanRequest->lineItems->every(function ($item) {
                        return $item->status === 'Delivered';
                    });

                    if ($allScanItemsDelivered && $scanRequest->status !== 'Delivered') {
                        $scanRequest->status = 'Delivered';
                        $scanRequest->save();
                    } else if (!$allScanItemsDelivered) {
                        Log::info("ScanRequest {$scanRequest->id} still has pending line items. Not yet Delivered.");
                    }
                } else {
                    Log::error("ScanRequest->lineItems relationship for ScanRequest ID: {$scanRequest->id} is not an Eloquent Collection. Type: " . gettype($scanRequest->lineItems));
                }
            } else {
                Log::info("LineItem {$lineItem->id} has no associated ScanRequest.");
            }
        });
    }
}
