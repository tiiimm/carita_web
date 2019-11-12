<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Charity extends Model
{
    protected $fillable = [
        'user_id', 'organization', 'contact_number', 'account_name', 'account_number', 'address', 'charity_category_id', 'photo', 'bio', 'bio_path', 'bio_path_type'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function category()
    {
        return $this->belongsTo('App\CharityCategory');
    }
    
    public function point()
    {
        return $this->hasOne('App\CharityPoint');
    }
    
    public function watch_logs()
    {
        return $this->hasMany('App\WatchLog');
    }
    
    public function achievements()
    {
        return $this->hasMany('App\CharityAchievement');
    }
}
