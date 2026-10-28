<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_number'; // Define la clave primaria real
    public $incrementing = false;              // No es un ID autoincrementable
    protected $keyType = 'string';             // Es tipo texto

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
}