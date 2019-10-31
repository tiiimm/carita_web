<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhilanthropistPoint extends Model
{
    protected $fillable = [
        'philanthropist_id', 'points'
    ];

    public function philanthropist()
    {
        return $this->belongsTo('App\Philanthropist');
    }
}
