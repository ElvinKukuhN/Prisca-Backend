<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_for_qoutations_id',
        'code',
        'description',
        'status'
    ];

    use UuidTraits;

    public function requestForQuotation()
    {
        return $this->hasMany(RequestForQoutation::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'doc_code', 'code');
    }
}
