<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grosir extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercial_info_id',
        'qty',
        'price',
    ];

    use UuidTraits;

    public function commercialInfo()
    {
        return $this->belongsTo(Etalase::class);
    }
}
