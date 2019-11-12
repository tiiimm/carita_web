<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharityEvent extends Model
{
    protected $fillable = [
        'charity_id', 'title', 'description', 'photo', 'venue', 'event_date', 'event_from', 'event_to'
    ];

    public function charity()
    {
        return $this->belongsTo('App\Charity');
    }
}
