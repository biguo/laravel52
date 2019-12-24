<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Country extends Model
{
    protected $table = 'country';

    public $timestamps = false;

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    public function usedBanner()
    {
        return $this->banners()->where('status', Status_Online)->select('id', 'title', DB::raw("concat('" . Upload_Domain . "', image) as image"), 'sort', 'content')
            ->orderBy('sort', 'asc')->get();
    }

}
