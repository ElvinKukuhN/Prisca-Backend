<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_for_qoutation_id',
        'product_id',
        'name',
        'quantity',
        'price',
        'amount',
        'discount'
    ];

    use UuidTraits;

    public function requestForQuotation()
    {
        return $this->belongsTo(RequestForQoutation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
