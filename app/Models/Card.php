<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'card';
    protected $fillable = ['code', 'mid', 'country_id', 'status', 'info', 'type', 'description', 'category','expired_at'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'mid');
    }
}
