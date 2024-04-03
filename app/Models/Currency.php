<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
    ];

    use UuidTraits;

    public function commercialInfo()
    {
        return $this->hasMany(CommercialInfo::class);
    }
}
