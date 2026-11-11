<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'payment_method',
        'subtotal',
        'discount',
        'gross_amount',
        'snap_token',
        'receipt_printed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'gross_amount' => 'integer',
            'receipt_printed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_id';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
