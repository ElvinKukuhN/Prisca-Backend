<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommercialInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'etalase_id',
        'product_id',
        'price',
        'payment_terms',
        'discount',
        'price_exp',
        'stock',
        'pre_order',
        'contract',
    ];

    use UuidTraits;

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function etalase()
    {
        return $this->belongsTo(Etalase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseQTY()
    {
        return $this->hasOne(PurchaseQTY::class);
    }

    public function grosir()
    {
        return $this->hasOne(Grosir::class);
    }
}
