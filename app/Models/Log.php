<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'serial_no',
        'log_no',
        'tag_no',
        'species',
        'origin',
        'length',
        'girth_butt',
        'girth_top',
        'diameter',
        'l_ref',
        'd_ref',
        'calc_length',
        'vol_cbm',
        'remarks',
        'buyer_name',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }
}
