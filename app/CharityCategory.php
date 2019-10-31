<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharityCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    public function charities()
    {
        return $this->hasMany('App\Charity');
    }

    public function philanthropists()
    {
        return $this->hasMany('App\Philanthropist');
    }
}
