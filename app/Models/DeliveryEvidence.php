<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryEvidence extends Model
{
    protected $table = 'delivery_evidences';

    protected $fillable = [
        'invoice_number',
        'loaded_photo_path',
        'unloaded_photo_path',
    ];

    protected $appends = ['loaded_url', 'unloaded_url'];

    public function getLoadedUrlAttribute(): ?string
    {
        return $this->loaded_photo_path ? asset('storage/' . $this->loaded_photo_path) : null;
    }

    public function getUnloadedUrlAttribute(): ?string
    {
        return $this->unloaded_photo_path ? asset('storage/' . $this->unloaded_photo_path) : null;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'invoice_number', 'invoice_number');
    }
}