<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'code',
        'description',
        'status'
    ];

    use UuidTraits;

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'doc_code', 'code');
    }

    public function requestForQoutation()
    {
        return $this->hasMany(RequestForQoutation::class);
    }
}
