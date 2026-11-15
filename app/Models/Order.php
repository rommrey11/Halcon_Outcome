<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_number',
        'customer_number',
        'order_date',
        'delivery_address',
        'status',
        'notes',
        'is_deleted',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_number', 'customer_number');
    }

    public function deliveryEvidence()
    {
        return $this->hasOne(DeliveryEvidence::class, 'invoice_number', 'invoice_number');
    }
}