<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Wasteland extends Model
{
    // 表名
    protected $table  = 'wasteland';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'basics_price', 'increase_price'
    ];
}
