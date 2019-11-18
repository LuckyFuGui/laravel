<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DailyCleaning extends Model
{
    // 表名
    protected $table  = 'daily_cleaning';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'hour', 'price'
    ];
}
