<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'tax',
        'discount',
        'grand_total',
        'status',
        'payment_status',
        'payment_method',
        'delivery_address',
        'delivery_time',
        'notes',
    ];

    protected $casts = [
        'subtotal'       => 'float',
        'tax'            => 'float',
        'discount'       => 'float',
        'grand_total'    => 'float',
        'status'         => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'delivery_time'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function kitchenTask(): HasOne
    {
        return $this->hasOne(KitchenTask::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getTimelineSteps(): array
    {
        $steps = [
            ['key' => 'menunggu_pembayaran', 'label' => 'Menunggu Pembayaran', 'icon' => '🕐'],
            ['key' => 'menunggu_verifikasi', 'label' => 'Bukti Dikirim', 'icon' => '📋'],
            ['key' => 'sedang_diproses', 'label' => 'Diverifikasi Admin', 'icon' => '✔'],
            ['key' => 'sedang_dimasak', 'label' => 'Sedang Dimasak', 'icon' => '🍳'],
            ['key' => 'sedang_dikirim', 'label' => 'Sedang Dikirim', 'icon' => '🚚'],
            ['key' => 'selesai', 'label' => 'Pesanan Selesai', 'icon' => '✅'],
        ];
        
        $currentStatus = $this->status->value;
        $statusOrder = ['menunggu_pembayaran', 'menunggu_verifikasi', 'sedang_diproses', 'sedang_dimasak', 'sedang_dikirim', 'selesai'];
        $currentIdx = array_search($currentStatus, $statusOrder);
        
        foreach ($steps as &$step) {
            $idx = array_search($step['key'], $statusOrder);
            $step['done'] = $idx !== false && $currentIdx !== false && $idx <= $currentIdx;
            $step['active'] = $step['key'] === $currentStatus;
        }
        
        return $steps;
    }
}
