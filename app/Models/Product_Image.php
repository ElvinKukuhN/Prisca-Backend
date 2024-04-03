<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Image extends Model
{
    use HasFactory;
    // protected $table = 'product__images';
    protected $fillable = [
        'product_id',
        'image',
    ];

    use UuidTraits;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
