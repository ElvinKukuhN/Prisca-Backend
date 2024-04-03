<?php

namespace App\Models;

use App\Http\Traits\UuidTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_code',
        'divisi_code',
        'departemen_code',
        'address'
    ];

    use UuidTraits;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'code');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_code', 'code');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_code', 'code');
    }
}
