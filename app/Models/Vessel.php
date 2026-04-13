<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_code',
        'vessel_name',
        'arrival_date',
        'status',
        'surveyor_access',
        'buyer_access',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'surveyor_access' => 'boolean',
        'buyer_access' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
