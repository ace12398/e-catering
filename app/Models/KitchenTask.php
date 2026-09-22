<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'assigned_chef_id',
        'station',
        'priority',
        'status',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_chef_id');
    }
}
