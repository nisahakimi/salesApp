<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'product_id',
        'user_id',
        'total_price',
        'quantity',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected static function booted()
    {

        static::creating(function ($model) {
            $model->created_by = Auth::user()->name ?? 'System';  // Pastikan user yang sedang login tersedia
            $model->updated_by = Auth::user()->name ?? 'System';
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->name ?? 'System';
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
