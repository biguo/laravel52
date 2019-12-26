<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRecord extends Model
{
    protected $table = 'account_record';
    protected $fillable = ['title', 'trade_no', 'type', 'country_id', 'mid', 'change','total_fee'];
}
