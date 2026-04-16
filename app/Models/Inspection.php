<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_id',
        'surveyor_id',
        'shift',
        'is_match',
        'actual_length',
        'actual_length_ft',
        'actual_length_in',
        'actual_mid_girth',
        'actual_gb',
        'actual_pb',
        'actual_diameter',
        'actual_vol_cbm',
        'actual_vol_cft',
        'actual_l_ref',
        'actual_d_ref',
        'buyer_name',
        'surveyor_remarks',
        'images',
        'verified_at',
    ];

    protected $casts = [
        'is_match' => 'boolean',
        'verified_at' => 'datetime',
        'images' => 'array',
    ];

    public function log()
    {
        return $this->belongsTo(Log::class);
    }

    public function surveyor()
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }
}
