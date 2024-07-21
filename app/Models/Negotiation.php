<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_for_qoutation_id',
        'description',
    ];

    use UuidTraits;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function request_for_qoutation(){
        return $this->belongsTo(RequestForQoutation::class);
    }
}
