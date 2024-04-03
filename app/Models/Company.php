<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    public function  userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'company_code', 'code');
    }

    public function divisis()
    {
        return $this->hasMany(Divisi::class, 'company_code', 'code');
    }
}
