<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'serial_no',
        'mark',
        'tag_no',
        'log_no',
        'species',
        'origin',
        'length',
        'girth_butt',
        'girth_top',
        'diameter',
        'vol_cbm',
        'l_ref',
        'd_ref',
        'length_after_ref',
        'buyer_name',
        'remarks',
    ];

    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }

    public function inspection()
    {
        return $this->hasOne(Inspection::class);
    }
}
