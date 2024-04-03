<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Divisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'company_code',
        'name'
    ];

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'divisi_code', 'code');
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'company_code', 'code');
    }

    public function departemen()
    {
        return $this->hasMany(Departemen::class, 'divisi_code', 'code');
    }
}
