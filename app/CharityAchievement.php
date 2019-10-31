<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharityAchievement extends Model
{
    protected $fillable = [
        'charity_id', 'title', 'description', 'photo', 'held_on'
    ];

    public function charity()
    {
        return $this->belongsTo('App\Charity');
    }
}
