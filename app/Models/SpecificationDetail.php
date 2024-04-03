<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecificationDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'productSpecification',
        'technicalSpecification',
        'feature',
        'partNumber',
        'satuan',
        'video',
        'condition',
    ];

    use UuidTraits;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
