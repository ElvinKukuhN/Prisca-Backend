<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestForQoutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'code',
        'harga_ongkir',
        'company_address'
    ];

    use UuidTraits;


    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function negotiation () {
        return $this->hasMany(Negotiation::class);
    }
}
