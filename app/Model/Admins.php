<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Admins extends Model
{
    // 表名
    protected $table  = 'admin';
    // 屏蔽字段
  	protected $guarded = [];
}
