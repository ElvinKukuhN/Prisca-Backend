<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'divisi_code',
        'name'
    ];

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'departemen_code', 'code');
    }


    public function divisi()
    {
        return $this->hasOne(Divisi::class, 'divisi_code', 'code');
    }
}
