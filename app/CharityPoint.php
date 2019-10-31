<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharityPoint extends Model
{
    protected $fillable = [
        'charity_id', 'points'
    ];

    public function charity()
    {
        return $this->belongsTo('App\Charity');
    }
}
