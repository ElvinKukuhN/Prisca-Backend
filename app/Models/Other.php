<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Other extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'incomterm',
        'warranty',
        'maintenance',
        'sku',
        'tags'
    ];

    use UuidTraits;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
