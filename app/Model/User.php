<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	// 表名
    protected $table  = 'user';
    // 屏蔽字段
  	protected $guarded=[];
}
