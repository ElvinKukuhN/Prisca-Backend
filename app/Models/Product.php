<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_id',
        'category_id',
        'user_id',
        'brand',
        'product_category_name',
        'status'
    ];

    use UuidTraits;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productImage()
    {
        return $this->hasMany(Product_Image::class);
    }

    public function specificationDetail()
    {
        return $this->hasOne(SpecificationDetail::class);
    }

    public function commercialInfo()
    {
        return $this->hasOne(CommercialInfo::class);
    }

    public function other()
    {
        return $this->hasOne(Other::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function lineItem()
    {
        return $this->hasMany(LineItem::class);
    }

    public function quotation()
    {
        return $this->hasMany(Quotation::class);
    }

    public function pengembalian() {
        return $this->hasMany(Pengembalian::class);
    }
}
