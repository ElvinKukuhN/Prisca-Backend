<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doc_code',
        'sequence',
        'approval_status',
        'doc_type',
        'last_activity'
    ];

    use UuidTraits;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function purchaseRequest() {
        return $this->belongsTo(PurchaseRequest::class, 'doc_code', 'code');
    }

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'doc_code', 'code');
    }
}
