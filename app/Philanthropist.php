<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Philanthropist extends Model
{
    protected $fillable = [
        'user_id', 'contact_number', 'birthday', 'sex', 'charity_category_id'
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
        return $this->hasOne('App\PhilanthropistPoint');
    }
}
