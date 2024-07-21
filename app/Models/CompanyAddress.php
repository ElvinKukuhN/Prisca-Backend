<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_companies_id',
        'address',
    ];

    use UuidTraits;

    public function userCompany() {
        return $this->belongsTo(UserCompany::class);
    }
}
