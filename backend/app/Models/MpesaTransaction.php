<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'order_id', 'phone', 'amount',
        'merchant_request_id', 'checkout_request_id',
        'result_code', 'result_desc', 'mpesa_receipt',
        'transaction_date', 'status',
        'raw_response', 'raw_callback',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'raw_response' => 'array',
        'raw_callback' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
