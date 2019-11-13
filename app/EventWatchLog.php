<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventWatchLog extends Model
{
    protected $fillable = [
        'user_id', 'event_id'
    ];

    public function event()
    {
        return $this->belongsTo('App\Event');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
