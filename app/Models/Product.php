<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
    ];
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
