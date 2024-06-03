<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'no_invoice',
        'bukti',
        'status',
        'invoice_pdf'
    ];

    use UuidTraits;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


}
