<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'card';
    protected $fillable = ['title', 'code', 'mid', 'country_id', 'status'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
