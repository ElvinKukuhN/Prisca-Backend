<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alamat',
        'bidang_usaha',
        'tanggal_berdiri',
        'npwp',
        'siup',
        'website'
    ];

    use UuidTraits;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
