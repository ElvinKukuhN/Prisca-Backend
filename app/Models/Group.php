<?php

namespace App\Models;

use App\Models\Product;
use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    use UuidTraits;

    public function product()
    {
        return $this->hasMany(Product::class);
    }
}
