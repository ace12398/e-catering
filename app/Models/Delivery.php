<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'courier_id',
        'vehicle',
        'status',
        'priority',
        'pickup_time',
        'delivered_time',
        'recipient_name',
        'recipient_phone',
        'proof_image',
        'notes',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivered_time' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
