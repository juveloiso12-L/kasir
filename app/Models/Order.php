<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $order_id
 * @property string $status
 * @property string $payment_method
 * @property int $subtotal
 * @property int $discount
 * @property int $gross_amount
 * @property string|null $snap_token
 * @property Carbon|null $receipt_printed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
