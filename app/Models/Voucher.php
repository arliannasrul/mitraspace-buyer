<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spent',
        'max_discount',
        'active',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'float',
        'min_spent' => 'float',
        'max_discount' => 'float',
        'active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the voucher is valid for a given subtotal.
     */
    public function isValidFor(float $subtotal, ?string &$errorMsg = ''): bool
    {
        if (!$this->active) {
            $errorMsg = 'Voucher ini sudah tidak aktif.';
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            $errorMsg = 'Voucher ini sudah kadaluarsa.';
            return false;
        }

        if ($subtotal < $this->min_spent) {
            $errorMsg = 'Minimum pembelian untuk menggunakan voucher ini adalah Rp ' . number_format($this->min_spent, 0, ',', '.');
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'flat') {
            return min($this->value, $subtotal);
        }

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount) {
                return min($discount, $this->max_discount);
            }
            return $discount;
        }

        return 0;
    }
}
