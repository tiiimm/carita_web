<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WatchLog extends Model
{
    protected $fillable = [
        'philanthropist_id', 'charity_id'
    ];

    public function charity()
    {
        return $this->belongsTo('App\Charity');
    }

    public function philanthropist()
    {
        return $this->belongsTo('App\Philanthropist');
    }
}
